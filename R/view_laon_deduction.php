<?php
    // My Loan Deductions (member menu: View Loan Deduction): month by month, what was due and what was deducted
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    require_once('../config/classes/MemberG3.php');
    require_once('../config/classes/LoanControls.php');
    $view = new View();
    $user = new User();
    $member = new MemberG3();
    if (!User::is_authenticated('../auth/') || !User::isStaff('../auth/logout.php')) {
        exit();
    }
    $memberId = $user->getMemberId($user->getEmployeeId($_SESSION['username']));
    $loans = array_values(array_filter($member->myLoans($memberId), function ($l) { return $l['is_debt']; }));
    $overdue = [];
    foreach (LoanControls::overdueLoans($memberId) as $od) {
        $overdue[(int) $od['loan_id']] = $od;
    }

    // Deductions per loan per month (YYYY-MM)
    $stmt = (new DB())->getConnection()->prepare("SELECT r.loan_id, DATE_FORMAT(r.loan_repayment_date, '%Y-%m') ym, SUM(r.amount_paid) amt, MAX(r.date_paid) recorded
                                                  FROM fudscoops_loan_repayments r JOIN fudscoops_loan l ON l.loan_id = r.loan_id
                                                  WHERE l.member_id = :m AND r.status = 1 AND COALESCE(r.is_deleted, 0) = 0 GROUP BY r.loan_id, ym");
    $stmt->execute([':m' => $memberId]);
    $paid = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $p) {
        $paid[(int) $p['loan_id']][$p['ym']] = $p;
    }

    $totalDeducted = array_sum(array_column($loans, 'repaid'));
    $thisYear = 0;
    foreach ($paid as $months) {
        foreach ($months as $ym => $p) {
            if (substr($ym, 0, 4) === date('Y')) {
                $thisYear += $p['amt'];
            }
        }
    }
    $repaying = array_filter($loans, function ($l) { return $l['stage'] === 'Repaying'; });
    $owed = array_sum(array_column($repaying, 'balance'));
    $monthly = array_sum(array_map(function ($l) { return min($l['monthly'], $l['balance']); }, $repaying));

    // Month rows for one loan: from the first deduction month until the schedule is covered (at least up to now)
    $schedule = function ($l) use ($paid) {
        $start = substr($l['repayment_start'] ?: $l['loan_date'], 0, 7);
        $got = isset($paid[(int) $l['loan_id']]) ? $paid[(int) $l['loan_id']] : [];
        $rows = [];
        $due = 0;
        $month = new DateTime($start . '-01');
        $now = date('Y-m');
        $last = $got ? max(array_keys($got)) : $start;
        for ($i = 0; $i < 120; $i++) {
            $ym = $month->format('Y-m');
            $instalment = $l['monthly'] > 0 ? min($l['monthly'], max(0, $l['total'] - $due)) : 0;
            if ($instalment <= 0.005 && $ym > $now && $ym > $last) {
                break;
            }
            $due += $instalment;
            $amt = isset($got[$ym]) ? (float) $got[$ym]['amt'] : 0;
            if ($amt > 0 && $amt + 0.5 >= $instalment) {
                $status = ['Deducted', 'ok'];
            } elseif ($amt > 0) {
                $status = ['Short', 'no'];
            } elseif ($instalment <= 0.005) {
                $status = ['–', ''];
            } elseif ($ym <= $now) {
                $status = ['Not recorded yet', 'wait'];
            } else {
                $status = ['Upcoming', ''];
            }
            if ($l['loan_status'] === 'Repaid' && $amt <= 0) {
                $month->modify('+1 month');
                continue; // a repaid loan only lists the months that had deductions
            }
            $rows[] = ['ym' => $ym, 'due' => $instalment, 'amt' => $amt, 'status' => $status,
                       'recorded' => isset($got[$ym]) ? $got[$ym]['recorded'] : null];
            $month->modify('+1 month');
        }
        return array_reverse($rows); // newest first
    };

    $h = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
    $money = function ($v) { return '₦' . number_format((float) $v, 2); };
    echo $view->subHeader('../');
?>
    <?php echo $view->subPartialNav('../'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->staffSideNav(); ?>
      <div class="main-panel">
        <div class="content-wrapper fud-dash">

          <div class="fud-hero">
            <div class="fud-hero-date">Loans</div>
            <h2><i class="mdi mdi-file-document-box"></i> My Loan Deductions</h2>
            <p>What is deducted from your salary for each loan, month by month.</p>
            <div class="fud-hero-actions">
              <a href="view_loan_approval.php"><i class="mdi mdi-cash-multiple"></i> My loans</a>
              <a href="payslip.php"><i class="mdi mdi-receipt"></i> Payslips</a>
            </div>
          </div>

          <?php foreach ($overdue as $od) { ?>
            <div class="fud-notice"><i class="mdi mdi-alert-circle"></i>
              <span>Your <?php echo $h($od['loan_type_name']); ?> is overdue: deductions are <?php echo $money($od['arrears']); ?> behind. Please contact the Treasurer.</span></div>
          <?php } ?>

          <div class="fud-stats">
            <div class="fud-stat"><div class="fud-stat-icon fud-green"><i class="mdi mdi-check-circle"></i></div>
              <div><div class="fud-stat-label">Deducted so far</div><div class="fud-stat-value"><?php echo $money($totalDeducted); ?></div>
                <div class="fud-stat-sub"><?php echo $money($thisYear); ?> in <?php echo date('Y'); ?></div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-purple"><i class="mdi mdi-calendar-clock"></i></div>
              <div><div class="fud-stat-label">Monthly deduction</div><div class="fud-stat-value"><?php echo $money($monthly); ?></div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-rose"><i class="mdi mdi-cash-multiple"></i></div>
              <div><div class="fud-stat-label">Still to repay</div><div class="fud-stat-value"><?php echo $money($owed); ?></div></div></div>
          </div>

          <?php if (!$loans) { ?>
            <div class="fud-section"><div class="fud-section-body">
              <p class="fud-empty mb-0">You have no loan being repaid, so nothing is deducted for loans.
                <a href="view_loan_approval.php">See my loans</a></p>
            </div></div>
          <?php } ?>

          <?php foreach ($loans as $l) {
              $od = isset($overdue[(int) $l['loan_id']]) ? $overdue[(int) $l['loan_id']] : null;
              $percent = $l['total'] > 0 ? min(100, round($l['repaid'] / $l['total'] * 100)) : 0;
              $rows = $schedule($l); ?>
            <div class="fud-section">
              <div class="fud-section-head">
                <h5><?php echo $h($l['loan_type_name'] ?: 'Loan'); ?> <span class="text-muted font-weight-normal">#<?php echo (int) $l['loan_id']; ?></span></h5>
                <div>
                  <?php if ($od) { ?><span class="fud-pill no mr-1">Overdue</span><?php } ?>
                  <span class="fud-pill <?php echo $l['stage'] === 'Fully repaid' || $l['stage'] === 'Repaying' ? 'ok' : 'wait'; ?>"><?php echo $h($l['stage']); ?></span>
                </div>
              </div>
              <div class="fud-section-body">
                <div class="fud-facts mb-3">
                  <div><span>Total to repay</span><?php echo $money($l['total']); ?></div>
                  <div><span>Monthly</span><?php echo $money($l['monthly']); ?> × <?php echo (int) $l['loan_tenor']; ?></div>
                  <div><span>Deductions from</span><?php echo $l['repayment_start'] ? date('F Y', strtotime($l['repayment_start'])) : '–'; ?></div>
                  <div><span><?php echo $l['balance'] > 0 ? 'Balance' : 'Fully repaid'; ?></span><?php echo $l['balance'] > 0 ? $money($l['balance']) : ($l['repaid_date'] ? date('d M Y', strtotime($l['repaid_date'])) : '✓'); ?></div>
                </div>
                <div class="d-flex justify-content-between small mb-1">
                  <span>Repaid <strong><?php echo $money($l['repaid']); ?></strong></span><span><?php echo $percent; ?>%</span>
                </div>
                <div class="fud-bar mb-3"><div style="width: <?php echo $percent; ?>%"></div></div>

                <div class="table-responsive">
                  <table class="table fud-table">
                    <thead><tr><th>Month</th><th class="num">Due (₦)</th><th class="num">Deducted (₦)</th><th>Status</th><th>Recorded</th></tr></thead>
                    <tbody>
                    <?php foreach ($rows as $r) { ?>
                      <tr class="<?php echo $r['status'][0] === 'Upcoming' ? 'text-muted' : ''; ?>">
                        <td><?php echo date('F Y', strtotime($r['ym'] . '-01')); ?></td>
                        <td class="num"><?php echo $r['due'] > 0 ? number_format($r['due'], 2) : '–'; ?></td>
                        <td class="num"><?php echo $r['amt'] > 0 ? number_format($r['amt'], 2) : '–'; ?></td>
                        <td><?php echo $r['status'][1] ? '<span class="fud-pill ' . $r['status'][1] . '">' . $r['status'][0] . '</span>' : $r['status'][0]; ?></td>
                        <td class="text-muted small"><?php echo $r['recorded'] ? $h(date('d M Y', strtotime($r['recorded']))) : ''; ?></td>
                      </tr>
                    <?php } ?>
                    </tbody>
                  </table>
                </div>
                <p class="text-muted small mb-0 mt-2">"Not recorded yet" means the Treasurer has not uploaded a deduction for that month. If your payslip shows one, contact the Treasurer.</p>
              </div>
            </div>
          <?php } ?>

        </div>
        <?php echo $view->subFooter('../'); ?>
