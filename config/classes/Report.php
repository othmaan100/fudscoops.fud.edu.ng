<?php
require_once('DB.php');

// Cooperative reports for Generate Report (config/reports/reports_page.php).
// build() returns a report as data, rendered on screen, as CSV or as PDF:
//   [
//     'title'    => 'Savings Deposits',
//     'subtitle' => 'Deduction months August 2026 to October 2026',
//     'summary'  => [['Label', value, 'money'|'int'|'text'], ...],
//     'tables'   => [[
//         'heading' => 'By month',
//         'columns' => [['Month', 'text'], ['Total', 'money'], ...],
//         'rows'    => [[...], ...],
//         'totals'  => [...] or null,   // same length as columns
//         'note'    => optional text
//     ], ...],
//     'notes'    => [optional notes about how figures are calculated]
//   ]
//
// Figures follow the system's own rules:
// - Savings balance = savings deposited - savings withdrawals approved by the Chairman
//   (MemberG1::getCurrentSavingsAmount); complete withdrawals are shown separately.
// - Savings are reported by deduction month (fudscoops_savings.month / year).
// - Loan repayments are deductions uploaded by the Treasurer (fudscoops_loan_repayments.status = 1,
//   amount_paid); rows with status 0 are the repayment schedule created when a loan is uploaded.
// - Shares count when share_status = 1.
// - Full members have treasurer_approval = 1 and chairman_approval = 1.
class Report{

    private $con;

    public function __construct()
    {
        $this->con = (new DB())->getConnection();
    }

    // Report catalogue: key => [title, group, needs staff number, description]
    public static function catalogue()
    {
        return [
            'summary'     => ['Cooperative Summary', 'Overview', false, 'Key figures for the period: members, savings, withdrawals, shares, loans and commodity.'],
            'statement'   => ['Member Statement', 'Overview', true, 'Full history for one member: savings, withdrawals, shares, loans and commodity.'],
            'savings'     => ['Savings Deposits', 'Savings', false, 'Savings deducted per month and per member.'],
            'withdrawals' => ['Withdrawals', 'Savings', false, 'Approved savings withdrawals and complete withdrawals.'],
            'loans'       => ['Loans', 'Loans', false, 'Loans granted, monthly repayment, amount repaid and outstanding balance.'],
            'repayments'  => ['Loan Repayments', 'Loans', false, 'Loan deductions received.'],
            'shares'      => ['Shares', 'Shares & Commodity', false, 'Share purchases and members\' share holdings.'],
            'commodity'   => ['Commodity', 'Shares & Commodity', false, 'Commodity applications by status.'],
            'membership'  => ['Membership', 'Membership', false, 'New members and pending applications.'],
        ];
    }

    public function build($key, $from, $to, $staffNo = '')
    {
        $method = 'report' . ucfirst($key);
        if (!array_key_exists($key, self::catalogue()) || !method_exists($this, $method)) {
            return null;
        }
        $report = $this->$method($from, $to, $staffNo);
        $report += ['summary' => [], 'tables' => [], 'notes' => []];
        return $report;
    }

    // ---------- helpers ----------

    private function rows($sql, $params = [])
    {
        $stmt = $this->con->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function value($sql, $params = [])
    {
        $stmt = $this->con->prepare($sql);
        $stmt->execute($params);
        $v = $stmt->fetchColumn();
        return $v === false || $v === null ? 0 : $v;
    }

    // Savings deduction month as a number, e.g. August 2026 => 202608
    private static function monthKey($date)
    {
        return (int) date('Ym', strtotime($date));
    }

    private static function periodText($from, $to)
    {
        return date('d M Y', strtotime($from)) . ' to ' . date('d M Y', strtotime($to));
    }

    private static function monthPeriodText($from, $to)
    {
        return date('F Y', strtotime($from)) . ' to ' . date('F Y', strtotime($to));
    }

    private static function fullName($row)
    {
        return preg_replace('/\s+/', ' ', trim(($row['fname'] ?? '') . ' ' . ($row['oname'] ?? '') . ' ' . ($row['lname'] ?? '')));
    }

    // ---------- reports ----------

    private function reportSummary($from, $to)
    {
        $p = [':from' => $from, ':to' => $to];
        $toEnd = [':to' => $to];
        $months = [':mfrom' => self::monthKey($from), ':mto' => self::monthKey($to)];

        $fullMembers = $this->value("SELECT COUNT(*) FROM fudscoops_member WHERE treasurer_approval = 1 AND chairman_approval = 1 AND is_deleted = 0 AND DATE(reg_date) <= :to", $toEnd);
        $newMembers = $this->value("SELECT COUNT(*) FROM fudscoops_member WHERE treasurer_approval = 1 AND chairman_approval = 1 AND is_deleted = 0 AND DATE(reg_date) BETWEEN :from AND :to", $p);
        $awaitingTreasurer = $this->value("SELECT COUNT(*) FROM fudscoops_member WHERE treasurer_approval = 0 AND is_deleted = 0");
        $awaitingChairman = $this->value("SELECT COUNT(*) FROM fudscoops_member WHERE treasurer_approval = 1 AND chairman_approval = 0 AND is_deleted = 0");

        $savingsPeriod = $this->value("SELECT SUM(savings_amount) FROM fudscoops_savings WHERE COALESCE(savings_is_deleted, 0) = 0
                                         AND CAST(year AS UNSIGNED) * 100 + CAST(month AS UNSIGNED) BETWEEN :mfrom AND :mto", $months);
        $savingsTotal = $this->value("SELECT SUM(savings_amount) FROM fudscoops_savings WHERE COALESCE(savings_is_deleted, 0) = 0
                                        AND CAST(year AS UNSIGNED) * 100 + CAST(month AS UNSIGNED) <= :mto", [':mto' => self::monthKey($to)]);
        $withdrawalsPeriod = $this->value("SELECT SUM(approved_withrawal_amount) FROM fudscoops_withrawals WHERE chairman_approval = 1
                                             AND COALESCE(withrawals_is_deleted, 0) = 0 AND DATE(approved_date) BETWEEN :from AND :to", $p);
        $withdrawalsTotal = $this->value("SELECT SUM(approved_withrawal_amount) FROM fudscoops_withrawals WHERE chairman_approval = 1
                                            AND COALESCE(withrawals_is_deleted, 0) = 0 AND DATE(approved_date) <= :to", $toEnd);
        $completePeriod = $this->value("SELECT SUM(approved_amount) FROM fudscoops_complete_withrawal WHERE chairman_approval = 1
                                          AND COALESCE(complete_withrawal_is_deleted, 0) = 0 AND DATE(date_approved) BETWEEN :from AND :to", $p);

        $sharesPeriod = $this->value("SELECT SUM(amount_paid) FROM fudscoops_shares WHERE share_status = 1 AND COALESCE(shares_is_deleted, 0) = 0 AND DATE(date) BETWEEN :from AND :to", $p);
        $sharesTotal = $this->value("SELECT SUM(amount_paid) FROM fudscoops_shares WHERE share_status = 1 AND COALESCE(shares_is_deleted, 0) = 0 AND DATE(date) <= :to", $toEnd);

        $loansPeriod = $this->rows("SELECT COUNT(*) n, SUM(COALESCE(amount_recommended, loan_amount)) amt FROM fudscoops_loan
                                     WHERE loan_status = 'Approved' AND STR_TO_DATE(LEFT(loan_date, 10), '%Y-%m-%d') BETWEEN :from AND :to", $p)[0];
        $loansGranted = $this->value("SELECT SUM(COALESCE(amount_recommended, loan_amount)) FROM fudscoops_loan
                                        WHERE loan_status = 'Approved' AND STR_TO_DATE(LEFT(loan_date, 10), '%Y-%m-%d') <= :to", $toEnd);
        $repaidPeriod = $this->value("SELECT SUM(amount_paid) FROM fudscoops_loan_repayments WHERE status = 1 AND COALESCE(is_deleted, 0) = 0
                                        AND loan_repayment_date BETWEEN :from AND :to", $p);
        $repaidTotal = $this->value("SELECT SUM(r.amount_paid) FROM fudscoops_loan_repayments r JOIN fudscoops_loan l ON l.loan_id = r.loan_id
                                       WHERE r.status = 1 AND COALESCE(r.is_deleted, 0) = 0 AND l.loan_status = 'Approved' AND r.loan_repayment_date <= :to", $toEnd);

        $commodityApproved = $this->value("SELECT SUM(total_amount) FROM fudscoops_commodity_loan_applications WHERE status = 'approved' AND DATE(application_date) BETWEEN :from AND :to", $p);
        $commodityPending = $this->value("SELECT COUNT(*) FROM fudscoops_commodity_loan_applications WHERE status = 'pending'");

        $byMonth = $this->rows("SELECT CAST(year AS UNSIGNED) y, CAST(month AS UNSIGNED) m, COUNT(DISTINCT member_idmember) members, SUM(savings_amount) total
                                 FROM fudscoops_savings WHERE COALESCE(savings_is_deleted, 0) = 0
                                   AND CAST(year AS UNSIGNED) * 100 + CAST(month AS UNSIGNED) BETWEEN :mfrom AND :mto
                                 GROUP BY y, m ORDER BY y, m", $months);
        $loansByType = $this->rows("SELECT t.loan_type_name, COUNT(*) n, SUM(COALESCE(l.amount_recommended, l.loan_amount)) amt
                                     FROM fudscoops_loan l LEFT JOIN fudscoops_loan_type t ON t.loan_type_id = l.loan_type_id
                                     WHERE l.loan_status = 'Approved' AND STR_TO_DATE(LEFT(l.loan_date, 10), '%Y-%m-%d') BETWEEN :from AND :to
                                     GROUP BY t.loan_type_name ORDER BY t.loan_type_name", $p);

        return [
            'title' => 'Cooperative Summary',
            'subtitle' => self::periodText($from, $to) . '. Balances as at ' . date('d M Y', strtotime($to)) . '.',
            'summary' => [
                ['Full members', $fullMembers, 'int'],
                ['New members in period', $newMembers, 'int'],
                ['Applications awaiting Treasurer', $awaitingTreasurer, 'int'],
                ['Applications awaiting Chairman', $awaitingChairman, 'int'],
                ['Savings deposited in period', $savingsPeriod, 'money'],
                ['Savings withdrawn in period', $withdrawalsPeriod, 'money'],
                ['Complete withdrawals in period', $completePeriod, 'money'],
                ['Savings balance (all members)', $savingsTotal - $withdrawalsTotal, 'money'],
                ['Shares bought in period', $sharesPeriod, 'money'],
                ['Total share capital', $sharesTotal, 'money'],
                ['Loans granted in period', $loansPeriod['amt'], 'money'],
                ['Loan repayments in period', $repaidPeriod, 'money'],
                ['Loans outstanding', $loansGranted - $repaidTotal, 'money'],
                ['Commodity approved in period', $commodityApproved, 'money'],
                ['Commodity applications pending', $commodityPending, 'int'],
            ],
            'tables' => [
                [
                    'heading' => 'Savings deposited by month',
                    'columns' => [['Month', 'text'], ['Members', 'int'], ['Amount', 'money']],
                    'rows' => array_map(function ($r) {
                        return [date('F Y', mktime(0, 0, 0, $r['m'], 1, $r['y'])), $r['members'], $r['total']];
                    }, $byMonth),
                    'totals' => ['Total', null, array_sum(array_column($byMonth, 'total'))],
                ],
                [
                    'heading' => 'Loans granted by type',
                    'columns' => [['Loan type', 'text'], ['Loans', 'int'], ['Amount', 'money']],
                    'rows' => array_map(function ($r) {
                        return [$r['loan_type_name'] ?: 'Unspecified', $r['n'], $r['amt']];
                    }, $loansByType),
                    'totals' => ['Total', array_sum(array_column($loansByType, 'n')), array_sum(array_column($loansByType, 'amt'))],
                ],
            ],
            'notes' => [
                'Savings balance = savings deposited minus savings withdrawals approved by the Chairman (as shown on members\' dashboards). Complete withdrawals are listed separately.',
                'Savings are counted by deduction month.',
            ],
        ];
    }

    private function reportStatement($from, $to, $staffNo)
    {
        $staffNo = strtoupper(trim($staffNo));
        $member = $this->rows("SELECT m.*, e.sp_no, e.fname, e.oname, e.lname, e.dept, e.cadre
                                FROM fudscoops_member m JOIN employee e ON e.employee_id = m.employee_id
                                WHERE UPPER(e.sp_no) = :sp", [':sp' => $staffNo]);
        if (!$member) {
            return [
                'title' => 'Member Statement',
                'subtitle' => $staffNo === '' ? 'Enter a staff number.' : 'No member found with staff number ' . $staffNo . '.',
            ];
        }
        $m = $member[0];
        $id = [':id' => $m['member_id'], ':to' => $to];

        $savings = $this->rows("SELECT CAST(year AS UNSIGNED) y, CAST(month AS UNSIGNED) mo, savings_date, savings_amount FROM fudscoops_savings
                                 WHERE member_idmember = :id AND COALESCE(savings_is_deleted, 0) = 0
                                   AND CAST(year AS UNSIGNED) * 100 + CAST(month AS UNSIGNED) <= :mto
                                 ORDER BY y, mo, savings_id", [':id' => $m['member_id'], ':mto' => self::monthKey($to)]);
        $withdrawals = $this->rows("SELECT w.proposed_date, w.approved_date, w.proposed_withrawal_amount, w.approved_withrawal_amount, w.chairman_approval, b.name bank, w.acount_number
                                     FROM fudscoops_withrawals w LEFT JOIN banks b ON b.id = w.bank_id
                                     WHERE w.member_id = :id AND COALESCE(w.withrawals_is_deleted, 0) = 0 AND DATE(w.proposed_date) <= :to
                                     ORDER BY w.proposed_date", $id);
        $complete = $this->rows("SELECT date_applied, date_approved, approved_amount, chairman_approval FROM fudscoops_complete_withrawal
                                  WHERE employee_id = :emp AND COALESCE(complete_withrawal_is_deleted, 0) = 0 AND DATE(date_applied) <= :to
                                  ORDER BY date_applied", [':emp' => $m['employee_id'], ':to' => $to]);
        $shares = $this->rows("SELECT date, amount_paid, unit_price FROM fudscoops_shares
                                WHERE member_id = :id AND share_status = 1 AND COALESCE(shares_is_deleted, 0) = 0 AND DATE(date) <= :to
                                ORDER BY date", $id);
        $loans = $this->loanRows($to, ' AND l.member_id = :member', [':member' => $m['member_id']]);
        $commodity = $this->rows("SELECT application_date, status, total_amount FROM fudscoops_commodity_loan_applications
                                   WHERE member_id = :id AND DATE(application_date) <= :to ORDER BY application_date", $id);

        $totalSavings = array_sum(array_column($savings, 'savings_amount'));
        $approvedWithdrawals = array_sum(array_map(function ($w) {
            return $w['chairman_approval'] == 1 ? $w['approved_withrawal_amount'] : 0;
        }, $withdrawals));

        $status = $m['treasurer_approval'] == 1 && $m['chairman_approval'] == 1 ? 'Full member'
                : ($m['treasurer_approval'] == 1 ? 'Awaiting Chairman' : 'Awaiting Treasurer');

        return [
            'title' => 'Member Statement',
            'subtitle' => self::fullName($m) . ' (' . $m['sp_no'] . '), as at ' . date('d M Y', strtotime($to)),
            'summary' => [
                ['Staff number', $m['sp_no'], 'text'],
                ['Name', self::fullName($m), 'text'],
                ['Department', $m['dept'], 'text'],
                ['Membership status', $status, 'text'],
                ['Date registered', $m['reg_date'] ? date('d M Y', strtotime($m['reg_date'])) : '', 'text'],
                ['Monthly savings', $m['proposed_monthly_savings'], 'money'],
                ['Total savings deposited', $totalSavings, 'money'],
                ['Savings withdrawn', $approvedWithdrawals, 'money'],
                ['Savings balance', $totalSavings - $approvedWithdrawals, 'money'],
                ['Share capital', array_sum(array_column($shares, 'amount_paid')), 'money'],
                ['Loans outstanding', array_sum(array_column($loans, 'outstanding')), 'money'],
            ],
            'tables' => [
                [
                    'heading' => 'Savings',
                    'columns' => [['Deduction month', 'text'], ['Date recorded', 'date'], ['Amount', 'money']],
                    'rows' => array_map(function ($s) {
                        return [date('F Y', mktime(0, 0, 0, $s['mo'], 1, $s['y'])), $s['savings_date'], $s['savings_amount']];
                    }, $savings),
                    'totals' => ['Total', null, $totalSavings],
                ],
                [
                    'heading' => 'Savings withdrawals',
                    'columns' => [['Requested', 'date'], ['Approved', 'date'], ['Requested amount', 'money'], ['Approved amount', 'money'], ['Status', 'text']],
                    'rows' => array_map(function ($w) {
                        return [$w['proposed_date'], $w['chairman_approval'] == 1 ? $w['approved_date'] : '', $w['proposed_withrawal_amount'],
                                $w['chairman_approval'] == 1 ? $w['approved_withrawal_amount'] : null, $w['chairman_approval'] == 1 ? 'Approved' : 'Pending'];
                    }, $withdrawals),
                    'totals' => ['Total approved', null, null, $approvedWithdrawals, null],
                ],
                [
                    'heading' => 'Complete withdrawal',
                    'columns' => [['Applied', 'date'], ['Approved', 'date'], ['Amount', 'money'], ['Status', 'text']],
                    'rows' => array_map(function ($c) {
                        return [$c['date_applied'], $c['chairman_approval'] == 1 ? $c['date_approved'] : '', $c['approved_amount'], $c['chairman_approval'] == 1 ? 'Approved' : 'Pending'];
                    }, $complete),
                    'totals' => null,
                ],
                [
                    'heading' => 'Shares',
                    'columns' => [['Date', 'date'], ['Amount', 'money'], ['Units', 'int']],
                    'rows' => array_map(function ($s) {
                        return [$s['date'], $s['amount_paid'], $s['unit_price'] > 0 ? floor($s['amount_paid'] / $s['unit_price']) : null];
                    }, $shares),
                    'totals' => ['Total', array_sum(array_column($shares, 'amount_paid')), null],
                ],
                $this->loanTable($loans, 'Loans'),
                [
                    'heading' => 'Commodity applications',
                    'columns' => [['Date', 'date'], ['Status', 'text'], ['Amount', 'money']],
                    'rows' => array_map(function ($c) {
                        return [$c['application_date'], ucfirst($c['status']), $c['total_amount']];
                    }, $commodity),
                    'totals' => null,
                ],
            ],
            'notes' => ['Savings balance = savings deposited minus approved savings withdrawals.'],
        ];
    }

    private function reportSavings($from, $to)
    {
        $months = [':mfrom' => self::monthKey($from), ':mto' => self::monthKey($to)];
        $byMonth = $this->rows("SELECT CAST(year AS UNSIGNED) y, CAST(month AS UNSIGNED) m, COUNT(DISTINCT member_idmember) members, SUM(savings_amount) total
                                 FROM fudscoops_savings WHERE COALESCE(savings_is_deleted, 0) = 0
                                   AND CAST(year AS UNSIGNED) * 100 + CAST(month AS UNSIGNED) BETWEEN :mfrom AND :mto
                                 GROUP BY y, m ORDER BY y, m", $months);
        $byMember = $this->rows("SELECT e.sp_no, e.fname, e.oname, e.lname, e.dept, m.proposed_monthly_savings,
                                        COUNT(*) entries, SUM(s.savings_amount) total
                                  FROM fudscoops_savings s
                                  JOIN fudscoops_member m ON m.member_id = s.member_idmember
                                  JOIN employee e ON e.employee_id = m.employee_id
                                  WHERE COALESCE(s.savings_is_deleted, 0) = 0
                                    AND CAST(s.year AS UNSIGNED) * 100 + CAST(s.month AS UNSIGNED) BETWEEN :mfrom AND :mto
                                  GROUP BY m.member_id, e.sp_no, e.fname, e.oname, e.lname, e.dept, m.proposed_monthly_savings
                                  ORDER BY e.fname, e.lname", $months);
        $total = array_sum(array_column($byMonth, 'total'));

        return [
            'title' => 'Savings Deposits',
            'subtitle' => 'Deduction months ' . self::monthPeriodText($from, $to),
            'summary' => [
                ['Total deposited', $total, 'money'],
                ['Members who saved', count($byMember), 'int'],
                ['Months', count($byMonth), 'int'],
            ],
            'tables' => [
                [
                    'heading' => 'By month',
                    'columns' => [['Month', 'text'], ['Members', 'int'], ['Amount', 'money']],
                    'rows' => array_map(function ($r) {
                        return [date('F Y', mktime(0, 0, 0, $r['m'], 1, $r['y'])), $r['members'], $r['total']];
                    }, $byMonth),
                    'totals' => ['Total', null, $total],
                ],
                [
                    'heading' => 'By member',
                    'columns' => [['Staff No', 'text'], ['Name', 'text'], ['Department', 'text'], ['Monthly savings', 'money'], ['Deductions', 'int'], ['Total deposited', 'money']],
                    'rows' => array_map(function ($r) {
                        return [$r['sp_no'], self::fullName($r), $r['dept'], $r['proposed_monthly_savings'], $r['entries'], $r['total']];
                    }, $byMember),
                    'totals' => ['Total', null, null, null, array_sum(array_column($byMember, 'entries')), array_sum(array_column($byMember, 'total'))],
                ],
            ],
        ];
    }

    private function reportWithdrawals($from, $to)
    {
        $p = [':from' => $from, ':to' => $to];
        $withdrawals = $this->rows("SELECT w.approved_date, e.sp_no, e.fname, e.oname, e.lname, e.dept, w.approved_withrawal_amount, b.name bank, w.account_name, w.acount_number
                                     FROM fudscoops_withrawals w
                                     JOIN fudscoops_member m ON m.member_id = w.member_id
                                     JOIN employee e ON e.employee_id = m.employee_id
                                     LEFT JOIN banks b ON b.id = w.bank_id
                                     WHERE w.chairman_approval = 1 AND COALESCE(w.withrawals_is_deleted, 0) = 0
                                       AND DATE(w.approved_date) BETWEEN :from AND :to
                                     ORDER BY w.approved_date, e.fname", $p);
        $complete = $this->rows("SELECT c.date_approved, e.sp_no, e.fname, e.oname, e.lname, e.dept, c.approved_amount, b.name bank, c.account_name, c.acount_number
                                  FROM fudscoops_complete_withrawal c
                                  JOIN employee e ON e.employee_id = c.employee_id
                                  LEFT JOIN banks b ON b.id = c.bank_id
                                  WHERE c.chairman_approval = 1 AND COALESCE(c.complete_withrawal_is_deleted, 0) = 0
                                    AND DATE(c.date_approved) BETWEEN :from AND :to
                                  ORDER BY c.date_approved, e.fname", $p);
        $pending = $this->value("SELECT COUNT(*) FROM fudscoops_withrawals WHERE chairman_approval = 0 AND COALESCE(withrawals_is_deleted, 0) = 0");
        $pendingComplete = $this->value("SELECT COUNT(*) FROM fudscoops_complete_withrawal WHERE chairman_approval = 0 AND COALESCE(complete_withrawal_is_deleted, 0) = 0");

        $mapRow = function ($amountKey, $dateKey) {
            return function ($r) use ($amountKey, $dateKey) {
                return [$r[$dateKey], $r['sp_no'], self::fullName($r), $r['dept'], $r['bank'], $r['acount_number'], $r[$amountKey]];
            };
        };
        $columns = [['Date approved', 'date'], ['Staff No', 'text'], ['Name', 'text'], ['Department', 'text'], ['Bank', 'text'], ['Account No', 'text'], ['Amount', 'money']];
        $total = array_sum(array_column($withdrawals, 'approved_withrawal_amount'));
        $totalComplete = array_sum(array_column($complete, 'approved_amount'));

        return [
            'title' => 'Withdrawals',
            'subtitle' => 'Approved ' . self::periodText($from, $to),
            'summary' => [
                ['Savings withdrawals approved', count($withdrawals), 'int'],
                ['Amount withdrawn', $total, 'money'],
                ['Complete withdrawals approved', count($complete), 'int'],
                ['Complete withdrawal amount', $totalComplete, 'money'],
                ['Savings withdrawals pending', $pending, 'int'],
                ['Complete withdrawals pending', $pendingComplete, 'int'],
            ],
            'tables' => [
                [
                    'heading' => 'Savings withdrawals',
                    'columns' => $columns,
                    'rows' => array_map($mapRow('approved_withrawal_amount', 'approved_date'), $withdrawals),
                    'totals' => ['Total', null, null, null, null, null, $total],
                ],
                [
                    'heading' => 'Complete withdrawals',
                    'columns' => $columns,
                    'rows' => array_map($mapRow('approved_amount', 'date_approved'), $complete),
                    'totals' => ['Total', null, null, null, null, null, $totalComplete],
                ],
            ],
        ];
    }

    // Loans with monthly repayment, amount repaid (deductions up to $to) and outstanding balance
    private function loanRows($to, $where = '', $params = [])
    {
        return array_map(function ($r) {
            $r['amount'] = (float) $r['amount'];
            $r['repaid'] = (float) $r['repaid'];
            $r['outstanding'] = max(0, $r['amount'] - $r['repaid']);
            return $r;
        }, $this->rows("
            SELECT l.loan_id, STR_TO_DATE(LEFT(l.loan_date, 10), '%Y-%m-%d') loan_date, t.loan_type_name, l.loan_tenor,
                   COALESCE(l.amount_recommended, l.loan_amount) amount,
                   e.sp_no, e.fname, e.oname, e.lname, e.dept,
                   (SELECT r.loan_repayment_amount FROM fudscoops_loan_repayments r
                     WHERE r.loan_id = l.loan_id AND COALESCE(r.is_deleted, 0) = 0
                     ORDER BY r.loan_repayment_id DESC LIMIT 1) monthly,
                   (SELECT COALESCE(SUM(r.amount_paid), 0) FROM fudscoops_loan_repayments r
                     WHERE r.loan_id = l.loan_id AND r.status = 1 AND COALESCE(r.is_deleted, 0) = 0
                       AND r.loan_repayment_date <= :to) repaid
            FROM fudscoops_loan l
            JOIN fudscoops_member m ON m.member_id = l.member_id
            JOIN employee e ON e.employee_id = m.employee_id
            LEFT JOIN fudscoops_loan_type t ON t.loan_type_id = l.loan_type_id
            WHERE l.loan_status = 'Approved' AND STR_TO_DATE(LEFT(l.loan_date, 10), '%Y-%m-%d') <= :to2 $where
            ORDER BY loan_date, e.fname", array_merge([':to' => $to, ':to2' => $to], $params)));
    }

    private function loanTable($loans, $heading, $withMember = false)
    {
        $columns = [['Date', 'date'], ['Loan type', 'text'], ['Tenor (months)', 'int'], ['Amount', 'money'], ['Monthly repayment', 'money'], ['Repaid', 'money'], ['Outstanding', 'money']];
        if ($withMember) {
            array_splice($columns, 1, 0, [['Staff No', 'text'], ['Name', 'text']]);
        }
        $rows = array_map(function ($l) use ($withMember) {
            $row = [$l['loan_date'], $l['loan_type_name'] ?: 'Unspecified', $l['loan_tenor'], $l['amount'], $l['monthly'], $l['repaid'], $l['outstanding']];
            if ($withMember) {
                array_splice($row, 1, 0, [$l['sp_no'], self::fullName($l)]);
            }
            return $row;
        }, $loans);
        $totals = ['Total', null, null, array_sum(array_column($loans, 'amount')), null, array_sum(array_column($loans, 'repaid')), array_sum(array_column($loans, 'outstanding'))];
        if ($withMember) {
            array_splice($totals, 1, 0, [null, null]);
        }
        return ['heading' => $heading, 'columns' => $columns, 'rows' => $rows, 'totals' => $totals];
    }

    private function reportLoans($from, $to)
    {
        $all = $this->loanRows($to);
        $inPeriod = array_values(array_filter($all, function ($l) use ($from) {
            return $l['loan_date'] >= $from;
        }));
        $outstanding = array_values(array_filter($all, function ($l) {
            return $l['outstanding'] > 0;
        }));

        return [
            'title' => 'Loans',
            'subtitle' => 'Granted ' . self::periodText($from, $to) . '. Outstanding balances as at ' . date('d M Y', strtotime($to)) . '.',
            'summary' => [
                ['Loans granted in period', count($inPeriod), 'int'],
                ['Amount granted in period', array_sum(array_column($inPeriod, 'amount')), 'money'],
                ['Loans with a balance', count($outstanding), 'int'],
                ['Total outstanding', array_sum(array_column($all, 'outstanding')), 'money'],
                ['Total repaid', array_sum(array_column($all, 'repaid')), 'money'],
            ],
            'tables' => [
                $this->loanTable($inPeriod, 'Loans granted in period', true),
                $this->loanTable($outstanding, 'All loans with an outstanding balance', true),
            ],
            'notes' => ['Repaid = loan deductions uploaded by the Treasurer. Monthly repayment is the scheduled deduction.'],
        ];
    }

    private function reportRepayments($from, $to)
    {
        $rows = $this->rows("SELECT r.loan_repayment_date, e.sp_no, e.fname, e.oname, e.lname, e.dept, t.loan_type_name, r.amount_paid
                              FROM fudscoops_loan_repayments r
                              JOIN fudscoops_loan l ON l.loan_id = r.loan_id
                              JOIN fudscoops_member m ON m.member_id = r.member_id
                              JOIN employee e ON e.employee_id = m.employee_id
                              LEFT JOIN fudscoops_loan_type t ON t.loan_type_id = l.loan_type_id
                              WHERE r.status = 1 AND COALESCE(r.is_deleted, 0) = 0 AND r.loan_repayment_date BETWEEN :from AND :to
                              ORDER BY r.loan_repayment_date, e.fname", [':from' => $from, ':to' => $to]);
        $total = array_sum(array_column($rows, 'amount_paid'));

        return [
            'title' => 'Loan Repayments',
            'subtitle' => 'Deductions ' . self::periodText($from, $to),
            'summary' => [
                ['Deductions', count($rows), 'int'],
                ['Amount repaid', $total, 'money'],
            ],
            'tables' => [[
                'heading' => 'Loan deductions',
                'columns' => [['Deduction date', 'date'], ['Staff No', 'text'], ['Name', 'text'], ['Department', 'text'], ['Loan type', 'text'], ['Amount', 'money']],
                'rows' => array_map(function ($r) {
                    return [$r['loan_repayment_date'], $r['sp_no'], self::fullName($r), $r['dept'], $r['loan_type_name'], $r['amount_paid']];
                }, $rows),
                'totals' => ['Total', null, null, null, null, $total],
            ]],
            'notes' => ['Only deductions uploaded by the Treasurer (Loan Deductions) are included.'],
        ];
    }

    private function reportShares($from, $to)
    {
        $unitPrice = (float) $this->value("SELECT price FROM fudscoops_share_unit ORDER BY shares_unit_id DESC LIMIT 1");
        $purchases = $this->rows("SELECT s.date, e.sp_no, e.fname, e.oname, e.lname, e.dept, s.amount_paid, s.unit_price
                                   FROM fudscoops_shares s
                                   JOIN fudscoops_member m ON m.member_id = s.member_id
                                   JOIN employee e ON e.employee_id = m.employee_id
                                   WHERE s.share_status = 1 AND COALESCE(s.shares_is_deleted, 0) = 0 AND DATE(s.date) BETWEEN :from AND :to
                                   ORDER BY s.date, e.fname", [':from' => $from, ':to' => $to]);
        $holdings = $this->rows("SELECT e.sp_no, e.fname, e.oname, e.lname, e.dept, SUM(s.amount_paid) total
                                  FROM fudscoops_shares s
                                  JOIN fudscoops_member m ON m.member_id = s.member_id
                                  JOIN employee e ON e.employee_id = m.employee_id
                                  WHERE s.share_status = 1 AND COALESCE(s.shares_is_deleted, 0) = 0 AND DATE(s.date) <= :to
                                  GROUP BY m.member_id, e.sp_no, e.fname, e.oname, e.lname, e.dept
                                  ORDER BY total DESC, e.fname", [':to' => $to]);
        $capital = array_sum(array_column($holdings, 'total'));
        $units = function ($amount, $price) {
            return $price > 0 ? floor($amount / $price) : null;
        };

        return [
            'title' => 'Shares',
            'subtitle' => 'Purchases ' . self::periodText($from, $to) . '. Holdings as at ' . date('d M Y', strtotime($to)) . '.',
            'summary' => [
                ['Share purchases in period', count($purchases), 'int'],
                ['Amount bought in period', array_sum(array_column($purchases, 'amount_paid')), 'money'],
                ['Shareholders', count($holdings), 'int'],
                ['Total share capital', $capital, 'money'],
                ['Current unit price', $unitPrice, 'money'],
            ],
            'tables' => [
                [
                    'heading' => 'Share purchases in period',
                    'columns' => [['Date', 'date'], ['Staff No', 'text'], ['Name', 'text'], ['Department', 'text'], ['Amount', 'money'], ['Units', 'int']],
                    'rows' => array_map(function ($r) use ($units) {
                        return [$r['date'], $r['sp_no'], self::fullName($r), $r['dept'], $r['amount_paid'], $units($r['amount_paid'], (float) $r['unit_price'])];
                    }, $purchases),
                    'totals' => ['Total', null, null, null, array_sum(array_column($purchases, 'amount_paid')), null],
                ],
                [
                    'heading' => 'Share holdings',
                    'columns' => [['Staff No', 'text'], ['Name', 'text'], ['Department', 'text'], ['Share capital', 'money'], ['Units at current price', 'int']],
                    'rows' => array_map(function ($r) use ($units, $unitPrice) {
                        return [$r['sp_no'], self::fullName($r), $r['dept'], $r['total'], $units($r['total'], $unitPrice)];
                    }, $holdings),
                    'totals' => ['Total', null, null, $capital, null],
                ],
            ],
        ];
    }

    private function reportCommodity($from, $to)
    {
        $rows = $this->rows("SELECT a.application_date, e.sp_no, e.fname, e.oname, e.lname, e.dept, a.status, a.total_amount, a.approval_date, a.disbursement_date
                              FROM fudscoops_commodity_loan_applications a
                              JOIN fudscoops_member m ON m.member_id = a.member_id
                              JOIN employee e ON e.employee_id = m.employee_id
                              WHERE DATE(a.application_date) BETWEEN :from AND :to
                              ORDER BY a.application_date, e.fname", [':from' => $from, ':to' => $to]);
        $byStatus = [];
        foreach ($rows as $r) {
            $s = ucfirst($r['status']);
            $byStatus[$s] = isset($byStatus[$s]) ? [$byStatus[$s][0] + 1, $byStatus[$s][1] + $r['total_amount']] : [1, (float) $r['total_amount']];
        }
        ksort($byStatus);

        return [
            'title' => 'Commodity',
            'subtitle' => 'Applications ' . self::periodText($from, $to),
            'summary' => [
                ['Applications', count($rows), 'int'],
                ['Total amount', array_sum(array_column($rows, 'total_amount')), 'money'],
            ],
            'tables' => [
                [
                    'heading' => 'By status',
                    'columns' => [['Status', 'text'], ['Applications', 'int'], ['Amount', 'money']],
                    'rows' => array_map(function ($status, $v) {
                        return [$status, $v[0], $v[1]];
                    }, array_keys($byStatus), $byStatus),
                    'totals' => ['Total', count($rows), array_sum(array_column($rows, 'total_amount'))],
                ],
                [
                    'heading' => 'Applications',
                    'columns' => [['Applied', 'date'], ['Staff No', 'text'], ['Name', 'text'], ['Department', 'text'], ['Status', 'text'], ['Amount', 'money'], ['Approved', 'date'], ['Disbursed', 'date']],
                    'rows' => array_map(function ($r) {
                        return [$r['application_date'], $r['sp_no'], self::fullName($r), $r['dept'], ucfirst($r['status']), $r['total_amount'], $r['approval_date'], $r['disbursement_date']];
                    }, $rows),
                    'totals' => ['Total', null, null, null, null, array_sum(array_column($rows, 'total_amount')), null, null],
                ],
            ],
        ];
    }

    private function reportMembership($from, $to)
    {
        $newMembers = $this->rows("SELECT m.reg_date, e.sp_no, e.fname, e.oname, e.lname, e.dept, e.cadre, m.proposed_monthly_savings
                                    FROM fudscoops_member m JOIN employee e ON e.employee_id = m.employee_id
                                    WHERE m.treasurer_approval = 1 AND m.chairman_approval = 1 AND m.is_deleted = 0
                                      AND DATE(m.reg_date) BETWEEN :from AND :to
                                    ORDER BY m.reg_date, e.fname", [':from' => $from, ':to' => $to]);
        // LEFT JOIN so applications whose staff record is missing still show (and match the summary counts)
        $pending = $this->rows("SELECT m.reg_date, m.employee_id, e.sp_no, e.fname, e.oname, e.lname, e.dept, m.proposed_monthly_savings, m.treasurer_approval
                                 FROM fudscoops_member m LEFT JOIN employee e ON e.employee_id = m.employee_id
                                 WHERE NOT (m.treasurer_approval = 1 AND m.chairman_approval = 1) AND m.is_deleted = 0
                                 ORDER BY m.treasurer_approval, m.reg_date");
        $fullMembers = $this->value("SELECT COUNT(*) FROM fudscoops_member WHERE treasurer_approval = 1 AND chairman_approval = 1 AND is_deleted = 0 AND DATE(reg_date) <= :to", [':to' => $to]);

        return [
            'title' => 'Membership',
            'subtitle' => 'New members ' . self::periodText($from, $to) . '. Pending applications as of today.',
            'summary' => [
                ['Full members as at ' . date('d M Y', strtotime($to)), $fullMembers, 'int'],
                ['New members in period', count($newMembers), 'int'],
                ['Monthly savings of new members', array_sum(array_column($newMembers, 'proposed_monthly_savings')), 'money'],
                ['Pending applications', count($pending), 'int'],
            ],
            'tables' => [
                [
                    'heading' => 'New members',
                    'columns' => [['Registered', 'date'], ['Staff No', 'text'], ['Name', 'text'], ['Department', 'text'], ['Cadre', 'text'], ['Monthly savings', 'money']],
                    'rows' => array_map(function ($r) {
                        return [$r['reg_date'], $r['sp_no'], self::fullName($r), $r['dept'], $r['cadre'], $r['proposed_monthly_savings']];
                    }, $newMembers),
                    'totals' => ['Total', null, null, null, null, array_sum(array_column($newMembers, 'proposed_monthly_savings'))],
                ],
                [
                    'heading' => 'Pending applications',
                    'columns' => [['Applied', 'date'], ['Staff No', 'text'], ['Name', 'text'], ['Department', 'text'], ['Monthly savings', 'money'], ['Status', 'text']],
                    'rows' => array_map(function ($r) {
                        $name = $r['sp_no'] === null ? 'Staff record missing (employee ID ' . $r['employee_id'] . ')' : self::fullName($r);
                        return [$r['reg_date'], $r['sp_no'], $name, $r['dept'], $r['proposed_monthly_savings'], $r['treasurer_approval'] == 1 ? 'Awaiting Chairman' : 'Awaiting Treasurer'];
                    }, $pending),
                    'totals' => null,
                ],
            ],
        ];
    }
}
