<?php
require_once('DB.php');
require_once('SavingsG1.php');

/**
 * Upload of new members by the Treasurer (Treasurer/upload.php -> Treasurer/uploadMembersExcel.php).
 *
 * Template (Members_uploads.csv), one row per staff member:
 *   Staff Number | Name (optional, checked against the staff record) | Monthly Savings |
 *   Opening Savings Balance (optional) | Balance As At (optional, MM/YYYY; default last month)
 * The old three-column file (Staff Number, Monthly Savings, Consolidated Savings) still works.
 *
 * Each row is checked first (plan) and nothing is saved until the Treasurer imports the checked file:
 *   - new staff (no member record): becomes a full member with the monthly savings;
 *   - an applicant not yet fully approved: approved with the monthly savings;
 *   - an existing member: nothing is changed (monthly savings change through Savings Update);
 *   - the opening balance (savings before the portal) is recorded once, as fudscoops_savings entry_type 'opening',
 *     so it never takes the place of a month's deduction.
 * The staff member's login (user.username = staff number) is raised from applicant (0) to member (1); officers'
 * access levels are never lowered.
 */
class MemberUpload
{
    const MAX_ROWS = 2000;

    private static function con()
    {
        return (new DB())->getConnection();
    }

    private static function normaliseStaffNo($s)
    {
        return strtoupper(preg_replace('/\s+/', '', trim((string) $s)));
    }

    private static function amount($s)
    {
        $s = preg_replace('/[^0-9.\-]/', '', str_replace(',', '', (string) $s));
        return $s === '' || !is_numeric($s) ? null : round((float) $s, 2);
    }

    // "09/2026", "9/2026", "2026-09", "Sep 2026", "September 2026", "30/09/2026" -> "2026-09"; '' -> default; false if unreadable
    public static function parseMonth($s)
    {
        $s = trim((string) $s);
        if ($s === '') {
            return date('Y-m', strtotime('first day of last month'));
        }
        if (preg_match('/^(\d{1,2})[\/\-.](\d{4})$/', $s, $m) && $m[1] >= 1 && $m[1] <= 12) {
            return sprintf('%04d-%02d', $m[2], $m[1]);
        }
        if (preg_match('/^(\d{4})[\/\-.](\d{1,2})$/', $s, $m) && $m[2] >= 1 && $m[2] <= 12) {
            return sprintf('%04d-%02d', $m[1], $m[2]);
        }
        if (preg_match('/^\d{1,2}[\/\-.](\d{1,2})[\/\-.](\d{4})$/', $s, $m) && $m[1] >= 1 && $m[1] <= 12) {
            return sprintf('%04d-%02d', $m[2], $m[1]);
        }
        $t = strtotime('1 ' . $s);
        return $t ? date('Y-m', $t) : false;
    }

    // Reads the CSV into rows keyed by field. Returns ['rows' => [...], 'error' => null|message, 'columns' => [...]]
    public static function readCsv($path)
    {
        $handle = @fopen($path, 'r');
        if (!$handle) {
            return ['rows' => [], 'error' => 'The file could not be read.', 'columns' => []];
        }
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return ['rows' => [], 'error' => 'The file is empty.', 'columns' => []];
        }
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header[0]);   // Excel's byte-order mark
        $map = [];
        foreach ($header as $i => $title) {
            $t = strtolower(trim((string) $title));
            if (strpos($t, 'staff') !== false || $t === 'sp no' || $t === 'sp_no') {
                $map['staff'] = $i;
            } elseif (strpos($t, 'opening') !== false || strpos($t, 'consolidated') !== false) {
                $map['opening'] = $i;
            } elseif (strpos($t, 'as at') !== false || strpos($t, 'as_at') !== false) {
                $map['as_at'] = $i;
            } elseif (strpos($t, 'monthly') !== false) {
                $map['monthly'] = $i;
            } elseif (strpos($t, 'name') !== false) {
                $map['name'] = $i;
            }
        }
        if (!isset($map['staff'], $map['monthly'])) {
            fclose($handle);
            return ['rows' => [], 'error' => 'The first row must be the template headings: Staff Number and Monthly Savings are required. Download the template and copy your data into it.', 'columns' => []];
        }
        $rows = [];
        $line = 1;
        while (($data = fgetcsv($handle)) !== false) {
            $line++;
            if (!array_filter($data, function ($v) { return trim((string) $v) !== ''; })) {
                continue;   // blank line
            }
            $get = function ($k) use ($map, $data) { return isset($map[$k], $data[$map[$k]]) ? trim((string) $data[$map[$k]]) : ''; };
            $rows[] = ['line' => $line, 'staff' => $get('staff'), 'name' => $get('name'), 'monthly' => $get('monthly'),
                       'opening' => $get('opening'), 'as_at' => $get('as_at')];
            if (count($rows) > self::MAX_ROWS) {
                fclose($handle);
                return ['rows' => [], 'error' => 'The file has more than ' . self::MAX_ROWS . ' rows. Split it into smaller files.', 'columns' => array_keys($map)];
            }
        }
        fclose($handle);
        if (!$rows) {
            return ['rows' => [], 'error' => 'The file has headings but no staff rows.', 'columns' => array_keys($map)];
        }
        return ['rows' => $rows, 'error' => null, 'columns' => array_keys($map)];
    }

    // Checks every row without saving anything. Each result: line, input, employee, action, errors[], warnings[].
    public static function plan($rows)
    {
        $con = self::con();
        $minimum = (float) SavingsG1::getMinMonthlySavings();
        $thisMonth = date('Y-m');

        // Staff, their member records, logins and existing opening balances, loaded once
        $employees = [];
        foreach ($con->query("SELECT employee_id, sp_no, title, fname, lname, oname, dept FROM employee") as $e) {
            $employees[self::normaliseStaffNo($e['sp_no'])] = $e;
        }
        $members = [];
        foreach ($con->query("SELECT member_id, employee_id, treasurer_approval, chairman_approval, is_deleted, proposed_monthly_savings FROM fudscoops_member") as $m) {
            $members[(int) $m['employee_id']] = $m;
        }
        $users = [];
        foreach ($con->query("SELECT username, access_level FROM user") as $u) {
            $users[self::normaliseStaffNo($u['username'])] = (int) $u['access_level'];
        }
        $openings = $con->query("SELECT member_idmember, SUM(savings_amount) FROM fudscoops_savings WHERE entry_type = 'opening' GROUP BY member_idmember")->fetchAll(PDO::FETCH_KEY_PAIR);

        $seen = [];
        $out = [];
        foreach ($rows as $r) {
            $res = ['line' => $r['line'], 'staff' => $r['staff'], 'file_name' => $r['name'], 'errors' => [], 'warnings' => [],
                    'employee' => null, 'name' => '', 'dept' => '', 'member_id' => null, 'action' => 'skip',
                    'monthly' => null, 'opening' => null, 'as_at' => null, 'add_opening' => false, 'login' => null];
            $key = self::normaliseStaffNo($r['staff']);
            if ($key === '') {
                $res['errors'][] = 'Staff number is missing.';
            } elseif (isset($seen[$key])) {
                $res['errors'][] = 'This staff number is already on line ' . $seen[$key] . ' of the file.';
            } elseif (!isset($employees[$key])) {
                $res['errors'][] = 'No staff record has this staff number. Check it against the staff list (e.g. SP/RA/1234).';
            }
            if ($key !== '' && !isset($seen[$key])) {
                $seen[$key] = $r['line'];
            }

            $monthly = self::amount($r['monthly']);
            if ($monthly === null) {
                $res['errors'][] = 'Monthly savings must be an amount.';
            } elseif ($monthly < $minimum) {
                $res['errors'][] = 'Monthly savings must be at least ₦' . number_format($minimum, 2) . '.';
            }
            $res['monthly'] = $monthly;

            $res['opening_in'] = $r['opening'];
            $opening = $r['opening'] === '' ? 0.0 : self::amount($r['opening']);
            if ($opening === null || $opening < 0) {
                $res['errors'][] = 'Opening savings balance must be an amount (or left blank).';
                $opening = 0.0;
            }
            $res['opening'] = $opening;
            $asAt = self::parseMonth($r['as_at']);
            if ($asAt === false) {
                $res['errors'][] = 'Balance as at must be a month, e.g. 09/2026.';
            } elseif ($asAt > $thisMonth) {
                $res['errors'][] = 'Balance as at cannot be a future month.';
            } else {
                $res['as_at'] = $asAt;
            }

            if (isset($employees[$key])) {
                $e = $employees[$key];
                $res['employee'] = (int) $e['employee_id'];
                $res['staff'] = $e['sp_no'];
                $res['name'] = preg_replace('/\s+/', ' ', trim($e['title'] . ' ' . $e['fname'] . ' ' . $e['lname'] . ' ' . $e['oname']));
                $res['dept'] = $e['dept'];
                // The name in the file (if given) must share a word with the staff record: catches a wrong staff number
                if ($r['name'] !== '') {
                    $words = function ($s) { return array_filter(preg_split('/[^a-z]+/', strtolower($s)), function ($w) { return strlen($w) > 1; }); };
                    if (!array_intersect($words($r['name']), $words($e['fname'] . ' ' . $e['lname'] . ' ' . $e['oname']))) {
                        $res['errors'][] = 'The name in the file (' . $r['name'] . ') does not match the staff record (' . $res['name'] . '). Check the staff number.';
                    }
                }
                $m = isset($members[(int) $e['employee_id']]) ? $members[(int) $e['employee_id']] : null;
                if ($m && (int) $m['is_deleted'] === 1) {
                    $res['errors'][] = 'This staff member\'s membership record was deleted. Ask the system admin to restore it.';
                } elseif ($m && $m['treasurer_approval'] == 1 && $m['chairman_approval'] == 1) {
                    $res['member_id'] = (int) $m['member_id'];
                    $res['action'] = 'existing';
                    $res['warnings'][] = 'Already a member (monthly savings ₦' . number_format((float) $m['proposed_monthly_savings'], 2) . '): nothing is changed. Use Savings Update to change the monthly savings.';
                } elseif ($m) {
                    $res['member_id'] = (int) $m['member_id'];
                    $res['action'] = 'approve';
                } else {
                    $res['action'] = 'create';
                }
                if ($opening > 0 && $res['action'] !== 'existing') {
                    if ($res['member_id'] && isset($openings[$res['member_id']])) {
                        $res['warnings'][] = 'An opening balance of ₦' . number_format((float) $openings[$res['member_id']], 2) . ' is already recorded; it is not added again.';
                    } else {
                        $res['add_opening'] = true;
                    }
                }
                $level = isset($users[$key]) ? $users[$key] : null;
                $res['login'] = $level;
                if ($level === null && $res['action'] !== 'existing') {
                    $res['warnings'][] = 'No portal login exists for this staff number yet; the member is recorded but cannot sign in until an account is created.';
                } elseif ($level !== null && $level >= 2 && $res['action'] !== 'existing') {
                    $res['warnings'][] = 'This staff number signs in as an officer; the officer login is not changed.';
                }
            }
            if ($res['errors']) {
                $res['action'] = 'error';
                $res['add_opening'] = false;
            }
            $out[] = $res;
        }
        return $out;
    }

    // Counts for a plan
    public static function summary($plan)
    {
        $s = ['rows' => count($plan), 'create' => 0, 'approve' => 0, 'existing' => 0, 'error' => 0, 'openings' => 0, 'opening_total' => 0.0, 'monthly_total' => 0.0];
        foreach ($plan as $p) {
            $s[$p['action'] === 'skip' ? 'error' : $p['action']]++;
            if ($p['add_opening']) {
                $s['openings']++;
                $s['opening_total'] += $p['opening'];
            }
            if (in_array($p['action'], ['create', 'approve'], true)) {
                $s['monthly_total'] += $p['monthly'];
            }
        }
        return $s;
    }

    // Saves the rows that passed the check. Each row is saved on its own (one bad row does not stop the rest).
    // Returns the plan with 'saved' => true|false and 'result' message per row.
    public static function import($plan, $by)
    {
        $con = self::con();
        foreach ($plan as &$p) {
            $p['saved'] = false;
            if (!in_array($p['action'], ['create', 'approve'], true)) {
                $p['result'] = $p['action'] === 'existing' ? 'Not changed (already a member)' : 'Not saved: ' . implode(' ', $p['errors']);
                continue;
            }
            try {
                $con->beginTransaction();
                if ($p['action'] === 'create') {
                    $stmt = $con->prepare("INSERT INTO fudscoops_member (employee_id, proposed_monthly_savings, savings_amount_update, reg_date, is_active,
                                                                         chairman_approval, treasurer_approval, is_deleted, updated_at)
                                           VALUES (:e, :s, CURDATE(), NOW(), 1, 1, 1, 0, CURDATE())");
                    $stmt->execute([':e' => $p['employee'], ':s' => $p['monthly']]);
                    $p['member_id'] = (int) $con->lastInsertId();
                } else {
                    $con->prepare("UPDATE fudscoops_member SET proposed_monthly_savings = :s, savings_amount_update = CURDATE(), is_active = 1,
                                          chairman_approval = 1, treasurer_approval = 1, updated_at = CURDATE()
                                   WHERE member_id = :m AND NOT (chairman_approval = 1 AND treasurer_approval = 1)")
                        ->execute([':s' => $p['monthly'], ':m' => $p['member_id']]);
                }
                if ($p['add_opening']) {
                    list($y, $mo) = explode('-', $p['as_at']);
                    $con->prepare("INSERT INTO fudscoops_savings (savings_amount, entry_type, month, year, savings_is_deleted, member_idmember)
                                   VALUES (:a, 'opening', :mo, :y, 0, :m)")
                        ->execute([':a' => $p['opening'], ':mo' => (int) $mo, ':y' => $y, ':m' => $p['member_id']]);
                }
                // Applicant login becomes a member login; officers (2 and above) keep their access
                $con->prepare("UPDATE user SET access_level = 1 WHERE username = :u AND access_level < 1")->execute([':u' => $p['staff']]);
                $con->commit();
                $p['saved'] = true;
                $p['result'] = ($p['action'] === 'create' ? 'Added as a member' : 'Applicant approved as a member')
                    . ($p['add_opening'] ? '; opening balance ₦' . number_format($p['opening'], 2) . ' recorded' : '');
            } catch (Exception $e) {
                if ($con->inTransaction()) {
                    $con->rollBack();
                }
                error_log('MemberUpload line ' . $p['line'] . ': ' . $e->getMessage());
                $p['result'] = 'Not saved: database error. Try this row again.';
            }
        }
        unset($p);
        return $plan;
    }
}
