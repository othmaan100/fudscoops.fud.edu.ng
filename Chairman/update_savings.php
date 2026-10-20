<?php
    // Monthly savings update requests waiting for the Chairman (ajax/chairman_savings_update_decision.php).
    // Approving sends the request to the Treasurer, who fixes the amount (Treasurer/treasurer_view_approved_savings.php).
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    require_once('../config/classes/SavingsG1.php');
    require_once('../config/classes/LoanControls.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../auth/') || !User::isChairman('../auth/logout.php')) {
        exit();
    }
    $h = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
    $money = function ($v) { return ((float) $v < 0 ? '−' : '') . '₦' . number_format(abs((float) $v), 2); };

    $savings = new SavingsG1();
    $requests = $savings->savingsUpdatesAwaitingChairman();
    $recent = $savings->savingsUpdatesRecent();
    $minimum = SavingsG1::getMinMonthlySavings();
    $increase = 0;
    $belowMin = 0;
    foreach ($requests as $i => $r) {
        $requests[$i]['name'] = preg_replace('/\s+/', ' ', trim($r['fname'] . ' ' . $r['lname'] . ' ' . $r['oname']));
        $requests[$i]['change'] = (float) $r['savings_update_amount'] - (float) $r['proposed_monthly_savings'];
        $requests[$i]['balance'] = LoanControls::savingsBalance($r['member_id']);
        $increase += $r['newer_requests'] ? 0 : $requests[$i]['change'];
        $belowMin += (float) $r['savings_update_amount'] < $minimum ? 1 : 0;
    }

    echo $view->subHeader('../');
?>
    <?php echo $view->subPartialNav('../'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->subSideNav(); ?>
      <div class="main-panel">
        <div class="content-wrapper fud-dash">

          <div class="fud-hero">
            <div class="fud-hero-date">Savings</div>
            <h2><i class="mdi mdi-calendar-edit"></i> Monthly savings updates</h2>
            <p>Members asking to change how much is deducted from their salary for savings each month. Approve to send the request to the
              Treasurer, who fixes the new amount; reject to keep the current amount.</p>
            <div class="fud-hero-actions">
              <a href="member_list.php"><i class="mdi mdi-account-group"></i> Members</a>
            </div>
          </div>

          <div class="fud-stats">
            <div class="fud-stat"><div class="fud-stat-icon fud-amber"><i class="mdi mdi-clock-outline"></i></div>
              <div><div class="fud-stat-label">Awaiting you</div><div class="fud-stat-value"><?php echo count($requests); ?></div>
                <div class="fud-stat-sub">request<?php echo count($requests) === 1 ? '' : 's'; ?></div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-blue"><i class="mdi mdi-trending-up"></i></div>
              <div><div class="fud-stat-label">Change in monthly savings</div><div class="fud-stat-value"><?php echo ($increase > 0 ? '+' : '') . $money($increase); ?></div>
                <div class="fud-stat-sub">if all are approved</div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon <?php echo $belowMin ? 'fud-rose' : 'fud-green'; ?>"><i class="mdi mdi-alert-circle-outline"></i></div>
              <div><div class="fud-stat-label">Below the minimum</div><div class="fud-stat-value"><?php echo $belowMin; ?></div>
                <div class="fud-stat-sub">minimum <?php echo $money($minimum); ?> a month</div></div></div>
          </div>

          <div class="fud-section">
            <div class="fud-section-head d-flex flex-wrap justify-content-between align-items-center">
              <h5><i class="mdi mdi-format-list-checks"></i> Requests</h5>
              <?php if ($requests) { ?>
                <div class="mt-2 mt-md-0">
                  <button type="button" id="approveSelected" class="btn btn-sm btn-success" disabled><i class="mdi mdi-check"></i> Approve selected</button>
                  <button type="button" id="rejectSelected" class="btn btn-sm btn-outline-danger" disabled><i class="mdi mdi-close"></i> Reject selected</button>
                </div>
              <?php } ?>
            </div>
            <div class="fud-section-body">
              <div class="msg"></div>
              <?php if (!$requests) { ?>
                <p class="fud-empty"><i class="mdi mdi-check-circle-outline"></i> No savings update requests are waiting.</p>
              <?php } else { ?>
                <input class="form-control fud-search" type="search" id="suSearch" placeholder="Search name, staff number, department…">
                <div class="table-responsive">
                  <table class="table fud-table mb-0" id="suTable">
                    <thead>
                      <tr><th style="width:28px"><input type="checkbox" id="selectAll" title="Select all"></th><th>Member</th><th>Requested</th>
                        <th class="num">Current monthly</th><th class="num">New monthly</th><th class="num">Change</th><th class="num">Savings balance</th><th class="text-right">Decision</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($requests as $r) {
                        $low = (float) $r['savings_update_amount'] < $minimum; ?>
                      <tr data-id="<?php echo (int) $r['id']; ?>" data-label="<?php echo $h($r['name'] . ' (' . $r['sp_no'] . ') → ' . $money($r['savings_update_amount'])); ?>">
                        <td><input type="checkbox" class="su-check" value="<?php echo (int) $r['id']; ?>"></td>
                        <td style="white-space:normal;min-width:170px">
                          <a href="member_profile.php?member_id=<?php echo (int) $r['member_id']; ?>" class="font-weight-bold text-dark"><?php echo $h($r['name']); ?></a>
                          <div class="small text-muted"><?php echo $h($r['sp_no']); ?><?php echo $r['dept'] ? ' · ' . $h($r['dept']) : ''; ?></div>
                          <?php if ($r['newer_requests']) { ?><div><span class="fud-pill wait">replaced by a newer request</span></div><?php } ?>
                        </td>
                        <td class="text-nowrap"><?php echo $r['update_proposed_date'] ? date('d M Y', strtotime($r['update_proposed_date'])) : '–'; ?></td>
                        <td class="num text-nowrap"><?php echo $money($r['proposed_monthly_savings']); ?></td>
                        <td class="num text-nowrap"><strong<?php echo $low ? ' class="text-danger"' : ''; ?>><?php echo $money($r['savings_update_amount']); ?></strong>
                          <?php if ($low) { ?><div><span class="fud-pill no">below minimum</span></div><?php } ?></td>
                        <td class="num text-nowrap <?php echo $r['change'] > 0 ? 'text-success' : ($r['change'] < 0 ? 'text-danger' : 'text-muted'); ?>"><?php echo ($r['change'] > 0 ? '+' : '') . $money($r['change']); ?></td>
                        <td class="num text-nowrap"><?php echo $money($r['balance']); ?></td>
                        <td class="text-right text-nowrap">
                          <a href="view_savings_update.php?id=<?php echo (int) $r['id']; ?>" class="btn btn-sm btn-light">Details</a>
                          <button type="button" class="btn btn-sm btn-success su-approve"><i class="mdi mdi-check"></i> Approve</button>
                          <button type="button" class="btn btn-sm btn-outline-danger su-reject"><i class="mdi mdi-close"></i> Reject</button>
                        </td>
                      </tr>
                    <?php } ?>
                    </tbody>
                  </table>
                </div>
              <?php } ?>
            </div>
          </div>

          <?php if ($recent) { ?>
            <div class="fud-section">
              <div class="fud-section-head"><h5><i class="mdi mdi-history"></i> Decided in the last 30 days</h5></div>
              <div class="fud-section-body">
                <div class="table-responsive">
                  <table class="table fud-table mb-0">
                    <thead><tr><th>Date</th><th>Member</th><th class="num">Requested</th><th>Chairman</th><th>Treasurer</th></tr></thead>
                    <tbody>
                    <?php foreach ($recent as $d) { ?>
                      <tr><td class="text-nowrap"><?php echo date('d M Y', strtotime($d['approved_at'])); ?></td>
                        <td><?php echo $h(trim($d['fname'] . ' ' . $d['lname'])); ?> <span class="small text-muted">(<?php echo $h($d['sp_no']); ?>)</span></td>
                        <td class="num"><?php echo $money($d['savings_update_amount']); ?></td>
                        <td><?php echo $d['is_secretary_approved'] == 1 ? '<span class="fud-pill ok">Approved</span>' : '<span class="fud-pill no">Rejected</span>'; ?></td>
                        <td><?php if ($d['is_secretary_approved'] != 1) { ?><span class="text-muted">–</span>
                          <?php } elseif ($d['is_treasurer_approved'] == 1) { ?><span class="fud-pill ok">Fixed at <?php echo $money($d['treasurer_approved_amount']); ?></span>
                          <?php } elseif ($d['is_treasurer_approved'] == -1) { ?><span class="fud-pill wait" title="The member made a newer request, which was approved instead">Replaced</span>
                          <?php } else { ?><span class="fud-pill wait">Pending</span><?php } ?></td></tr>
                    <?php } ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          <?php } ?>

        </div>
        <?php echo $view->subFooter('../'); ?>

<script>
(function () {
    var table = document.getElementById('suTable');
    if (!table) { return; }
    var msg = document.querySelector('.msg');
    var checks = function () { return Array.prototype.slice.call(table.querySelectorAll('.su-check')); };
    var selected = function () { return checks().filter(function (c) { return c.checked && c.closest('tr').style.display !== 'none'; }).map(function (c) { return c.value; }); };
    var rowOf = function (id) { return table.querySelector('tr[data-id="' + id + '"]'); };
    function refresh() {
        var n = selected().length;
        ['approveSelected', 'rejectSelected'].forEach(function (b) {
            var el = document.getElementById(b);
            el.disabled = !n;
            el.innerHTML = '<i class="mdi mdi-' + (b === 'approveSelected' ? 'check' : 'close') + '"></i> ' + (b === 'approveSelected' ? 'Approve' : 'Reject') + ' selected' + (n ? ' (' + n + ')' : '');
        });
    }
    function decide(action, ids) {
        if (!ids.length) { return; }
        var lines = ids.map(function (id) { return '• ' + rowOf(id).getAttribute('data-label'); }).join('\n');
        if (!confirm((action === 'approve' ? 'Approve and send to the Treasurer' : 'Reject') + ' ' + (ids.length === 1 ? 'this request' : ids.length + ' requests') + '?\n\n' + lines)) { return; }
        var data = new FormData();
        data.append('action', action);
        ids.forEach(function (id) { data.append('ids[]', id); });
        var buttons = Array.prototype.slice.call(document.querySelectorAll('#suTable button, #approveSelected, #rejectSelected'));
        buttons.forEach(function (b) { b.disabled = true; });
        fetch('../ajax/chairman_savings_update_decision.php', { method: 'POST', body: data, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                msg.innerHTML = '<div class="alert alert-' + (res.success ? 'success' : 'danger') + '"></div>';
                msg.firstChild.textContent = res.message;
                if (res.done) { setTimeout(function () { location.reload(); }, 1200); } else { buttons.forEach(function (b) { b.disabled = false; }); refresh(); }
            })
            .catch(function () { msg.innerHTML = '<div class="alert alert-danger">Network or server error. Please try again.</div>'; buttons.forEach(function (b) { b.disabled = false; }); refresh(); });
    }
    document.getElementById('selectAll').addEventListener('change', function () {
        var on = this.checked;
        checks().forEach(function (c) { if (c.closest('tr').style.display !== 'none') { c.checked = on; } });
        refresh();
    });
    table.addEventListener('change', function (e) { if (e.target.classList.contains('su-check')) { refresh(); } });
    table.addEventListener('click', function (e) {
        var b = e.target.closest('.su-approve, .su-reject');
        if (b) { decide(b.classList.contains('su-approve') ? 'approve' : 'reject', [b.closest('tr').getAttribute('data-id')]); }
    });
    document.getElementById('approveSelected').addEventListener('click', function () { decide('approve', selected()); });
    document.getElementById('rejectSelected').addEventListener('click', function () { decide('reject', selected()); });
    document.getElementById('suSearch').addEventListener('input', function () {
        var q = this.value.toLowerCase();
        table.querySelectorAll('tbody tr').forEach(function (r) { r.style.display = r.textContent.toLowerCase().indexOf(q) !== -1 ? '' : 'none'; });
        refresh();
    });
})();
</script>
