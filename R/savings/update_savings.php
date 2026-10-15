<?php
    // Update monthly savings (member). Submitted by js/custom.js (#update_savings_amount) to ajax/update_savings_amount.php
    require_once('../../config/classes/View.php');
    require_once('../../config/classes/User.php');
    require_once('../../config/classes/MemberG1.php');
    require_once('../../config/classes/SavingsG1.php');
    require_once('../../config/classes/Dashboard.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../../auth/') || !User::isStaff('../../auth/logout.php')) {
        exit();
    }
    $minMonthlySavings = SavingsG1::getMinMonthlySavings();
    $employeeId = $user->getEmployeeId($_SESSION['username']);
    $memberId = $user->getMemberId($employeeId);
    $info = (new Dashboard())->memberSavingsInfo($memberId, $employeeId);
    $balance = (new MemberG1())->getCurrentSavingsAmount($_SESSION['username']);
    $h = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };

    echo $view->subHeader('../../');
?>
    <?php echo $view->subPartialNav('../../'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->staffSideNav(); ?>
      <div class="main-panel">
        <div class="content-wrapper fud-dash fud-apply">

          <div class="fud-hero">
            <div class="fud-hero-date">FUD Staff Cooperative Society Limited</div>
            <h2><i class="mdi mdi-cash-refund"></i> Update Monthly Savings</h2>
            <p>Increase or reduce the amount deducted from your salary for savings each month.</p>
          </div>

          <div class="fud-stats">
            <div class="fud-stat"><div class="fud-stat-icon fud-blue"><i class="mdi mdi-calendar-clock"></i></div>
              <div><div class="fud-stat-label">Current monthly savings</div><div class="fud-stat-value">₦<?php echo number_format($info['monthly']); ?></div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-amber"><i class="mdi mdi-arrow-collapse-down"></i></div>
              <div><div class="fud-stat-label">Minimum monthly savings</div><div class="fud-stat-value">₦<?php echo number_format($minMonthlySavings); ?></div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-green"><i class="mdi mdi-safe"></i></div>
              <div><div class="fud-stat-label">Savings balance</div><div class="fud-stat-value">₦<?php echo number_format((float) $balance, 2); ?></div></div></div>
          </div>

          <?php if ($info['pending_update']) { ?>
            <div class="fud-notice"><i class="mdi mdi-clock-outline"></i>
              <span>Your request to change your monthly savings to ₦<?php echo number_format($info['pending_update']); ?> is awaiting approval.
                A new request replaces it.</span></div>
          <?php } ?>

          <form id="update_savings_amount" autocomplete="off" data-current="<?php echo (float) $info['monthly']; ?>">
            <input type="hidden" id="staff_no" name="staff_no" value="<?php echo $h($_SESSION['username']); ?>">
            <div class="row">
              <div class="col-lg-8">
                <div class="fud-section">
                  <div class="fud-section-head"><h5><span class="fud-num">1</span> New monthly amount</h5></div>
                  <div class="fud-section-body">
                    <label for="savings_amount">Amount to save each month</label>
                    <div class="input-group fud-amount">
                      <div class="input-group-prepend"><span class="input-group-text">₦</span></div>
                      <input type="number" class="form-control" id="savings_amount" name="savings_amount" required
                             min="<?php echo (float) $minMonthlySavings; ?>" step="0.01" placeholder="<?php echo number_format($minMonthlySavings, 2, ".", ""); ?> or more">
                    </div>
                    <small class="d-block mt-2 text-muted" id="changeNote">At least ₦<?php echo number_format($minMonthlySavings); ?>.</small>

                    <div class="custom-control custom-checkbox mt-4">
                      <input type="checkbox" class="custom-control-input" id="savingsUpdate" name="undertaking" required>
                      <label class="custom-control-label" for="savingsUpdate">I ask FUD Staff Cooperative Society Limited to deduct this amount from my salary each month as savings.</label>
                    </div>
                    <div class="msg"></div>
                    <button type="submit" id="savingWithdrwal" class="btn btn-primary btn-lg mt-3">
                      <span id="spinnerUpdate" class="hidden-message spinner-border spinner-border-sm" style="display: none"></span>
                      <i class="mdi mdi-send"></i> Submit request</button>
                  </div>
                </div>
              </div>
              <div class="col-lg-4">
                <div class="fud-section">
                  <div class="fud-section-body">
                    <div class="fud-next mt-0 pt-0 border-0">
                      <div class="fud-next-title">What happens next</div>
                      <ol>
                        <li>The Chairman approves your request</li>
                        <li>The Treasurer confirms the amount</li>
                        <li>The new amount is deducted from your salary from then on</li>
                      </ol>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </form>

        </div>
        <?php echo $view->subFooter('../../'); ?>

<script>
document.getElementById('savings_amount').addEventListener('input', function () {
    var current = parseFloat(this.form.dataset.current) || 0, value = parseFloat(this.value) || 0, note = document.getElementById('changeNote');
    if (!value) { note.textContent = 'At least ₦<?php echo number_format($minMonthlySavings); ?>.'; return; }
    if (value < <?php echo (float) $minMonthlySavings; ?>) { note.textContent = 'Below the minimum of ₦<?php echo number_format($minMonthlySavings); ?>.'; note.className = 'd-block mt-2 text-danger'; return; }
    var diff = value - current;
    note.className = 'd-block mt-2 ' + (diff >= 0 ? 'text-success' : 'text-primary');
    note.textContent = diff === 0 ? 'The same as now.' : (diff > 0 ? '₦' + diff.toLocaleString() + ' more' : '₦' + (-diff).toLocaleString() + ' less') + ' each month than now.';
});
</script>
