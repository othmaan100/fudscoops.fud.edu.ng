<?php
    // Approved withdrawals register: every savings withdrawal and complete withdrawal the Chairman approved in a date range
    // (by approval date), with totals and a CSV download. Replaces view_withdrawals_report.php (the same list by date).
    // Requests still being decided are on withdrawal_endorsement.php and complete_withdrawals.php.
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../auth/') || !User::isBur('../auth/logout.php')) {
        exit();
    }
    $h = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
    $money = function ($v) { return '₦' . number_format((float) $v, 2); };
    $isDate = function ($d) { return is_string($d) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && strtotime($d); };

    // Range: quick presets or from/to (default this month)
    $presets = [
        'month' => ['This month', date('Y-m-01'), date('Y-m-d')],
        'last'  => ['Last month', date('Y-m-01', strtotime('first day of last month')), date('Y-m-t', strtotime('first day of last month'))],
        'year'  => ['This year', date('Y-01-01'), date('Y-m-d')],
        'all'   => ['All time', '2000-01-01', date('Y-m-d')],
    ];
    $preset = isset($_GET['range'], $presets[$_GET['range']]) ? $_GET['range'] : null;
    if ($preset) {
        list(, $from, $to) = $presets[$preset];
    } else {
        $from = isset($_GET['from']) && $isDate($_GET['from']) ? $_GET['from'] : date('Y-m-01');
        $to = isset($_GET['to']) && $isDate($_GET['to']) ? $_GET['to'] : date('Y-m-d');
        if ($from > $to) {
            list($from, $to) = [$to, $from];
        }
        $preset = !isset($_GET['from']) ? 'month' : null;
    }
    $type = isset($_GET['type']) && in_array($_GET['type'], ['savings', 'complete'], true) ? $_GET['type'] : '';

    $con = (new DB())->getConnection();
    $rows = [];
    if ($type !== 'complete') {
        // Old approvals did not record the approval date (0000-00-00): the request date is used instead
        $stmt = $con->prepare("SELECT 'savings' kind, w.withrawals_id id, CASE WHEN w.approved_date IS NULL OR w.approved_date < '1990-01-01' THEN w.proposed_date ELSE w.approved_date END approved_date, w.proposed_date, w.approved_withrawal_amount amount, 0 owed,
                                      w.acount_number, w.account_name, w.bank_id, b.name bank_name, e.sp_no, e.fname, e.lname, e.oname, m.member_id
                               FROM fudscoops_withrawals w
                               JOIN fudscoops_member m ON m.member_id = w.member_id
                               JOIN employee e ON e.employee_id = m.employee_id
                               LEFT JOIN banks b ON b.id = w.bank_id
                               WHERE w.chairman_approval = 1 AND COALESCE(w.withrawals_is_deleted, 0) = 0
                                 AND DATE(CASE WHEN w.approved_date IS NULL OR w.approved_date < '1990-01-01' THEN w.proposed_date ELSE w.approved_date END) BETWEEN :f AND :t");
        $stmt->execute([':f' => $from, ':t' => $to]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    if ($type !== 'savings') {
        $stmt = $con->prepare("SELECT 'complete' kind, c.comp_withdrwal_id id, c.date_approved approved_date, c.date_applied proposed_date, c.approved_amount amount,
                                      IF(c.settled_at IS NOT NULL, c.amount_owed, 0) owed, c.acount_number, c.account_name, c.bank_id, b.name bank_name,
                                      e.sp_no, e.fname, e.lname, e.oname, m.member_id
                               FROM fudscoops_complete_withrawal c
                               JOIN fudscoops_member m ON m.employee_id = c.employee_id
                               JOIN employee e ON e.employee_id = c.employee_id
                               LEFT JOIN banks b ON b.id = c.bank_id
                               WHERE c.chairman_approval = 1 AND COALESCE(c.complete_withrawal_is_deleted, 0) = 0
                                 AND DATE(c.date_approved) BETWEEN :f AND :t");
        $stmt->execute([':f' => $from, ':t' => $to]);
        $rows = array_merge($rows, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    usort($rows, function ($a, $b) { return strcmp($b['approved_date'], $a['approved_date']) ?: $b['id'] - $a['id']; });
    $who = function ($r) { return preg_replace('/\s+/', ' ', trim($r['fname'] . ' ' . $r['lname'] . ' ' . $r['oname'])); };
    $kindLabel = ['savings' => 'Savings withdrawal', 'complete' => 'Complete withdrawal'];

    if (isset($_GET['export']) && $_GET['export'] === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="approved_withdrawals_' . $from . '_to_' . $to . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Approved on', 'Type', 'Staff Number', 'Name', 'Amount paid', 'Paid to loans/commodity from savings', 'Bank', 'Account Number', 'Account Name', 'Requested on']);
        foreach ($rows as $r) {
            fputcsv($out, [substr($r['approved_date'], 0, 10), $kindLabel[$r['kind']], $r['sp_no'], $who($r), number_format((float) $r['amount'], 2, '.', ''),
                           $r['owed'] > 0 ? number_format((float) $r['owed'], 2, '.', '') : '', $r['bank_name'] ?: $r['bank_id'], $r['acount_number'], $r['account_name'],
                           $r['proposed_date'] ? substr($r['proposed_date'], 0, 10) : 'uploaded']);
        }
        fclose($out);
        exit();
    }

    $total = array_sum(array_column($rows, 'amount'));
    $members = count(array_unique(array_column($rows, 'member_id')));
    $counts = ['savings' => 0, 'complete' => 0];
    $sums = ['savings' => 0, 'complete' => 0];
    foreach ($rows as $r) {
        $counts[$r['kind']]++;
        $sums[$r['kind']] += (float) $r['amount'];
    }
    $qs = function ($extra = []) use ($from, $to, $type, $preset) {
        $p = $preset ? ['range' => $preset] : ['from' => $from, 'to' => $to];
        if ($type) { $p['type'] = $type; }
        return '?' . http_build_query(array_merge($p, $extra));
    };

    echo $view->subHeader('../');
?>
    <?php echo $view->subPartialNav('../'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->treasurerSideNav(); ?>
      <div class="main-panel">
        <div class="content-wrapper fud-dash">

          <div class="fud-hero">
            <div class="fud-hero-date">Savings withdrawals</div>
            <h2><i class="mdi mdi-check-all"></i> Approved withdrawals</h2>
            <p>Every withdrawal the Chairman approved, by approval date: savings withdrawals and complete withdrawals (members leaving).
              Use it to pay members and to reconcile payments.</p>
            <div class="fud-hero-actions">
              <a href="withdrawal_endorsement.php"><i class="mdi mdi-bank-transfer-out"></i> Requests being decided</a>
              <a href="complete_withdrawals.php"><i class="mdi mdi-account-remove"></i> Complete withdrawals</a>
              <a href="uploadWithdrawals.php"><i class="mdi mdi-cloud-upload"></i> Upload withdrawals</a>
            </div>
          </div>

          <div class="fud-section">
            <div class="fud-section-body d-flex flex-wrap align-items-center justify-content-between" style="gap:.75rem">
              <div class="fud-tabs mb-0">
                <?php foreach ($presets as $k => $p) { ?>
                  <a href="?<?php echo http_build_query(array_filter(['range' => $k, 'type' => $type])); ?>" class="<?php echo $preset === $k ? 'active' : ''; ?>"><?php echo $p[0]; ?></a>
                <?php } ?>
              </div>
              <form class="form-inline" method="get" style="gap:.4rem">
                <?php if ($type) { ?><input type="hidden" name="type" value="<?php echo $h($type); ?>"><?php } ?>
                <label class="small text-muted mr-1" for="from">From</label>
                <input type="date" id="from" name="from" class="form-control form-control-sm" value="<?php echo $h($from === '2000-01-01' ? '' : $from); ?>" required>
                <label class="small text-muted mx-1" for="to">to</label>
                <input type="date" id="to" name="to" class="form-control form-control-sm" value="<?php echo $h($to); ?>" required>
                <button class="btn btn-sm btn-outline-primary">Show</button>
              </form>
            </div>
          </div>

          <div class="fud-stats">
            <div class="fud-stat"><div class="fud-stat-icon fud-blue"><i class="mdi mdi-cash-multiple"></i></div>
              <div><div class="fud-stat-label">Total paid out</div><div class="fud-stat-value"><?php echo $money($total); ?></div>
                <div class="fud-stat-sub"><?php echo $preset === 'all' ? 'all time' : date('d M Y', strtotime($from)) . ' – ' . date('d M Y', strtotime($to)); ?></div></div></div>
            <a class="fud-stat fud-stat-link" href="<?php echo $h($qs(['type' => 'savings'])); ?>"><div class="fud-stat-icon fud-green"><i class="mdi mdi-bank-transfer-out"></i></div>
              <div><div class="fud-stat-label">Savings withdrawals</div><div class="fud-stat-value"><?php echo $counts['savings']; ?></div>
                <div class="fud-stat-sub"><?php echo $money($sums['savings']); ?></div></div></a>
            <a class="fud-stat fud-stat-link" href="<?php echo $h($qs(['type' => 'complete'])); ?>"><div class="fud-stat-icon fud-purple"><i class="mdi mdi-account-remove"></i></div>
              <div><div class="fud-stat-label">Complete withdrawals</div><div class="fud-stat-value"><?php echo $counts['complete']; ?></div>
                <div class="fud-stat-sub"><?php echo $money($sums['complete']); ?></div></div></a>
            <div class="fud-stat"><div class="fud-stat-icon fud-amber"><i class="mdi mdi-account-multiple"></i></div>
              <div><div class="fud-stat-label">Members paid</div><div class="fud-stat-value"><?php echo $members; ?></div></div></div>
          </div>

          <div class="fud-section">
            <div class="fud-section-head d-flex flex-wrap justify-content-between align-items-center">
              <div class="fud-tabs mb-0">
                <a href="<?php echo $h($qs(['type' => ''])); ?>" class="<?php echo $type === '' ? 'active' : ''; ?>">All</a>
                <a href="<?php echo $h($qs(['type' => 'savings'])); ?>" class="<?php echo $type === 'savings' ? 'active' : ''; ?>">Savings withdrawals</a>
                <a href="<?php echo $h($qs(['type' => 'complete'])); ?>" class="<?php echo $type === 'complete' ? 'active' : ''; ?>">Complete withdrawals</a>
              </div>
              <?php if ($rows) { ?><a class="btn btn-sm btn-outline-secondary" href="<?php echo $h($qs(['export' => 'csv'])); ?>"><i class="mdi mdi-download"></i> Download CSV</a><?php } ?>
            </div>
            <div class="fud-section-body">
              <?php if (!$rows) { ?>
                <p class="fud-empty">No withdrawals were approved in this period.</p>
              <?php } else { ?>
                <input class="form-control fud-search" type="search" id="wdSearch" placeholder="Search name, staff number, bank, account…">
                <div class="table-responsive">
                  <table class="table fud-table mb-0" id="wdTable">
                    <thead><tr><th>Approved</th><th>Member</th><th>Type</th><th class="num">Amount</th><th>Pay into</th><th>Requested</th></tr></thead>
                    <tbody>
                    <?php foreach ($rows as $r) { ?>
                      <tr>
                        <td class="text-nowrap"><?php echo date('d M Y', strtotime($r['approved_date'])); ?></td>
                        <td style="white-space:normal;min-width:170px"><a href="member_profile.php?member_id=<?php echo (int) $r['member_id']; ?>" class="font-weight-bold text-dark"><?php echo $h($who($r)); ?></a>
                          <div class="small text-muted"><?php echo $h($r['sp_no']); ?></div></td>
                        <td><span class="fud-pill <?php echo $r['kind'] === 'complete' ? 'wait' : 'ok'; ?>"><?php echo $r['kind'] === 'complete' ? 'Complete' : 'Savings'; ?></span></td>
                        <td class="num text-nowrap"><strong><?php echo $money($r['amount']); ?></strong>
                          <?php if ($r['owed'] > 0) { ?><div class="small text-muted" title="Paid to loans or commodity from savings">+ <?php echo $money($r['owed']); ?> to debts</div><?php } ?></td>
                        <td class="small" style="white-space:normal;min-width:150px"><?php echo $h($r['bank_name'] ?: ($r['bank_id'] ?: '–')); ?>
                          <div class="text-muted"><?php echo $h($r['acount_number'] ?: '–'); ?><?php echo $r['account_name'] ? ' · ' . $h($r['account_name']) : ''; ?></div></td>
                        <td class="small text-muted text-nowrap"><?php echo $r['proposed_date'] ? date('d M Y', strtotime($r['proposed_date'])) : 'uploaded'; ?></td>
                      </tr>
                    <?php } ?>
                    </tbody>
                    <tfoot><tr class="fud-total"><td colspan="3"><strong id="wdCount"><?php echo count($rows); ?> withdrawal(s)</strong></td><td class="num"><strong id="wdSum"><?php echo $money($total); ?></strong></td><td colspan="2"></td></tr></tfoot>
                  </table>
                </div>
              <?php } ?>
            </div>
          </div>

        </div>
        <?php echo $view->subFooter('../'); ?>

<script>
(function () {
    var search = document.getElementById('wdSearch');
    if (!search) { return; }
    var rows = document.querySelectorAll('#wdTable tbody tr');
    search.addEventListener('input', function () {
        var q = search.value.toLowerCase(), n = 0, sum = 0;
        rows.forEach(function (r) {
            var show = r.textContent.toLowerCase().indexOf(q) !== -1;
            r.style.display = show ? '' : 'none';
            if (show) { n++; sum += parseFloat(r.cells[3].querySelector('strong').textContent.replace(/[^0-9.]/g, '')) || 0; }
        });
        document.getElementById('wdCount').textContent = n + ' withdrawal(s)';
        document.getElementById('wdSum').textContent = '₦' + sum.toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    });
})();
</script>
