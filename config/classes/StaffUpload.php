<?php
require_once('DB.php');

/**
 * Upload of staff into the staff list (employee table) by the Treasurer (Treasurer/upload_staff.php ->
 * Treasurer/uploadStaff.php). Members can only be uploaded (Treasurer/upload.php) for staff already in this list.
 *
 * File: the HR list as exported, e.g. "S/N, STAFF ID, GIFMIS CODE, FULL NAME, date-apptm" (template Staff_uploads.csv).
 * Headings are matched by name; optional extra columns: Title, Gender, Cadre, Department, Phone, Email.
 *
 * Per row (checked first; nothing is saved until the Treasurer imports the checked file):
 *   - Staff ID in any of SPRN3237, SP/RN/3237, sp/rn 3237 -> SP/RN/3237 (JPR3322 -> JP/R/3322);
 *   - a staff ID already in the list is not changed; if the name there is different the row is a problem (the ID
 *     belongs to someone else), and a missing GIFMIS code is added to the existing record;
 *   - a GIFMIS code already recorded for another staff member is a problem (likely the same person under another
 *     staff ID), so nobody is added twice;
 *   - new staff get an employee record (name split into title / first / last / other names), their GIFMIS code
 *     (ippis_static_record) and a login (username = staff ID, first password = staff ID, applicant level 0) so
 *     they can apply for membership.
 */
class StaffUpload
{
    const MAX_ROWS = 3000;
    private static $titles = ['DR', 'PROF', 'MR', 'MRS', 'MISS', 'MS', 'ENGR', 'MALAM', 'MAL', 'ALHAJI', 'HAJIYA', 'ARC', 'BARR', 'PHARM'];

    private static function con()
    {
        return (new DB())->getConnection();
    }

    private static function key($s)
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $s));
    }

    // SPRN3237 / SP/RN/3237 / sp-rn 3237 -> SP/RN/3237; JPR3322 -> JP/R/3322; false if not a staff ID
    public static function staffNo($s)
    {
        $k = self::key($s);
        if (preg_match('/^(SP|JP)(R[A-Z]?)(\d{1,6})$/', $k, $m)) {
            return $m[1] . '/' . $m[2] . '/' . $m[3];
        }
        return false;
    }

    // Splits "DR. HAFIZU MUSA USMAN" into title, first, last and other names (the employee table's columns)
    public static function splitName($full)
    {
        $full = trim(preg_replace('/\s+/', ' ', str_replace('.', '. ', (string) $full)));
        $words = $full === '' ? [] : explode(' ', $full);
        $title = '';
        while ($words && in_array(strtoupper(rtrim($words[0], '.')), self::$titles, true)) {
            $title = trim($title . ' ' . strtoupper(rtrim(array_shift($words), '.')));
        }
        $words = array_values(array_filter($words, function ($w) { return trim($w, '.') !== ''; }));
        return ['title' => $title, 'fname' => isset($words[0]) ? $words[0] : '', 'lname' => isset($words[1]) ? $words[1] : '',
                'oname' => count($words) > 2 ? implode(' ', array_slice($words, 2)) : '',
                'full' => trim($title . ' ' . implode(' ', $words))];
    }

    // "01-Dec-21", "1-Nov-23", "11/19/24" (m/d/y), "19/11/2024" (d/m/y), "2026-02-01" -> "dd/mm/yyyy"; '' -> ''; false if unreadable
    public static function appointmentDate($s)
    {
        $s = trim((string) $s);
        if ($s === '') {
            return '';
        }
        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $s, $m) && checkdate($m[2], $m[3], $m[1])) {
            return sprintf('%02d/%02d/%04d', $m[3], $m[2], $m[1]);
        }
        if (preg_match('/^(\d{1,2})[\/.](\d{1,2})[\/.](\d{2}|\d{4})$/', $s, $m)) {
            $y = strlen($m[3]) === 2 ? 2000 + (int) $m[3] : (int) $m[3];
            // Day first unless that is impossible (Excel often writes US m/d/y, e.g. 11/19/24)
            if (checkdate($m[2], $m[1], $y)) {
                return sprintf('%02d/%02d/%04d', $m[1], $m[2], $y);
            }
            if (checkdate($m[1], $m[2], $y)) {
                return sprintf('%02d/%02d/%04d', $m[2], $m[1], $y);
            }
            return false;
        }
        if (preg_match('/^(\d{1,2})[\s\-]([A-Za-z]{3,9})[\s\-,]*(\d{2}|\d{4})$/', $s, $m)) {
            $y = strlen($m[3]) === 2 ? 2000 + (int) $m[3] : (int) $m[3];
            $t = strtotime($m[1] . ' ' . $m[2] . ' ' . $y);
            return $t ? date('d/m/Y', $t) : false;
        }
        return false;
    }

    // Reads the CSV. Returns ['rows' => [...], 'error' => null|message, 'columns' => [...]]
    public static function readCsv($path)
    {
        $raw = @file_get_contents($path);
        if ($raw === false) {
            return ['rows' => [], 'error' => 'The file could not be read.', 'columns' => []];
        }
        $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);
        if (!preg_match('//u', $raw)) {
            $raw = mb_convert_encoding($raw, 'UTF-8', 'Windows-1252');   // Excel CSV (e.g. ABDUL’AZIZ)
        }
        $raw = str_replace(["\xE2\x80\x98", "\xE2\x80\x99"], "'", $raw);
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $raw);
        rewind($handle);
        $header = fgetcsv($handle);
        $map = [];
        foreach ((array) $header as $i => $title) {
            $t = strtolower(trim((string) $title));
            if ($t === '' || $t === 's/n' || $t === 'sn') {
                continue;
            }
            if (strpos($t, 'staff') !== false || strpos($t, 'sp no') !== false || $t === 'sp_no') {
                $map['staff'] = $i;
            } elseif (strpos($t, 'gifmis') !== false || strpos($t, 'ippis') !== false) {
                $map['code'] = $i;
            } elseif (strpos($t, 'date') !== false || strpos($t, 'appt') !== false || strpos($t, 'appointment') !== false) {
                $map['doa'] = $i;
            } elseif (strpos($t, 'dep') !== false || strpos($t, 'unit') !== false) {
                $map['dept'] = $i;
            } elseif (strpos($t, 'cadre') !== false) {
                $map['cadre'] = $i;
            } elseif (strpos($t, 'gender') !== false || $t === 'sex') {
                $map['gender'] = $i;
            } elseif (strpos($t, 'phone') !== false || strpos($t, 'gsm') !== false) {
                $map['phone'] = $i;
            } elseif (strpos($t, 'mail') !== false) {
                $map['email'] = $i;
            } elseif (strpos($t, 'title') !== false) {
                $map['title'] = $i;
            } elseif (strpos($t, 'name') !== false) {
                $map['name'] = $i;
            }
        }
        if (!isset($map['staff'], $map['name'])) {
            fclose($handle);
            return ['rows' => [], 'error' => 'The first row must be the headings, with at least STAFF ID and FULL NAME. Download the template to see the layout.', 'columns' => array_keys($map)];
        }
        $rows = [];
        $line = 1;
        while (($data = fgetcsv($handle)) !== false) {
            $line++;
            if (!array_filter($data, function ($v) { return trim((string) $v) !== ''; })) {
                continue;
            }
            $row = ['line' => $line];
            foreach (['staff', 'code', 'name', 'doa', 'dept', 'cadre', 'gender', 'phone', 'email', 'title'] as $k) {
                $row[$k] = isset($map[$k], $data[$map[$k]]) ? trim(preg_replace('/\s+/', ' ', (string) $data[$map[$k]])) : '';
            }
            $rows[] = $row;
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

    private static function words($s)
    {
        return array_values(array_filter(preg_split('/[^a-z]+/', strtolower((string) $s)), function ($w) { return strlen($w) > 2; }));
    }

    // Checks every row without saving. action: create | existing | error. Also 'add_code' for existing staff.
    public static function plan($rows)
    {
        $con = self::con();
        $staff = [];
        foreach ($con->query("SELECT employee_id, sp_no, title, fname, lname, oname FROM employee") as $e) {
            $staff[self::key($e['sp_no'])][] = $e;   // a few staff IDs appear more than once in the list
        }
        $codes = [];
        $hasCode = [];
        foreach ($con->query("SELECT i.ippis_no, i.employee_id, e.sp_no, i.fullname FROM ippis_static_record i LEFT JOIN employee e ON e.employee_id = i.employee_id") as $i) {
            if (trim((string) $i['ippis_no']) !== '') {
                $codes[self::key($i['ippis_no'])] = $i;
            }
            $hasCode[(int) $i['employee_id']] = true;
        }
        $logins = [];
        foreach ($con->query("SELECT username FROM user") as $u) {
            $logins[self::key($u['username'])] = true;
        }

        $seenStaff = [];
        $seenCode = [];
        $out = [];
        foreach ($rows as $r) {
            $p = ['line' => $r['line'], 'input' => $r, 'staff' => '', 'name' => self::splitName($r['name']), 'code' => strtoupper(str_replace(' ', '', $r['code'])),
                  'doa' => '', 'action' => 'create', 'add_code' => false, 'login' => false, 'employee_id' => null, 'existing_name' => '', 'errors' => [], 'warnings' => []];
            $sp = self::staffNo($r['staff']);
            if ($r['staff'] === '') {
                $p['errors'][] = 'Staff ID is missing.';
            } elseif ($sp === false) {
                $p['errors'][] = 'Staff ID "' . $r['staff'] . '" is not in the usual form (e.g. SPRN3237 or SP/RN/3237).';
            } else {
                $p['staff'] = $sp;
                $k = self::key($sp);
                if (isset($seenStaff[$k])) {
                    $p['errors'][] = 'This staff ID is already on line ' . $seenStaff[$k] . ' of the file.';
                }
                $seenStaff[$k] = $r['line'];
            }
            if ($p['name']['fname'] === '') {
                $p['errors'][] = 'Full name is missing.';
            }
            $doa = self::appointmentDate($r['doa']);
            if ($doa === false) {
                $p['errors'][] = 'Date of appointment "' . $r['doa'] . '" is not a date (e.g. 01-Nov-23 or 01/11/2023).';
            } else {
                $p['doa'] = $doa;
                if ($doa === '') {
                    $p['warnings'][] = 'No date of appointment.';
                }
            }
            if ($r['email'] !== '' && !filter_var($r['email'], FILTER_VALIDATE_EMAIL)) {
                $p['errors'][] = 'Email "' . $r['email'] . '" is not an email address.';
            }
            $codeKey = self::key($p['code']);
            if ($codeKey !== '' && isset($seenCode[$codeKey])) {
                $p['errors'][] = 'This GIFMIS code is already on line ' . $seenCode[$codeKey] . ' of the file.';
            }
            if ($codeKey !== '') {
                $seenCode[$codeKey] = $r['line'];
            }

            if ($p['staff'] !== '' && isset($staff[self::key($p['staff'])])) {
                // Already in the staff list: nothing changes unless the name shows the ID belongs to someone else.
                // Where the ID is listed more than once, use the record whose name matches.
                $candidates = $staff[self::key($p['staff'])];
                $e = $candidates[0];
                foreach ($candidates as $c) {
                    if (array_intersect(self::words($p['name']['full']), self::words($c['fname'] . ' ' . $c['lname'] . ' ' . $c['oname']))) {
                        $e = $c;
                        break;
                    }
                }
                $p['action'] = 'existing';
                $p['employee_id'] = (int) $e['employee_id'];
                $p['existing_name'] = preg_replace('/\s+/', ' ', trim($e['title'] . ' ' . $e['fname'] . ' ' . $e['lname'] . ' ' . $e['oname']));
                if ($p['name']['fname'] !== '' && !array_intersect(self::words($p['name']['full']), self::words($p['existing_name']))) {
                    $p['errors'][] = 'Staff ID ' . $p['staff'] . ' is already in the staff list for ' . $p['existing_name'] . '. Check the staff ID.';
                } elseif ($codeKey !== '' && isset($codes[$codeKey]) && (int) $codes[$codeKey]['employee_id'] !== $p['employee_id']) {
                    $p['errors'][] = 'GIFMIS code ' . $p['code'] . ' is recorded for ' . ($codes[$codeKey]['sp_no'] ?: 'another staff member') . ' (' . $codes[$codeKey]['fullname'] . ').';
                } elseif ($codeKey !== '' && !isset($hasCode[$p['employee_id']])) {
                    $p['add_code'] = true;
                }
            } elseif ($codeKey !== '' && isset($codes[$codeKey])) {
                $c = $codes[$codeKey];
                $p['errors'][] = 'GIFMIS code ' . $p['code'] . ' is already recorded for ' . ($c['sp_no'] ?: 'another staff member') . ' (' . $c['fullname'] . '): '
                    . 'probably the same person under another staff ID. Use that staff ID, or correct the record first.';
            }
            if ($p['action'] === 'create' && $p['staff'] !== '') {
                $p['login'] = !isset($logins[self::key($p['staff'])]);
                if (!$p['login']) {
                    $p['warnings'][] = 'A login for this staff ID already exists; it is kept.';
                }
            }
            if ($p['errors']) {
                $p['action'] = 'error';
                $p['add_code'] = false;
                $p['login'] = false;
            }
            $out[] = $p;
        }
        return $out;
    }

    public static function summary($plan)
    {
        $s = ['rows' => count($plan), 'create' => 0, 'existing' => 0, 'error' => 0, 'codes_added' => 0, 'logins' => 0];
        foreach ($plan as $p) {
            $s[$p['action']]++;
            $s['codes_added'] += $p['add_code'] ? 1 : 0;
            $s['logins'] += $p['login'] ? 1 : 0;
        }
        return $s;
    }

    // Saves the rows that passed the check, each on its own. Returns the plan with 'saved' and 'result'.
    public static function import($plan)
    {
        $con = self::con();
        foreach ($plan as &$p) {
            $p['saved'] = false;
            if ($p['action'] === 'error') {
                $p['result'] = 'Not saved: ' . implode(' ', $p['errors']);
                continue;
            }
            if ($p['action'] === 'existing' && !$p['add_code']) {
                $p['result'] = 'Already in the staff list';
                continue;
            }
            $in = $p['input'];
            try {
                $con->beginTransaction();
                if ($p['action'] === 'create') {
                    // Inserted again only if nobody added this staff ID since the check
                    $dup = $con->prepare("SELECT employee_id FROM employee WHERE REPLACE(REPLACE(UPPER(sp_no), '/', ''), ' ', '') = :k");
                    $dup->execute([':k' => self::key($p['staff'])]);
                    if ($dup->fetchColumn()) {
                        $con->rollBack();
                        $p['result'] = 'Not saved: this staff ID was added in the meantime. Check the file again.';
                        continue;
                    }
                    $n = $p['name'];
                    $title = $in['title'] !== '' ? strtoupper(rtrim($in['title'], '.')) : $n['title'];
                    $cadre = $in['cadre'] !== '' ? strtoupper($in['cadre']) : (strpos($p['staff'], 'SP/RA/') === 0 ? 'ACADEMIC' : '');
                    $gender = strtoupper(substr($in['gender'], 0, 1));
                    $stmt = $con->prepare("INSERT INTO employee (sp_no, title, fname, lname, oname, gender, cadre, nature_of_appo, do_first_appo, do_present_appo,
                                                                 `rank`, salary_structure, qualification, dept, email, phone_no, permanent_address, lga_id,
                                                                 corporate_mail, email_status, office_no, specialization, publication, type, status)
                                           VALUES (:sp, :title, :f, :l, :o, :g, :cadre, '', :doa, :doa2, '', '', '', :dept, :email, :phone, '', 0, '', '', '', '', '', 0, 0)");
                    $stmt->execute([':sp' => $p['staff'], ':title' => $title, ':f' => $n['fname'], ':l' => $n['lname'], ':o' => $n['oname'],
                                    ':g' => in_array($gender, ['M', 'F'], true) ? $gender : null, ':cadre' => $cadre, ':doa' => $p['doa'] ?: null, ':doa2' => $p['doa'],
                                    ':dept' => strtoupper($in['dept']), ':email' => $in['email'] ?: null, ':phone' => $in['phone'] ?: null]);
                    $p['employee_id'] = (int) $con->lastInsertId();
                    if ($p['login']) {
                        $con->prepare("INSERT INTO user (username, password, access_level) VALUES (:u, :p, 0)")
                            ->execute([':u' => $p['staff'], ':p' => md5($p['staff'])]);
                    }
                }
                if ($p['code'] !== '' && ($p['action'] === 'create' || $p['add_code'])) {
                    $con->prepare("INSERT INTO ippis_static_record (legacy, ippis_no, fullname, doa, dob_static, employee_id)
                                   VALUES (NULL, :code, :name, :doa, '', :e)")
                        ->execute([':code' => $p['code'], ':name' => strtoupper($p['name']['full']), ':doa' => $p['doa'], ':e' => $p['employee_id']]);
                }
                $con->commit();
                $p['saved'] = true;
                $p['result'] = $p['action'] === 'create'
                    ? 'Added to the staff list' . ($p['login'] ? '; login created' : '')
                    : 'GIFMIS code added to the existing record';
            } catch (Exception $e) {
                if ($con->inTransaction()) {
                    $con->rollBack();
                }
                error_log('StaffUpload line ' . $p['line'] . ': ' . $e->getMessage());
                $p['result'] = 'Not saved: database error. Try this row again.';
            }
        }
        unset($p);
        return $plan;
    }
}
