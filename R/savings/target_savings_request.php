<?php
    // Target savings application (member). Submitted by js/custom.js (#targetsavingsrequest) to ajax/targetsavingsrequest.php
    require_once('../../config/classes/View.php');
    require_once('../../config/classes/User.php');
    require_once('../../config/classes/MemberG1.php');
    require_once('../../config/classes/Dashboard.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../../auth/') || !User::isStaff('../../auth/logout.php')) {
        exit();
    }
    $employeeId = $user->getEmployeeId($_SESSION['username']);
    $memberId = $user->getMemberId($employeeId);
    $info = (new Dashboard())->memberSavingsInfo($memberId, $employeeId);
    $target = $info['target'];
    // Senior staff (SP) save and wait at least 24 months; junior staff (JP) 12 (also checked by the server)
    $grade = strtoupper(explode('/', $_SESSION['username'])[0]);
    $minMonths = $grade === 'SP' ? 24 : 12;
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
            <h2><i class="mdi mdi-target"></i> Target Savings Application</h2>
            <p>Save towards a goal for a fixed period. Members on target savings can also apply for a Soft Loan.</p>
          </div>

          <?php if ($target) { ?>
            <div class="fud-notice info"><i class="mdi mdi-information-outline"></i>
              <span>You applied on <?php echo date('d/m/Y', strtotime($target['date_saved'])); ?> to save ₦<?php echo number_format((float) $target['savings_amount']); ?>
                for <?php echo (int) $target['saving_period']; ?> months
                (<?php echo $target['guarantor_status'] == 1 ? 'accepted by your referee' : ($target['guarantor_status'] == -1 ? 'declined by your referee' : 'awaiting your referee'); ?>).
                Apply below only if you want another target savings plan.</span></div>
          <?php } ?>

          <form id="targetsavingsrequest" autocomplete="off">
            <input type="hidden" id="grade" name="grade" value="<?php echo $h($grade); ?>">
            <input type="hidden" name="member_id" id="member_id" value="<?php echo (int) $memberId; ?>">
            <div class="row">
              <div class="col-lg-8">
                <div class="fud-section">
                  <div class="fud-section-head"><h5><span class="fud-num">1</span> Your plan</h5></div>
                  <div class="fud-section-body">
                    <div class="form-group">
                      <label for="amount">Amount to save</label>
                      <div class="input-group fud-amount">
                        <div class="input-group-prepend"><span class="input-group-text">₦</span></div>
                        <input type="number" class="form-control" id="amount" name="amount" min="1" step="1" placeholder="0" required>
                      </div>
                    </div>
                    <div class="form-row">
                      <div class="form-group col-md-6">
                        <label for="periodSave">Months to save</label>
                        <input type="number" class="form-control" id="periodSave" name="periodSave" min="<?php echo $minMonths; ?>" step="1"
                               placeholder="<?php echo $minMonths; ?> or more" required>
                      </div>
                      <div class="form-group col-md-6">
                        <label for="periodWithdraw">Months before withdrawal</label>
                        <input type="number" class="form-control" id="periodWithdraw" name="periodWithdraw" min="<?php echo $minMonths; ?>" step="1"
                               placeholder="<?php echo $minMonths; ?> or more" required>
                      </div>
                    </div>
                    <small class="text-muted"><?php echo $grade === 'SP' ? 'Senior' : 'Junior'; ?> staff: at least <?php echo $minMonths; ?> months for both.</small>
                  </div>
                </div>

                <div class="fud-section">
                  <div class="fud-section-head"><h5><span class="fud-num">2</span> Referee</h5>
                    <small class="text-muted">Senior staff (SP) member</small></div>
                  <div class="fud-section-body">
                    <label for="guarantor_sp_no">Referee's staff number</label>
                    <div class="input-group">
                      <input type="text" class="form-control" id="guarantor_sp_no" name="guarantor_sp_no" placeholder="e.g. SP/RA/1234" required>
                      <div class="input-group-append">
                        <button type="button" class="btn btn-success" id="verify_guarantor" rel="<?php echo $h($_SESSION['username']); ?>">
                          <i class="mdi mdi-account-check"></i> Verify</button>
                      </div>
                    </div>
                    <div class="guarantor mt-2"></div>
                  </div>
                </div>
              </div>

              <div class="col-lg-4">
                <div class="fud-section fud-summary">
                  <div class="fud-section-head"><h5><i class="mdi mdi-file-document-box"></i> Confirm</h5></div>
                  <div class="fud-section-body">
                    <ul class="fud-checks">
                      <li>Your savings stay locked for the period you choose.</li>
                      <li>Your referee must be a senior staff member of the cooperative.</li>
                    </ul>
                    <div class="custom-control custom-checkbox mb-3">
                      <input type="checkbox" class="custom-control-input" id="savingsUpdate" name="undertaking" required>
                      <label class="custom-control-label" for="savingsUpdate">I agree to save this amount for the period above and not to withdraw it before then.</label>
                    </div>
                    <div class="msg"></div>
                    <button type="submit" id="savingWithdrwal" class="btn btn-primary btn-block btn-lg"><i class="mdi mdi-send"></i> Submit application</button>
                  </div>
                </div>
              </div>
            </div>
          </form>

        </div>
        <?php echo $view->subFooter('../../'); ?>
