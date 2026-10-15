<?php
    // Complete withdrawal (member withdraws all savings). Submitted by js/custom.js (#completeWithdrawal) to ajax/complete_withdrawal.php
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    require_once('../config/classes/MemberG1.php');
    require_once('../config/classes/Dashboard.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../auth/') || !User::isStaff('../auth/logout.php')) {
        exit();
    }
    $member = new MemberG1();
    $banks = $member->getBanks();
    $employeeId = $user->getEmployeeId($_SESSION['username']);
    $memberId = $user->getMemberId($employeeId);
    $balance = (float) $member->getCurrentSavingsAmount($_SESSION['username']);
    $info = (new Dashboard())->memberSavingsInfo($memberId, $employeeId);
    // Loans, commodity and guarantees that stop or affect leaving (LoanControls::leavingObligations)
    require_once('../config/classes/LoanControls.php');
    $obligations = LoanControls::leavingObligations($memberId, $employeeId, $balance);
    $owed = $obligations['total_owed'];
    $h = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };

    echo $view->subHeader('../');
?>
    <?php echo $view->subPartialNav('../'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->staffSideNav(); ?>
      <div class="main-panel">
        <div class="content-wrapper fud-dash fud-apply">

          <div class="fud-hero">
            <div class="fud-hero-date">FUD Staff Cooperative Society Limited</div>
            <h2><i class="mdi mdi-account-remove"></i> Complete Withdrawal</h2>
            <p>Withdraw all your savings from the cooperative.</p>
          </div>

          <div class="fud-stats">
            <div class="fud-stat"><div class="fud-stat-icon fud-green"><i class="mdi mdi-safe"></i></div>
              <div><div class="fud-stat-label">Savings balance</div><div class="fud-stat-value">₦<?php echo number_format($balance, 2); ?></div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-blue"><i class="mdi mdi-calendar-clock"></i></div>
              <div><div class="fud-stat-label">Monthly savings</div><div class="fud-stat-value">₦<?php echo number_format($info['monthly']); ?></div></div></div>
          </div>

          <?php if ($info['pending_complete']) { ?>
            <div class="fud-notice"><i class="mdi mdi-clock-outline"></i>
              <span>Your complete withdrawal request is awaiting approval.</span>
              <a href="index.php">Dashboard <i class="mdi mdi-arrow-right"></i></a></div>
          <?php } elseif ($obligations['blockers'] || ($owed > 0 && !$obligations['can_settle']) || $obligations['payout'] <= 0) { ?>
            <div class="fud-section"><div class="fud-section-head"><h5><i class="mdi mdi-lock-outline"></i> You cannot withdraw completely yet</h5></div>
              <div class="fud-section-body"><ul class="mb-0">
                <?php foreach ($obligations['blockers'] as $b) { ?><li class="mb-1"><?php echo $h($b); ?></li><?php } ?>
                <?php if ($owed > 0 && !$obligations['can_settle']) { ?><li>You owe ₦<?php echo number_format($owed, 2); ?> on loans or commodity. Your savings do not cover it. Repay the rest first.</li><?php } ?>
                <?php if (!$obligations['blockers'] && $owed <= 0 && $obligations['payout'] <= 0) { ?><li>There is nothing to pay out: your savings balance is ₦<?php echo number_format($balance, 2); ?>.</li><?php } ?>
              </ul></div></div>
          <?php } else { ?>

          <div class="fud-notice"><i class="mdi mdi-alert-circle"></i>
            <span>This asks for <strong>all</strong> your savings to be paid out. To take out only part of your savings, use
              <a href="savings/savings_withdrawal.php" class="ml-0">Withdraw Savings</a>.</span></div>

          <form id="completeWithdrawal" autocomplete="off">
            <input type="hidden" id="staff_no" name="staff_no" value="<?php echo $h($_SESSION['username']); ?>">
            <div class="row">
              <div class="col-lg-8">
                <div class="fud-section">
                  <div class="fud-section-head"><h5><span class="fud-num">1</span> Pay into</h5>
                    <small class="text-muted">Your own bank account</small></div>
                  <div class="fud-section-body">
                    <div class="form-row">
                      <div class="form-group col-md-6">
                        <label for="bank">Bank</label>
                        <select class="form-control" id="bank" name="bank_id" required>
                          <option value="">Select a bank</option>
                          <?php foreach ($banks as $bank) { ?>
                            <option value="<?php echo $h($bank['id']); ?>"><?php echo $h($bank['name']); ?></option>
                          <?php } ?>
                        </select>
                      </div>
                      <div class="form-group col-md-6">
                        <label for="sort-code">Sort code <small class="text-muted">(optional)</small></label>
                        <input type="text" inputmode="numeric" class="form-control" id="sort-code" name="sort-code" maxlength="20">
                      </div>
                    </div>
                    <div class="form-row">
                      <div class="form-group col-md-6">
                        <label for="acct-no">Account number</label>
                        <input type="text" inputmode="numeric" class="form-control" id="acct-no" name="acct_no" placeholder="10 digits"
                               maxlength="10" pattern="\d{10}" title="Enter the 10-digit account number" required>
                      </div>
                      <div class="form-group col-md-6">
                        <label for="acct_name">Account name</label>
                        <input type="text" class="form-control" id="acct_name" name="acct_name" placeholder="As it appears on the account" required>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-lg-4">
                <div class="fud-section fud-summary">
                  <div class="fud-section-head"><h5><i class="mdi mdi-file-document-box"></i> Confirm</h5></div>
                  <div class="fud-section-body">
                    <dl class="fud-sum"><dt>Savings</dt><dd>₦<?php echo number_format($balance, 2); ?></dd>
                      <?php if ($owed > 0) { ?>
                        <?php foreach ($obligations['owed'] as $o) { ?><dt>Pays off <?php echo $h($o['label']); ?></dt><dd>−₦<?php echo number_format($o['balance'], 2); ?></dd><?php } ?>
                      <?php } ?>
                        <dt class="fud-total">Paid to you (everything left)</dt><dd class="fud-total">₦<?php echo number_format($obligations['payout'], 2); ?></dd>
                    </dl>
                    <?php if ($owed > 0) { ?>
                      <div class="custom-control custom-checkbox mb-3">
                        <input type="checkbox" class="custom-control-input" id="settleFromSavings" name="settle_from_savings" value="1" required>
                        <label class="custom-control-label" for="settleFromSavings">Use my savings to pay the ₦<?php echo number_format($owed, 2); ?> I owe, and pay me the rest.</label>
                      </div>
                    <?php } ?>
                    <div class="custom-control custom-checkbox mb-3">
                      <input type="checkbox" class="custom-control-input" id="completeAgree" required>
                      <label class="custom-control-label" for="completeAgree">I ask to withdraw all my savings into the bank account above.</label>
                    </div>
                    <div class="msg"></div>
                    <button type="submit" class="btn btn-danger btn-block btn-lg"><i class="mdi mdi-send"></i> Submit request</button>
                    <div class="fud-next">
                      <div class="fud-next-title">What happens next</div>
                      <ol><li>The Treasurer works out the amount due</li><li>The Chairman approves it</li><li>The money is paid into your account</li></ol>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </form>
          <?php } ?>

        </div>
        <?php echo $view->subFooter('../'); ?>
