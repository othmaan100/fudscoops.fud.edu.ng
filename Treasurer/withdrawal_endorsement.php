<?php
    // Savings withdrawals for the Treasurer: requests waiting for the Treasurer (each opens view_withdrawal_details.php
    // to set the amount or reject), and those sent to the Chairman. Paper and online requests can also be decided in
    // bulk by uploading a CSV (uploadWithdrawals.php).
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    require_once('../config/classes/LoanControls.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../auth/') || !User::isBur('../auth/logout.php')) {
        exit();
    }
    $h = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
    $money = function ($v) { return ((float) $v < 0 ? '−' : '') . '₦' . number_format(abs((float) $v), 2); };

    $con = (new DB())->getConnection();
    $rows = $con->query("SELECT w.*, b.name AS bank_name, e.sp_no, e.fname, e.lname, e.oname, e.dept
                         FROM fudscoops_withrawals w
                         JOIN fudscoops_member m ON m.member_id = w.member_id
                         JOIN employee e ON e.employee_id = m.employee_id
                         LEFT JOIN banks b ON b.id = w.bank_id
                         WHERE w.chairman_approval = 0 AND w.status IN (0, 1) AND COALESCE(w.withrawals_is_deleted, 0) = 0
                         ORDER BY w.withrawals_id")->fetchAll(PDO::FETCH_ASSOC);
    $groups = ['you' => [], 'chairman' => []];
    $requested = 0;
    $overLimit = 0;
    foreach ($rows as $r) {
        $r['name'] = preg_replace('/\s+/', ' ', trim($r['fname'] . ' ' . $r['lname'] . ' ' . $r['oname']));
        $r['balance'] = LoanControls::savingsBalance($r['member_id']);
        $r['max'] = max(0, $r['balance'] - LoanControls::withdrawalMinBalance());
        if ((int) $r['status'] === 1) {
            $groups['chairman'][] = $r;
        } else {
            $r['over'] = (float) $r['proposed_withrawal_amount'] > $r['max'] + 0.005;
            $overLimit += $r['over'] ? 1 : 0;
            $requested += (float) $r['proposed_withrawal_amount'];
            $groups['you'][] = $r;
        }
    }
    $tab = isset($_GET['tab']) && $_GET['tab'] === 'chairman' ? 'chairman' : 'you';

    echo $view->subHeader('../');
?>
    <?php echo $view->subPartialNav('../'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->treasurerSideNav(); ?>
      <div class="main-panel">
        <div class="content-wrapper fud-dash">

          <div class="fud-hero">
            <div class="fud-hero-date">Savings withdrawals</div>
            <h2><i class="mdi mdi-bank-transfer-out"></i> Savings withdrawals</h2>
            <p>Members asking to withdraw part of their savings. Open a request to set the amount to pay (or reject it with a reason);
              it then goes to the Chairman. To decide many at once, including paper applications, upload a CSV.</p>
            <div class="fud-hero-actions">
              <a href="uploadWithdrawals.php"><i class="mdi mdi-cloud-upload"></i> Upload withdrawals</a>
              <a href="complete_withdrawals.php"><i class="mdi mdi-account-remove"></i> Complete withdrawals</a>
              <a href="approved_withdrawals.php"><i class="mdi mdi-check-all"></i> Approved withdrawals</a>
            </div>
          </div>

          <div class="fud-stats">
            <a class="fud-stat fud-stat-link" href="?tab=you"><div class="fud-stat-icon fud-amber"><i class="mdi mdi-clock-outline"></i></div>
              <div><div class="fud-stat-label">Awaiting you</div><div class="fud-stat-value"><?php echo count($groups['you']); ?></div>
                <div class="fud-stat-sub"><?php echo $money($requested); ?> requested</div></div></a>
            <div class="fud-stat"><div class="fud-stat-icon <?php echo $overLimit ? 'fud-rose' : 'fud-green'; ?>"><i class="mdi mdi-alert-circle-outline"></i></div>
              <div><div class="fud-stat-label">Asking for more than allowed</div><div class="fud-stat-value"><?php echo $overLimit; ?></div>
                <div class="fud-stat-sub"><?php echo $money(LoanControls::withdrawalMinBalance()); ?> must stay in savings</div></div></div>
            <a class="fud-stat fud-stat-link" href="?tab=chairman"><div class="fud-stat-icon fud-blue"><i class="mdi mdi-gavel"></i></div>
              <div><div class="fud-stat-label">With the Chairman</div><div class="fud-stat-value"><?php echo count($groups['chairman']); ?></div>
                <div class="fud-stat-sub"><?php echo $money(array_sum(array_column($groups['chairman'], 'approved_withrawal_amount'))); ?> approved by you</div></div></a>
          </div>

          <div class="fud-section">
            <div class="fud-section-body">
              <div class="d-flex flex-wrap justify-content-between align-items-center mb-2" style="gap:.5rem">
                <div class="fud-tabs mb-0">
                  <a href="?tab=you" class="<?php echo $tab === 'you' ? 'active' : ''; ?>">Awaiting you <span><?php echo count($groups['you']); ?></span></a>
                  <a href="?tab=chairman" class="<?php echo $tab === 'chairman' ? 'active' : ''; ?>">With the Chairman <span><?php echo count($groups['chairman']); ?></span></a>
                </div>
                <?php if ($tab === 'you' && $groups['you']) { ?>
                  <a class="btn btn-sm btn-outline-secondary" href="uploadWithdrawals.php?export=applied" title="These requests in the upload layout, with the amount that can be paid">
                    <i class="mdi mdi-download"></i> Download as upload template</a>
                <?php } ?>
              </div>
              <?php $list = $groups[$tab]; if (!$list) { ?>
                <p class="fud-empty"><i class="mdi mdi-check-circle-outline"></i> <?php echo $tab === 'you' ? 'No withdrawal requests are waiting for you.' : 'Nothing is with the Chairman.'; ?></p>
              <?php } else { ?>
                <input class="form-control fud-search" type="search" id="wdSearch" placeholder="Search name, staff number, bank…">
                <div class="table-responsive">
                  <table class="table fud-table mb-0" id="wdTable">
                    <thead><tr><th>#</th><th>Member</th><th>Requested</th><th class="num">Asked</th>
                      <?php if ($tab === 'chairman') { ?><th class="num">You approved</th><?php } ?>
                      <th class="num">Savings balance</th><th class="num">Most allowed</th><th>Pay into</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($list as $r) { ?>
                      <tr>
                        <td class="text-muted"><?php echo sprintf('%04d', $r['withrawals_id']); ?></td>
                        <td style="white-space:normal;min-width:170px"><a class="font-weight-bold text-dark" href="view_withdrawal_details.php?withrawals_id=<?php echo (int) $r['withrawals_id']; ?>"><?php echo $h($r['name']); ?></a>
                          <div class="small text-muted"><?php echo $h($r['sp_no']); ?><?php echo $r['dept'] ? ' · ' . $h($r['dept']) : ''; ?></div></td>
                        <td class="text-nowrap"><?php echo $r['proposed_date'] ? date('d M Y', strtotime($r['proposed_date'])) : '<span class="text-muted">uploaded</span>'; ?></td>
                        <td class="num text-nowrap"><?php echo $r['proposed_withrawal_amount'] !== null ? $money($r['proposed_withrawal_amount']) : '–'; ?>
                          <?php if (!empty($r['over'])) { ?><div><span class="fud-pill no">over the limit</span></div><?php } ?></td>
                        <?php if ($tab === 'chairman') { ?><td class="num text-nowrap"><strong><?php echo $money($r['approved_withrawal_amount']); ?></strong></td><?php } ?>
                        <td class="num text-nowrap"><?php echo $money($r['balance']); ?></td>
                        <td class="num text-nowrap"><?php echo $money($r['max']); ?></td>
                        <td class="small" style="white-space:normal;min-width:140px"><?php echo $h($r['bank_name'] ?: $r['bank_id'] ?: '–'); ?>
                          <div class="text-muted"><?php echo $h($r['acount_number']); ?><?php echo $r['account_name'] ? ' · ' . $h($r['account_name']) : ''; ?></div></td>
                        <td class="text-right"><a class="btn btn-sm <?php echo $tab === 'you' ? 'btn-primary' : 'btn-outline-primary'; ?>" href="view_withdrawal_details.php?withrawals_id=<?php echo (int) $r['withrawals_id']; ?>"><?php echo $tab === 'you' ? 'Review' : 'Open'; ?></a></td>
                      </tr>
                    <?php } ?>
                    </tbody>
                  </table>
                </div>
              <?php } ?>
            </div>
          </div>

        </div>
        <?php echo $view->subFooter('../'); ?>
<script>
(function () {
    var s = document.getElementById('wdSearch');
    if (!s) { return; }
    s.addEventListener('input', function () {
        var q = s.value.toLowerCase();
        document.querySelectorAll('#wdTable tbody tr').forEach(function (r) { r.style.display = r.textContent.toLowerCase().indexOf(q) !== -1 ? '' : 'none'; });
    });
})();
</script>
