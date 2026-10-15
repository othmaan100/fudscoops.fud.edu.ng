<?php
    // A member's savings withdrawal request: who, how much, where to pay, their savings, and the Treasurer's decision
    // (ajax/withdrawal_decision.php). Approving sends it to the Chairman; rejecting needs a reason the member sees.
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    require_once('../config/classes/Dashboard.php');
    require_once('../config/classes/LoanControls.php');
    require_once('../config/classes/SavingsG1.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../auth/') || !User::isBur('../auth/logout.php')) {
        exit();
    }
    $id = isset($_GET['withrawals_id']) ? (int) $_GET['withrawals_id'] : 0;
    $con = (new DB())->getConnection();
    $stmt = $con->prepare("SELECT w.*, m.member_id, m.employee_id, m.proposed_monthly_savings, e.sp_no, e.title, e.fname, e.lname, e.oname,
                                  e.dept, e.phone_no, e.permanent_address, e.nature_of_appo, b.name AS bank_name
                           FROM fudscoops_withrawals w
                           JOIN fudscoops_member m ON m.member_id = w.member_id
                           JOIN employee e ON e.employee_id = m.employee_id
                           LEFT JOIN banks b ON b.id = w.bank_id
                           WHERE w.withrawals_id = :id");
    $stmt->execute([':id' => $id]);
    $w = $stmt->fetch(PDO::FETCH_ASSOC);
    $h = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
    $money = function ($v) { return '₦' . number_format((float) $v, 2); };

    if ($w) {
        $balance = LoanControls::savingsBalance($w['member_id']);
        $minBalance = LoanControls::withdrawalMinBalance();
        $max = LoanControls::maxWithdrawal($w['member_id']);
        $statement = (new Dashboard())->memberStatement($w['member_id'], $w['employee_id']);
        $recent = array_slice(array_reverse($statement['lines']), 0, 6);
        $name = preg_replace('/\s+/', ' ', trim($w['title'] . ' ' . $w['fname'] . ' ' . $w['lname'] . ' ' . $w['oname']));
        // Stage
        if ((int) $w['status'] === -1) {
            $stage = ['Rejected by Treasurer', 'no'];
        } elseif ((int) $w['chairman_approval'] === 1) {
            $stage = ['Approved', 'ok'];
        } elseif ((int) $w['status'] === 1 || (float) $w['approved_withrawal_amount'] > 0) {
            $stage = ['Awaiting Chairman', 'wait'];
        } else {
            $stage = ['Awaiting Treasurer', 'wait'];
        }
        $canDecide = (int) $w['chairman_approval'] === 0 && (int) $w['status'] !== -1;
        $suggested = (float) $w['approved_withrawal_amount'] > 0 ? (float) $w['approved_withrawal_amount'] : min((float) $w['proposed_withrawal_amount'], $max);
        // Payslips for the last three months of this year (slip.php only serves the current year)
        $payslips = [];
        for ($k = 1; $k <= 3; $k++) {
            $time = strtotime("first day of -$k month");
            if (date('Y', $time) == date('Y')) {
                $payslips[date('n', $time)] = date('F', $time);
            }
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
            <div class="fud-notice"><i class="mdi mdi-alert-circle"></i><span>Withdrawal request not found.</span>
              <a href="withdrawal_endorsement.php">Savings withdrawals <i class="mdi mdi-arrow-right"></i></a></div>
          <?php } else { ?>

          <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div>
              <h3 class="mb-1">Savings withdrawal #<?php echo (int) $w['withrawals_id']; ?> <span class="fud-pill <?php echo $stage[1]; ?>" style="font-size:.8rem"><?php echo $stage[0]; ?></span></h3>
              <div class="text-muted"><?php echo $h($name); ?> (<?php echo $h($w['sp_no']); ?>) <?php echo $w['proposed_date'] ? '· requested ' . date('d M Y, g:ia', strtotime($w['proposed_date'])) : '· uploaded by the Treasurer'; ?></div>
            </div>
            <a href="withdrawal_endorsement.php" class="btn btn-light btn-sm"><i class="mdi mdi-arrow-left"></i> All requests</a>
          </div>

          <div class="fud-stats">
            <div class="fud-stat"><div class="fud-stat-icon fud-blue"><i class="mdi mdi-bank-transfer-out"></i></div>
              <div><div class="fud-stat-label">Amount requested</div><div class="fud-stat-value"><?php echo $money($w['proposed_withrawal_amount']); ?></div>
                <?php if ((float) $w['approved_withrawal_amount'] > 0) { ?><div class="fud-stat-sub">approved <?php echo $money($w['approved_withrawal_amount']); ?></div><?php } ?></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-green"><i class="mdi mdi-safe"></i></div>
              <div><div class="fud-stat-label">Savings balance</div><div class="fud-stat-value"><?php echo $money($balance); ?></div>
                <div class="fud-stat-sub">monthly savings <?php echo $money($w['proposed_monthly_savings']); ?></div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon <?php echo $w['proposed_withrawal_amount'] > $max ? 'fud-rose' : 'fud-purple'; ?>"><i class="mdi mdi-scale-balance"></i></div>
              <div><div class="fud-stat-label">Most that can be withdrawn</div><div class="fud-stat-value"><?php echo $money($max); ?></div>
                <div class="fud-stat-sub"><?php echo $money($minBalance); ?> must stay in savings</div></div></div>
          </div>
          <?php if ($canDecide && $w['proposed_withrawal_amount'] > $max) { ?>
            <div class="fud-notice"><i class="mdi mdi-alert-circle"></i><span>The member asked for more than can be withdrawn. You can approve up to <?php echo $money($max); ?>.</span></div>
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
                    <div><span>Home address</span><?php echo $h($w['permanent_address'] ?: '–'); ?></div>
                  </div>
                  <?php if ($payslips) { ?>
                    <p class="mt-3 mb-0"><strong>Payslips:</strong>
                      <?php foreach ($payslips as $m => $mn) { ?><a class="ml-2" href="slip.php?legacy=<?php echo urlencode($w['sp_no']); ?>&s=<?php echo $m; ?>"><?php echo $mn; ?></a><?php } ?></p>
                  <?php } ?>
                </div>
              </div>

              <div class="fud-section">
                <div class="fud-section-head"><h5><span class="fud-num">2</span> Pay into</h5></div>
                <div class="fud-section-body">
                  <div class="fud-facts">
                    <div><span>Bank</span><?php echo $h($w['bank_name'] ?: $w['bank_id'] ?: '–'); ?></div>
                    <div><span>Account number</span><?php echo $h($w['acount_number'] ?: '–'); ?></div>
                    <div><span>Account name</span><?php echo $h($w['account_name'] ?: '–'); ?></div>
                  </div>
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
                  <?php } ?>
                </div>
              </div>
            </div>

            <div class="col-lg-5">
              <div class="fud-section fud-summary">
                <div class="fud-section-head"><h5><i class="mdi mdi-gavel"></i> Your decision</h5></div>
                <div class="fud-section-body">
                  <?php if ($canDecide) { ?>
                    <?php if ($stage[0] === 'Awaiting Chairman') { ?>
                      <p class="small text-muted">You approved <?php echo $money($w['approved_withrawal_amount']); ?>. You can change it until the Chairman decides.</p>
                    <?php } ?>
                    <form id="withdrawalDecision">
                      <input type="hidden" name="withrawals_id" value="<?php echo (int) $w['withrawals_id']; ?>">
                      <div class="form-group">
                        <label for="wdAmount">Amount to approve</label>
                        <div class="input-group fud-amount">
                          <div class="input-group-prepend"><span class="input-group-text">₦</span></div>
                          <input type="number" class="form-control" id="wdAmount" name="amount" min="0.01" step="0.01" max="<?php echo $max; ?>" value="<?php echo $suggested > 0 ? $suggested : ''; ?>">
                        </div>
                        <small class="text-muted">Up to <?php echo $money($max); ?>.</small>
                      </div>
                      <div class="form-group">
                        <label for="wdComment">Comment <small class="text-muted">(required when rejecting; the member sees it)</small></label>
                        <textarea class="form-control" id="wdComment" name="comment" rows="3"><?php echo $h($w['treasurer_comment']); ?></textarea>
                      </div>
                      <div class="msg"></div>
                      <button type="button" class="btn btn-success btn-block btn-lg" data-action="approve"><i class="mdi mdi-check"></i> Approve and send to Chairman</button>
                      <button type="button" class="btn btn-outline-danger btn-block" data-action="reject"><i class="mdi mdi-close"></i> Reject request</button>
                    </form>
                  <?php } elseif ($stage[0] === 'Approved') { ?>
                    <div class="fud-notice success mb-0"><i class="mdi mdi-check-circle"></i>
                      <span>Approved for <?php echo $money($w['approved_withrawal_amount']); ?><?php echo substr($w['approved_date'], 0, 4) > '2000' ? ' on ' . date('d M Y', strtotime($w['approved_date'])) : ''; ?>.</span></div>
                  <?php } else { ?>
                    <div class="fud-notice mb-0"><i class="mdi mdi-close-circle"></i>
                      <span>Rejected<?php echo $w['treasurer_decided_at'] ? ' on ' . date('d M Y', strtotime($w['treasurer_decided_at'])) : ''; ?><?php echo $w['treasurer_comment'] ? ': ' . $h($w['treasurer_comment']) : '.'; ?></span></div>
                  <?php } ?>
                  <?php if (!$canDecide && $w['treasurer_decided_by']) { ?><p class="small text-muted mt-2 mb-0">Treasurer decision by <?php echo $h($w['treasurer_decided_by']); ?>.</p><?php } ?>
                  <div class="fud-next">
                    <div class="fud-next-title">Next steps</div>
                    <ol><li>You approve the amount (or reject with a reason)</li><li>The Chairman approves it</li><li>The money is paid into the account above</li></ol>
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
    var form = document.getElementById('withdrawalDecision');
    if (!form) { return; }
    form.querySelectorAll('[data-action]').forEach(function (button) {
        button.addEventListener('click', function () {
            var action = button.getAttribute('data-action');
            var amount = parseFloat(form.amount.value) || 0;
            if (action === 'approve' && !(amount > 0)) { alert('Enter the amount to approve.'); return; }
            if (action === 'reject' && !form.comment.value.trim()) { alert('Give the reason for rejecting; the member will see it.'); form.comment.focus(); return; }
            if (!confirm(action === 'approve' ? 'Approve ₦' + amount.toLocaleString('en-NG', {minimumFractionDigits: 2}) + ' and send it to the Chairman?' : 'Reject this withdrawal request?')) { return; }
            var data = new FormData(form);
            data.append('action', action);
            var msg = form.querySelector('.msg');
            form.querySelectorAll('button').forEach(function (b) { b.disabled = true; });
            fetch('../ajax/withdrawal_decision.php', { method: 'POST', body: data, credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    msg.innerHTML = '<div class="alert mt-2 alert-' + (res.ok ? 'success' : 'danger') + '"></div>';
                    msg.firstChild.textContent = res.message;
                    if (res.ok) { setTimeout(function () { location.reload(); }, 1000); }
                    else { form.querySelectorAll('button').forEach(function (b) { b.disabled = false; }); }
                })
                .catch(function () { msg.innerHTML = '<div class="alert alert-danger mt-2">Network or server error. Please try again.</div>'; form.querySelectorAll('button').forEach(function (b) { b.disabled = false; }); });
        });
    });
})();
</script>
