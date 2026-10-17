<?php
    // A member's complete withdrawal (leaving the cooperative): savings, what they owe or guarantee, where to pay,
    // and the Treasurer's decision (ajax/complete_withdrawal_decision.php). Approving sends it to the Chairman;
    // rejecting needs a reason the member sees.
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    require_once('../config/classes/Dashboard.php');
    require_once('../config/classes/LoanControls.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../auth/') || !User::isBur('../auth/logout.php')) {
        exit();
    }
    $id = isset($_GET['withrawals_id']) ? (int) $_GET['withrawals_id'] : 0;
    $con = (new DB())->getConnection();
    $stmt = $con->prepare("SELECT c.*, m.member_id, m.proposed_monthly_savings, m.reg_date, e.sp_no, e.title, e.fname, e.lname, e.oname,
                                  e.dept, e.phone_no, e.permanent_address, e.nature_of_appo, b.name AS bank_name
                           FROM fudscoops_complete_withrawal c
                           JOIN fudscoops_member m ON m.employee_id = c.employee_id
                           JOIN employee e ON e.employee_id = c.employee_id
                           LEFT JOIN banks b ON b.id = c.bank_id
                           WHERE c.comp_withdrwal_id = :id AND COALESCE(c.complete_withrawal_is_deleted, 0) = 0");
    $stmt->execute([':id' => $id]);
    $w = $stmt->fetch(PDO::FETCH_ASSOC);
    $h = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
    $money = function ($v) { return ((float) $v < 0 ? '−' : '') . '₦' . number_format(abs((float) $v), 2); };

    if ($w) {
        $balance = LoanControls::savingsBalance($w['member_id']);
        $ob = LoanControls::leavingObligations($w['member_id'], $w['employee_id'], $balance);
        $statement = (new Dashboard())->memberStatement($w['member_id'], $w['employee_id']);
        $recent = array_slice(array_reverse($statement['lines']), 0, 6);
        $name = preg_replace('/\s+/', ' ', trim($w['title'] . ' ' . $w['fname'] . ' ' . $w['lname'] . ' ' . $w['oname']));
        $noAccount = !preg_match('/^\d{10}$/', (string) $w['acount_number']);
        $owesUnpaid = $ob['total_owed'] > 0 && !$w['settle_from_savings'];
        $canPay = !$ob['blockers'] && !$owesUnpaid && $ob['payout'] > 0;
        if ((int) $w['chairman_approval'] === 1) {
            $stage = ['Approved', 'ok'];
        } elseif ((int) $w['chairman_approval'] === -1) {
            $stage = ['Rejected', 'no'];
        } elseif ((float) $w['approved_amount'] > 0) {
            $stage = ['Awaiting Chairman', 'wait'];
        } else {
            $stage = ['Awaiting Treasurer', 'wait'];
        }
        $canDecide = (int) $w['chairman_approval'] === 0;
        $suggested = (float) $w['approved_amount'] > 0 ? (float) $w['approved_amount'] : $ob['payout'];
        // Payslips for the last three months of this year (slip.php only serves the current year)
        $payslips = [];
        for ($k = 1; $k <= 3; $k++) {
            $time = strtotime("first day of -$k month");
            if (date('Y', $time) == date('Y')) {
                $payslips[date('n', $time)] = date('F', $time);
            }
        }
        // A hint when the account number was lost: the last account used for a savings withdrawal
        $lastAccount = null;
        if ($noAccount) {
            $q = $con->prepare("SELECT w.acount_number, w.account_name, b.name AS bank_name FROM fudscoops_withrawals w LEFT JOIN banks b ON b.id = w.bank_id
                                WHERE w.member_id = :m AND w.acount_number REGEXP '^[0-9]{10}$' ORDER BY w.withrawals_id DESC LIMIT 1");
            $q->execute([':m' => $w['member_id']]);
            $lastAccount = $q->fetch(PDO::FETCH_ASSOC) ?: null;
        }
    }
    echo $view->subHeader('../');
?>
    <?php echo $view->subPartialNav('../'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->treasurerSideNav(); ?>
      <div class="main-panel">
        <div class="content-wrapper fud-dash fud-apply">

          <?php if (!$w) { ?>
            <div class="fud-notice"><i class="mdi mdi-alert-circle"></i><span>Complete withdrawal request not found.</span>
              <a href="complete_withdrawals.php">Complete withdrawals <i class="mdi mdi-arrow-right"></i></a></div>
          <?php } else { ?>

          <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div>
              <h3 class="mb-1">Complete withdrawal #<?php echo (int) $w['comp_withdrwal_id']; ?> <span class="fud-pill <?php echo $stage[1]; ?>" style="font-size:.8rem"><?php echo $stage[0]; ?></span></h3>
              <div class="text-muted"><?php echo $h($name); ?> (<?php echo $h($w['sp_no']); ?>)<?php echo $w['date_applied'] ? ' · applied ' . date('d M Y, g:ia', strtotime($w['date_applied'])) : ''; ?></div>
            </div>
            <div>
              <a href="member_profile.php?member_id=<?php echo (int) $w['member_id']; ?>" class="btn btn-light btn-sm"><i class="mdi mdi-account-details"></i> Member record</a>
              <a href="complete_withdrawals.php" class="btn btn-light btn-sm"><i class="mdi mdi-arrow-left"></i> All requests</a>
            </div>
          </div>

          <div class="fud-stats">
            <div class="fud-stat"><div class="fud-stat-icon fud-green"><i class="mdi mdi-safe"></i></div>
              <div><div class="fud-stat-label">Savings balance</div><div class="fud-stat-value"><?php echo $money($balance); ?></div>
                <div class="fud-stat-sub">member since <?php echo $w['reg_date'] ? date('M Y', strtotime($w['reg_date'])) : '–'; ?></div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon <?php echo $ob['total_owed'] > 0 ? 'fud-rose' : 'fud-purple'; ?>"><i class="mdi mdi-cash-multiple"></i></div>
              <div><div class="fud-stat-label">Owes</div><div class="fud-stat-value"><?php echo $money($ob['total_owed']); ?></div>
                <div class="fud-stat-sub"><?php echo $ob['total_owed'] > 0 ? ($w['settle_from_savings'] ? 'to be paid from savings' : 'not paying from savings') : 'loans and commodity'; ?></div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-blue"><i class="mdi mdi-bank-transfer-out"></i></div>
              <div><div class="fud-stat-label"><?php echo (float) $w['approved_amount'] > 0 ? 'Amount approved' : 'To pay out'; ?></div>
                <div class="fud-stat-value"><?php echo $money((float) $w['approved_amount'] > 0 ? $w['approved_amount'] : $ob['payout']); ?></div>
                <div class="fud-stat-sub">savings go to zero</div></div></div>
          </div>

          <?php if ($canDecide) { ?>
            <?php foreach ($ob['blockers'] as $b) { ?>
              <div class="fud-notice"><i class="mdi mdi-block-helper"></i><span><?php echo $h(str_replace(['Your ', 'You are '], ['The member\'s ', 'The member is '], $b)); ?></span></div>
            <?php } ?>
            <?php if ($owesUnpaid) { ?>
              <div class="fud-notice"><i class="mdi mdi-alert-circle"></i><span>The member owes <?php echo $money($ob['total_owed']); ?> and did not choose to pay it from savings. The Chairman cannot approve until it is repaid; you can reject the request with that reason.</span></div>
            <?php } ?>
            <?php if (!$ob['blockers'] && !$owesUnpaid && $ob['payout'] <= 0) { ?>
              <div class="fud-notice"><i class="mdi mdi-alert-circle"></i><span>There is nothing to pay out: the savings balance is <?php echo $money($balance); ?><?php echo $ob['total_owed'] > 0 ? ' and the member owes ' . $money($ob['total_owed']) : ''; ?>. Reject the request with that reason.</span></div>
            <?php } ?>
            <?php if ($noAccount) { ?>
              <div class="fud-notice"><i class="mdi mdi-alert-circle"></i><span>The account number on this request was lost (it was too long for the old database column). Confirm it with the member and enter it below before approving.</span></div>
            <?php } ?>
          <?php } ?>

          <div class="row">
            <div class="col-lg-7">
              <div class="fud-section">
                <div class="fud-section-head"><h5><span class="fud-num">1</span> Member</h5></div>
                <div class="fud-section-body">
                  <div class="fud-facts">
                    <div><span>Name</span><?php echo $h($name); ?></div>
                    <div><span>Staff number</span><?php echo $h($w['sp_no']); ?></div>
                    <div><span>Department / unit</span><?php echo $h($w['dept'] ?: '–'); ?></div>
                    <div><span>Phone</span><?php echo $h($w['phone_no'] ?: '–'); ?></div>
                    <div><span>Appointment</span><?php echo $h($w['nature_of_appo'] ?: '–'); ?></div>
                    <div><span>Monthly savings</span><?php echo $money($w['proposed_monthly_savings']); ?></div>
                    <div><span>Home address</span><?php echo $h($w['permanent_address'] ?: '–'); ?></div>
                  </div>
                  <?php if ($payslips) { ?>
                    <p class="mt-3 mb-0"><strong>Payslips:</strong>
                      <?php foreach ($payslips as $mo => $mn) { ?><a class="ml-2" href="slip.php?legacy=<?php echo urlencode($w['sp_no']); ?>&s=<?php echo $mo; ?>"><?php echo $mn; ?></a><?php } ?></p>
                  <?php } ?>
                </div>
              </div>

              <div class="fud-section">
                <div class="fud-section-head"><h5><span class="fud-num">2</span> Loans, commodity and guarantees</h5></div>
                <div class="fud-section-body">
                  <?php if (!$ob['owed'] && !$ob['blockers']) { ?>
                    <p class="mb-0 text-success"><i class="mdi mdi-check-circle"></i> Owes no loan or commodity and is not guaranteeing an open loan.</p>
                  <?php } else { ?>
                    <?php if ($ob['owed']) { ?>
                      <table class="table fud-table">
                        <thead><tr><th>Owed</th><th class="num">Balance</th></tr></thead>
                        <tbody>
                        <?php foreach ($ob['owed'] as $o) { ?>
                          <tr><td><?php echo $h($o['label']); ?></td><td class="num"><?php echo $money($o['balance']); ?></td></tr>
                        <?php } ?>
                        <tr class="fud-total"><td><strong>Total</strong></td><td class="num"><strong><?php echo $money($ob['total_owed']); ?></strong></td></tr>
                        </tbody>
                      </table>
                      <p class="small text-muted mb-2"><?php echo $w['settle_from_savings']
                          ? 'The member chose to pay this from savings. It is recorded when the Chairman approves, and the rest is paid out.'
                          : 'The member did not choose to pay this from savings.'; ?></p>
                    <?php } ?>
                    <?php foreach ($ob['blockers'] as $b) { ?>
                      <p class="small text-danger mb-1"><i class="mdi mdi-block-helper"></i> <?php echo $h(str_replace(['Your ', 'You are '], ['The member\'s ', 'The member is '], $b)); ?></p>
                    <?php } ?>
                  <?php } ?>
                </div>
              </div>

              <div class="fud-section">
                <div class="fud-section-head"><h5><span class="fud-num">3</span> Recent savings and withdrawals</h5></div>
                <div class="fud-section-body">
                  <?php if (!$recent) { ?><p class="fud-empty">No savings recorded.</p><?php } else { ?>
                  <table class="table fud-table mb-0">
                    <thead><tr><th>Date</th><th>Description</th><th class="num">Amount (₦)</th><th class="num">Balance (₦)</th></tr></thead>
                    <tbody>
                    <?php foreach ($recent as $l) { ?>
                      <tr><td class="text-nowrap"><?php echo $l['exact'] ? date('d M Y', strtotime($l['date'])) : date('M Y', strtotime($l['date'])); ?></td>
                        <td><?php echo $h($l['label']); ?></td>
                        <td class="num <?php echo $l['credit'] ? 'text-success' : 'text-danger'; ?>"><?php echo ($l['credit'] ? '+' : '−') . number_format($l['credit'] ?: $l['debit'], 2); ?></td>
                        <td class="num"><?php echo number_format($l['balance'], 2); ?></td></tr>
                    <?php } ?>
                    </tbody>
                  </table>
                  <p class="small mt-2 mb-0"><a href="member_profile.php?member_id=<?php echo (int) $w['member_id']; ?>">Full statement and member record <i class="mdi mdi-arrow-right"></i></a></p>
                  <?php } ?>
                </div>
              </div>
            </div>

            <div class="col-lg-5">
              <div class="fud-section">
                <div class="fud-section-head"><h5><i class="mdi mdi-bank"></i> Pay into</h5></div>
                <div class="fud-section-body">
                  <div class="fud-facts">
                    <div><span>Bank</span><?php echo $h($w['bank_name'] ?: '–'); ?></div>
                    <div><span>Account number</span><?php echo $noAccount ? '<span class="text-danger">Missing</span>' : $h($w['acount_number']); ?></div>
                    <div><span>Account name</span><?php echo $h($w['account_name'] ?: '–'); ?></div>
                  </div>
                  <?php if ($noAccount && $lastAccount) { ?>
                    <p class="small text-muted mt-2 mb-0">Last account used for a savings withdrawal: <?php echo $h($lastAccount['acount_number']); ?>
                      (<?php echo $h(trim($lastAccount['bank_name'] . ' · ' . $lastAccount['account_name'], ' ·')); ?>). Confirm with the member before using it.</p>
                  <?php } ?>
                </div>
              </div>

              <div class="fud-section fud-summary">
                <div class="fud-section-head"><h5><i class="mdi mdi-gavel"></i> Your decision</h5></div>
                <div class="fud-section-body">
                  <?php if ($canDecide) { ?>
                    <?php if ($stage[0] === 'Awaiting Chairman') { ?>
                      <p class="small text-muted">You approved <?php echo $money($w['approved_amount']); ?>. You can change it until the Chairman decides.</p>
                    <?php } ?>
                    <form id="cwDecision">
                      <input type="hidden" name="comp_withdrwal_id" value="<?php echo (int) $w['comp_withdrwal_id']; ?>">
                      <div class="form-group">
                        <label for="cwAmount">Amount to pay out</label>
                        <div class="input-group fud-amount">
                          <div class="input-group-prepend"><span class="input-group-text">₦</span></div>
                          <input type="number" class="form-control" id="cwAmount" name="amount" min="0.01" step="0.01" max="<?php echo $ob['payout']; ?>" value="<?php echo $suggested > 0 ? $suggested : ''; ?>">
                        </div>
                        <small class="text-muted">Up to <?php echo $money($ob['payout']); ?>: the savings balance<?php echo $ob['total_owed'] > 0 ? ' less ' . $money($ob['total_owed']) . ' owed' : ''; ?>. No minimum balance stays.</small>
                      </div>
                      <?php if ($noAccount) { ?>
                        <div class="form-group">
                          <label for="cwAccount">Account number <small class="text-muted">(confirmed with the member)</small></label>
                          <input type="text" class="form-control" id="cwAccount" name="account_number" maxlength="10" inputmode="numeric" pattern="\d{10}" placeholder="10 digits">
                        </div>
                      <?php } ?>
                      <div class="form-group">
                        <label for="cwComment">Comment <small class="text-muted">(required when rejecting; the member sees it)</small></label>
                        <textarea class="form-control" id="cwComment" name="comment" rows="3"><?php echo $h($w['treasurer_comment']); ?></textarea>
                      </div>
                      <div class="msg"></div>
                      <button type="button" class="btn btn-success btn-block btn-lg" data-action="approve"<?php echo $canPay ? '' : ' disabled title="This request cannot be paid yet; see the notes above."'; ?>><i class="mdi mdi-check"></i> Approve and send to Chairman</button>
                      <button type="button" class="btn btn-outline-danger btn-block" data-action="reject"><i class="mdi mdi-close"></i> Reject request</button>
                    </form>
                  <?php } elseif ($stage[0] === 'Approved') { ?>
                    <div class="fud-notice success mb-0"><i class="mdi mdi-check-circle"></i>
                      <span>Approved for <?php echo $money($w['approved_amount']); ?><?php echo $w['date_approved'] ? ' on ' . date('d M Y', strtotime($w['date_approved'])) : ''; ?><?php echo $w['settled_at'] && $w['amount_owed'] > 0 ? '; ' . $money($w['amount_owed']) . ' paid to loans/commodity from savings' : ''; ?>.</span></div>
                  <?php } else { ?>
                    <div class="fud-notice mb-0"><i class="mdi mdi-close-circle"></i>
                      <span>Rejected<?php echo $w['treasurer_decided_at'] ? ' on ' . date('d M Y', strtotime($w['treasurer_decided_at'])) : ''; ?><?php echo $w['treasurer_comment'] ? ': ' . $h($w['treasurer_comment']) : '.'; ?></span></div>
                  <?php } ?>
                  <?php if (!$canDecide && $w['treasurer_decided_by']) { ?><p class="small text-muted mt-2 mb-0">Treasurer decision by <?php echo $h($w['treasurer_decided_by']); ?>.</p><?php } ?>
                  <div class="fud-next">
                    <div class="fud-next-title">Next steps</div>
                    <ol><li>You set the amount to pay (or reject with a reason)</li>
                      <li>The Chairman approves it<?php echo $ob['total_owed'] > 0 && $w['settle_from_savings'] ? ', and what the member owes is paid from savings' : ''; ?></li>
                      <li>The money is paid into the account above and the savings go to zero</li></ol>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <?php } ?>

        </div>
        <?php echo $view->subFooter('../'); ?>

<script>
(function () {
    var form = document.getElementById('cwDecision');
    if (!form) { return; }
    form.querySelectorAll('[data-action]').forEach(function (button) {
        button.addEventListener('click', function () {
            var action = button.getAttribute('data-action');
            var amount = parseFloat(form.amount.value) || 0;
            if (action === 'approve') {
                if (!(amount > 0)) { alert('Enter the amount to pay out.'); return; }
                if (form.account_number && !/^\d{10}$/.test(form.account_number.value.trim())) { alert('Enter the member\'s 10-digit account number.'); form.account_number.focus(); return; }
            }
            if (action === 'reject' && !form.comment.value.trim()) { alert('Give the reason for rejecting; the member will see it.'); form.comment.focus(); return; }
            if (!confirm(action === 'approve' ? 'Approve paying ₦' + amount.toLocaleString('en-NG', {minimumFractionDigits: 2}) + ' and send it to the Chairman?' : 'Reject this complete withdrawal request?')) { return; }
            var data = new FormData(form);
            data.append('action', action);
            var msg = form.querySelector('.msg');
            var buttons = form.querySelectorAll('button');
            var wasDisabled = Array.prototype.map.call(buttons, function (b) { return b.disabled; });
            buttons.forEach(function (b) { b.disabled = true; });
            var restore = function () { buttons.forEach(function (b, i) { b.disabled = wasDisabled[i]; }); };
            fetch('../ajax/complete_withdrawal_decision.php', { method: 'POST', body: data, credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    msg.innerHTML = '<div class="alert mt-2 alert-' + (res.ok ? 'success' : 'danger') + '"></div>';
                    msg.firstChild.textContent = res.message;
                    if (res.ok) { setTimeout(function () { location.reload(); }, 1000); } else { restore(); }
                })
                .catch(function () { msg.innerHTML = '<div class="alert alert-danger mt-2">Network or server error. Please try again.</div>'; restore(); });
        });
    });
})();
</script>
