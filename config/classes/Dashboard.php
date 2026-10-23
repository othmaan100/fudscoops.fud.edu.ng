<?php
require_once('DB.php');

// Figures for the role dashboards (R/, UR/, Chairman/, Treasurer/, Secretary/ index.php).
// Read-only. The "needs your attention" counts use the same conditions as each officer's work list,
// so a count matches what the officer sees when they open the list.
class Dashboard{

    private $con;

    public function __construct()
    {
        $this->con = (new DB())->getConnection();
    }

    private function value($sql, $params = [])
    {
        $stmt = $this->con->prepare($sql);
        $stmt->execute($params);
        $v = $stmt->fetchColumn();
        return $v === false || $v === null ? 0 : $v;
    }

    private function row($sql, $params = [])
    {
        $stmt = $this->con->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // "Good morning" etc. and today's date in Nigerian time
    public static function greeting()
    {
        $now = new DateTime('now', new DateTimeZone('Africa/Lagos'));
        $hour = (int) $now->format('G');
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 16 ? 'Good afternoon' : 'Good evening');
        return [$greeting, $now->format('l, j F Y')];
    }

    // Name for the greeting. User::getFullname() returns an HTML "No data found" message for accounts
    // without a staff record (e.g. "chairman"), so fall back to the given text.
    public static function displayName($user, $username, $fallback)
    {
        $name = (string) $user->getFullname($username);
        if ($name === '' || strpos($name, '<') !== false) {
            return $fallback;
        }
        return preg_replace('/\s+/', ' ', trim($name));
    }

    // ---------- Cooperative overview (officers) ----------

    public function cooperativeTotals()
    {
        $savings = $this->value("SELECT COALESCE(SUM(savings_amount), 0) FROM fudscoops_savings WHERE COALESCE(savings_is_deleted, 0) = 0");
        $withdrawn = $this->value("SELECT COALESCE(SUM(approved_withrawal_amount), 0) FROM fudscoops_withrawals WHERE chairman_approval = 1 AND COALESCE(withrawals_is_deleted, 0) = 0")
                   + $this->value("SELECT COALESCE(SUM(approved_amount + CASE WHEN settled_at IS NOT NULL THEN amount_owed ELSE 0 END), 0) FROM fudscoops_complete_withrawal
                                   WHERE chairman_approval = 1 AND COALESCE(complete_withrawal_is_deleted, 0) = 0");
        $loans = $this->value("SELECT COALESCE(SUM(COALESCE(total_payable, NULLIF(amount_recommended, 0), loan_amount)), 0) FROM fudscoops_loan WHERE loan_status IN ('Disbursed', 'Approved')");
        $repaid = $this->value("SELECT COALESCE(SUM(r.amount_paid), 0) FROM fudscoops_loan_repayments r JOIN fudscoops_loan l ON l.loan_id = r.loan_id
                                WHERE r.status = 1 AND COALESCE(r.is_deleted, 0) = 0 AND l.loan_status IN ('Disbursed', 'Approved')");
        $lastMonth = $this->con->query("SELECT CAST(year AS UNSIGNED) y, CAST(month AS UNSIGNED) m, SUM(savings_amount) total
                                        FROM fudscoops_savings WHERE COALESCE(savings_is_deleted, 0) = 0
                                        GROUP BY y, m ORDER BY y DESC, m DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        return [
            'members' => (int) $this->value("SELECT COUNT(*) FROM fudscoops_member WHERE treasurer_approval = 1 AND chairman_approval = 1 AND is_deleted = 0"),
            'savings_balance' => $savings - $withdrawn,
            'share_capital' => $this->value("SELECT COALESCE(SUM(amount_paid), 0) FROM fudscoops_shares WHERE share_status = 1 AND shares_is_deleted = 0"),
            'loans_outstanding' => $loans - $repaid,
            'open_loans' => (int) $this->value("SELECT COUNT(*) FROM fudscoops_loan WHERE loan_status IN ('Disbursed', 'Approved')"),
            'last_month_label' => $lastMonth ? date('F Y', mktime(0, 0, 0, $lastMonth['m'], 1, $lastMonth['y'])) : '',
            'last_month_total' => $lastMonth ? $lastMonth['total'] : 0,
        ];
    }

    // ---------- Officer work queues: [label, count, icon, link] ----------

    public function pendingFor($role)
    {
        $c = function ($sql) { return (int) $this->value($sql); };
        // Overdue loans (LoanControls::overdueLoans) are shown to the Treasurer, Chairman and Secretary
        require_once('LoanControls.php');
        $overdue = ['Overdue loans', count(LoanControls::overdueLoans()), 'mdi-alert-circle', 'overdue_loans.php'];
        switch ($role) {
            case 'chairman':
                return [
                    ['Membership applications to authorize', $c("SELECT COUNT(*) FROM fudscoops_member WHERE treasurer_approval = 1 AND chairman_approval = 0"), 'mdi-account-plus', 'member_list.php'],
                    ['Savings update requests', $c("SELECT COUNT(*) FROM fudscoops_update_savings WHERE is_secretary_approved = 0"), 'mdi-cash-refund', 'update_savings.php'],
                    ['Savings withdrawals to approve', $c("SELECT COUNT(*) FROM fudscoops_withrawals WHERE status = 1 AND chairman_approval = 0 AND approved_withrawal_amount <> 0"), 'mdi-bank-transfer-out', 'withdrawal_endorsement.php'],
                    ['Complete withdrawals to approve', $c("SELECT COUNT(*) FROM fudscoops_complete_withrawal WHERE chairman_approval = 0 AND approved_amount <> 0"), 'mdi-account-remove', 'complete_withdrawals.php'],
                    ['Share purchases to approve', $c("SELECT COUNT(*) FROM fudscoops_shares WHERE entry_type = 'purchase' AND share_status = 0 AND secretary_endorsement = 1 AND chairman_approval = 0 AND shares_is_deleted = 0"), 'mdi-chart-pie', 'manage_share_purchase.php'],
                    ['Share transfers to approve', $c("SELECT COUNT(*) FROM fudscoops_shares_transfer WHERE share_transfer_is_deleted = 0 AND is_accepted = 1 AND share_transfer_status = 0 AND secretary_endorsement = 1 AND chairman_approval = 0"), 'mdi-swap-horizontal', 'manage_share_transfer.php'],
                    ['Share conversions to approve', $c("SELECT COUNT(*) FROM fudscoops_shares_conversion WHERE share_conversion_is_deleted = 0 AND conversion_status = 0 AND chairman_approval = 0"), 'mdi-sync', 'manage_share_conversion.php'],
                    ['Loans to authorize', $c("SELECT COUNT(*) FROM fudscoops_loan WHERE loan_status = 'Recommended' AND chairman_approval = 0"), 'mdi-cash-multiple', 'loans.php'],
                    $overdue,
                    ['Commodity requests to authorize', $c("SELECT COUNT(*) FROM fudscoops_commodity_loan_applications WHERE status = 'recommended'"), 'mdi-cart-outline', 'approved_commodity_Request.php?stage=recommended'],
                ];
            case 'treasurer':
                return [
                    ['Membership applications to review', $c("SELECT COUNT(*) FROM fudscoops_member WHERE treasurer_approval = 0 AND is_deleted = 0"), 'mdi-account-plus', 'member_list.php'],
                    ['Savings updates to confirm', $c("SELECT COUNT(DISTINCT employee_id) FROM fudscoops_update_savings WHERE is_secretary_approved = 1 AND is_treasurer_approved = 0"), 'mdi-cash-refund', 'treasurer_view_approved_savings.php'],
                    ['Savings withdrawals to process', $c("SELECT COUNT(*) FROM fudscoops_withrawals WHERE status = 0 AND chairman_approval = 0 AND approved_withrawal_amount = 0"), 'mdi-bank-transfer-out', 'withdrawal_endorsement.php'],
                    ['Complete withdrawals to process', $c("SELECT COUNT(*) FROM fudscoops_complete_withrawal WHERE chairman_approval = 0 AND approved_amount = 0"), 'mdi-account-remove', 'complete_withdrawals.php'],
                    ['Share purchases to confirm', $c("SELECT COUNT(*) FROM fudscoops_shares WHERE entry_type = 'purchase' AND share_status = 0 AND chairman_approval = 1 AND shares_is_deleted = 0"), 'mdi-chart-pie', 'manage_share_purchase.php'],
                    ['Share transfers to complete', $c("SELECT COUNT(*) FROM fudscoops_shares_transfer WHERE share_transfer_is_deleted = 0 AND is_accepted = 1 AND chairman_approval = 1 AND share_transfer_status = 0"), 'mdi-swap-horizontal', 'manage_share_transfer.php'],
                    ['Share conversions to complete', $c("SELECT COUNT(*) FROM fudscoops_shares_conversion WHERE share_conversion_is_deleted = 0 AND chairman_approval = 1 AND conversion_status = 0"), 'mdi-sync', 'manage_share_conversion.php'],
                    ['Loan applications to review', $c("SELECT COUNT(*) FROM fudscoops_loan WHERE guarantor_status = 1 AND loan_status = 'pending'"), 'mdi-cash-multiple', 'loans.php'],
                    ['Loans to pay out', $c("SELECT COUNT(*) FROM fudscoops_loan WHERE loan_status = 'Approve' AND chairman_approval = 1"), 'mdi-bank-transfer', 'loans.php'],
                    $overdue,
                    ['Loan overpayments to settle', $c("SELECT COUNT(*) FROM fudscoops_loan_overpayments WHERE status = 'chosen'"), 'mdi-cash-refund', 'overpayments.php'],
                    ['Commodity requests to review', $c("SELECT COUNT(*) FROM fudscoops_commodity_loan_applications WHERE status = 'pending'"), 'mdi-cart-outline', 'view_commodity_request.php?stage=pending'],
                    ['Commodity to supply', $c("SELECT COUNT(*) FROM fudscoops_commodity_loan_applications WHERE status = 'authorized'"), 'mdi-truck', 'view_commodity_request.php?stage=authorized'],
                ];
            case 'secretary':
                return [
                    ['Share purchases to endorse', $c("SELECT COUNT(*) FROM fudscoops_shares WHERE entry_type = 'purchase' AND share_status = 0 AND secretary_endorsement = 0 AND shares_is_deleted = 0"), 'mdi-chart-pie', 'manage_share_purchase.php'],
                    ['Share transfers to endorse', $c("SELECT COUNT(*) FROM fudscoops_shares_transfer WHERE share_transfer_is_deleted = 0 AND is_accepted = 1 AND share_transfer_status = 0 AND secretary_endorsement = 0"), 'mdi-swap-horizontal', 'manage_share_transfer.php'],
                    ['Membership applications in progress', $c("SELECT COUNT(*) FROM fudscoops_member WHERE NOT (treasurer_approval = 1 AND chairman_approval = 1) AND is_deleted = 0"), 'mdi-account-plus', 'member_list.php'],
                    ['Loan applications in progress', $c("SELECT COUNT(*) FROM fudscoops_loan WHERE loan_status IN ('pending', 'Recommended', 'Approve') AND guarantor_status <> -1"), 'mdi-cash-multiple', 'loans.php'],
                    $overdue,
                    ['Commodity requests in progress', $c("SELECT COUNT(*) FROM fudscoops_commodity_loan_applications WHERE status IN ('pending', 'recommended', 'authorized')"), 'mdi-cart-outline', 'view_commodity_request.php'],
                ];
        }
        return [];
    }

    // ---------- Personal notices (any logged-in staff) ----------

    // Loans this person is asked to guarantee and share transfers offered to them
    public function personalRequests($employeeId)
    {
        return [
            'guarantees' => (int) $this->value("SELECT COUNT(*) FROM fudscoops_loan WHERE guarantor_employee_id = :e AND guarantor_status = 0", [':e' => $employeeId]),
            'transfers' => (int) $this->value("SELECT COUNT(*) FROM fudscoops_shares_transfer WHERE received_employee_id = :e AND is_accepted = 0
                                                AND share_transfer_status = 0 AND share_transfer_is_deleted = 0", [':e' => $employeeId]),
        ];
    }

    // Loans fully repaid in the last 60 days: the member's own, and loans this person guaranteed (released)
    public function recentlyRepaid($memberId, $employeeId)
    {
        $since = date('Y-m-d', strtotime('-60 days'));
        $own = $this->con->prepare("SELECT l.loan_id, l.repaid_date, t.loan_type_name FROM fudscoops_loan l
                                    LEFT JOIN fudscoops_loan_type t ON t.loan_type_id = l.loan_type_id
                                    WHERE l.member_id = :m AND l.loan_status = 'Repaid' AND l.repaid_date >= :since");
        $own->execute([':m' => $memberId, ':since' => $since]);
        $guaranteed = $this->con->prepare("SELECT l.loan_id, l.repaid_date, e.fname, e.lname FROM fudscoops_loan l
                                           JOIN fudscoops_member m ON m.member_id = l.member_id JOIN employee e ON e.employee_id = m.employee_id
                                           WHERE l.guarantor_employee_id = :e AND l.loan_status = 'Repaid' AND l.repaid_date >= :since");
        $guaranteed->execute([':e' => $employeeId, ':since' => $since]);
        return ['own' => $own->fetchAll(PDO::FETCH_ASSOC), 'guaranteed' => $guaranteed->fetchAll(PDO::FETCH_ASSOC)];
    }

    // ---------- Member dashboard ----------

    public function memberLoans($memberId)
    {
        require_once('MemberG3.php');
        $stmt = $this->con->prepare("
            SELECT l.*, t.loan_type_name,
                   COALESCE(l.total_payable, NULLIF(l.amount_recommended, 0), l.loan_amount) + 0 AS amount,
                   (SELECT COALESCE(SUM(r.amount_paid), 0) FROM fudscoops_loan_repayments r
                     WHERE r.loan_id = l.loan_id AND r.status = 1 AND COALESCE(r.is_deleted, 0) = 0) AS repaid
            FROM fudscoops_loan l LEFT JOIN fudscoops_loan_type t ON t.loan_type_id = l.loan_type_id
            WHERE l.member_id = :m ORDER BY l.loan_id DESC");
        $stmt->execute([':m' => $memberId]);
        $loans = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($loans as &$loan) {
            $loan['stage'] = MemberG3::loanStageLabel($loan);
            $loan['outstanding'] = $loan['stage'] === 'Repaying' ? max(0, $loan['amount'] - $loan['repaid']) : 0;
        }
        return $loans;
    }

    // Requests the member is waiting on
    // A member's savings statement (R/savings/savings_history.php): every saving and approved withdrawal, oldest first,
    // with a running balance (the same sums as LoanControls::savingsBalance), plus requests still pending.
    public function memberStatement($memberId, $employeeId)
    {
        $lines = [];
        // Overpayments the Treasurer added to savings (LoanControls::settleOverpayment saves them as a dated savings row)
        $stmt = $this->con->prepare("SELECT loan_id, amount, DATE(settled_at) d FROM fudscoops_loan_overpayments
                                     WHERE member_id = :m AND status = 'settled' AND member_choice = 'savings'");
        $stmt->execute([':m' => $memberId]);
        $overpaid = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = $this->con->prepare("SELECT savings_id, savings_amount, entry_type, month, year, savings_date FROM fudscoops_savings WHERE member_idmember = :m");
        $stmt->execute([':m' => $memberId]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $s) {
            $monthStart = sprintf('%04d-%02d-01', (int) $s['year'], (int) $s['month']);
            // Monthly savings uploaded without a date are placed at the start of their month
            $date = $s['savings_date'] ?: $monthStart;
            $label = $s['entry_type'] === 'opening' ? 'Opening balance (savings before the portal), as at ' . date('F Y', strtotime($monthStart))
                : 'Savings for ' . date('F Y', strtotime($monthStart));
            foreach ($overpaid as $k => $o) {
                if ($s['savings_date'] === $o['d'] && abs($s['savings_amount'] - round($o['amount'], 2)) < 0.01) {
                    $label = 'Loan overpayment added to savings (loan #' . (int) $o['loan_id'] . ')';
                    unset($overpaid[$k]);
                    break;
                }
            }
            $lines[] = ['date' => $date, 'sort' => $date . ' 0 ' . str_pad($s['savings_id'], 10, '0', STR_PAD_LEFT),
                        'label' => $label, 'exact' => (bool) $s['savings_date'],
                        'credit' => (float) $s['savings_amount'], 'debit' => 0, 'kind' => 'saving'];
        }
        $stmt = $this->con->prepare("SELECT withrawals_id, approved_withrawal_amount, approved_date, proposed_date, account_name, acount_number
                                     FROM fudscoops_withrawals WHERE member_id = :m AND chairman_approval = 1");
        $stmt->execute([':m' => $memberId]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $w) {
            $date = substr($w['approved_date'] ?: $w['proposed_date'], 0, 10);
            $lines[] = ['date' => $date, 'sort' => $date . ' 1 ' . str_pad($w['withrawals_id'], 10, '0', STR_PAD_LEFT),
                        'label' => 'Withdrawal' . ($w['acount_number'] ? ' to account ' . $w['acount_number'] : ''), 'exact' => true,
                        'credit' => 0, 'debit' => (float) $w['approved_withrawal_amount'], 'kind' => 'withdrawal'];
        }
        $stmt = $this->con->prepare("SELECT comp_withdrwal_id, approved_amount, amount_owed, settled_at, date_approved, date_applied
                                     FROM fudscoops_complete_withrawal WHERE employee_id = :e AND chairman_approval = 1
                                       AND COALESCE(complete_withrawal_is_deleted, 0) = 0");
        $stmt->execute([':e' => $employeeId]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $c) {
            $date = substr($c['date_approved'] ?: $c['date_applied'], 0, 10);
            $lines[] = ['date' => $date, 'sort' => $date . ' 2 ' . str_pad($c['comp_withdrwal_id'], 10, '0', STR_PAD_LEFT),
                        'label' => 'Complete withdrawal', 'exact' => true, 'credit' => 0, 'debit' => (float) $c['approved_amount'], 'kind' => 'withdrawal'];
            if ($c['settled_at'] && (float) $c['amount_owed'] > 0) {
                $lines[] = ['date' => $date, 'sort' => $date . ' 3 ' . str_pad($c['comp_withdrwal_id'], 10, '0', STR_PAD_LEFT),
                            'label' => 'Loans and commodity paid from savings', 'exact' => true, 'credit' => 0, 'debit' => (float) $c['amount_owed'], 'kind' => 'withdrawal'];
            }
        }
        usort($lines, function ($a, $b) { return strcmp($a['sort'], $b['sort']); });
        $balance = 0;
        foreach ($lines as &$l) {
            $balance = round($balance + $l['credit'] - $l['debit'], 2);
            $l['balance'] = $balance;
        }
        unset($l);

        // Not yet in the balance: withdrawal requests awaiting approval, and overpayments the member chose to add to savings
        $pending = [];
        $stmt = $this->con->prepare("SELECT proposed_withrawal_amount, approved_withrawal_amount, proposed_date FROM fudscoops_withrawals
                                     WHERE member_id = :m AND chairman_approval = 0 AND status <> -1 AND COALESCE(withrawals_is_deleted, 0) = 0");
        $stmt->execute([':m' => $memberId]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $w) {
            $pending[] = ['label' => 'Withdrawal request awaiting approval', 'date' => substr($w['proposed_date'], 0, 10),
                          'amount' => -(float) ($w['approved_withrawal_amount'] > 0 ? $w['approved_withrawal_amount'] : $w['proposed_withrawal_amount'])];
        }
        $stmt = $this->con->prepare("SELECT amount, chosen_at FROM fudscoops_loan_overpayments WHERE member_id = :m AND status = 'chosen' AND member_choice = 'savings'");
        $stmt->execute([':m' => $memberId]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $o) {
            $pending[] = ['label' => 'Loan overpayment to be added to savings (waiting for the Treasurer)', 'date' => substr($o['chosen_at'], 0, 10),
                          'amount' => (float) $o['amount']];
        }
        return ['lines' => $lines, 'pending' => $pending, 'balance' => $balance];
    }

    // Savings withdrawal requests the Treasurer rejected in the last 60 days, with the reason (member notices)
    public function recentWithdrawalRejections($memberId)
    {
        // Rejected by the Treasurer (status -1) or by the Chairman (chairman_approval -1); reason = the one who rejected it
        $stmt = $this->con->prepare("SELECT withrawals_id, proposed_withrawal_amount, proposed_date,
                                            IF(chairman_approval = -1, chairman_comment, treasurer_comment) AS reason,
                                            IF(chairman_approval = -1, 'Chairman', 'Treasurer') AS rejected_by,
                                            IF(chairman_approval = -1, chairman_decided_at, treasurer_decided_at) AS decided_at
                                     FROM fudscoops_withrawals
                                     WHERE member_id = :m AND (status = -1 OR chairman_approval = -1) AND COALESCE(withrawals_is_deleted, 0) = 0
                                     HAVING decided_at >= NOW() - INTERVAL 60 DAY ORDER BY decided_at DESC");
        $stmt->execute([':m' => $memberId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Figures for the member's savings pages (R/savings/*.php, R/complete_withdrawal.php)
    public function memberSavingsInfo($memberId, $employeeId)
    {
        $target = $this->con->prepare("SELECT savings_amount, saving_period, withdraw_period, date_saved, guarantor_status, chairman_approval
                                       FROM fudscoops_Target_saving WHERE member_id = :m AND Target_saving_is_deleted = 0
                                       ORDER BY target_saving_id DESC LIMIT 1");
        $target->execute([':m' => $memberId]);
        return [
            'monthly' => (float) $this->value("SELECT proposed_monthly_savings FROM fudscoops_member WHERE member_id = :m", [':m' => $memberId]),
            'pending_update' => $this->value("SELECT u.savings_update_amount FROM fudscoops_update_savings u
                                              WHERE u.employee_id = :e AND u.is_treasurer_approved = 0 AND u.is_secretary_approved IN (0, 1)
                                              ORDER BY u.id DESC LIMIT 1", [':e' => $employeeId]),
            'pending_withdrawal' => $this->value("SELECT proposed_withrawal_amount FROM fudscoops_withrawals WHERE member_id = :m AND chairman_approval = 0 AND status <> -1
                                                  AND COALESCE(withrawals_is_deleted, 0) = 0 ORDER BY withrawals_id DESC LIMIT 1", [':m' => $memberId]),
            'pending_complete' => (int) $this->value("SELECT COUNT(*) FROM fudscoops_complete_withrawal WHERE employee_id = :e AND chairman_approval = 0 AND COALESCE(complete_withrawal_is_deleted, 0) = 0", [':e' => $employeeId]) > 0,
            'target' => $target->fetch(PDO::FETCH_ASSOC) ?: null,
            // A complete withdrawal the Treasurer rejected in the last 60 days, with the reason
            'rejected_complete' => $this->row("SELECT reason AS treasurer_comment, rejected_by, decided_at, date_applied FROM (
                                                 SELECT COALESCE(chairman_comment, treasurer_comment) AS reason,
                                                        IF(chairman_decided_at IS NOT NULL, 'Chairman', 'Treasurer') AS rejected_by,
                                                        COALESCE(chairman_decided_at, treasurer_decided_at) AS decided_at, date_applied
                                                 FROM fudscoops_complete_withrawal
                                                 WHERE employee_id = :e AND chairman_approval = -1 AND COALESCE(complete_withrawal_is_deleted, 0) = 0) r
                                               WHERE decided_at >= NOW() - INTERVAL 60 DAY ORDER BY decided_at DESC LIMIT 1", [':e' => $employeeId]),
        ];
    }

    public function memberPending($memberId)
    {
        $items = [];
        $purchase = $this->value("SELECT amount_paid FROM fudscoops_shares WHERE member_id = :m AND entry_type = 'purchase' AND share_status = 0 AND shares_is_deleted = 0 LIMIT 1", [':m' => $memberId]);
        if ($purchase) {
            $items[] = ['Share purchase of ₦' . number_format($purchase) . ' awaiting approval', 'mdi-chart-pie'];
        }
        $conversion = $this->value("SELECT amount_paid FROM fudscoops_shares_conversion WHERE member_id = :m AND conversion_status = 0 AND share_conversion_is_deleted = 0 LIMIT 1", [':m' => $memberId]);
        if ($conversion) {
            $items[] = ['Conversion of ₦' . number_format((float) $conversion) . ' savings to shares awaiting approval', 'mdi-sync'];
        }
        $update = $this->value("SELECT u.savings_update_amount FROM fudscoops_update_savings u JOIN fudscoops_member m ON m.employee_id = u.employee_id
                                WHERE m.member_id = :m AND u.is_treasurer_approved = 0 AND u.is_secretary_approved IN (0, 1) ORDER BY u.id DESC LIMIT 1", [':m' => $memberId]);
        if ($update) {
            $items[] = ['Change of monthly savings to ₦' . number_format($update) . ' awaiting approval', 'mdi-cash-refund'];
        }
        $withdrawals = (int) $this->value("SELECT COUNT(*) FROM fudscoops_withrawals WHERE member_id = :m AND chairman_approval = 0 AND status <> -1 AND COALESCE(withrawals_is_deleted, 0) = 0", [':m' => $memberId]);
        if ($withdrawals) {
            $items[] = [$withdrawals . ' savings withdrawal request(s) awaiting approval', 'mdi-bank-transfer-out'];
        }
        return $items;
    }

    // ---------- Applicant dashboard ----------

    // Membership application progress: 0 not applied, 1 awaiting Treasurer, 2 awaiting Chairman, 3 member
    public function applicationStage($employeeId)
    {
        $stmt = $this->con->prepare("SELECT treasurer_approval, chairman_approval, proposed_monthly_savings, reg_date FROM fudscoops_member WHERE employee_id = :e");
        $stmt->execute([':e' => $employeeId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return [0, null];
        }
        if ($row['treasurer_approval'] == 1 && $row['chairman_approval'] == 1) {
            return [3, $row];
        }
        return [$row['treasurer_approval'] == 1 ? 2 : 1, $row];
    }
}
