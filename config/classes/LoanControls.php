<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once('DB.php');
require_once('Settings.php');

/*
 * Loan controls (2026-10-14; db/2026-10-14_loan_controls.sql)
 *
 * Overdue loans
 *   A month counts once the Treasurer has recorded deductions for it: a month is "processed" for a loan
 *   type when any loan of that type has a deduction in that month. For each loan being repaid, every
 *   processed month from its first deduction month is due one instalment (the last one only what is left).
 *   A loan is overdue when what it has repaid is less than what was due (arrears), and the months where
 *   less than the instalment came in are counted as missed. Months nobody has uploaded never count, so a
 *   month not yet uploaded does not make every loan overdue.
 *
 * Over-repayments
 *   When deductions pass what a loan owes, the excess is recorded in fudscoops_loan_overpayments (open).
 *   The member chooses: add it to savings, pay one of their other loans, or a refund to a bank account
 *   (chosen). The Treasurer settles it (settled). Re-uploading a month so the excess changes updates or
 *   removes an unsettled record.
 *
 * Leaving members (complete withdrawal)
 *   Blocked while the member is guaranteeing someone else's open loan, or has a loan or commodity request
 *   that is still being decided or awaiting payment/supply. Loans and commodity being repaid must be
 *   repaid first, or the member chooses to settle them from savings (posted when the Chairman approves).
 */
class LoanControls
{
    private static function con()
    {
        $con = (new DB())->getConnection();
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $con;
    }

    private static function totalOf($loan)
    {
        if (!empty($loan['total_payable'])) {
            return (float) $loan['total_payable'];
        }
        return (float) (!empty($loan['amount_recommended']) ? $loan['amount_recommended'] : $loan['loan_amount']);
    }

    private static function repaidOf($con, $loanId)
    {
        $stmt = $con->prepare("SELECT COALESCE(SUM(amount_paid), 0) FROM fudscoops_loan_repayments
                               WHERE loan_id = :id AND status = 1 AND COALESCE(is_deleted, 0) = 0");
        $stmt->execute([':id' => $loanId]);
        return (float) $stmt->fetchColumn();
    }

    // ================================================================ overdue

    // Loans behind on repayment. $memberId limits it to one member.
    public static function overdueLoans($memberId = null)
    {
        $con = self::con();
        $sql = "SELECT l.loan_id, l.member_id, l.loan_type_id, l.loan_status, l.guarantor_employee_id, l.total_payable, l.amount_recommended, l.loan_amount,
                       l.repayment_start, l.loan_date, t.loan_type_name, e.sp_no, e.fname, e.lname, e.oname,
                       (SELECT CAST(REPLACE(r.loan_repayment_amount, ',', '') AS DECIMAL(14,2)) FROM fudscoops_loan_repayments r
                         WHERE r.loan_id = l.loan_id AND r.status = 0 AND COALESCE(r.is_deleted, 0) = 0
                         ORDER BY r.loan_repayment_id DESC LIMIT 1) AS monthly
                FROM fudscoops_loan l
                LEFT JOIN fudscoops_loan_type t ON t.loan_type_id = l.loan_type_id
                LEFT JOIN fudscoops_member m ON m.member_id = l.member_id
                LEFT JOIN employee e ON e.employee_id = m.employee_id
                WHERE l.loan_status IN ('Disbursed', 'Approved')";
        $params = [];
        if ($memberId !== null) {
            $sql .= " AND l.member_id = :m";
            $params[':m'] = $memberId;
        }
        $stmt = $con->prepare($sql);
        $stmt->execute($params);
        $loans = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$loans) {
            return [];
        }

        // Months with deductions recorded, per loan type, and each loan's deductions per month
        $processed = [];
        foreach ($con->query("SELECT l.loan_type_id, DATE_FORMAT(r.loan_repayment_date, '%Y-%m') ym
                              FROM fudscoops_loan_repayments r JOIN fudscoops_loan l ON l.loan_id = r.loan_id
                              WHERE r.status = 1 AND COALESCE(r.is_deleted, 0) = 0 GROUP BY 1, 2")->fetchAll(PDO::FETCH_ASSOC) as $p) {
            $processed[$p['loan_type_id']][] = $p['ym'];
        }
        $paid = [];
        foreach ($con->query("SELECT loan_id, DATE_FORMAT(loan_repayment_date, '%Y-%m') ym, SUM(amount_paid) amt
                              FROM fudscoops_loan_repayments WHERE status = 1 AND COALESCE(is_deleted, 0) = 0 GROUP BY 1, 2")->fetchAll(PDO::FETCH_ASSOC) as $p) {
            $paid[$p['loan_id']][$p['ym']] = (float) $p['amt'];
        }

        $now = date('Y-m');
        $overdue = [];
        foreach ($loans as $loan) {
            $total = self::totalOf($loan);
            $monthly = (float) $loan['monthly'];
            if ($monthly <= 0 || $total <= 0) {
                continue;
            }
            $start = substr($loan['repayment_start'] ?: $loan['loan_date'], 0, 7);
            $months = isset($processed[$loan['loan_type_id']]) ? $processed[$loan['loan_type_id']] : [];
            sort($months);
            $due = 0;
            $missed = [];
            foreach ($months as $ym) {
                if ($ym < $start || $ym > $now || $due >= $total - 0.005) {
                    continue;
                }
                $instalment = min($monthly, $total - $due);
                $due += $instalment;
                $got = isset($paid[$loan['loan_id']][$ym]) ? $paid[$loan['loan_id']][$ym] : 0;
                if ($got + 0.5 < $instalment) {
                    $missed[] = $ym;
                }
            }
            $repaid = isset($paid[$loan['loan_id']]) ? array_sum($paid[$loan['loan_id']]) : 0;
            $arrears = round($due - $repaid, 2);
            if ($arrears > 0.5) {
                $loan['due'] = $due;
                $loan['repaid'] = $repaid;
                $loan['arrears'] = $arrears;
                $loan['balance'] = max(0, $total - $repaid);
                $loan['missed'] = $missed;
                $loan['monthly'] = $monthly;
                $loan['last_paid'] = isset($paid[$loan['loan_id']]) ? max(array_keys($paid[$loan['loan_id']])) : null;
                $overdue[] = $loan;
            }
        }
        usort($overdue, function ($a, $b) { return $b['arrears'] < $a['arrears'] ? -1 : ($b['arrears'] > $a['arrears'] ? 1 : 0); });
        return $overdue;
    }

    // Overdue loans this staff member guaranteed (guarantors are told on their member dashboard)
    public static function overdueGuaranteed($employeeId)
    {
        return array_values(array_filter(self::overdueLoans(), function ($loan) use ($employeeId) {
            return (int) $loan['guarantor_employee_id'] === (int) $employeeId;
        }));
    }

    // Officer list of overdue loans. $detailsPage: where the loan link goes for this role.
    public static function overdueTable($detailsPage = 'view_loan_details.php')
    {
        $rows = self::overdueLoans();
        if (!$rows) {
            return '<p class="fud-empty">No loan is behind on its deductions.</p>';
        }
        $h = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
        $html = '<div class="table-responsive"><table class="table fud-table"><thead><tr><th>Member</th><th>Loan</th>'
              . '<th class="num">Monthly (₦)</th><th class="num">Due so far (₦)</th><th class="num">Repaid (₦)</th><th class="num">Behind by (₦)</th>'
              . '<th>Missed or short months</th><th>Last deduction</th><th></th></tr></thead><tbody>';
        foreach ($rows as $r) {
            $months = array_map(function ($ym) { return date('M Y', strtotime($ym . '-01')); }, $r['missed']);
            $html .= '<tr><td>' . $h(trim($r['fname'] . ' ' . $r['lname'])) . '<div class="text-muted small">' . $h($r['sp_no']) . '</div></td>'
                   . '<td>' . $h($r['loan_type_name']) . ' #' . (int) $r['loan_id'] . '</td>'
                   . '<td class="num">' . number_format($r['monthly'], 2) . '</td>'
                   . '<td class="num">' . number_format($r['due'], 2) . '</td>'
                   . '<td class="num">' . number_format($r['repaid'], 2) . '</td>'
                   . '<td class="num"><span class="fud-pill no">' . number_format($r['arrears'], 2) . '</span></td>'
                   . '<td>' . ($months ? $h(implode(', ', $months)) : '–') . '</td>'
                   . '<td>' . ($r['last_paid'] ? date('M Y', strtotime($r['last_paid'] . '-01')) : 'none') . '</td>'
                   . '<td><a class="btn btn-sm btn-outline-primary" href="' . $detailsPage . '?loan_id=' . (int) $r['loan_id'] . '">Open</a></td></tr>';
        }
        return $html . '</tbody></table></div>';
    }

    // ================================================================ over-repayments

    // Called after any deduction is recorded: keep the loan's unsettled excess in step with its deductions
    public static function syncOverpayment($loanId)
    {
        $con = self::con();
        $stmt = $con->prepare("SELECT loan_id, member_id, total_payable, amount_recommended, loan_amount FROM fudscoops_loan WHERE loan_id = :id");
        $stmt->execute([':id' => $loanId]);
        $loan = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$loan) {
            return 0;
        }
        $stmt = $con->prepare("SELECT COALESCE(SUM(amount), 0) FROM fudscoops_loan_overpayments WHERE loan_id = :id AND status = 'settled'");
        $stmt->execute([':id' => $loanId]);
        $excess = round(self::repaidOf($con, $loanId) - self::totalOf($loan) - (float) $stmt->fetchColumn(), 2);

        $stmt = $con->prepare("SELECT overpayment_id, amount FROM fudscoops_loan_overpayments WHERE loan_id = :id AND status IN ('open', 'chosen') LIMIT 1");
        $stmt->execute([':id' => $loanId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($excess > 0.005) {
            if ($row) {
                if (abs($row['amount'] - $excess) > 0.005) {
                    $con->prepare("UPDATE fudscoops_loan_overpayments SET amount = :a, updated_at = NOW() WHERE overpayment_id = :id")
                        ->execute([':a' => $excess, ':id' => $row['overpayment_id']]);
                }
            } else {
                $con->prepare("INSERT INTO fudscoops_loan_overpayments (loan_id, member_id, amount, status) VALUES (:l, :m, :a, 'open')")
                    ->execute([':l' => $loanId, ':m' => $loan['member_id'], ':a' => $excess]);
            }
            return $excess;
        }
        if ($row) {
            $con->prepare("DELETE FROM fudscoops_loan_overpayments WHERE overpayment_id = :id")->execute([':id' => $row['overpayment_id']]);
        }
        return 0;
    }

    // $status: null = not settled (open or chosen); or a status; $memberId limits to one member
    public static function overpayments($status = null, $memberId = null)
    {
        $sql = "SELECT o.*, t.loan_type_name, e.sp_no, e.fname, e.lname, tt.loan_type_name AS target_type
                FROM fudscoops_loan_overpayments o
                JOIN fudscoops_loan l ON l.loan_id = o.loan_id
                LEFT JOIN fudscoops_loan_type t ON t.loan_type_id = l.loan_type_id
                LEFT JOIN fudscoops_member m ON m.member_id = o.member_id
                LEFT JOIN employee e ON e.employee_id = m.employee_id
                LEFT JOIN fudscoops_loan tl ON tl.loan_id = o.target_loan_id
                LEFT JOIN fudscoops_loan_type tt ON tt.loan_type_id = tl.loan_type_id
                WHERE " . ($status === null ? "o.status IN ('open', 'chosen')" : "o.status = :st");
        $params = $status === null ? [] : [':st' => $status];
        if ($memberId !== null) {
            $sql .= " AND o.member_id = :m";
            $params[':m'] = $memberId;
        }
        $stmt = self::con()->prepare($sql . " ORDER BY o.overpayment_id DESC");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function overpayment($id)
    {
        $stmt = self::con()->prepare("SELECT o.*, t.loan_type_name FROM fudscoops_loan_overpayments o
                                      JOIN fudscoops_loan l ON l.loan_id = o.loan_id
                                      LEFT JOIN fudscoops_loan_type t ON t.loan_type_id = l.loan_type_id
                                      WHERE o.overpayment_id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // The member's loans an overpayment can go to (being repaid, with a balance)
    public static function loansToPay($memberId)
    {
        $con = self::con();
        $stmt = $con->prepare("SELECT l.*, t.loan_type_name FROM fudscoops_loan l LEFT JOIN fudscoops_loan_type t ON t.loan_type_id = l.loan_type_id
                               WHERE l.member_id = :m AND l.loan_status IN ('Disbursed', 'Approved')");
        $stmt->execute([':m' => $memberId]);
        $out = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $loan) {
            $loan['balance'] = round(self::totalOf($loan) - self::repaidOf($con, $loan['loan_id']), 2);
            if ($loan['balance'] > 0.005) {
                $out[] = $loan;
            }
        }
        return $out;
    }

    // Member's choice. $choice: savings | loan | refund. Returns 1 or a message.
    public static function chooseOverpayment($id, $memberId, $choice, $targetLoanId, $bank, $accountNumber, $accountName)
    {
        $op = self::overpayment($id);
        if (!$op || (int) $op['member_id'] !== (int) $memberId) {
            return 'Overpayment not found.';
        }
        if ($op['status'] === 'settled') {
            return 'The Treasurer has already settled this overpayment.';
        }
        $target = null;
        $bank = trim((string) $bank);
        $accountNumber = trim((string) $accountNumber);
        $accountName = trim((string) $accountName);
        if ($choice === 'loan') {
            foreach (self::loansToPay($memberId) as $loan) {
                if ((int) $loan['loan_id'] === (int) $targetLoanId) {
                    $target = (int) $targetLoanId;
                }
            }
            if (!$target) {
                return 'Choose one of your loans that is being repaid.';
            }
        } elseif ($choice === 'refund') {
            if ($bank === '' || $accountName === '' || !preg_match('/^\d{10}$/', $accountNumber)) {
                return 'Enter your bank, 10-digit account number and account name for the refund.';
            }
        } elseif ($choice !== 'savings') {
            return 'Choose what to do with the overpayment.';
        }
        self::con()->prepare("UPDATE fudscoops_loan_overpayments
                              SET member_choice = :c, target_loan_id = :t, bank_name = :b, account_number = :n, account_name = :an,
                                  status = 'chosen', chosen_at = NOW(), updated_at = NOW()
                              WHERE overpayment_id = :id AND status IN ('open', 'chosen')")
            ->execute([':c' => $choice, ':t' => $target, ':b' => $choice === 'refund' ? $bank : null,
                       ':n' => $choice === 'refund' ? $accountNumber : null, ':an' => $choice === 'refund' ? $accountName : null, ':id' => $id]);
        return 1;
    }

    // Treasurer carries out the member's choice. $note: transfer reference for refunds. Returns 1 or a message.
    public static function settleOverpayment($id, $note, $by)
    {
        $op = self::overpayment($id);
        if (!$op || $op['status'] !== 'chosen') {
            return $op && $op['status'] === 'open' ? 'The member has not chosen what to do with this overpayment yet.' : 'This overpayment is not awaiting settlement.';
        }
        $note = trim((string) $note);
        if ($op['member_choice'] === 'refund' && $note === '') {
            return 'Enter the transfer reference for the refund.';
        }
        $con = self::con();
        try {
            $con->beginTransaction();
            if ($op['member_choice'] === 'savings') {
                $con->prepare("INSERT INTO fudscoops_savings (savings_amount, month, year, savings_date, savings_is_deleted, member_idmember)
                               VALUES (:a, :mo, :y, CURDATE(), 0, :m)")
                    ->execute([':a' => round((float) $op['amount'], 2), ':mo' => date('n'), ':y' => date('Y'), ':m' => $op['member_id']]);
                $note = trim('Added to savings. ' . $note);
            } elseif ($op['member_choice'] === 'loan') {
                $result = self::creditLoan($con, $op['target_loan_id'], $op['amount'], 'overpayment #' . $id);
                if ($result !== 1) {
                    $con->rollBack();
                    return $result;
                }
                $note = trim('Paid to loan #' . (int) $op['target_loan_id'] . '. ' . $note);
            } else {
                $note = 'Refunded, ref ' . $note;
            }
            $con->prepare("UPDATE fudscoops_loan_overpayments SET status = 'settled', settled_by = :by, settled_at = NOW(), settle_note = :n, updated_at = NOW()
                           WHERE overpayment_id = :id")->execute([':by' => $by, ':n' => $note, ':id' => $id]);
            $con->commit();
        } catch (Exception $e) {
            if ($con->inTransaction()) {
                $con->rollBack();
            }
            error_log('Settle overpayment: ' . $e->getMessage());
            return 'Could not settle the overpayment. Please try again.';
        }
        if ($op['member_choice'] === 'loan') {
            self::afterCredit($op['target_loan_id']);
        }
        return 1;
    }

    // Adds a payment to a loan's deductions for the current month (on top of any deduction already there)
    public static function creditLoan($con, $loanId, $amount, $why = '')
    {
        $stmt = $con->prepare("SELECT loan_id, member_id, loan_status FROM fudscoops_loan WHERE loan_id = :id");
        $stmt->execute([':id' => $loanId]);
        $loan = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$loan || !in_array($loan['loan_status'], ['Disbursed', 'Approved'], true)) {
            return 'The loan to pay is no longer being repaid.';
        }
        $stmt = $con->prepare("SELECT loan_repayment_id FROM fudscoops_loan_repayments
                               WHERE loan_id = :id AND status = 1 AND COALESCE(is_deleted, 0) = 0
                                 AND DATE_FORMAT(loan_repayment_date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m') LIMIT 1");
        $stmt->execute([':id' => $loanId]);
        $existing = $stmt->fetchColumn();
        if ($existing) {
            $con->prepare("UPDATE fudscoops_loan_repayments SET amount_paid = amount_paid + :a, date_paid = CURDATE() WHERE loan_repayment_id = :id")
                ->execute([':a' => $amount, ':id' => $existing]);
        } else {
            $con->prepare("INSERT INTO fudscoops_loan_repayments (loan_id, member_id, amount_paid, loan_repayment_date, date_paid, status, loan_repayment_amount, is_deleted)
                           VALUES (:id, :m, :a, CURDATE(), CURDATE(), 1, 0, 0)")
                ->execute([':id' => $loanId, ':m' => $loan['member_id'], ':a' => $amount]);
        }
        return 1;
    }

    // After money is added to a loan outside saveRepayment: fully repaid? overpaid?
    public static function afterCredit($loanId)
    {
        require_once('MemberG3.php');
        (new MemberG3())->updateRepaidStatus($loanId);
        self::syncOverpayment($loanId);
    }

    // ================================================================ withdrawals

    // Savings that must stay in the account after any withdrawal (Savings Settings: withdrawal_min_balance)
    public static function withdrawalMinBalance()
    {
        require_once('SavingsG1.php');
        return (float) Settings::get('withdrawal_min_balance', SavingsG1::getMinMonthlySavings());
    }

    // Savings less approved withdrawals and approved complete withdrawals (same as MemberG3::memberSavingsBalance)
    public static function savingsBalance($memberId)
    {
        $stmt = self::con()->prepare("SELECT
                (SELECT COALESCE(SUM(savings_amount), 0) FROM fudscoops_savings WHERE member_idmember = :m1)
              - (SELECT COALESCE(SUM(approved_withrawal_amount), 0) FROM fudscoops_withrawals WHERE member_id = :m2 AND chairman_approval = 1)
              - (SELECT COALESCE(SUM(c.approved_amount + CASE WHEN c.settled_at IS NOT NULL THEN c.amount_owed ELSE 0 END), 0)
                   FROM fudscoops_complete_withrawal c JOIN fudscoops_member cm ON cm.employee_id = c.employee_id
                  WHERE cm.member_id = :m3 AND c.chairman_approval = 1 AND COALESCE(c.complete_withrawal_is_deleted, 0) = 0)");
        $stmt->execute([':m1' => $memberId, ':m2' => $memberId, ':m3' => $memberId]);
        return round((float) $stmt->fetchColumn(), 2);
    }

    // Most a member can take out now: balance, less anything being paid from savings, less the minimum balance.
    // $keepMinimum = false for complete withdrawals: a leaving member is paid everything that is left.
    public static function maxWithdrawal($memberId, $alsoFromSavings = 0, $keepMinimum = true)
    {
        return max(0, round(self::savingsBalance($memberId) - $alsoFromSavings - ($keepMinimum ? self::withdrawalMinBalance() : 0), 2));
    }

    // Message when $amount is more than a member may withdraw, otherwise null
    public static function withdrawalLimitError($memberId, $amount, $alsoFromSavings = 0, $keepMinimum = true)
    {
        $max = self::maxWithdrawal($memberId, $alsoFromSavings, $keepMinimum);
        if ((float) $amount > $max + 0.005) {
            $why = [];
            if ($keepMinimum) {
                $why[] = '₦' . number_format(self::withdrawalMinBalance(), 2) . ' must stay in savings';
            }
            if ($alsoFromSavings > 0) {
                $why[] = '₦' . number_format($alsoFromSavings, 2) . ' goes to loans or commodity owed';
            }
            return 'At most ₦' . number_format($max, 2) . ' can be withdrawn' . ($why ? ': ' . implode(', and ', $why) : '') . '.';
        }
        return null;
    }

    // ================================================================ leaving members

    // What stops a member leaving, and what they owe. $savings: current savings balance.
    public static function leavingObligations($memberId, $employeeId, $savings)
    {
        $con = self::con();
        $blockers = [];
        $owed = [];

        $stmt = $con->prepare("SELECT l.*, t.loan_type_name FROM fudscoops_loan l LEFT JOIN fudscoops_loan_type t ON t.loan_type_id = l.loan_type_id
                               WHERE l.member_id = :m AND l.guarantor_status <> -1 AND l.loan_status NOT IN ('Disapprove', 'Repaid')");
        $stmt->execute([':m' => $memberId]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $loan) {
            if (in_array($loan['loan_status'], ['Disbursed', 'Approved'], true)) {
                $balance = round(self::totalOf($loan) - self::repaidOf($con, $loan['loan_id']), 2);
                if ($balance > 0.005) {
                    $owed[] = ['kind' => 'loan', 'id' => (int) $loan['loan_id'], 'label' => $loan['loan_type_name'] . ' #' . $loan['loan_id'], 'balance' => $balance];
                }
            } else {
                $blockers[] = 'Your ' . $loan['loan_type_name'] . ' application is still in progress. Wait for the decision, or ask the Treasurer to decline it.';
            }
        }

        require_once('CommodityLoan.php');
        foreach (CommodityLoan::memberRequests($memberId) as $req) {
            if (in_array($req['status'], ['pending', 'recommended', 'authorized'], true)) {
                $blockers[] = 'Your commodity request for ' . $req['commodity_name'] . ' is still in progress.';
            } elseif (in_array($req['status'], ['supplied', 'completed'], true) && $req['balance'] > 0.005) {
                $owed[] = ['kind' => 'commodity', 'id' => (int) $req['application_id'], 'label' => 'Commodity: ' . $req['commodity_name'], 'balance' => round($req['balance'], 2)];
            }
        }

        $stmt = $con->prepare("SELECT l.loan_id, l.loan_status, l.total_payable, l.amount_recommended, l.loan_amount, e.fname, e.lname
                               FROM fudscoops_loan l JOIN fudscoops_member m ON m.member_id = l.member_id JOIN employee e ON e.employee_id = m.employee_id
                               WHERE l.guarantor_employee_id = :e AND l.member_id <> :m AND l.guarantor_status IN (0, 1)
                                 AND l.loan_status NOT IN ('Disapprove', 'Repaid')");
        $stmt->execute([':e' => $employeeId, ':m' => $memberId]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $g) {
            $running = in_array($g['loan_status'], ['Disbursed', 'Approved'], true);
            if (!$running || self::totalOf($g) - self::repaidOf($con, $g['loan_id']) > 0.005) {
                $blockers[] = 'You are guarantor for ' . trim($g['fname'] . ' ' . $g['lname']) . '\'s loan, which is not yet fully repaid.';
            }
        }

        $total = round(array_sum(array_column($owed, 'balance')), 2);
        return [
            'blockers'   => $blockers,
            'owed'       => $owed,
            'total_owed' => $total,
            // a leaving member is paid everything left after what they owe (no minimum balance)
            'can_settle' => !$blockers && $total > 0 && $savings - $total > 0.005,
            'payout'     => max(0, round($savings - $total, 2)),
            'can_leave'  => !$blockers && $total <= 0.005,
        ];
    }

    // Pays everything the member owes from savings (Chairman approval of a complete withdrawal)
    public static function settleFromSavings($obligations, $by)
    {
        require_once('CommodityLoan.php');
        $con = self::con();
        foreach ($obligations['owed'] as $item) {
            if ($item['kind'] === 'loan') {
                $result = self::creditLoan($con, $item['id'], $item['balance'], 'complete withdrawal');
                if ($result !== 1) {
                    return $result;
                }
                self::afterCredit($item['id']);
            } else {
                $result = CommodityLoan::creditRequest($item['id'], $item['balance'], $by);
                if ($result !== 1) {
                    return $result;
                }
            }
        }
        return 1;
    }
}
