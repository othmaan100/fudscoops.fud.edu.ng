<?php
// Member loan application page, shared by R/business_loan.php and R/soft_loan.php.
// Expects: $view, $user, $loanTypeName ('Business Loan' / 'Soft Loan'), $loanTypeKey ('business' / 'soft').
// Field names and ids are the ones js/custom.js and ajax/business_loan.php use.
require_once __DIR__ . '/../classes/MemberG1.php';
require_once __DIR__ . '/../classes/MemberG3.php';
require_once __DIR__ . '/../classes/Settings.php';

$staff = $user->getStaffInformation($_SESSION['username']);
if (!is_array($staff)) {
    $staff = [];
}
$staff += ['fname' => '', 'lname' => '', 'oname' => '', 'sp_no' => $_SESSION['username'], 'dept' => '', 'phone_no' => ''];
$memberId = $user->getMemberId($user->getEmployeeId($_SESSION['username']));
$banks = (new MemberG1())->getBanks();
$ctx = (new MemberG3())->loanApplicationContext($memberId, $loanTypeName);
$guarantorMax = (int) Settings::get('guarantor_max_open_loans', 2);

$h = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
$naira = function ($v) { return ((float) $v < 0 ? '-' : '') . '₦' . number_format(abs((float) $v)); };
$pct = rtrim(rtrim(number_format($ctx['rate'], 2), '0'), '.');
$multiple = rtrim(rtrim(number_format($ctx['multiple'], 2), '0'), '.');
$isSoft = $loanTypeKey === 'soft';

// Repayment periods offered (up to the maximum set for the loan type)
$tenors = array_values(array_filter([3, 6, 9, 12], function ($t) use ($ctx) { return $t <= $ctx['max_tenor']; }));
if ($ctx['max_tenor'] > 0 && !in_array($ctx['max_tenor'], $tenors)) {
    $tenors[] = $ctx['max_tenor'];
}

$blocked = null;
if ($isSoft && !$ctx['is_target']) {
    $blocked = ['info', 'mdi-target', 'Soft loans are available to members on target savings. Start a target savings plan to apply.',
                'savings/target_savings_request.php', 'Target savings'];
} elseif ($ctx['open_loan']) {
    $open = $ctx['open_loan'];
    $blocked = ['', 'mdi-information-outline', 'You already have a ' . $loanTypeName . ' (' . strtolower($open['stage'])
                . ($open['stage'] === 'Repaying' ? ', balance ' . $naira($open['balance']) : '') . '). You can apply for another once it is fully repaid.',
                'view_loan_approval.php', 'View my loans'];
}

echo $view->subHeader('../');
?>
    <?php echo $view->subPartialNav('../'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->staffSideNav(); ?>
      <div class="main-panel">
        <div class="content-wrapper fud-dash fud-apply">

          <div class="fud-hero">
            <div class="fud-hero-date">FUD Staff Cooperative Society Limited</div>
            <h2><i class="mdi <?php echo $isSoft ? 'mdi-wallet' : 'mdi-briefcase-outline'; ?>"></i> <?php echo $h($loanTypeName); ?> Application</h2>
            <p><?php echo $isSoft
                ? 'A loan for members on target savings, repaid by monthly salary deduction.'
                : 'Finance materials from a supplier of your choice, repaid by monthly salary deduction.'; ?></p>
          </div>

          <div class="fud-stats">
            <div class="fud-stat">
              <div class="fud-stat-icon fud-green"><i class="mdi mdi-safe"></i></div>
              <div><div class="fud-stat-label">Your savings balance</div>
                <div class="fud-stat-value"><?php echo $naira($ctx['savings']); ?></div></div>
            </div>
            <div class="fud-stat">
              <div class="fud-stat-icon fud-blue"><i class="mdi mdi-cash-multiple"></i></div>
              <div><div class="fud-stat-label">You can borrow up to</div>
                <div class="fud-stat-value"><?php echo $ctx['limit'] === null ? 'No limit' : $naira($ctx['limit']); ?></div>
                <?php if ($ctx['limit'] !== null) { ?><div class="fud-stat-sub"><?php echo $multiple; ?>× savings, less loans you owe</div><?php } ?></div>
            </div>
            <div class="fud-stat">
              <div class="fud-stat-icon fud-amber"><i class="mdi mdi-percent"></i></div>
              <div><div class="fud-stat-label">Interest</div>
                <div class="fud-stat-value"><?php echo $pct; ?>% flat</div>
                <div class="fud-stat-sub">added once to the approved amount</div></div>
            </div>
            <div class="fud-stat">
              <div class="fud-stat-icon fud-purple"><i class="mdi mdi-calendar-clock"></i></div>
              <div><div class="fud-stat-label">Repayment period</div>
                <div class="fud-stat-value">Up to <?php echo (int) $ctx['max_tenor']; ?> months</div></div>
            </div>
          </div>

          <?php if ($blocked) { ?>
            <div class="fud-notice <?php echo $blocked[0]; ?>">
              <i class="mdi <?php echo $blocked[1]; ?>"></i>
              <span><?php echo $h($blocked[2]); ?></span>
              <a href="<?php echo $blocked[3]; ?>"><?php echo $h($blocked[4]); ?> <i class="mdi mdi-arrow-right"></i></a>
            </div>
          <?php } else { ?>

          <form id="business_loan" autocomplete="off" data-rate="<?php echo (float) $ctx['rate']; ?>"
                data-limit="<?php echo $ctx['limit'] === null ? '' : (float) $ctx['limit']; ?>" data-type-name="<?php echo $h($loanTypeName); ?>">
            <input type="hidden" name="loan_type" value="<?php echo $h($loanTypeKey); ?>">

            <div class="row">
              <div class="col-lg-8">

                <!-- 1. Applicant -->
                <div class="fud-section">
                  <div class="fud-section-head"><h5><span class="fud-num">1</span> Your details</h5>
                    <small class="text-muted">From your staff record</small></div>
                  <div class="fud-section-body">
                    <div class="fud-facts">
                      <div><span>Name</span><?php echo $h(trim($staff['fname'] . ' ' . $staff['lname'] . ' ' . $staff['oname'])); ?></div>
                      <div><span>Staff / membership no.</span><?php echo $h($staff['sp_no']); ?></div>
                      <div><span>Department / unit</span><?php echo $h($staff['dept']); ?></div>
                      <div><span>Phone</span><?php echo $staff['phone_no'] !== '' ? $h($staff['phone_no']) : '–'; ?></div>
                    </div>
                  </div>
                </div>

                <!-- 2. Loan -->
                <div class="fud-section">
                  <div class="fud-section-head"><h5><span class="fud-num">2</span> The loan</h5></div>
                  <div class="fud-section-body">
                    <div class="form-group">
                      <label for="meterials">Material(s) needed</label>
                      <input type="text" class="form-control" id="meterials" name="meterials" placeholder="e.g. 2 bags of rice, a laptop" required>
                    </div>
                    <div class="form-group">
                      <label for="reason">Reason</label>
                      <textarea class="form-control" id="reason" name="reason" rows="3" placeholder="What the loan is for" required></textarea>
                    </div>
                    <div class="form-group">
                      <label for="amount_applied">Amount you are applying for</label>
                      <div class="input-group fud-amount">
                        <div class="input-group-prepend"><span class="input-group-text">₦</span></div>
                        <input type="text" inputmode="decimal" class="form-control" id="amount_applied" name="amount-applied" placeholder="0" required>
                      </div>
                      <small class="amount_msg d-block mt-1"></small>
                    </div>
                    <label class="d-block">Repay over</label>
                    <div class="fud-tenors">
                      <?php foreach ($tenors as $i => $t) { ?>
                        <label class="fud-tenor">
                          <input type="radio" name="loan_repayment" value="<?php echo $t; ?>" <?php echo $i === 0 ? 'required' : ''; ?>>
                          <span><strong><?php echo $t; ?></strong> months</span>
                        </label>
                      <?php } ?>
                    </div>
                  </div>
                </div>

                <!-- 3. Supplier -->
                <div class="fud-section">
                  <div class="fud-section-head"><h5><span class="fud-num">3</span> Supplier</h5>
                    <small class="text-muted">The cooperative pays the supplier directly</small></div>
                  <div class="fud-section-body">
                    <div class="form-row">
                      <div class="form-group col-md-6">
                        <label for="supplier_name">Supplier's name</label>
                        <input type="text" class="form-control" id="supplier_name" name="supplier_name" placeholder="As it appears on the bank account" required>
                      </div>
                      <div class="form-group col-md-6">
                        <label for="supplier_gsm-no">Phone number</label>
                        <input type="tel" class="form-control" id="supplier_gsm-no" name="supplier_gsm-no" placeholder="080..." pattern="[0-9+ ]{7,15}" required>
                      </div>
                    </div>
                    <div class="form-group">
                      <label for="supplier_address">Address</label>
                      <input type="text" class="form-control" id="supplier_address" name="supplier_address" required>
                    </div>
                    <div class="form-row">
                      <div class="form-group col-md-5">
                        <label for="bank">Bank</label>
                        <select class="form-control" id="bank" name="bank" required>
                          <option value="">Select a bank</option>
                          <?php foreach ($banks as $bank) { ?>
                            <option value="<?php echo $h($bank['id']); ?>"><?php echo $h($bank['name']); ?></option>
                          <?php } ?>
                        </select>
                      </div>
                      <div class="form-group col-md-4">
                        <label for="account_number">Account number</label>
                        <input type="text" inputmode="numeric" class="form-control" id="account_number" name="account_number" placeholder="10 digits"
                               maxlength="10" pattern="\d{10}" title="Enter the 10-digit account number" required>
                      </div>
                      <div class="form-group col-md-3">
                        <label for="supplier_sort-code">Sort code <small class="text-muted">(optional)</small></label>
                        <input type="text" inputmode="numeric" class="form-control" id="supplier_sort-code" name="supplier_sort-code" maxlength="20">
                      </div>
                    </div>
                  </div>
                </div>

                <!-- 4. Guarantor -->
                <div class="fud-section">
                  <div class="fud-section-head"><h5><span class="fud-num">4</span> Guarantor</h5>
                    <small class="text-muted">Senior staff (SP) member</small></div>
                  <div class="fud-section-body">
                    <label for="guarantor_sp_no">Guarantor's staff number</label>
                    <div class="input-group">
                      <input type="text" class="form-control" id="guarantor_sp_no" name="guarantor_sp_no" placeholder="e.g. SP/RA/1234" required>
                      <div class="input-group-append">
                        <button type="button" class="btn btn-success" id="verify_guarantor" rel="<?php echo $h($_SESSION['username']); ?>">
                          <i class="mdi mdi-account-check"></i> Verify
                        </button>
                      </div>
                    </div>
                    <div class="guarantor mt-2"></div>
                    <small class="text-muted">Your guarantor must accept from their own account before the Treasurer reviews your application.</small>
                  </div>
                </div>

              </div>

              <!-- Summary -->
              <div class="col-lg-4">
                <div class="fud-section fud-summary">
                  <div class="fud-section-head"><h5><i class="mdi mdi-file-document-box"></i> Summary</h5></div>
                  <div class="fud-section-body">
                    <dl class="fud-sum">
                      <dt>Amount</dt><dd id="sumAmount">–</dd>
                      <dt>Interest (<?php echo $pct; ?>%)</dt><dd id="sumInterest">–</dd>
                      <dt class="fud-total">Total to repay</dt><dd class="fud-total" id="sumTotal">–</dd>
                      <dt>Monthly deduction</dt><dd id="sumMonthly">–</dd>
                    </dl>
                    <p class="fud-sum-note">An estimate. The Treasurer fixes the final amount and period; the Chairman then authorizes it.</p>

                    <ul class="fud-checks">
                      <li>You must be an approved member.</li>
                      <li>Only one <?php echo $h($loanTypeName); ?> at a time.</li>
                      <li>Your guarantor may not be guaranteeing <?php echo $guarantorMax; ?> other loans.</li>
                      <li>Your net salary must cover the monthly deduction.</li>
                    </ul>
                    <div class="custom-control custom-checkbox mb-3">
                      <input type="checkbox" class="custom-control-input" id="agree" required>
                      <label class="custom-control-label" for="agree">I agree that the monthly deduction is taken from my salary until the loan is repaid.</label>
                    </div>

                    <div class="msg"></div>
                    <button type="submit" class="btn btn-primary btn-block btn-lg"><i class="mdi mdi-send"></i> Submit application</button>

                    <div class="fud-next">
                      <div class="fud-next-title">What happens next</div>
                      <ol>
                        <li>Your guarantor accepts</li>
                        <li>The Treasurer fixes the amount</li>
                        <li>The Chairman authorizes it</li>
                        <li>The supplier is paid; deductions start the following month</li>
                      </ol>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </form>
          <?php } ?>

        </div>
        <?php echo $view->subFooter('../'); ?>
