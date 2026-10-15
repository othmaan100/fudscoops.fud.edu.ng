<?php
    // Savings History (member): every month saved and every approved withdrawal, with a running balance
    // (Dashboard::memberStatement). ?year=YYYY filters; ?export=csv downloads the statement.
    require_once('../../config/classes/View.php');
    require_once('../../config/classes/User.php');
    require_once('../../config/classes/Dashboard.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../../auth/') || !User::isStaff('../../auth/logout.php')) {
        exit();
    }
    $employeeId = $user->getEmployeeId($_SESSION['username']);
    $memberId = $user->getMemberId($employeeId);
    $statement = (new Dashboard())->memberStatement($memberId, $employeeId);
    $lines = $statement['lines'];

    $years = array_values(array_unique(array_map(function ($l) { return substr($l['date'], 0, 4); }, $lines)));
    rsort($years);
    $year = isset($_GET['year']) && in_array($_GET['year'], $years, true) ? $_GET['year'] : '';
    $shown = array_values(array_filter($lines, function ($l) use ($year) { return $year === '' || substr($l['date'], 0, 4) === $year; }));
    $when = function ($l) { return $l['exact'] ? date('d M Y', strtotime($l['date'])) : date('M Y', strtotime($l['date'])); };

    if (isset($_GET['export']) && $_GET['export'] === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="savings_statement_' . str_replace('/', '-', $_SESSION['username']) . ($year ? '_' . $year : '') . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Date', 'Description', 'Saved', 'Withdrawn', 'Balance']);
        foreach ($shown as $l) {
            fputcsv($out, [$when($l), $l['label'], $l['credit'] ?: '', $l['debit'] ?: '', $l['balance']]);
        }
        fclose($out);
        exit();
    }

    $totalSaved = array_sum(array_column($lines, 'credit'));
    $totalWithdrawn = array_sum(array_column($lines, 'debit'));
    $savedThisYear = array_sum(array_map(function ($l) { return substr($l['date'], 0, 4) === date('Y') ? $l['credit'] : 0; }, $lines));
    $h = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
    $money = function ($v) { return '₦' . number_format((float) $v, 2); };
    echo $view->subHeader('../../');
?>
    <?php echo $view->subPartialNav('../../'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->staffSideNav(); ?>
      <div class="main-panel">
        <div class="content-wrapper fud-dash">

          <div class="fud-hero">
            <div class="fud-hero-date">Savings</div>
            <h2><i class="mdi mdi-book-open-variant"></i> Savings History</h2>
            <p>Every amount saved and every withdrawal, with your balance after each.</p>
            <div class="fud-hero-actions">
              <a href="savings_withdrawal.php"><i class="mdi mdi-bank-transfer-out"></i> Withdraw savings</a>
              <a href="update_savings.php"><i class="mdi mdi-cash-refund"></i> Change monthly savings</a>
            </div>
          </div>

          <div class="fud-stats">
            <div class="fud-stat"><div class="fud-stat-icon fud-green"><i class="mdi mdi-safe"></i></div>
              <div><div class="fud-stat-label">Savings balance</div><div class="fud-stat-value"><?php echo $money($statement['balance']); ?></div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-blue"><i class="mdi mdi-arrow-down-bold-circle"></i></div>
              <div><div class="fud-stat-label">Total saved</div><div class="fud-stat-value"><?php echo $money($totalSaved); ?></div>
                <div class="fud-stat-sub"><?php echo $money($savedThisYear); ?> in <?php echo date('Y'); ?></div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-amber"><i class="mdi mdi-arrow-up-bold-circle"></i></div>
              <div><div class="fud-stat-label">Total withdrawn</div><div class="fud-stat-value"><?php echo $money($totalWithdrawn); ?></div></div></div>
          </div>

          <?php if ($statement['pending']) { ?>
            <div class="fud-section">
              <div class="fud-section-head"><h5><i class="mdi mdi-clock-outline"></i> Pending – not in your balance yet</h5></div>
              <div class="fud-section-body">
                <table class="table fud-table mb-0"><tbody>
                  <?php foreach ($statement['pending'] as $p) { ?>
                    <tr><td><?php echo $h($p['label']); ?><div class="text-muted small">since <?php echo date('d M Y', strtotime($p['date'])); ?></div></td>
                      <td class="num <?php echo $p['amount'] < 0 ? 'text-danger' : 'text-success'; ?>"><?php echo ($p['amount'] < 0 ? '−' : '+') . $money(abs($p['amount'])); ?></td></tr>
                  <?php } ?>
                </tbody></table>
              </div>
            </div>
          <?php } ?>

          <div class="fud-section">
            <div class="fud-section-body">
              <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div class="fud-tabs mb-2">
                  <a class="<?php echo $year === '' ? 'active' : ''; ?>" href="?">All <span><?php echo count($lines); ?></span></a>
                  <?php foreach ($years as $y) { ?>
                    <a class="<?php echo $year === $y ? 'active' : ''; ?>" href="?year=<?php echo $y; ?>"><?php echo $y; ?></a>
                  <?php } ?>
                </div>
                <div class="fud-tabs mb-2" id="kindFilter">
                  <a href="#" class="active" data-kind="all">Everything</a>
                  <a href="#" data-kind="saving">Savings</a>
                  <a href="#" data-kind="withdrawal">Withdrawals</a>
                </div>
              </div>
              <?php if (!$shown) { ?>
                <p class="fud-empty">No savings or withdrawals recorded yet.</p>
              <?php } else { ?>
              <div class="table-responsive">
                <table class="table fud-table" id="statement">
                  <thead><tr><th>Date</th><th>Description</th><th class="num">Saved (₦)</th><th class="num">Withdrawn (₦)</th><th class="num">Balance (₦)</th></tr></thead>
                  <tbody>
                  <?php foreach (array_reverse($shown) as $l) { ?>
                    <tr data-kind="<?php echo $l['kind']; ?>">
                      <td class="text-nowrap"><?php echo $when($l); ?></td>
                      <td><?php echo $h($l['label']); ?></td>
                      <td class="num text-success"><?php echo $l['credit'] ? number_format($l['credit'], 2) : ''; ?></td>
                      <td class="num text-danger"><?php echo $l['debit'] ? number_format($l['debit'], 2) : ''; ?></td>
                      <td class="num"><strong><?php echo number_format($l['balance'], 2); ?></strong></td>
                    </tr>
                  <?php } ?>
                  </tbody>
                </table>
              </div>
              <div class="d-flex justify-content-between align-items-center mt-2">
                <p class="text-muted small mb-0">Newest first. Monthly savings uploaded without a date are shown by month.</p>
                <a class="btn btn-sm btn-outline-secondary" href="?<?php echo $year ? 'year=' . $year . '&amp;' : ''; ?>export=csv"><i class="mdi mdi-download"></i> Download (CSV)</a>
              </div>
              <?php } ?>
            </div>
          </div>

        </div>
        <?php echo $view->subFooter('../../'); ?>

<script>
(function () {
    var filter = document.getElementById('kindFilter');
    if (!filter) { return; }
    filter.addEventListener('click', function (e) {
        var link = e.target.closest('a[data-kind]');
        if (!link) { return; }
        e.preventDefault();
        filter.querySelectorAll('a').forEach(function (a) { a.classList.toggle('active', a === link); });
        var kind = link.getAttribute('data-kind');
        document.querySelectorAll('#statement tbody tr').forEach(function (tr) {
            tr.style.display = kind === 'all' || tr.getAttribute('data-kind') === kind ? '' : 'none';
        });
    });
})();
</script>
