<?php
    // Loan Deductions (Treasurer): for a chosen month, which loans have their deduction recorded and which do not.
    // Single deductions are recorded on each loan's schedule (view_loan_deduction_details.php); a month's file is
    // uploaded on batch_loan_deduction.php. ?export=csv downloads the loans still to record, in the upload layout.
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    require_once('../config/classes/MemberG3.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../auth/') || !User::isBur('../auth/logout.php')) {
        exit();
    }
    $g3 = new MemberG3();
    $month = isset($_GET['month']) && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $_GET['month']) ? $_GET['month'] : date('Y-m');
    $con = (new DB())->getConnection();

    // Loans paid out (being repaid or repaid), with this month's deduction and the monthly instalment
    $loans = array_values(array_filter($g3->officerLoans(), function ($l) { return $l['is_debt']; }));
    $stmt = $con->prepare("SELECT loan_id, SUM(amount_paid) amt FROM fudscoops_loan_repayments
                           WHERE status = 1 AND COALESCE(is_deleted, 0) = 0 AND DATE_FORMAT(loan_repayment_date, '%Y-%m') = :m GROUP BY loan_id");
    $stmt->execute([':m' => $month]);
    $thisMonth = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $instalments = $con->query("SELECT r.loan_id, CAST(REPLACE(r.loan_repayment_amount, ',', '') AS DECIMAL(14,2)) FROM fudscoops_loan_repayments r
                                JOIN (SELECT loan_id, MAX(loan_repayment_id) id FROM fudscoops_loan_repayments WHERE status = 0 AND COALESCE(is_deleted, 0) = 0 GROUP BY loan_id) x
                                  ON x.id = r.loan_repayment_id")->fetchAll(PDO::FETCH_KEY_PAIR);

    foreach ($loans as &$l) {
        $l['monthly'] = isset($instalments[$l['loan_id']]) ? (float) $instalments[$l['loan_id']] : 0;
        $l['this_month'] = isset($thisMonth[$l['loan_id']]) ? (float) $thisMonth[$l['loan_id']] : 0;
        $start = substr($l['repayment_start'] ?: $l['loan_date'], 0, 7);
        // Due this month: being repaid and started, or a deduction was recorded anyway
        $balanceBefore = $l['balance'] + $l['this_month'];
        $l['due'] = ($l['loan_status'] !== 'Repaid' && $start <= $month) || $l['this_month'] > 0;
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
    $tab = isset($_GET['tab'], $tabs[$_GET['tab']]) ? $_GET['tab'] : 'pending';
    $counts = [];
    foreach ($tabs as $k => $t) {
        $counts[$k] = count(array_filter($loans, $t[1]));
    }
    $rows = array_values(array_filter($loans, $tabs[$tab][1]));
    $due = array_filter($loans, function ($l) { return $l['due']; });
    $expected = array_sum(array_column($due, 'expected'));
    $recorded = array_sum(array_column($loans, 'this_month'));
    $who = function ($l) { return preg_replace('/\s+/', ' ', trim($l['fname'] . ' ' . $l['oname'] . ' ' . $l['lname'])); };
    $monthName = date('F Y', strtotime($month . '-01'));

    // The loans still to record this month, in the batch upload layout (Staff Number, mm/dd/yyyy, amount)
    if (isset($_GET['export']) && $_GET['export'] === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="loan_deductions_to_record_' . $month . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Staff Number', 'Repayment Month (mm/dd/yyyy)', 'Amount Deducted (blank = full monthly instalment)', 'Name', 'Loan']);
        foreach ($loans as $l) {
            if ($l['month_status'][0] === 'Not recorded') {
                fputcsv($out, [$l['sp_no'], date('m/t/Y', strtotime($month . '-01')), number_format($l['expected'], 2, '.', ''),
                               $who($l), $l['loan_type_name'] . ' #' . $l['loan_id']]);
            }
        }
        fclose($out);
        exit();
    }

    $h = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
    $money = function ($v) { return '₦' . number_format((float) $v, 2); };
    $prev = date('Y-m', strtotime($month . '-01 -1 month'));
    $next = date('Y-m', strtotime($month . '-01 +1 month'));
    echo $view->subHeader('../');
?>
    <?php echo $view->subPartialNav('../'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->treasurerSideNav(); ?>
      <div class="main-panel">
        <div class="content-wrapper fud-dash">

          <div class="fud-hero">
            <div class="fud-hero-date">Loans</div>
            <h2><i class="mdi mdi-file-document-box"></i> Loan Deductions</h2>
            <p>Check each month's salary deductions against the loans being repaid. Record a single deduction on a loan's schedule, or upload the month's file.</p>
            <div class="fud-hero-actions">
              <a href="batch_loan_deduction.php"><i class="mdi mdi-cloud-upload"></i> Upload deductions</a>
              <a href="overdue_loans.php"><i class="mdi mdi-alert-circle"></i> Overdue loans</a>
              <a href="overpayments.php"><i class="mdi mdi-cash-refund"></i> Overpayments</a>
            </div>
          </div>

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
              <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <input type="search" id="loanSearch" class="form-control fud-search" placeholder="Search by name, staff number or loan #">
                <?php if ($counts['pending']) { ?>
                  <a class="btn btn-outline-secondary btn-sm" href="?month=<?php echo $month; ?>&amp;export=csv"
                     title="The loans not yet recorded for this month, in the batch upload layout">
                    <i class="mdi mdi-download"></i> Download <?php echo $counts['pending']; ?> to record (CSV)</a>
                <?php } ?>
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
                      <td><a class="btn btn-sm btn-outline-primary" href="view_loan_deduction_details.php?loan_id=<?php echo (int) $l['loan_id']; ?>"><?php echo $l['loan_status'] === 'Repaid' ? 'Schedule' : 'Record'; ?></a></td>
                    </tr>
                  <?php } ?>
                  </tbody>
                </table>
              </div>
              <p class="text-muted small mb-0 mt-2" id="loanCount"><?php echo count($rows); ?> loan(s)</p>
              <?php } ?>
            </div>
          </div>

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
