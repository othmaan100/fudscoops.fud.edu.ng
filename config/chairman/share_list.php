<?php
// Chairman's share lists (Chairman/manage_share_purchase.php, manage_share_transfer.php, manage_share_conversion.php).
// Expects $view and $kind ('purchase' / 'transfer' / 'conversion'). Shows the requests waiting for the Chairman, with
// a checkbox each for bulk authorization (js/fud-bulk.js) and a link to the detail page, where one can be decided.
require_once __DIR__ . '/decisions.php';

$con = (new DB())->getConnection();
$h = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
$money = function ($v) { return '₦' . number_format((float) $v, 2); };
$name = function ($f, $l, $o) { return preg_replace('/\s+/', ' ', trim($f . ' ' . $l . ' ' . $o)); };

if ($kind === 'purchase') {
    $title = 'Share purchases';
    $type = 'share_purchase';
    $intro = 'Members buying shares. The Secretary has endorsed each one; authorize to send it to the Treasurer, who confirms the payment.';
    $rows = $con->query("SELECT s.shares_id AS id, s.amount_paid, s.unit_price, s.teller_no, s.date, s.secretary_comment, e.sp_no, e.fname, e.lname, e.oname
                         FROM fudscoops_shares s JOIN fudscoops_member m ON m.member_id = s.member_id JOIN employee e ON e.employee_id = m.employee_id
                         WHERE s.entry_type = 'purchase' AND s.share_status = 0 AND s.secretary_endorsement = 1 AND s.chairman_approval = 0
                           AND s.shares_is_deleted = 0 ORDER BY s.date")->fetchAll(PDO::FETCH_ASSOC);
    $waitingSecretary = (int) $con->query("SELECT COUNT(*) FROM fudscoops_shares WHERE entry_type = 'purchase' AND share_status = 0
                                           AND secretary_endorsement = 0 AND shares_is_deleted = 0")->fetchColumn();
    $detail = function ($r) { return 'view_share_details.php?shares_id=' . (int) $r['id']; };
} elseif ($kind === 'transfer') {
    $title = 'Share transfers';
    $type = 'share_transfer';
    $intro = 'Members passing shares to another member. The receiver has accepted and the Secretary has endorsed each one.';
    $rows = $con->query("SELECT t.transfer_id AS id, t.worth AS amount_paid, t.transfer_date AS date, t.secretary_comment,
                                e2.sp_no, e2.fname, e2.lname, e2.oname, e1.sp_no AS to_sp, e1.fname AS to_fname, e1.lname AS to_lname, e1.oname AS to_oname
                         FROM fudscoops_shares_transfer t
                         LEFT JOIN employee e1 ON e1.employee_id = t.received_employee_id
                         LEFT JOIN employee e2 ON e2.employee_id = t.transfer_employee_id
                         WHERE t.share_transfer_is_deleted = 0 AND t.is_accepted = 1 AND t.share_transfer_status = 0
                           AND t.secretary_endorsement = 1 AND t.chairman_approval = 0 ORDER BY t.transfer_date")->fetchAll(PDO::FETCH_ASSOC);
    $waitingSecretary = (int) $con->query("SELECT COUNT(*) FROM fudscoops_shares_transfer WHERE share_transfer_is_deleted = 0 AND share_transfer_status = 0
                                           AND secretary_endorsement = 0")->fetchColumn();
    $detail = function ($r) { return 'view_share_transfer_details.php?transfer_id=' . (int) $r['id']; };
} else {
    $title = 'Share conversions';
    $type = 'share_conversion';
    $intro = 'Members converting savings into shares. Authorize to send each one to the Treasurer, who completes it.';
    $rows = $con->query("SELECT c.conversion_id AS id, c.amount_paid, c.conversion_date AS date, e.sp_no, e.fname, e.lname, e.oname
                         FROM fudscoops_shares_conversion c JOIN fudscoops_member m ON m.member_id = c.member_id JOIN employee e ON e.employee_id = m.employee_id
                         WHERE c.share_conversion_is_deleted = 0 AND c.conversion_status = 0 AND c.chairman_approval = 0
                         ORDER BY c.conversion_date")->fetchAll(PDO::FETCH_ASSOC);
    $waitingSecretary = 0;
    $detail = function ($r) { return 'view_share_conversion_details.php?conversion_id=' . (int) $r['id']; };
}
$total = array_sum(array_column($rows, 'amount_paid'));

echo $view->subHeader('../');
?>
    <?php echo $view->subPartialNav('../'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->subSideNav(); ?>
      <div class="main-panel">
        <div class="content-wrapper fud-dash">

          <div class="fud-hero">
            <div class="fud-hero-date">Shares</div>
            <h2><i class="mdi mdi-chart-pie"></i> <?php echo $title; ?> awaiting your authorization</h2>
            <p><?php echo $intro; ?> Open one to see its details and decide, or tick several and authorize them together.</p>
            <div class="fud-hero-actions">
              <a href="manage_share_purchase.php"><i class="mdi mdi-cart"></i> Purchases</a>
              <a href="manage_share_transfer.php"><i class="mdi mdi-swap-horizontal"></i> Transfers</a>
              <a href="manage_share_conversion.php"><i class="mdi mdi-autorenew"></i> Conversions</a>
            </div>
          </div>

          <div class="fud-stats">
            <div class="fud-stat"><div class="fud-stat-icon fud-amber"><i class="mdi mdi-clock-outline"></i></div>
              <div><div class="fud-stat-label">Awaiting you</div><div class="fud-stat-value"><?php echo count($rows); ?></div>
                <div class="fud-stat-sub"><?php echo $money($total); ?></div></div></div>
            <?php if ($kind !== 'conversion') { ?>
            <div class="fud-stat"><div class="fud-stat-icon fud-blue"><i class="mdi mdi-account-clock"></i></div>
              <div><div class="fud-stat-label">Awaiting the Secretary</div><div class="fud-stat-value"><?php echo $waitingSecretary; ?></div>
                <div class="fud-stat-sub">come to you once endorsed</div></div></div>
            <?php } ?>
          </div>

          <div class="fud-section">
            <div class="fud-section-head"><h5><i class="mdi mdi-format-list-checks"></i> Requests</h5></div>
            <div class="fud-section-body">
              <?php if (!$rows) { ?>
                <p class="fud-empty"><i class="mdi mdi-check-circle-outline"></i> Nothing is waiting for you.</p>
              <?php } else { ?>
              <input class="form-control fud-search" type="search" id="shareSearch" placeholder="Search name or staff number…">
              <div class="table-responsive" data-bulk="<?php echo $type; ?>">
                <table class="table fud-table mb-0" id="shareTable">
                  <thead><tr><th style="width:28px"><input type="checkbox" class="bulk-all" title="Select all"></th>
                    <th>Member</th><?php if ($kind === 'transfer') { ?><th>To</th><?php } ?><th class="num">Amount</th><th>Date</th><th></th></tr></thead>
                  <tbody>
                  <?php foreach ($rows as $r) {
                      $who = $name($r['fname'], $r['lname'], $r['oname']);
                      $label = $who . ' (' . $r['sp_no'] . ') – ' . $money($r['amount_paid']); ?>
                    <tr>
                      <td><input type="checkbox" class="bulk-check" value="<?php echo (int) $r['id']; ?>" data-label="<?php echo $h($label); ?>"></td>
                      <td style="white-space:normal;min-width:170px"><a href="<?php echo $detail($r); ?>" class="font-weight-bold text-dark"><?php echo $h($who); ?></a>
                        <div class="small text-muted"><?php echo $h($r['sp_no']); ?></div>
                        <?php if (!empty($r['secretary_comment'])) { ?><div class="small text-muted"><i class="mdi mdi-comment-text-outline"></i> Secretary: <?php echo $h($r['secretary_comment']); ?></div><?php } ?></td>
                      <?php if ($kind === 'transfer') { ?><td style="white-space:normal"><?php echo $h($name($r['to_fname'], $r['to_lname'], $r['to_oname'])); ?><div class="small text-muted"><?php echo $h($r['to_sp']); ?></div></td><?php } ?>
                      <td class="num text-nowrap"><strong><?php echo $money($r['amount_paid']); ?></strong>
                        <?php if ($kind === 'purchase' && (float) $r['unit_price'] > 0) { ?><div class="small text-muted"><?php echo number_format($r['amount_paid'] / $r['unit_price'], 2); ?> units</div><?php } ?></td>
                      <td class="text-nowrap"><?php echo $r['date'] ? date('d M Y', strtotime($r['date'])) : '–'; ?></td>
                      <td class="text-right"><a class="btn btn-sm btn-outline-primary" href="<?php echo $detail($r); ?>">Details</a></td>
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
        <?php echo chairmanBulkScript(); ?>
<script>
(function () {
    var s = document.getElementById('shareSearch');
    if (!s) { return; }
    s.addEventListener('input', function () {
        var q = s.value.toLowerCase();
        document.querySelectorAll('#shareTable tbody tr').forEach(function (r) { r.style.display = r.textContent.toLowerCase().indexOf(q) !== -1 ? '' : 'none'; });
    });
})();
</script>
