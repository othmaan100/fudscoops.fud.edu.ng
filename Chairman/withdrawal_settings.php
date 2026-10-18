<?php
    // Chairman sets the minimum savings balance that cannot be withdrawn (setting withdrawal_min_balance, used by
    // LoanControls::withdrawalMinBalance for savings withdrawals and complete withdrawals). The Treasurer can also
    // change it on Treasurer/savings_settings.php. A new amount applies to every check from then on, including
    // requests already waiting.
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    require_once('../config/classes/Settings.php');
    require_once('../config/classes/LoanControls.php');
    require_once('../config/classes/MemberG1.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../auth/') || !User::isChairman('../auth/logout.php')) {
        exit();
    }
    $h = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
    $money = function ($v) { return '₦' . number_format((float) $v, 2); };

    // Post/Redirect/Get so a page refresh does not re-submit
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['withdrawal_min_balance'])) {
        $raw = preg_replace('/[^0-9.]/', '', (string) $_POST['withdrawal_min_balance']);
        if ($raw === '' || !is_numeric($raw) || (float) $raw < 0) {
            $_SESSION['settings_message'] = ['danger', 'Enter a valid amount (0 or more).'];
        } elseif (Settings::set('withdrawal_min_balance', (string) round((float) $raw, 2), $_SESSION['username'])) {
            $_SESSION['settings_message'] = ['success', 'The minimum savings balance is now ₦' . number_format((float) $raw, 2) . '. It applies to every withdrawal check from now on.'];
        } else {
            $_SESSION['settings_message'] = ['danger', 'Could not save the setting. Please try again.'];
        }
        header('location: withdrawal_settings.php');
        exit();
    }
    $message = isset($_SESSION['settings_message']) ? $_SESSION['settings_message'] : null;
    unset($_SESSION['settings_message']);

    $current = LoanControls::withdrawalMinBalance();
    $last = Settings::lastUpdate('withdrawal_min_balance');

    // Figures for the preview: every member's savings balance, and each waiting request's amount with what else
    // comes out of savings first (a leaving member's debts paid from savings)
    $con = (new DB())->getConnection();
    $balances = array_map('floatval', array_column((new MemberG1())->treasurerMemberDirectory(), 'savings_balance'));
    $pending = [];
    foreach ($con->query("SELECT w.member_id, IF(w.approved_withrawal_amount > 0, w.approved_withrawal_amount, w.proposed_withrawal_amount) amount
                          FROM fudscoops_withrawals w WHERE w.chairman_approval = 0 AND w.status <> -1 AND COALESCE(w.withrawals_is_deleted, 0) = 0") as $w) {
        $pending[] = ['type' => 'w', 'balance' => LoanControls::savingsBalance($w['member_id']), 'amount' => (float) $w['amount'], 'first' => 0];
    }
    foreach ($con->query("SELECT c.employee_id, c.approved_amount, c.settle_from_savings, m.member_id FROM fudscoops_complete_withrawal c
                          JOIN fudscoops_member m ON m.employee_id = c.employee_id
                          WHERE c.chairman_approval = 0 AND c.approved_amount > 0 AND COALESCE(c.complete_withrawal_is_deleted, 0) = 0") as $c) {
        $bal = LoanControls::savingsBalance($c['member_id']);
        $ob = LoanControls::leavingObligations($c['member_id'], $c['employee_id'], $bal);
        $pending[] = ['type' => 'c', 'balance' => $bal, 'amount' => (float) $c['approved_amount'], 'first' => $c['settle_from_savings'] ? $ob['total_owed'] : 0];
    }

    echo $view->subHeader('../');
?>
    <?php echo $view->subPartialNav('../'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->subSideNav(); ?>
      <div class="main-panel">
        <div class="content-wrapper fud-dash fud-apply">

          <div class="fud-hero">
            <div class="fud-hero-date">Settings</div>
            <h2><i class="mdi mdi-lock-outline"></i> Minimum savings balance</h2>
            <p>The amount every member must keep in savings. It cannot be withdrawn: savings withdrawals and complete
              withdrawals (members leaving) both leave at least this much in the account.</p>
            <div class="fud-hero-actions">
              <a href="withdrawal_endorsement.php"><i class="mdi mdi-bank-transfer-out"></i> Savings withdrawals</a>
              <a href="complete_withdrawals.php"><i class="mdi mdi-account-remove"></i> Complete withdrawals</a>
            </div>
          </div>

          <?php if ($message) { ?>
            <div class="alert alert-<?php echo $h($message[0]); ?>"><?php echo $h($message[1]); ?></div>
          <?php } ?>

          <div class="row">
            <div class="col-lg-5">
              <div class="fud-section fud-summary">
                <div class="fud-section-head"><h5><i class="mdi mdi-cog"></i> Set the amount</h5></div>
                <div class="fud-section-body">
                  <div class="fud-facts mb-3">
                    <div><span>Current minimum</span><strong style="font-size:1.4rem"><?php echo $money($current); ?></strong></div>
                    <div><span>Last changed</span><?php echo $last && $last['updated_at'] ? $h(date('d M Y, g:ia', strtotime($last['updated_at']))) . ($last['updated_by'] ? ' by ' . $h($last['updated_by']) : '') : 'Not changed yet (the default is the minimum monthly savings)'; ?></div>
                  </div>
                  <form method="POST" id="minForm">
                    <div class="form-group">
                      <label for="minAmount">New minimum balance</label>
                      <div class="input-group fud-amount">
                        <div class="input-group-prepend"><span class="input-group-text">₦</span></div>
                        <input type="number" class="form-control" id="minAmount" name="withdrawal_min_balance" min="0" step="0.01" value="<?php echo $h($current); ?>" required>
                      </div>
                      <small class="text-muted">0 means members may withdraw all their savings.</small>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block btn-lg"><i class="mdi mdi-content-save"></i> Save</button>
                  </form>
                  <div class="fud-next">
                    <div class="fud-next-title">Where it applies</div>
                    <ol>
                      <li>Members cannot request a withdrawal that leaves less than this in savings.</li>
                      <li>The Treasurer and you cannot approve such a withdrawal either; the check runs again at approval, so requests already waiting use the new amount.</li>
                      <li>A member leaving is paid their savings less what they owe and less this amount.</li>
                    </ol>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-lg-7">
              <div class="fud-section">
                <div class="fud-section-head"><h5><i class="mdi mdi-chart-bar"></i> What this amount means now</h5></div>
                <div class="fud-section-body">
                  <p class="small text-muted">Updates as you type the new amount.</p>
                  <div class="fud-stats mb-0">
                    <div class="fud-stat"><div class="fud-stat-icon fud-blue"><i class="mdi mdi-account-multiple"></i></div>
                      <div><div class="fud-stat-label">Members who cannot withdraw anything</div><div class="fud-stat-value" id="pvBelow">–</div>
                        <div class="fud-stat-sub" id="pvBelowSub"></div></div></div>
                    <div class="fud-stat"><div class="fud-stat-icon fud-green"><i class="mdi mdi-lock"></i></div>
                      <div><div class="fud-stat-label">Savings held back</div><div class="fud-stat-value" id="pvHeld">–</div>
                        <div class="fud-stat-sub">across all members</div></div></div>
                    <div class="fud-stat"><div class="fud-stat-icon fud-amber" id="pvBlockedIcon"><i class="mdi mdi-alert-circle-outline"></i></div>
                      <div><div class="fud-stat-label">Waiting requests that could not be approved</div><div class="fud-stat-value" id="pvBlocked">–</div>
                        <div class="fud-stat-sub" id="pvBlockedSub"></div></div></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
        <?php echo $view->subFooter('../'); ?>

<script>
(function () {
    var balances = <?php echo json_encode($balances); ?>;
    var pending = <?php echo json_encode($pending); ?>;
    var current = <?php echo json_encode($current); ?>;
    var input = document.getElementById('minAmount');
    var fmt = function (v) { return '₦' + v.toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); };
    function update() {
        var min = Math.max(0, parseFloat(input.value) || 0);
        var below = 0, held = 0, blocked = 0, blockedW = 0, blockedC = 0;
        balances.forEach(function (b) {
            if (b <= min) { below++; }
            held += Math.min(Math.max(b, 0), min);
        });
        pending.forEach(function (p) {
            if (p.amount > p.balance - p.first - min + 0.005) { blocked++; if (p.type === 'w') { blockedW++; } else { blockedC++; } }
        });
        document.getElementById('pvBelow').textContent = below.toLocaleString();
        document.getElementById('pvBelowSub').textContent = 'of ' + balances.length.toLocaleString() + ' have ' + fmt(min) + ' or less';
        document.getElementById('pvHeld').textContent = fmt(held);
        document.getElementById('pvBlocked').textContent = blocked + ' of ' + pending.length;
        document.getElementById('pvBlockedSub').textContent = blockedW + ' savings, ' + blockedC + ' complete withdrawal' + (blockedC === 1 ? '' : 's');
        document.getElementById('pvBlockedIcon').className = 'fud-stat-icon ' + (blocked ? 'fud-rose' : 'fud-green');
    }
    input.addEventListener('input', update);
    document.getElementById('minForm').addEventListener('submit', function (e) {
        var min = parseFloat(input.value);
        if (isNaN(min) || min < 0) { e.preventDefault(); alert('Enter a valid amount (0 or more).'); return; }
        if (Math.abs(min - current) < 0.005) { e.preventDefault(); alert('That is already the minimum balance.'); return; }
        if (!confirm('Change the minimum savings balance from ' + fmt(current) + ' to ' + fmt(min) + '?\n\nIt applies to every withdrawal check from now on, including requests already waiting.')) { e.preventDefault(); }
    });
    update();
})();
</script>
