<?php
    // Complete withdrawals (members leaving) the Treasurer approved, waiting for the Chairman's final approval
    // (ajax/chairman_complete_withdrawal_decision.php). Approving pays what the member owes from savings (if they chose
    // that) and pays out the Treasurer's amount; the minimum balance stays. Rejecting needs a reason the member sees.
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    require_once('../config/classes/MemberG1.php');
    require_once('../config/classes/LoanControls.php');
    require_once('../config/chairman/decisions.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../auth/') || !User::isChairman('../auth/logout.php')) {
        exit();
    }
    $h = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
    $money = function ($v) { return ((float) $v < 0 ? '−' : '') . '₦' . number_format(abs((float) $v), 2); };

    $waiting = [];
    $recent = [];
    $attention = 0;
    $total = 0;
    foreach ((new MemberG1())->treasurerCompleteWithdrawals() as $r) {
        $r['name'] = preg_replace('/\s+/', ' ', trim($r['fname'] . ' ' . $r['lname'] . ' ' . $r['oname']));
        if ((int) $r['chairman_approval'] === 0 && (float) $r['approved_amount'] > 0) {
            $r['balance'] = LoanControls::savingsBalance($r['member_id']);
            $r['ob'] = LoanControls::leavingObligations($r['member_id'], $r['employee_id'], $r['balance']);
            $owed = $r['settle_from_savings'] ? $r['ob']['total_owed'] : 0;
            // The same checks as MemberG1::chairmanApproveComWithdrawal
            $problems = $r['ob']['blockers'];
            if ($r['ob']['total_owed'] > 0 && !$r['settle_from_savings']) {
                $problems[] = 'Owes ' . $money($r['ob']['total_owed']) . ' and did not choose to pay it from savings.';
            }
            if ($limit = LoanControls::withdrawalLimitError($r['member_id'], $r['approved_amount'], $owed)) {
                $problems[] = $limit;
            }
            // Less than the full amount due: shown to the Chairman, but it can still be approved
            $r['short'] = $limit ? null : LoanControls::completePayoutShortError($r['ob'], $r['approved_amount']);
            $r['full'] = $r['ob']['payout'];
            if (!preg_match('/^\d{10}$/', (string) $r['acount_number'])) {
                $problems[] = 'The account number is missing; the Treasurer must enter it.';
            }
            $r['problems'] = $problems;
            $r['after'] = $r['balance'] - $owed - (float) $r['approved_amount'];
            $attention += $problems ? 1 : 0;
            $total += (float) $r['approved_amount'];
            $waiting[] = $r;
        } elseif ((int) $r['chairman_approval'] !== 0 && $r['chairman_decided_at'] && strtotime($r['chairman_decided_at']) >= strtotime('-30 days')) {
            $recent[] = $r;
        }
    }
    $waiting = array_reverse($waiting);   // oldest first
    usort($recent, function ($a, $b) { return strcmp($b['chairman_decided_at'], $a['chairman_decided_at']); });

    echo $view->subHeader('../');
?>
    <?php echo $view->subPartialNav('../'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->subSideNav(); ?>
      <div class="main-panel">
        <div class="content-wrapper fud-dash">

          <div class="fud-hero">
            <div class="fud-hero-date">Savings withdrawals</div>
            <h2><i class="mdi mdi-account-remove"></i> Complete withdrawals awaiting your approval</h2>
            <p>Members leaving the cooperative. The Treasurer has checked each request and set the amount to pay. Approving pays any loan or
              commodity the member chose to settle from savings, then pays out the amount; the minimum balance (<?php echo $money(LoanControls::withdrawalMinBalance()); ?>) stays in savings.</p>
            <div class="fud-hero-actions">
              <a href="withdrawal_endorsement.php"><i class="mdi mdi-bank-transfer-out"></i> Savings withdrawals</a>
              <a href="member_list.php"><i class="mdi mdi-account-group"></i> Members</a>
            </div>
          </div>

          <div class="fud-stats">
            <div class="fud-stat"><div class="fud-stat-icon fud-amber"><i class="mdi mdi-clock-outline"></i></div>
              <div><div class="fud-stat-label">Awaiting you</div><div class="fud-stat-value"><?php echo count($waiting); ?></div>
                <div class="fud-stat-sub">request<?php echo count($waiting) === 1 ? '' : 's'; ?></div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-blue"><i class="mdi mdi-cash-multiple"></i></div>
              <div><div class="fud-stat-label">Total to pay out</div><div class="fud-stat-value"><?php echo $money($total); ?></div>
                <div class="fud-stat-sub">as set by the Treasurer</div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon <?php echo $attention ? 'fud-rose' : 'fud-green'; ?>"><i class="mdi mdi-alert-circle-outline"></i></div>
              <div><div class="fud-stat-label">Cannot be approved</div><div class="fud-stat-value"><?php echo $attention; ?></div>
                <div class="fud-stat-sub">see the reasons below</div></div></div>
          </div>

          <div class="fud-section" id="rejectPanel" style="display:none">
            <div class="fud-section-head"><h5><i class="mdi mdi-close-circle text-danger"></i> Reject <span id="rejectCount">1 request</span></h5></div>
            <div class="fud-section-body">
              <p class="small text-muted mb-2" id="rejectWho"></p>
              <div class="form-group">
                <label for="rejectReason">Reason <small class="text-muted">(required; the member sees it and can apply again)</small></label>
                <textarea class="form-control" id="rejectReason" rows="3" maxlength="500"></textarea>
              </div>
              <button type="button" class="btn btn-danger" id="rejectConfirm"><i class="mdi mdi-close"></i> Reject</button>
              <button type="button" class="btn btn-light" id="rejectCancel">Cancel</button>
            </div>
          </div>

          <div class="fud-section">
            <div class="fud-section-head"><h5><i class="mdi mdi-format-list-checks"></i> Requests</h5></div>
            <div class="fud-section-body">
              <div class="msg"></div>
              <?php if (!$waiting) { ?>
                <p class="fud-empty"><i class="mdi mdi-check-circle-outline"></i> Nothing is waiting for you. Requests appear here once the Treasurer sets the amount.</p>
              <?php } else { ?>
                <div class="table-responsive" data-bulk="complete_withdrawal">
                  <table class="table fud-table mb-0" id="cwTable">
                    <thead>
                      <tr><th style="width:28px"><input type="checkbox" class="bulk-all" title="Select all"></th><th>Member</th><th class="num">Savings now</th><th class="num">Owes</th><th class="num">To pay out</th><th class="num">Left in savings</th><th>Pay into</th><th class="text-right">Decision</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($waiting as $r) {
                        $label = $r['name'] . ' (' . $r['sp_no'] . ') – ' . $money($r['approved_amount']); ?>
                      <tr data-id="<?php echo (int) $r['comp_withdrwal_id']; ?>" data-label="<?php echo $h($label); ?>">
                        <td><input type="checkbox" class="bulk-check" value="<?php echo (int) $r['comp_withdrwal_id']; ?>" data-label="<?php echo $h($label); ?>"<?php echo $r['problems'] ? ' disabled title="Cannot be approved: see the reasons below"' : ''; ?>></td>
                        <td style="white-space:normal;min-width:170px">
                          <a href="view_com_withdrawal_details.php?id=<?php echo (int) $r['comp_withdrwal_id']; ?>" class="font-weight-bold text-dark"><?php echo $h($r['name']); ?></a>
                          <div class="small text-muted"><?php echo $h($r['sp_no']); ?><?php echo $r['dept'] ? ' · ' . $h($r['dept']) : ''; ?></div>
                          <div class="small text-muted">applied <?php echo $r['date_applied'] ? date('d M Y', strtotime($r['date_applied'])) : '–'; ?></div>
                        </td>
                        <td class="num text-nowrap"><?php echo $money($r['balance']); ?></td>
                        <td class="num text-nowrap"><?php if ($r['ob']['total_owed'] > 0) { ?><span class="text-danger"><?php echo $money($r['ob']['total_owed']); ?></span>
                          <div class="small text-muted"><?php echo $r['settle_from_savings'] ? 'paid from savings' : 'not from savings'; ?></div><?php } else { ?><span class="text-muted">–</span><?php } ?></td>
                        <td class="num text-nowrap"><strong><?php echo $money($r['approved_amount']); ?></strong>
                          <?php if ($r['full'] - (float) $r['approved_amount'] > 0.005) { ?><div><span class="fud-pill wait">full payout <?php echo $money($r['full']); ?></span></div><?php } ?></td>
                        <td class="num text-nowrap<?php echo $r['after'] < LoanControls::withdrawalMinBalance() - 0.005 ? ' text-danger font-weight-bold' : ''; ?>"><?php echo $money($r['after']); ?></td>
                        <td class="small" style="white-space:normal;min-width:140px"><?php echo $h($r['bank_name'] ?: '–'); ?>
                          <div class="text-muted"><?php echo preg_match('/^\d{10}$/', (string) $r['acount_number']) ? $h($r['acount_number']) : '<span class="text-danger">missing</span>'; ?><?php echo $r['account_name'] ? ' · ' . $h($r['account_name']) : ''; ?></div></td>
                        <td class="text-right text-nowrap">
                          <a href="view_com_withdrawal_details.php?id=<?php echo (int) $r['comp_withdrwal_id']; ?>" class="btn btn-sm btn-light">Details</a>
                          <button type="button" class="btn btn-sm btn-success cw-approve"<?php echo $r['problems'] ? ' disabled' : ''; ?>><i class="mdi mdi-check"></i> Approve</button>
                          <button type="button" class="btn btn-sm btn-outline-danger cw-reject"><i class="mdi mdi-close"></i> Reject</button>
                        </td>
                      </tr>
                      <?php if ($r['problems'] || $r['short'] || $r['treasurer_comment']) { ?>
                        <tr class="cw-note"><td></td><td colspan="7" class="small pt-0" style="white-space:normal;border-top:0">
                          <?php foreach ($r['problems'] as $p) { ?><div class="text-danger"><i class="mdi mdi-block-helper"></i> <?php echo $h(str_replace(['Your ', 'You are '], ['The member\'s ', 'The member is '], $p)); ?></div><?php } ?>
                          <?php if ($r['short']) { ?><div class="text-warning" style="color:#8a5d00 !important"><i class="mdi mdi-information-outline"></i> <?php echo $h(str_replace('A complete withdrawal pays out everything the member is due', 'The full amount due to this member is', $r['short'])); ?> You can still approve the Treasurer's amount.</div><?php } ?>
                          <?php if ($r['treasurer_comment']) { ?><div class="text-muted"><i class="mdi mdi-comment-text-outline"></i> Treasurer<?php echo $r['treasurer_decided_by'] ? ' (' . $h($r['treasurer_decided_by']) . ')' : ''; ?>: <?php echo $h($r['treasurer_comment']); ?></div><?php } ?>
                        </td></tr>
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
              <div class="fud-section-head"><h5><i class="mdi mdi-history"></i> Your decisions in the last 30 days</h5></div>
              <div class="fud-section-body">
                <div class="table-responsive">
                  <table class="table fud-table mb-0">
                    <thead><tr><th>Date</th><th>Member</th><th class="num">Amount</th><th>Decision</th></tr></thead>
                    <tbody>
                    <?php foreach ($recent as $d) { ?>
                      <tr><td class="text-nowrap"><?php echo date('d M Y, g:ia', strtotime($d['chairman_decided_at'])); ?></td>
                        <td><?php echo $h($d['name']); ?> <span class="small text-muted">(<?php echo $h($d['sp_no']); ?>)</span></td>
                        <td class="num"><?php echo $money($d['approved_amount']); ?></td>
                        <td style="white-space:normal"><?php echo $d['chairman_approval'] == 1 ? '<span class="fud-pill ok">Approved</span>'
                            : '<span class="fud-pill no">Rejected</span> <span class="small text-muted">' . $h($d['chairman_comment']) . '</span>'; ?></td></tr>
                    <?php } ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          <?php } ?>

        </div>
        <?php echo $view->subFooter('../'); ?>
        <?php echo chairmanBulkScript(); ?>

<script>
(function () {
    var table = document.getElementById('cwTable');
    if (!table) { return; }
    var msg = document.querySelector('.msg'), panel = document.getElementById('rejectPanel'), reason = document.getElementById('rejectReason');
    var pending = [];
    var rowOf = function (id) { return table.querySelector('tr[data-id="' + id + '"]'); };
    function send(action, ids, why, buttons) {
        var data = new FormData();
        data.append('action', action);
        ids.forEach(function (id) { data.append('ids[]', id); });
        if (why) { data.append('reason', why); }
        buttons.forEach(function (b) { b.disabled = true; });
        fetch('../ajax/chairman_complete_withdrawal_decision.php', { method: 'POST', body: data, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                var box = document.createElement('div');
                var failed = (res.results || []).filter(function (r) { return !r.ok; });
                box.className = 'alert alert-' + (res.success && !failed.length ? 'success' : 'danger');
                box.textContent = res.message;
                failed.forEach(function (r) { var d = document.createElement('div'); d.className = 'small mt-1'; d.textContent = r.message; box.appendChild(d); });
                msg.innerHTML = ''; msg.appendChild(box);
                msg.scrollIntoView({ behavior: 'smooth', block: 'center' });
                if (res.done) { setTimeout(function () { location.reload(); }, failed.length ? 4000 : 1200); }
                else { buttons.forEach(function (b) { b.disabled = false; }); }
            })
            .catch(function () { msg.innerHTML = '<div class="alert alert-danger">Network or server error. Please try again.</div>'; buttons.forEach(function (b) { b.disabled = false; }); });
    }
    table.addEventListener('click', function (e) {
        var b = e.target.closest('.cw-approve, .cw-reject');
        if (!b) { return; }
        var id = b.closest('tr').getAttribute('data-id'), label = rowOf(id).getAttribute('data-label');
        if (b.classList.contains('cw-approve')) {
            if (!confirm('Approve this complete withdrawal?\n\n' + label + '\n\nAny loan or commodity the member chose to settle is paid from savings now, and the amount is paid out.')) { return; }
            send('approve', [id], null, Array.prototype.slice.call(table.querySelectorAll('button')));
        } else {
            pending = [id];
            document.getElementById('rejectCount').textContent = '1 request';
            document.getElementById('rejectWho').textContent = label;
            panel.style.display = '';
            panel.scrollIntoView({ behavior: 'smooth', block: 'center' });
            reason.focus();
        }
    });
    document.getElementById('rejectCancel').addEventListener('click', function () { panel.style.display = 'none'; pending = []; });
    document.getElementById('rejectConfirm').addEventListener('click', function () {
        var why = reason.value.trim();
        if (!why) { alert('Give the reason for rejecting; the member will see it.'); reason.focus(); return; }
        if (pending.length) { send('reject', pending, why, [this, document.getElementById('rejectCancel')]); }
    });
})();
</script>
