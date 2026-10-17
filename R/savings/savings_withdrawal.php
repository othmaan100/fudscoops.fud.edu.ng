<?php
    // Withdraw savings (member). Submitted by js/custom.js (#savingswithdrawal) to ajax/savingswithdrawal.php
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
    $member = new MemberG1();
    $banks = $member->getBanks();
    $employeeId = $user->getEmployeeId($_SESSION['username']);
    $memberId = $user->getMemberId($employeeId);
    $balance = (float) $member->getCurrentSavingsAmount($_SESSION['username']);
    $info = (new Dashboard())->memberSavingsInfo($memberId, $employeeId);
    $minMonthlySavings = SavingsG1::getMinMonthlySavings();
    require_once('../../config/classes/LoanControls.php');
    $minBalance = LoanControls::withdrawalMinBalance();
    $maxWithdrawal = LoanControls::maxWithdrawal($memberId);
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
            <h2><i class="mdi mdi-bank-transfer-out"></i> Withdraw Savings</h2>
            <p>Withdraw part of your savings into your bank account.</p>
          </div>

          <div class="fud-stats">
            <div class="fud-stat"><div class="fud-stat-icon fud-green"><i class="mdi mdi-safe"></i></div>
              <div><div class="fud-stat-label">Savings balance</div><div class="fud-stat-value">₦<?php echo number_format($balance, 2); ?></div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-blue"><i class="mdi mdi-calendar-clock"></i></div>
              <div><div class="fud-stat-label">Monthly savings</div><div class="fud-stat-value">₦<?php echo number_format($info['monthly']); ?></div></div></div>
          </div>

          <?php foreach ((new Dashboard())->recentWithdrawalRejections($memberId) as $rj) { ?>
            <div class="fud-notice"><i class="mdi mdi-close-circle"></i>
              <span>Your request of ₦<?php echo number_format((float) $rj['proposed_withrawal_amount'], 2); ?> on <?php echo date('d M Y', strtotime($rj['proposed_date'])); ?> was rejected by the <?php echo $rj['rejected_by']; ?><?php echo $rj['reason'] ? ': ' . $h($rj['reason']) : '.'; ?></span></div>
          <?php } ?>
          <?php if ($info['pending_withdrawal']) { ?>
            <div class="fud-notice"><i class="mdi mdi-clock-outline"></i>
              <span>Your withdrawal request of ₦<?php echo number_format((float) $info['pending_withdrawal']); ?> is awaiting approval.
                You can make another request once it has been decided.</span>
              <a href="../index.php">Dashboard <i class="mdi mdi-arrow-right"></i></a></div>
          <?php } else { ?>

          <form id="savingswithdrawal" autocomplete="off">
            <input type="hidden" name="member_id" id="member_id" value="<?php echo (int) $memberId; ?>">
            <div class="row">
              <div class="col-lg-8">
                <div class="fud-section">
                  <div class="fud-section-head"><h5><span class="fud-num">1</span> Amount</h5></div>
                  <div class="fud-section-body">
                    <label for="withdrawal_amount">Amount to withdraw</label>
                    <div class="input-group fud-amount">
                      <div class="input-group-prepend"><span class="input-group-text">₦</span></div>
                      <input type="number" class="form-control" id="withdrawal_amount" name="withdrawal_amount" min="1"
                             max="<?php echo $maxWithdrawal; ?>" step="0.01" placeholder="0.00" required>
                    </div>
                    <small class="d-block mt-2 text-muted">Up to ₦<?php echo number_format($maxWithdrawal, 2); ?>: ₦<?php echo number_format($minBalance, 2); ?> must stay in your savings.</small>
                  </div>
                </div>

                <div class="fud-section">
                  <div class="fud-section-head"><h5><span class="fud-num">2</span> Pay into</h5>
                    <small class="text-muted">Your own bank account</small></div>
                  <div class="fud-section-body">
                    <div class="form-row">
                      <div class="form-group col-md-6">
                        <label for="bank">Bank</label>
                        <select class="form-control" id="bank" name="bank_id" required>
                          <option value="">Select a bank</option>
                          <?php foreach ($banks as $bank) { ?>
                            <option value="<?php echo $h($bank['name']); ?>"><?php echo $h($bank['name']); ?></option>
                          <?php } ?>
                        </select>
                      </div>
                      <div class="form-group col-md-6">
                        <label for="acct_no">Account number</label>
                        <input type="text" inputmode="numeric" class="form-control" id="acct_no" name="acct_no" placeholder="10 digits"
                               maxlength="10" pattern="\d{10}" title="Enter the 10-digit account number" required>
                      </div>
                    </div>
                    <div class="form-group mb-0">
                      <label for="acct_name">Account name</label>
                      <input type="text" class="form-control" id="acct_name" name="acct_name" placeholder="As it appears on the account" required>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-lg-4">
                <div class="fud-section fud-summary">
                  <div class="fud-section-head"><h5><i class="mdi mdi-file-document-box"></i> Confirm</h5></div>
                  <div class="fud-section-body">
                    <dl class="fud-sum">
                      <dt>Withdraw</dt><dd id="sumAmount">–</dd>
                      <dt>Balance after</dt><dd id="sumAfter">₦<?php echo number_format($balance, 2); ?></dd>
                    </dl>
                    <ul class="fud-checks">
                      <li>₦<?php echo number_format($minBalance, 2); ?> must stay in your savings.</li>
                      <li>Keep saving at least ₦<?php echo number_format($minMonthlySavings, 2); ?> a month.</li>
                      <li>One withdrawal request at a time.</li>
                    </ul>
                    <div class="custom-control custom-checkbox mb-3">
                      <input type="checkbox" class="custom-control-input" id="savingsUpdate" name="undertaking" required>
                      <label class="custom-control-label" for="savingsUpdate">I authorize the withdrawal of this amount from my savings into the bank account above.</label>
                    </div>
                    <div class="msg"></div>
                    <button type="submit" id="savingWithdrwal" class="btn btn-primary btn-block btn-lg"><i class="mdi mdi-send"></i> Submit request</button>
                    <div class="fud-next">
                      <div class="fud-next-title">What happens next</div>
                      <ol><li>The Treasurer processes the request</li><li>The Chairman approves it</li><li>The money is paid into your account</li></ol>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </form>
          <?php } ?>

        </div>
        <?php echo $view->subFooter('../../'); ?>

<script>
(function () {
    var input = document.getElementById('withdrawal_amount');
    if (!input) { return; }
    var balance = <?php echo json_encode($balance); ?>;
    var money = function (v) { return '₦' + v.toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); };
    input.addEventListener('input', function () {
        var v = parseFloat(input.value) || 0;
        document.getElementById('sumAmount').textContent = v ? money(v) : '–';
        var after = document.getElementById('sumAfter');
        after.textContent = money(balance - v);
        after.className = balance - v < 0 ? 'text-danger' : '';
    });
})();
</script>
