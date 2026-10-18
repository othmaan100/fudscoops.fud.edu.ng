<?php
    // Savings withdrawals the Treasurer approved, waiting for the Chairman's final approval (ajax/bulk_approve_withdrawal.php).
    // Approving pays the Treasurer's amount out of savings; rejecting needs a reason the member sees.
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    require_once('../config/classes/MemberG1.php');
    require_once('../config/classes/LoanControls.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../auth/') || !User::isChairman('../auth/logout.php')) {
        exit();
    }
    $members = new MemberG1();
    $h = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
    $money = function ($v) { return ((float) $v < 0 ? '−' : '') . '₦' . number_format(abs((float) $v), 2); };

    $requests = $members->getchairmanWithdrawalEndorsment();
    $minBalance = LoanControls::withdrawalMinBalance();
    $total = 0;
    $attention = 0;
    foreach ($requests as $i => $r) {
        $requests[$i]['balance'] = LoanControls::savingsBalance($r['member_id']);
        $requests[$i]['problem'] = LoanControls::withdrawalLimitError($r['member_id'], $r['approved_withrawal_amount']);
        $requests[$i]['name'] = preg_replace('/\s+/', ' ', trim($r['title'] . ' ' . $r['fname'] . ' ' . $r['lname'] . ' ' . $r['oname']));
        $total += (float) $r['approved_withrawal_amount'];
        $attention += $requests[$i]['problem'] ? 1 : 0;
    }

    // What the Chairman decided in the last 14 days
    $con = (new DB())->getConnection();
    $recent = $con->query("SELECT w.withrawals_id, w.approved_withrawal_amount, w.proposed_withrawal_amount, w.chairman_approval,
                                  w.chairman_comment, w.chairman_decided_at, e.sp_no, e.fname, e.lname
                           FROM fudscoops_withrawals w
                           JOIN fudscoops_member m ON m.member_id = w.member_id
                           JOIN employee e ON e.employee_id = m.employee_id
                           WHERE w.chairman_decided_at >= NOW() - INTERVAL 14 DAY AND COALESCE(w.withrawals_is_deleted, 0) = 0
                           ORDER BY w.chairman_decided_at DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);

    echo $view->subHeader('../');
?>
    <?php echo $view->subPartialNav('../'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->subSideNav(); ?>
      <div class="main-panel">
        <div class="content-wrapper fud-dash">

          <div class="fud-hero">
            <div class="fud-hero-date">Savings withdrawals</div>
            <h2><i class="mdi mdi-bank-transfer-out"></i> Withdrawals awaiting your approval</h2>
            <p>The Treasurer has checked these requests and set the amount to pay. Approve to pay it out of the member's savings, or reject with a reason the member will see.</p>
            <div class="fud-hero-actions">
              <a href="approved_withdrawals.php"><i class="mdi mdi-check-all"></i> Approved withdrawals</a>
              <a href="complete_withdrawals.php"><i class="mdi mdi-account-off"></i> Complete withdrawals</a>
            </div>
          </div>

          <div class="fud-stats">
            <div class="fud-stat"><div class="fud-stat-icon fud-amber"><i class="mdi mdi-clock-outline"></i></div>
              <div><div class="fud-stat-label">Awaiting you</div><div class="fud-stat-value"><?php echo count($requests); ?></div>
                <div class="fud-stat-sub">request<?php echo count($requests) === 1 ? '' : 's'; ?></div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-blue"><i class="mdi mdi-cash-multiple"></i></div>
              <div><div class="fud-stat-label">Total to pay out</div><div class="fud-stat-value"><?php echo $money($total); ?></div>
                <div class="fud-stat-sub">as approved by the Treasurer</div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon <?php echo $attention ? 'fud-rose' : 'fud-green'; ?>"><i class="mdi mdi-alert-circle-outline"></i></div>
              <div><div class="fud-stat-label">Need attention</div><div class="fud-stat-value"><?php echo $attention; ?></div>
                <div class="fud-stat-sub"><?php echo $money($minBalance); ?> must stay in savings</div></div></div>
          </div>

          <?php if ($attention) { ?>
            <div class="fud-notice"><i class="mdi mdi-alert-circle"></i>
              <span><?php echo $attention; ?> request<?php echo $attention === 1 ? ' would' : 's would'; ?> take the member's savings below the minimum and cannot be approved as they are.
                The Treasurer can still lower the amount (Treasurer &rsaquo; Savings Withdrawals), or you can reject them with a reason.</span></div>
          <?php } ?>

          <div class="fud-section" id="rejectPanel" style="display:none">
            <div class="fud-section-head"><h5><i class="mdi mdi-close-circle text-danger"></i> Reject <span id="rejectCount">1 request</span></h5></div>
            <div class="fud-section-body">
              <p class="small text-muted mb-2" id="rejectWho"></p>
              <div class="form-group">
                <label for="rejectReason">Reason <small class="text-muted">(required; the member sees it)</small></label>
                <textarea class="form-control" id="rejectReason" rows="3" maxlength="500"></textarea>
              </div>
              <button type="button" class="btn btn-danger" id="rejectConfirm"><i class="mdi mdi-close"></i> Reject</button>
              <button type="button" class="btn btn-light" id="rejectCancel">Cancel</button>
            </div>
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
                <p class="fud-empty"><i class="mdi mdi-check-circle-outline"></i> Nothing is waiting for you. Requests appear here once the Treasurer approves them.</p>
              <?php } else { ?>
                <input class="form-control fud-search" type="search" id="searchEndorsment" placeholder="Search by name, staff number, bank…">
                <div class="table-responsive">
                  <table class="table fud-table" id="withdrawalTable">
                    <thead>
                      <tr>
                        <th style="width:28px"><input type="checkbox" id="selectAll" title="Select all"></th>
                        <th>Member</th>
                        <th class="num">Requested</th>
                        <th class="num">Treasurer approved</th>
                        <th class="num">Savings now → after</th>
                        <th>Pay into</th>
                        <th class="text-right">Decision</th>
                      </tr>
                    </thead>
                    <tbody id="endorsementBody">
                    <?php foreach ($requests as $r) {
                        $after = $r['balance'] - (float) $r['approved_withrawal_amount'];
                        $label = $r['name'] . ' (' . $r['sp_no'] . ') – ' . $money($r['approved_withrawal_amount']); ?>
                      <tr data-id="<?php echo (int) $r['withrawals_id']; ?>" data-label="<?php echo $h($label); ?>">
                        <td><input type="checkbox" class="wd-check" value="<?php echo (int) $r['withrawals_id']; ?>"<?php echo $r['problem'] ? ' data-problem="1"' : ''; ?>></td>
                        <td style="white-space:normal;min-width:170px">
                          <a href="view_withdrawal.php?withrawals_id=<?php echo (int) $r['withrawals_id']; ?>" class="font-weight-bold text-dark"><?php echo $h($r['name']); ?></a>
                          <div class="small text-muted"><?php echo $h($r['sp_no']); ?><?php echo $r['dept'] ? ' · ' . $h($r['dept']) : ''; ?></div>
                        </td>
                        <td class="num text-nowrap">
                          <?php echo $money($r['proposed_withrawal_amount']); ?>
                          <div class="small text-muted"><?php echo $r['proposed_date'] ? date('d M Y', strtotime($r['proposed_date'])) : 'uploaded'; ?></div>
                        </td>
                        <td class="num text-nowrap">
                          <strong><?php echo $money($r['approved_withrawal_amount']); ?></strong>
                          <?php if ((float) $r['approved_withrawal_amount'] < (float) $r['proposed_withrawal_amount'] - 0.005) { ?><div><span class="fud-pill wait">less than asked</span></div>
                          <?php } elseif ((float) $r['approved_withrawal_amount'] > (float) $r['proposed_withrawal_amount'] + 0.005) { ?><div><span class="fud-pill no">more than asked</span></div><?php } ?>
                          <div class="small text-muted"><?php echo $r['treasurer_decided_at'] ? date('d M Y', strtotime($r['treasurer_decided_at'])) : ''; ?></div>
                        </td>
                        <td class="num text-nowrap">
                          <?php echo $money($r['balance']); ?> → <span class="<?php echo $r['problem'] ? 'text-danger font-weight-bold' : ''; ?>"><?php echo $money($after); ?></span>
                          <?php if ($r['problem']) { ?><div><span class="fud-pill no" title="<?php echo $h($r['problem']); ?>">below minimum</span></div><?php } ?>
                        </td>
                        <td style="white-space:normal;min-width:150px">
                          <?php echo $h($r['bank_name'] ?: $r['bank_id'] ?: '–'); ?>
                          <div class="small text-muted"><?php echo $h($r['acount_number'] ?: '–'); ?><?php echo $r['account_name'] ? ' · ' . $h($r['account_name']) : ''; ?></div>
                        </td>
                        <td class="text-right text-nowrap">
                          <a href="view_withdrawal.php?withrawals_id=<?php echo (int) $r['withrawals_id']; ?>" class="btn btn-sm btn-light">Details</a>
                          <button type="button" class="btn btn-sm btn-success wd-approve"<?php echo $r['problem'] ? ' disabled title="' . $h($r['problem']) . '"' : ''; ?>><i class="mdi mdi-check"></i> Approve</button>
                          <button type="button" class="btn btn-sm btn-outline-danger wd-reject"><i class="mdi mdi-close"></i> Reject</button>
                        </td>
                      </tr>
                      <?php if ($r['treasurer_comment']) { ?>
                        <tr class="wd-note" data-for="<?php echo (int) $r['withrawals_id']; ?>">
                          <td></td>
                          <td colspan="6" class="small text-muted pt-0" style="white-space:normal;border-top:0">
                            <i class="mdi mdi-comment-text-outline"></i> Treasurer<?php echo $r['treasurer_decided_by'] ? ' (' . $h($r['treasurer_decided_by']) . ')' : ''; ?>: <?php echo $h($r['treasurer_comment']); ?>
                          </td>
                        </tr>
                      <?php } ?>
                    <?php } ?>
                    </tbody>
                  </table>
                </div>
              <?php } ?>
            </div>
          </div>

          <?php if ($recent) { ?>
            <div class="fud-section">
              <div class="fud-section-head"><h5><i class="mdi mdi-history"></i> Your decisions in the last 14 days</h5></div>
              <div class="fud-section-body">
                <div class="table-responsive">
                  <table class="table fud-table mb-0">
                    <thead><tr><th>Date</th><th>Member</th><th class="num">Amount</th><th>Decision</th></tr></thead>
                    <tbody>
                    <?php foreach ($recent as $d) { ?>
                      <tr>
                        <td class="text-nowrap"><?php echo date('d M Y, g:ia', strtotime($d['chairman_decided_at'])); ?></td>
                        <td><?php echo $h(trim($d['fname'] . ' ' . $d['lname'])); ?> <span class="text-muted small">(<?php echo $h($d['sp_no']); ?>)</span></td>
                        <td class="num"><?php echo $money($d['chairman_approval'] == 1 ? $d['approved_withrawal_amount'] : $d['proposed_withrawal_amount']); ?></td>
                        <td style="white-space:normal">
                          <?php if ($d['chairman_approval'] == 1) { ?><span class="fud-pill ok">Approved</span>
                          <?php } else { ?><span class="fud-pill no">Rejected</span> <span class="small text-muted"><?php echo $h($d['chairman_comment']); ?></span><?php } ?>
                        </td>
                      </tr>
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
    var table = document.getElementById('withdrawalTable');
    if (!table) { return; }
    var msg = document.querySelector('.msg');
    var panel = document.getElementById('rejectPanel');
    var reason = document.getElementById('rejectReason');
    var pendingReject = [];
    var checks = function () { return Array.prototype.slice.call(table.querySelectorAll('.wd-check')); };
    var selected = function () { return checks().filter(function (c) { return c.checked && c.closest('tr').style.display !== 'none'; }); };
    var rowOf = function (id) { return table.querySelector('tr[data-id="' + id + '"]'); };

    function refresh() {
        var sel = selected();
        document.getElementById('approveSelected').disabled = !sel.length;
        document.getElementById('rejectSelected').disabled = !sel.length;
        document.getElementById('approveSelected').innerHTML = '<i class="mdi mdi-check"></i> Approve selected' + (sel.length ? ' (' + sel.length + ')' : '');
        document.getElementById('rejectSelected').innerHTML = '<i class="mdi mdi-close"></i> Reject selected' + (sel.length ? ' (' + sel.length + ')' : '');
    }
    function show(ok, text, results) {
        var box = document.createElement('div');
        box.className = 'alert alert-' + (ok ? 'success' : 'danger');
        box.textContent = text;
        (results || []).forEach(function (r) {
            if (r.ok) { return; }
            var row = rowOf(r.id), line = document.createElement('div');
            line.className = 'small mt-1';
            line.textContent = (row ? row.getAttribute('data-label') : '#' + r.id) + ': ' + r.message;
            box.appendChild(line);
        });
        msg.innerHTML = '';
        msg.appendChild(box);
        msg.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    function send(action, ids, why, buttons) {
        var data = new FormData();
        data.append('action', action);
        ids.forEach(function (id) { data.append('withdrawal_ids[]', id); });
        if (why) { data.append('reason', why); }
        buttons.forEach(function (b) { b.disabled = true; });
        fetch('../ajax/bulk_approve_withdrawal.php', { method: 'POST', body: data, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                show(res.success && !(res.results || []).some(function (r) { return !r.ok; }), res.message, res.results);
                if (res.done) { setTimeout(function () { location.reload(); }, (res.results || []).some(function (r) { return !r.ok; }) ? 4000 : 1200); }
                else { buttons.forEach(function (b) { b.disabled = false; }); refresh(); }
            })
            .catch(function () { show(false, 'Network or server error. Please try again.'); buttons.forEach(function (b) { b.disabled = false; }); refresh(); });
    }
    function approve(ids) {
        var blocked = ids.filter(function (id) { return rowOf(id).querySelector('[data-problem]'); });
        if (blocked.length) { alert(blocked.length + ' of the selected requests would take savings below the minimum and cannot be approved. Untick them or reject them.'); return; }
        var lines = ids.map(function (id) { return '• ' + rowOf(id).getAttribute('data-label'); }).join('\n');
        if (!confirm('Approve ' + (ids.length === 1 ? 'this withdrawal' : ids.length + ' withdrawals') + '?\n\n' + lines + '\n\nThe amount will be taken from the member\'s savings.')) { return; }
        send('approve', ids, null, Array.prototype.slice.call(document.querySelectorAll('.wd-approve, .wd-reject, #approveSelected, #rejectSelected')));
    }
    function askReject(ids) {
        pendingReject = ids;
        document.getElementById('rejectCount').textContent = ids.length === 1 ? '1 request' : ids.length + ' requests';
        document.getElementById('rejectWho').textContent = ids.map(function (id) { return rowOf(id).getAttribute('data-label'); }).join(' · ');
        panel.style.display = '';
        panel.scrollIntoView({ behavior: 'smooth', block: 'center' });
        reason.focus();
    }

    document.getElementById('selectAll').addEventListener('change', function () {
        var on = this.checked;
        checks().forEach(function (c) { if (c.closest('tr').style.display !== 'none') { c.checked = on; } });
        refresh();
    });
    table.addEventListener('change', function (e) { if (e.target.classList.contains('wd-check')) { refresh(); } });
    table.addEventListener('click', function (e) {
        var b = e.target.closest('.wd-approve, .wd-reject');
        if (!b) { return; }
        var id = b.closest('tr').getAttribute('data-id');
        if (b.classList.contains('wd-approve')) { approve([id]); } else { askReject([id]); }
    });
    document.getElementById('approveSelected').addEventListener('click', function () { approve(selected().map(function (c) { return c.value; })); });
    document.getElementById('rejectSelected').addEventListener('click', function () { askReject(selected().map(function (c) { return c.value; })); });
    document.getElementById('rejectCancel').addEventListener('click', function () { panel.style.display = 'none'; pendingReject = []; });
    document.getElementById('rejectConfirm').addEventListener('click', function () {
        var why = reason.value.trim();
        if (!why) { alert('Give the reason for rejecting; the member will see it.'); reason.focus(); return; }
        if (!pendingReject.length) { return; }
        send('reject', pendingReject, why, [this, document.getElementById('rejectCancel')]);
    });

    document.getElementById('searchEndorsment').addEventListener('input', function () {
        var q = this.value.toLowerCase();
        table.querySelectorAll('tbody tr[data-id]').forEach(function (tr) {
            var shown = tr.textContent.toLowerCase().indexOf(q) !== -1;
            tr.style.display = shown ? '' : 'none';
            var note = table.querySelector('tr.wd-note[data-for="' + tr.getAttribute('data-id') + '"]');
            if (note) { note.style.display = shown ? '' : 'none'; }
        });
        refresh();
    });
})();
</script>
