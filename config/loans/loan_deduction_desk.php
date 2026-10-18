<?php
// Loan deductions, shared by Treasurer/, Chairman/ and Secretary/ loan_deduction.php.
// Expects: $view, $role ('treasurer' / 'chairman' / 'secretary'), $sideNav (View method for the role's menu).
// Views:
//   ?month=YYYY-MM[&tab=]  one month: which loans have their deduction recorded and which do not (default: this month)
//   ?view=history[&year=]  month-by-month summary for a year
//   ?loan_id=N             one loan's deduction history, month by month
// Only the Treasurer records deductions (view_loan_deduction_details.php, batch_loan_deduction.php); the others read.
// ?export=csv (Treasurer) downloads the loans still to record in the upload layout; ?export=month downloads the
// month's deductions for everyone.
require_once __DIR__ . '/../classes/MemberG3.php';

$isTreasurer = $role === 'treasurer';
$g3 = new MemberG3();
$con = (new DB())->getConnection();
$h = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
$money = function ($v) { return '₦' . number_format((float) $v, 2); };
$who = function ($l) { return preg_replace('/\s+/', ' ', trim($l['fname'] . ' ' . $l['oname'] . ' ' . $l['lname'])); };
$thisMonthKey = date('Y-m');

// Loans paid out (being repaid or repaid), with the monthly instalment and every recorded deduction by month
$loans = array_values(array_filter($g3->officerLoans(), function ($l) { return $l['is_debt']; }));
$instalments = $con->query("SELECT r.loan_id, CAST(REPLACE(r.loan_repayment_amount, ',', '') AS DECIMAL(14,2)) FROM fudscoops_loan_repayments r
                            JOIN (SELECT loan_id, MAX(loan_repayment_id) id FROM fudscoops_loan_repayments WHERE status = 0 AND COALESCE(is_deleted, 0) = 0 GROUP BY loan_id) x
                              ON x.id = r.loan_repayment_id")->fetchAll(PDO::FETCH_KEY_PAIR);
$paidByMonth = [];
foreach ($con->query("SELECT loan_id, DATE_FORMAT(loan_repayment_date, '%Y-%m') ym, SUM(amount_paid) amt, MAX(date_paid) recorded
                      FROM fudscoops_loan_repayments WHERE status = 1 AND COALESCE(is_deleted, 0) = 0 AND loan_repayment_date IS NOT NULL
                      GROUP BY loan_id, ym") as $p) {
    $paidByMonth[(int) $p['loan_id']][$p['ym']] = ['amount' => (float) $p['amt'], 'recorded' => $p['recorded']];
}
foreach ($loans as &$l) {
    $l['monthly'] = isset($instalments[$l['loan_id']]) ? (float) $instalments[$l['loan_id']] : 0;
    $l['start'] = substr($l['repayment_start'] ?: $l['loan_date'], 0, 7);
    // The last month a deduction was due: the month it was repaid (or open-ended)
    $l['end'] = $l['loan_status'] === 'Repaid' ? substr($l['repaid_date'] ?: $l['start'], 0, 7) : '9999-12';
    $l['paid'] = isset($paidByMonth[(int) $l['loan_id']]) ? $paidByMonth[(int) $l['loan_id']] : [];
}
unset($l);
$paidIn = function ($l, $ym) { return isset($l['paid'][$ym]) ? $l['paid'][$ym]['amount'] : 0; };
// A month is "processed" for a loan type once the Treasurer has recorded deductions for it (as LoanControls::overdueLoans).
// A loan with nothing recorded counts as missed only in a processed month; otherwise that month's file was not uploaded.
$processed = [];
foreach ($loans as $l) {
    foreach (array_keys($l['paid']) as $ym) {
        $processed[$l['loan_type_id']][$ym] = true;
    }
}
$isProcessed = function ($l, $ym) use ($processed) { return isset($processed[$l['loan_type_id']][$ym]); };

// ---------------------------------------------------------------- one loan's history
$loanId = isset($_GET['loan_id']) ? (int) $_GET['loan_id'] : 0;
$loan = null;
if ($loanId) {
    foreach ($loans as $l) {
        if ((int) $l['loan_id'] === $loanId) {
            $loan = $l;
        }
    }
}
$history = [];
if ($loan) {
    // Every month from the first deduction to now (or to the month it was repaid), plus any month with a deduction
    $months = [];
    for ($t = strtotime($loan['start'] . '-01'); date('Y-m', $t) <= min($thisMonthKey, $loan['end']); $t = strtotime('+1 month', $t)) {
        $months[date('Y-m', $t)] = true;
    }
    foreach (array_keys($loan['paid']) as $ym) {
        $months[$ym] = true;
    }
    ksort($months);
    $cumulative = 0;
    foreach (array_keys($months) as $ym) {
        $amount = $paidIn($loan, $ym);
        $expected = min($loan['monthly'], max(0, round($loan['total'] - $cumulative, 2)));
        $cumulative += $amount;
        if ($amount <= 0) {
            $status = $expected <= 0 ? ['–', ''] : ($ym > $thisMonthKey ? ['Upcoming', ''] : ($isProcessed($loan, $ym) ? ['Missed', 'no'] : ['Not uploaded', '']));
        } elseif ($amount + 0.5 < $expected) {
            $status = ['Short', 'wait'];
        } else {
            $status = ['Recorded', 'ok'];
        }
        $history[] = ['ym' => $ym, 'expected' => $expected, 'amount' => $amount, 'recorded' => $amount > 0 ? $loan['paid'][$ym]['recorded'] : null,
                      'balance' => max(0, round($loan['total'] - $cumulative, 2)), 'status' => $status];
    }
    $history = array_reverse($history);
}

// ---------------------------------------------------------------- year summary
$view_ = $loan ? 'loan' : (isset($_GET['view']) && $_GET['view'] === 'history' ? 'history' : 'month');
$year = isset($_GET['year']) && preg_match('/^\d{4}$/', $_GET['year']) ? $_GET['year'] : date('Y');
$summary = [];
if ($view_ === 'history') {
    for ($m = 12; $m >= 1; $m--) {
        $ym = sprintf('%s-%02d', $year, $m);
        $row = ['ym' => $ym, 'due' => 0, 'scheduled' => 0, 'recorded' => 0, 'count' => 0, 'short' => 0, 'missed' => 0, 'not_uploaded' => 0, 'members' => []];
        foreach ($loans as $l) {
            $amount = $paidIn($l, $ym);
            $due = ($l['start'] <= $ym && $ym <= $l['end']) || $amount > 0;
            if (!$due) {
                continue;
            }
            $row['due']++;
            $row['scheduled'] += $l['monthly'];
            if ($amount > 0) {
                $row['recorded'] += $amount;
                $row['count']++;
                $row['members'][$l['member_id']] = true;
                $row['short'] += $amount + 0.5 < $l['monthly'] && $ym !== $l['end'] ? 1 : 0;
            } elseif ($ym <= $thisMonthKey) {
                $row[$isProcessed($l, $ym) ? 'missed' : 'not_uploaded']++;
            }
        }
        if ($row['due'] || $ym <= $thisMonthKey) {
            $summary[] = $row;
        }
    }
    $years = range((int) date('Y') + 1, 2024);
}

// ---------------------------------------------------------------- one month
$month = isset($_GET['month']) && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $_GET['month']) ? $_GET['month'] : $thisMonthKey;
foreach ($loans as &$l) {
    $l['this_month'] = $paidIn($l, $month);
    // Due this month: being repaid and started, or a deduction was recorded anyway
    $balanceBefore = $l['balance'] + $l['this_month'];
    $l['due'] = ($l['loan_status'] !== 'Repaid' && $l['start'] <= $month) || $l['this_month'] > 0;
    $l['expected'] = $l['due'] ? min($l['monthly'], max(0, $balanceBefore)) : 0;
    if (!$l['due']) {
        $l['month_status'] = ['Not due', ''];
    } elseif ($l['this_month'] <= 0) {
        $l['month_status'] = ['Not recorded', 'wait'];
    } elseif ($l['this_month'] + 0.5 < $l['expected']) {
        $l['month_status'] = ['Short', 'no'];
    } else {
        $l['month_status'] = ['Recorded', 'ok'];
    }
}
unset($l);

$tabs = [
    'pending'  => ['Not recorded', function ($l) { return $l['month_status'][0] === 'Not recorded'; }],
    'recorded' => ['Recorded', function ($l) { return $l['month_status'][0] === 'Recorded'; }],
    'short'    => ['Short', function ($l) { return $l['month_status'][0] === 'Short'; }],
    'overdue'  => ['Overdue', function ($l) { return $l['arrears'] > 0; }],
    'repaid'   => ['Fully repaid', function ($l) { return $l['loan_status'] === 'Repaid'; }],
    'all'      => ['All', function ($l) { return true; }],
];
$tab = isset($_GET['tab'], $tabs[$_GET['tab']]) ? $_GET['tab'] : ($isTreasurer ? 'pending' : 'all');
$counts = [];
foreach ($tabs as $k => $t) {
    $counts[$k] = count(array_filter($loans, $t[1]));
}
$rows = array_values(array_filter($loans, $tabs[$tab][1]));
$due = array_filter($loans, function ($l) { return $l['due']; });
$expected = array_sum(array_column($due, 'expected'));
$recorded = array_sum(array_column($loans, 'this_month'));
$monthName = date('F Y', strtotime($month . '-01'));

if (isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=utf-8');
    $out = fopen('php://output', 'w');
    if ($_GET['export'] === 'csv' && $isTreasurer) {
        // The loans still to record this month, in the batch upload layout (Staff Number, mm/dd/yyyy, amount)
        header('Content-Disposition: attachment; filename="loan_deductions_to_record_' . $month . '.csv"');
        fputcsv($out, ['Staff Number', 'Repayment Month (mm/dd/yyyy)', 'Amount Deducted (blank = full monthly instalment)', 'Name', 'Loan']);
        foreach ($loans as $l) {
            if ($l['month_status'][0] === 'Not recorded') {
                fputcsv($out, [$l['sp_no'], date('m/t/Y', strtotime($month . '-01')), number_format($l['expected'], 2, '.', ''),
                               $who($l), $l['loan_type_name'] . ' #' . $l['loan_id']]);
            }
        }
    } else {
        // Every loan due this month with what was deducted
        header('Content-Disposition: attachment; filename="loan_deductions_' . $month . '.csv"');
        fputcsv($out, ['Staff Number', 'Name', 'Loan', 'Monthly instalment', 'Due this month', 'Deducted this month', 'Repaid to date', 'Balance', 'Status']);
        foreach ($loans as $l) {
            if ($l['due']) {
                fputcsv($out, [$l['sp_no'], $who($l), $l['loan_type_name'] . ' #' . $l['loan_id'], number_format($l['monthly'], 2, '.', ''),
                               number_format($l['expected'], 2, '.', ''), number_format($l['this_month'], 2, '.', ''),
                               number_format((float) $l['repaid'], 2, '.', ''), number_format($l['balance'], 2, '.', ''), $l['month_status'][0]]);
            }
        }
    }
    fclose($out);
    exit();
}

$prev = date('Y-m', strtotime($month . '-01 -1 month'));
$next = date('Y-m', strtotime($month . '-01 +1 month'));
echo $view->subHeader('../');
?>
    <?php echo $view->subPartialNav('../'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->$sideNav(); ?>
      <div class="main-panel">
        <div class="content-wrapper fud-dash">

          <div class="fud-hero">
            <div class="fud-hero-date">Loans</div>
            <h2><i class="mdi mdi-file-document-box"></i> Loan Deductions</h2>
            <p><?php echo $isTreasurer
                ? 'Check each month\'s salary deductions against the loans being repaid. Record a single deduction on a loan\'s schedule, or upload the month\'s file.'
                : 'The salary deductions recorded by the Treasurer against the loans being repaid: by month, as a yearly history, and for each loan.'; ?></p>
            <div class="fud-hero-actions">
              <?php if ($isTreasurer) { ?><a href="batch_loan_deduction.php"><i class="mdi mdi-cloud-upload"></i> Upload deductions</a><?php } ?>
              <a href="?view=history"><i class="mdi mdi-history"></i> Deduction history</a>
              <a href="overdue_loans.php"><i class="mdi mdi-alert-circle"></i> Overdue loans</a>
              <?php if ($isTreasurer) { ?><a href="overpayments.php"><i class="mdi mdi-cash-refund"></i> Overpayments</a><?php } ?>
            </div>
          </div>

          <div class="fud-tabs">
            <a href="?month=<?php echo $month; ?>" class="<?php echo $view_ === 'month' ? 'active' : ''; ?>"><i class="mdi mdi-calendar-month"></i> By month</a>
            <a href="?view=history&amp;year=<?php echo substr($month, 0, 4); ?>" class="<?php echo $view_ === 'history' ? 'active' : ''; ?>"><i class="mdi mdi-history"></i> Yearly history</a>
            <?php if ($view_ === 'loan') { ?><a href="#" class="active"><i class="mdi mdi-cash"></i> Loan #<?php echo (int) $loan['loan_id']; ?></a><?php } ?>
          </div>

          <?php if ($loanId && !$loan) { ?>
            <div class="fud-notice"><i class="mdi mdi-alert-circle"></i><span>That loan has not been paid out, so it has no deductions.</span></div>
          <?php } ?>

          <?php if ($view_ === 'loan') { ?>
          <!-- ======================= one loan ======================= -->
          <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div>
              <h3 class="mb-1"><?php echo $h($loan['loan_type_name']); ?> #<?php echo (int) $loan['loan_id']; ?>
                <span class="fud-pill <?php echo $loan['loan_status'] === 'Repaid' ? 'ok' : ($loan['arrears'] > 0 ? 'no' : 'wait'); ?>" style="font-size:.8rem"><?php echo $loan['loan_status'] === 'Repaid' ? 'Fully repaid' : ($loan['arrears'] > 0 ? 'Overdue' : 'Repaying'); ?></span></h3>
              <div class="text-muted"><?php echo $h($who($loan)); ?> (<?php echo $h($loan['sp_no']); ?>) · deductions from <?php echo date('F Y', strtotime($loan['start'] . '-01')); ?></div>
            </div>
            <div>
              <a href="view_loan_details.php?loan_id=<?php echo (int) $loan['loan_id']; ?>" class="btn btn-light btn-sm"><i class="mdi mdi-file-document"></i> Loan details</a>
              <?php if ($isTreasurer) { ?><a href="view_loan_deduction_details.php?loan_id=<?php echo (int) $loan['loan_id']; ?>" class="btn btn-primary btn-sm"><i class="mdi mdi-pencil"></i> Record a deduction</a><?php } ?>
            </div>
          </div>
          <div class="fud-stats">
            <div class="fud-stat"><div class="fud-stat-icon fud-blue"><i class="mdi mdi-cash-multiple"></i></div>
              <div><div class="fud-stat-label">Total to repay</div><div class="fud-stat-value"><?php echo $money($loan['total']); ?></div>
                <div class="fud-stat-sub"><?php echo $money($loan['monthly']); ?> a month · <?php echo (int) $loan['loan_tenor']; ?> months</div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-green"><i class="mdi mdi-check-circle"></i></div>
              <div><div class="fud-stat-label">Deducted so far</div><div class="fud-stat-value"><?php echo $money($loan['repaid']); ?></div>
                <div class="fud-stat-sub"><?php echo count($loan['paid']); ?> month<?php echo count($loan['paid']) === 1 ? '' : 's'; ?> recorded</div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon <?php echo $loan['arrears'] > 0 ? 'fud-rose' : 'fud-amber'; ?>"><i class="mdi mdi-scale-balance"></i></div>
              <div><div class="fud-stat-label">Balance</div><div class="fud-stat-value"><?php echo $money($loan['balance']); ?></div>
                <div class="fud-stat-sub"><?php echo $loan['arrears'] > 0 ? $money($loan['arrears']) . ' behind' : 'up to date'; ?></div></div></div>
          </div>
          <div class="fud-section">
            <div class="fud-section-head"><h5><i class="mdi mdi-history"></i> Month by month</h5></div>
            <div class="fud-section-body">
              <?php if (!$history) { ?><p class="fud-empty">No deductions are due yet.</p><?php } else { ?>
              <div class="table-responsive">
                <table class="table fud-table mb-0">
                  <thead><tr><th>Month</th><th class="num">Due (₦)</th><th class="num">Deducted (₦)</th><th class="num">Balance after (₦)</th><th>Status</th><th>Recorded on</th></tr></thead>
                  <tbody>
                  <?php foreach ($history as $r) { ?>
                    <tr><td class="text-nowrap"><a href="?month=<?php echo $r['ym']; ?>&amp;tab=all"><?php echo date('F Y', strtotime($r['ym'] . '-01')); ?></a></td>
                      <td class="num"><?php echo number_format($r['expected'], 2); ?></td>
                      <td class="num"><?php echo $r['amount'] > 0 ? number_format($r['amount'], 2) : '–'; ?></td>
                      <td class="num"><?php echo number_format($r['balance'], 2); ?></td>
                      <td><?php echo $r['status'][1] ? '<span class="fud-pill ' . $r['status'][1] . '">' . $r['status'][0] . '</span>' : '<span class="text-muted small">' . $r['status'][0] . '</span>'; ?></td>
                      <td class="small text-muted"><?php echo $r['recorded'] ? $h(date('d M Y', strtotime($r['recorded']))) : ''; ?></td></tr>
                  <?php } ?>
                  </tbody>
                </table>
              </div>
              <?php } ?>
            </div>
          </div>

          <?php } elseif ($view_ === 'history') { ?>
          <!-- ======================= year summary ======================= -->
          <div class="fud-section">
            <div class="fud-section-head d-flex flex-wrap justify-content-between align-items-center">
              <h5><i class="mdi mdi-history"></i> Deductions in <?php echo $h($year); ?></h5>
              <form class="form-inline" method="get">
                <input type="hidden" name="view" value="history">
                <select name="year" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                  <?php foreach ($years as $y) { ?><option value="<?php echo $y; ?>"<?php echo (string) $y === $year ? ' selected' : ''; ?>><?php echo $y; ?></option><?php } ?>
                </select>
              </form>
            </div>
            <div class="fud-section-body">
              <?php $yearRecorded = array_sum(array_column($summary, 'recorded')); ?>
              <div class="fud-facts mb-3">
                <div><span>Recorded in <?php echo $h($year); ?></span><strong><?php echo $money($yearRecorded); ?></strong></div>
                <div><span>Deductions</span><?php echo array_sum(array_column($summary, 'count')); ?></div>
                <div><span>Missed (file uploaded, nothing for the loan)</span><?php echo array_sum(array_column($summary, 'missed')); ?></div>
              </div>
              <?php if (!$summary) { ?><p class="fud-empty">No loans were being repaid in <?php echo $h($year); ?>.</p><?php } else { ?>
              <div class="table-responsive">
                <table class="table fud-table mb-0">
                  <thead><tr><th>Month</th><th class="num">Loans due</th><th class="num">Scheduled (₦)</th><th class="num">Recorded (₦)</th><th>Progress</th><th class="num">Deductions</th><th class="num">Missed</th><th></th></tr></thead>
                  <tbody>
                  <?php foreach ($summary as $r) {
                      $pct = $r['scheduled'] > 0 ? min(100, round($r['recorded'] / $r['scheduled'] * 100)) : 0;
                      $future = $r['ym'] > $thisMonthKey; ?>
                    <tr<?php echo $future ? ' class="text-muted"' : ''; ?>>
                      <td class="text-nowrap"><strong><?php echo date('F', strtotime($r['ym'] . '-01')); ?></strong><?php echo $r['ym'] === $thisMonthKey ? ' <span class="fud-pill wait">this month</span>' : ''; ?></td>
                      <td class="num"><?php echo $r['due']; ?></td>
                      <td class="num"><?php echo number_format($r['scheduled'], 2); ?></td>
                      <td class="num"><?php echo $r['recorded'] > 0 ? number_format($r['recorded'], 2) : '–'; ?></td>
                      <td style="min-width:120px"><?php if ($r['due'] && !$future) { ?>
                        <div class="progress" style="height:8px" title="<?php echo $pct; ?>% of the scheduled amount recorded"><div class="progress-bar <?php echo $pct >= 100 ? 'bg-success' : ($pct > 0 ? 'bg-warning' : 'bg-danger'); ?>" style="width:<?php echo max($pct, 3); ?>%"></div></div>
                        <div class="small text-muted"><?php echo $pct; ?>%</div><?php } elseif ($future && $r['due']) { ?><span class="small">upcoming</span><?php } ?></td>
                      <td class="num"><?php echo $r['count'] ?: '–'; ?><?php echo $r['short'] ? '<div class="small text-warning">' . $r['short'] . ' short</div>' : ''; ?></td>
                      <td class="num"><?php echo $r['missed'] ? '<span class="text-danger font-weight-bold">' . $r['missed'] . '</span>' : '–'; ?>
                        <?php if ($r['not_uploaded']) { ?><div class="small text-muted" title="No deductions were recorded this month for these loans' types: the month's file has not been uploaded"><?php echo $r['not_uploaded']; ?> not uploaded</div><?php } ?></td>
                      <td class="text-right"><a class="btn btn-sm btn-outline-primary" href="?month=<?php echo $r['ym']; ?>&amp;tab=all">Open</a></td>
                    </tr>
                  <?php } ?>
                  </tbody>
                </table>
              </div>
              <p class="small text-muted mt-2 mb-0">Scheduled is each due loan's monthly instalment. Missed counts loans with no deduction in a month whose deduction file was uploaded for that loan type;
                "not uploaded" counts loans whose type had no deductions recorded that month at all (the file has not been uploaded yet).</p>
              <?php } ?>
            </div>
          </div>

          <?php } else { ?>
          <!-- ======================= one month ======================= -->
          <div class="fud-section">
            <div class="fud-section-body d-flex flex-wrap align-items-center justify-content-between">
              <div class="d-flex align-items-center fud-month-nav">
                <a class="btn btn-light btn-sm" href="?month=<?php echo $prev; ?>&amp;tab=<?php echo $tab; ?>" title="Previous month"><i class="mdi mdi-chevron-left"></i></a>
                <h4 class="mb-0 mx-3"><?php echo $monthName; ?></h4>
                <a class="btn btn-light btn-sm" href="?month=<?php echo $next; ?>&amp;tab=<?php echo $tab; ?>" title="Next month"><i class="mdi mdi-chevron-right"></i></a>
              </div>
              <form class="form-inline" method="get">
                <input type="hidden" name="tab" value="<?php echo $h($tab); ?>">
                <input type="month" name="month" class="form-control form-control-sm mr-2" value="<?php echo $month; ?>">
                <button class="btn btn-sm btn-outline-primary">Go</button>
              </form>
            </div>
          </div>

          <div class="fud-stats">
            <div class="fud-stat"><div class="fud-stat-icon fud-blue"><i class="mdi mdi-cash-multiple"></i></div>
              <div><div class="fud-stat-label">Loans due in <?php echo date('F', strtotime($month . '-01')); ?></div><div class="fud-stat-value"><?php echo count($due); ?></div>
                <div class="fud-stat-sub"><?php echo $money($expected); ?> expected</div></div></div>
            <a class="fud-stat fud-stat-link" href="?month=<?php echo $month; ?>&amp;tab=recorded">
              <div class="fud-stat-icon fud-green"><i class="mdi mdi-check-circle"></i></div>
              <div><div class="fud-stat-label">Recorded</div><div class="fud-stat-value"><?php echo $money($recorded); ?></div>
                <div class="fud-stat-sub"><?php echo $counts['recorded'] + $counts['short']; ?> loan(s)<?php echo $counts['short'] ? ', ' . $counts['short'] . ' short' : ''; ?></div></div></a>
            <a class="fud-stat fud-stat-link" href="?month=<?php echo $month; ?>&amp;tab=pending">
              <div class="fud-stat-icon <?php echo $counts['pending'] ? 'fud-amber' : 'fud-green'; ?>"><i class="mdi mdi-clock-outline"></i></div>
              <div><div class="fud-stat-label">Not recorded yet</div><div class="fud-stat-value"><?php echo $counts['pending']; ?></div>
                <div class="fud-stat-sub"><?php echo $money(max(0, $expected - $recorded)); ?> outstanding</div></div></a>
            <a class="fud-stat fud-stat-link" href="?month=<?php echo $month; ?>&amp;tab=overdue">
              <div class="fud-stat-icon <?php echo $counts['overdue'] ? 'fud-rose' : 'fud-green'; ?>"><i class="mdi mdi-alert-circle"></i></div>
              <div><div class="fud-stat-label">Overdue loans</div><div class="fud-stat-value"><?php echo $counts['overdue']; ?></div></div></a>
          </div>

          <div class="fud-section">
            <div class="fud-section-body">
              <div class="fud-tabs">
                <?php foreach ($tabs as $k => $t) { ?>
                  <a class="<?php echo $tab === $k ? 'active' : ''; ?>" href="?month=<?php echo $month; ?>&amp;tab=<?php echo $k; ?>"><?php echo $t[0]; ?> <span><?php echo $counts[$k]; ?></span></a>
                <?php } ?>
              </div>
              <div class="d-flex flex-wrap justify-content-between align-items-center mb-3" style="gap:.5rem">
                <input type="search" id="loanSearch" class="form-control fud-search mb-0" placeholder="Search by name, staff number or loan #">
                <div>
                  <?php if ($isTreasurer && $counts['pending']) { ?>
                    <a class="btn btn-outline-secondary btn-sm" href="?month=<?php echo $month; ?>&amp;export=csv"
                       title="The loans not yet recorded for this month, in the batch upload layout">
                      <i class="mdi mdi-download"></i> Download <?php echo $counts['pending']; ?> to record (CSV)</a>
                  <?php } ?>
                  <?php if (count($due)) { ?>
                    <a class="btn btn-outline-secondary btn-sm" href="?month=<?php echo $month; ?>&amp;export=month" title="Every loan due this month with what was deducted">
                      <i class="mdi mdi-download"></i> <?php echo date('M Y', strtotime($month . '-01')); ?> deductions (CSV)</a>
                  <?php } ?>
                </div>
              </div>

              <?php if (!$rows) { ?>
                <p class="fud-empty">No loans here for <?php echo $monthName; ?>.</p>
              <?php } else { ?>
              <div class="table-responsive">
                <table class="table fud-table" id="loanList">
                  <thead><tr><th>Member</th><th>Loan</th><th class="num">Monthly (₦)</th><th class="num"><?php echo date('M Y', strtotime($month . '-01')); ?> (₦)</th>
                    <th class="num">Repaid (₦)</th><th class="num">Balance (₦)</th><th>Status</th><th></th></tr></thead>
                  <tbody>
                  <?php foreach ($rows as $l) { ?>
                    <tr data-search="<?php echo $h(strtolower($who($l) . ' ' . $l['sp_no'] . ' #' . $l['loan_id'] . ' ' . $l['loan_id'])); ?>">
                      <td class="fud-wrap"><?php echo $h($who($l)); ?><div class="text-muted small"><?php echo $h($l['sp_no']); ?></div></td>
                      <td><?php echo $h($l['loan_type_name']); ?> <span class="text-muted">#<?php echo (int) $l['loan_id']; ?></span>
                        <div class="text-muted small">from <?php echo $l['repayment_start'] ? date('M Y', strtotime($l['repayment_start'])) : '–'; ?></div></td>
                      <td class="num"><?php echo number_format($l['monthly'], 2); ?></td>
                      <td class="num"><?php echo $l['this_month'] > 0 ? number_format($l['this_month'], 2) : '–'; ?>
                        <?php if ($l['due'] && $l['expected'] > 0) { ?><div class="text-muted small">due <?php echo number_format($l['expected'], 2); ?></div><?php } ?></td>
                      <td class="num"><?php echo number_format((float) $l['repaid'], 2); ?></td>
                      <td class="num"><?php echo number_format($l['balance'], 2); ?></td>
                      <td><?php echo $l['month_status'][1] ? '<span class="fud-pill ' . $l['month_status'][1] . '">' . $l['month_status'][0] . '</span>' : '<span class="text-muted small">' . $l['month_status'][0] . '</span>'; ?>
                        <?php if ($l['arrears'] > 0) { ?><div><span class="fud-pill no">₦<?php echo number_format($l['arrears'], 2); ?> behind</span></div><?php } ?>
                        <?php if ($l['loan_status'] === 'Repaid') { ?><div><span class="fud-pill ok">Fully repaid</span></div><?php } ?></td>
                      <td class="text-nowrap">
                        <a class="btn btn-sm btn-light" href="?loan_id=<?php echo (int) $l['loan_id']; ?>" title="This loan's deductions, month by month"><i class="mdi mdi-history"></i> History</a>
                        <?php if ($isTreasurer) { ?><a class="btn btn-sm btn-outline-primary" href="view_loan_deduction_details.php?loan_id=<?php echo (int) $l['loan_id']; ?>"><?php echo $l['loan_status'] === 'Repaid' ? 'Schedule' : 'Record'; ?></a><?php } ?>
                      </td>
                    </tr>
                  <?php } ?>
                  </tbody>
                </table>
              </div>
              <p class="text-muted small mb-0 mt-2" id="loanCount"><?php echo count($rows); ?> loan(s)</p>
              <?php } ?>
            </div>
          </div>
          <?php } ?>

        </div>
        <?php echo $view->subFooter('../'); ?>

<script>
(function () {
    var search = document.getElementById('loanSearch');
    var rows = document.querySelectorAll('#loanList tbody tr');
    var count = document.getElementById('loanCount');
    if (!search || !rows.length) { return; }
    search.addEventListener('input', function () {
        var q = search.value.trim().toLowerCase(), shown = 0;
        rows.forEach(function (row) {
            var match = !q || row.getAttribute('data-search').indexOf(q) !== -1;
            row.style.display = match ? '' : 'none';
            if (match) { shown++; }
        });
        count.textContent = shown + ' loan(s)';
    });
})();
</script>
