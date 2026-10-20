<?php
    // Members' savings by month: what each approved member saved in a month (fudscoops_savings by its month/year) against
    // their monthly savings, who saved less, and who has nothing recorded. ?view=year shows the month-by-month totals.
    // ?export=csv downloads the month. Savings are recorded on uploadDeposit.php.
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    require_once('../config/classes/MemberG1.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../auth/') || !User::isBur('../auth/logout.php')) {
        exit();
    }
    $h = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
    $money = function ($v) { return ((float) $v < 0 ? '−' : '') . '₦' . number_format(abs((float) $v), 2); };
    $con = (new DB())->getConnection();
    $ymExpr = "CONCAT(s.year, '-', LPAD(s.month, 2, '0'))";

    $mode = isset($_GET['view']) && $_GET['view'] === 'year' ? 'year' : 'month';
    $latest = $con->query("SELECT MAX($ymExpr) FROM fudscoops_savings s")->fetchColumn() ?: date('Y-m');
    $month = isset($_GET['month']) && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $_GET['month']) ? $_GET['month'] : $latest;
    $year = isset($_GET['year']) && preg_match('/^\d{4}$/', $_GET['year']) ? $_GET['year'] : substr($month, 0, 4);

    if ($mode === 'year') {
        $stmt = $con->prepare("SELECT $ymExpr ym, COUNT(DISTINCT s.member_idmember) members, SUM(s.savings_amount) total, COUNT(*) entries
                               FROM fudscoops_savings s WHERE s.year = :y GROUP BY ym ORDER BY ym DESC");
        $stmt->execute([':y' => $year]);
        $months = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $years = $con->query("SELECT DISTINCT year FROM fudscoops_savings ORDER BY year DESC")->fetchAll(PDO::FETCH_COLUMN);
    } else {
        // Approved members with their monthly savings and balance, and what they saved this month
        $members = (new MemberG1())->treasurerMemberDirectory();
        $stmt = $con->prepare("SELECT s.member_idmember, SUM(s.savings_amount) amt, COUNT(*) n, MAX(s.savings_date) d
                               FROM fudscoops_savings s WHERE $ymExpr = :m GROUP BY s.member_idmember");
        $stmt->execute([':m' => $month]);
        $saved = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $s) {
            $saved[(int) $s['member_idmember']] = $s;
        }
        $rows = [];
        $known = [];
        foreach ($members as $m) {
            $known[(int) $m['member_id']] = true;
            $s = isset($saved[(int) $m['member_id']]) ? $saved[(int) $m['member_id']] : null;
            $amount = $s ? (float) $s['amt'] : 0;
            $monthly = (float) $m['proposed_monthly_savings'];
            $status = !$s ? 'none' : ($monthly > 0 && $amount + 0.005 < $monthly ? 'less' : 'saved');
            $rows[] = ['member_id' => $m['member_id'], 'sp_no' => $m['sp_no'], 'name' => preg_replace('/\s+/', ' ', trim($m['fname'] . ' ' . $m['lname'] . ' ' . $m['oname'])),
                       'dept' => $m['dept'], 'monthly' => $monthly, 'amount' => $amount, 'entries' => $s ? (int) $s['n'] : 0, 'date' => $s ? $s['d'] : null,
                       'balance' => (float) $m['savings_balance'], 'status' => $status];
        }
        // Savings recorded for people who are not (or no longer) approved members
        $others = array_diff_key($saved, $known);
        $tabs = ['all' => 'All members', 'saved' => 'Saved', 'less' => 'Saved less than monthly', 'none' => 'Nothing recorded'];
        $tab = isset($_GET['tab'], $tabs[$_GET['tab']]) ? $_GET['tab'] : 'all';
        $counts = ['all' => count($rows), 'saved' => 0, 'less' => 0, 'none' => 0];
        foreach ($rows as $r) {
            $counts[$r['status']]++;
        }
        $shown = $tab === 'all' ? $rows : array_values(array_filter($rows, function ($r) use ($tab) { return $r['status'] === $tab; }));
        $totalSaved = array_sum(array_column($saved, 'amt'));
        $expected = array_sum(array_column($rows, 'monthly'));
        $monthName = date('F Y', strtotime($month . '-01'));

        if (isset($_GET['export']) && $_GET['export'] === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="savings_' . $month . ($tab !== 'all' ? '_' . $tab : '') . '.csv"');
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Staff Number', 'Name', 'Department', 'Monthly savings', 'Saved in ' . $monthName, 'Status', 'Savings balance']);
            foreach ($shown as $r) {
                fputcsv($out, [$r['sp_no'], $r['name'], $r['dept'], number_format($r['monthly'], 2, '.', ''), number_format($r['amount'], 2, '.', ''),
                               $r['status'] === 'none' ? 'Nothing recorded' : ($r['status'] === 'less' ? 'Less than monthly' : 'Saved'), number_format($r['balance'], 2, '.', '')]);
            }
            fclose($out);
            exit();
        }
    }
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
            <div class="fud-hero-date">Savings</div>
            <h2><i class="mdi mdi-wallet"></i> Members savings</h2>
            <p>What each member saved in a month against their monthly savings: who saved, who saved less, and who has nothing recorded.</p>
            <div class="fud-hero-actions">
              <a href="uploadDeposit.php"><i class="mdi mdi-cloud-upload"></i> Upload savings</a>
              <a href="treasurer_view_approved_savings.php"><i class="mdi mdi-calendar-edit"></i> Savings updates</a>
              <a href="member_list.php"><i class="mdi mdi-account-group"></i> Members</a>
            </div>
          </div>

          <div class="fud-tabs">
            <a href="?month=<?php echo $month; ?>" class="<?php echo $mode === 'month' ? 'active' : ''; ?>"><i class="mdi mdi-calendar-month"></i> By month</a>
            <a href="?view=year&amp;year=<?php echo $h($year); ?>" class="<?php echo $mode === 'year' ? 'active' : ''; ?>"><i class="mdi mdi-chart-bar"></i> Yearly summary</a>
          </div>

          <?php if ($mode === 'year') { ?>
          <div class="fud-section">
            <div class="fud-section-head d-flex flex-wrap justify-content-between align-items-center">
              <h5><i class="mdi mdi-chart-bar"></i> Savings in <?php echo $h($year); ?></h5>
              <form method="get"><input type="hidden" name="view" value="year">
                <select name="year" class="form-control form-control-sm" onchange="this.form.submit()">
                  <?php foreach ($years as $y) { ?><option value="<?php echo $h($y); ?>"<?php echo (string) $y === $year ? ' selected' : ''; ?>><?php echo $h($y); ?></option><?php } ?>
                </select></form>
            </div>
            <div class="fud-section-body">
              <?php if (!$months) { ?><p class="fud-empty">No savings recorded in <?php echo $h($year); ?>.</p><?php } else {
                  $max = max(array_map('floatval', array_column($months, 'total'))); ?>
              <div class="fud-facts mb-3">
                <div><span>Saved in <?php echo $h($year); ?></span><strong><?php echo $money(array_sum(array_column($months, 'total'))); ?></strong></div>
                <div><span>Months recorded</span><?php echo count($months); ?></div>
              </div>
              <div class="table-responsive">
                <table class="table fud-table mb-0">
                  <thead><tr><th>Month</th><th class="num">Members</th><th class="num">Saved</th><th style="min-width:160px"></th><th></th></tr></thead>
                  <tbody>
                  <?php foreach ($months as $m) { ?>
                    <tr><td class="text-nowrap"><strong><?php echo date('F', strtotime($m['ym'] . '-01')); ?></strong></td>
                      <td class="num"><?php echo number_format($m['members']); ?></td>
                      <td class="num"><?php echo $money($m['total']); ?></td>
                      <td><div class="progress" style="height:8px"><div class="progress-bar bg-success" style="width:<?php echo $max > 0 ? max(2, round($m['total'] / $max * 100)) : 0; ?>%"></div></div></td>
                      <td class="text-right"><a class="btn btn-sm btn-outline-primary" href="?month=<?php echo $h($m['ym']); ?>">Open</a></td></tr>
                  <?php } ?>
                  </tbody>
                </table>
              </div>
              <?php } ?>
            </div>
          </div>

          <?php } else { ?>
          <div class="fud-section">
            <div class="fud-section-body d-flex flex-wrap align-items-center justify-content-between">
              <div class="d-flex align-items-center">
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
            <div class="fud-stat"><div class="fud-stat-icon fud-green"><i class="mdi mdi-cash-multiple"></i></div>
              <div><div class="fud-stat-label">Saved in <?php echo date('F', strtotime($month . '-01')); ?></div><div class="fud-stat-value"><?php echo $money($totalSaved); ?></div>
                <div class="fud-stat-sub"><?php echo $money($expected); ?> monthly savings due</div></div></div>
            <a class="fud-stat fud-stat-link" href="?month=<?php echo $month; ?>&amp;tab=saved"><div class="fud-stat-icon fud-blue"><i class="mdi mdi-account-check"></i></div>
              <div><div class="fud-stat-label">Saved their full amount</div><div class="fud-stat-value"><?php echo number_format($counts['saved']); ?></div>
                <div class="fud-stat-sub">of <?php echo number_format($counts['all']); ?> members</div></div></a>
            <a class="fud-stat fud-stat-link" href="?month=<?php echo $month; ?>&amp;tab=less"><div class="fud-stat-icon fud-amber"><i class="mdi mdi-arrow-down-bold-circle-outline"></i></div>
              <div><div class="fud-stat-label">Saved less than monthly</div><div class="fud-stat-value"><?php echo number_format($counts['less']); ?></div></div></a>
            <a class="fud-stat fud-stat-link" href="?month=<?php echo $month; ?>&amp;tab=none"><div class="fud-stat-icon <?php echo $counts['none'] ? 'fud-rose' : 'fud-green'; ?>"><i class="mdi mdi-account-alert"></i></div>
              <div><div class="fud-stat-label">Nothing recorded</div><div class="fud-stat-value"><?php echo number_format($counts['none']); ?></div></div></a>
          </div>

          <?php if ($others) { ?>
            <div class="fud-notice"><i class="mdi mdi-information-outline"></i>
              <span><?php echo count($others); ?> savings record<?php echo count($others) === 1 ? '' : 's'; ?> this month (<?php echo $money(array_sum(array_column($others, 'amt'))); ?>) belong to people who are not approved members; they are included in the total but not in the list.</span></div>
          <?php } ?>

          <div class="fud-section">
            <div class="fud-section-body">
              <div class="fud-tabs">
                <?php foreach ($tabs as $k => $label) { ?>
                  <a class="<?php echo $tab === $k ? 'active' : ''; ?>" href="?month=<?php echo $month; ?>&amp;tab=<?php echo $k; ?>"><?php echo $label; ?> <span><?php echo number_format($counts[$k]); ?></span></a>
                <?php } ?>
              </div>
              <div class="d-flex flex-wrap justify-content-between align-items-center mb-2" style="gap:.5rem">
                <input type="search" id="svSearch" class="form-control fud-search mb-0" placeholder="Search name, staff number, department…">
                <div>
                  <span class="small text-muted mr-2" id="svCount"></span>
                  <a class="btn btn-sm btn-outline-secondary" href="?month=<?php echo $month; ?>&amp;tab=<?php echo $tab; ?>&amp;export=csv"><i class="mdi mdi-download"></i> Download CSV</a>
                </div>
              </div>
              <?php if (!$shown) { ?>
                <p class="fud-empty">No members here for <?php echo $monthName; ?>.</p>
              <?php } else { ?>
              <div class="table-responsive">
                <table class="table fud-table mb-0" id="svTable">
                  <thead><tr><th>Member</th><th>Department</th><th class="num">Monthly savings</th><th class="num">Saved in <?php echo date('M Y', strtotime($month . '-01')); ?></th><th>Status</th><th class="num">Savings balance</th></tr></thead>
                  <tbody>
                  <?php foreach ($shown as $r) { ?>
                    <tr>
                      <td style="white-space:normal;min-width:170px"><a href="member_profile.php?member_id=<?php echo (int) $r['member_id']; ?>" class="font-weight-bold text-dark"><?php echo $h($r['name']); ?></a>
                        <div class="small text-muted"><?php echo $h($r['sp_no']); ?></div></td>
                      <td class="small" style="white-space:normal"><?php echo $h($r['dept'] ?: '–'); ?></td>
                      <td class="num"><?php echo $money($r['monthly']); ?></td>
                      <td class="num"><?php echo $r['entries'] ? '<strong>' . $money($r['amount']) . '</strong>' . ($r['entries'] > 1 ? '<div class="small text-muted">' . $r['entries'] . ' entries</div>' : '') : '<span class="text-muted">–</span>'; ?></td>
                      <td><?php echo $r['status'] === 'saved' ? '<span class="fud-pill ok">Saved</span>' : ($r['status'] === 'less' ? '<span class="fud-pill wait">Less</span>' : '<span class="fud-pill no">Nothing</span>'); ?></td>
                      <td class="num<?php echo $r['balance'] < 0 ? ' text-danger' : ''; ?>"><?php echo $money($r['balance']); ?></td>
                    </tr>
                  <?php } ?>
                  </tbody>
                </table>
              </div>
              <div class="d-flex justify-content-between align-items-center mt-2" id="svPager">
                <button type="button" class="btn btn-sm btn-light" data-page="-1"><i class="mdi mdi-chevron-left"></i> Previous</button>
                <span class="small text-muted" id="svPage"></span>
                <button type="button" class="btn btn-sm btn-light" data-page="1">Next <i class="mdi mdi-chevron-right"></i></button>
              </div>
              <?php } ?>
            </div>
          </div>
          <?php } ?>

        </div>
        <?php echo $view->subFooter('../'); ?>

<script>
(function () {
    var table = document.getElementById('svTable');
    if (!table) { return; }
    var rows = Array.prototype.slice.call(table.tBodies[0].rows), matching = rows, page = 0, size = 50;
    var search = document.getElementById('svSearch');
    function apply() {
        var q = search.value.toLowerCase().trim();
        matching = rows.filter(function (r) { return !q || r.textContent.toLowerCase().indexOf(q) !== -1; });
        var pages = Math.max(1, Math.ceil(matching.length / size));
        page = Math.min(Math.max(page, 0), pages - 1);
        rows.forEach(function (r) { r.style.display = 'none'; });
        matching.slice(page * size, page * size + size).forEach(function (r) { r.style.display = ''; });
        document.getElementById('svCount').textContent = matching.length.toLocaleString() + ' member(s)';
        document.getElementById('svPage').textContent = 'Page ' + (page + 1) + ' of ' + pages;
        document.querySelector('#svPager [data-page="-1"]').disabled = page === 0;
        document.querySelector('#svPager [data-page="1"]').disabled = page >= pages - 1;
    }
    search.addEventListener('input', function () { page = 0; apply(); });
    document.querySelectorAll('#svPager [data-page]').forEach(function (b) {
        b.addEventListener('click', function () { page += parseInt(b.getAttribute('data-page'), 10); apply(); table.scrollIntoView({ block: 'start' }); });
    });
    apply();
})();
</script>
