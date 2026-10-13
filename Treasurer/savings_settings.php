<?php
    // Treasurer sets the minimum monthly savings used for membership applications and savings updates
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    require_once('../config/classes/SavingsG1.php');
    require_once('../config/classes/Settings.php');
    $view = new View();
    $user = new User();

    if (!User::is_authenticated('../auth/') || !User::isBur('../auth/logout.php')) {
        exit();
    }

    // Loan and share eligibility rules (see MemberG3::loanEligibilityError and MemberG2 share ledger)
    $rules = [
        'loan_interest_rate'               => ['Loan interest (flat %)', 'Added once to the approved amount when the Treasurer fixes a new loan (e.g. 10% of ₦100,000 = ₦10,000). Loans already fixed keep their rate.', 10, 'decimal'],
        'loan_max_savings_multiple'        => ['Loan limit (times savings balance)', 'A member\'s open loans plus a new loan may not exceed this many times their savings balance. 0 = no limit.', 2, 'decimal'],
        'guarantor_max_open_loans'         => ['Loans one member may guarantee at a time', 'Open (pending or not fully repaid) loans. 0 = no limit.', 2, 'int'],
        'share_min_balance_after_transfer' => ['Minimum shares kept after a transfer (₦)', 'A member must keep at least this amount of shares after transferring shares to another member.', 20000, 'int'],
    ];

    // Post/Redirect/Get so a page refresh does not re-submit
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rules_form'])) {
        $saved = true;
        foreach ($rules as $key => $rule) {
            $raw = isset($_POST[$key]) ? trim($_POST[$key]) : '';
            if ($raw === '' || !is_numeric($raw) || $raw < 0) {
                $_SESSION['settings_message'] = ['danger', 'Enter a valid number for "' . $rule[0] . '".'];
                header('location: savings_settings.php');
                exit();
            }
            $value = $rule[3] === 'int' ? (string) (int) $raw : (string) round((float) $raw, 2);
            $saved = Settings::set($key, $value, $_SESSION['username']) && $saved;
        }
        $_SESSION['settings_message'] = $saved ? ['success', 'Loan and share rules saved.'] : ['danger', 'Could not save the settings. Contact system admin.'];
        header('location: savings_settings.php');
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['min_monthly_savings'])) {
        $amount = SavingsG1::parseAmount($_POST['min_monthly_savings']);
        if ($amount === null || $amount < 1) {
            $_SESSION['settings_message'] = ['danger', 'Enter a valid minimum amount.'];
        } elseif (Settings::set('min_monthly_savings', $amount, $_SESSION['username'])) {
            $_SESSION['settings_message'] = ['success', 'Minimum monthly savings changed to ₦' . number_format($amount) . '.'];
        } else {
            $_SESSION['settings_message'] = ['danger', 'Could not save the setting. Contact system admin.'];
        }
        header('location: savings_settings.php');
        exit();
    }

    $message = isset($_SESSION['settings_message']) ? $_SESSION['settings_message'] : null;
    unset($_SESSION['settings_message']);

    $minMonthlySavings = SavingsG1::getMinMonthlySavings();
    $lastUpdate = Settings::lastUpdate('min_monthly_savings');

    echo $view->subHeader('../');
?>
    <!-- partial:partials/_navbar.html -->
    <?php  echo $view->subPartialNav('../')?>
    <!-- partial -->
    <div class="container-fluid page-body-wrapper">
      <!-- partial:partials/_sidebar.html -->
      <?php  echo $view->treasurerSideNav()?>
      <!-- partial -->
      <div class="main-panel">
        <div class="content-wrapper">

          <div class="row">
            <div class="col-md-12 grid-margin">
              <h2>TREASURER ACCOUNT</h2>
              <h4 class="text-primary">Savings Settings</h4>
            </div>
          </div>

          <?php if ($message) { ?>
            <div class="row">
              <div class="col-md-8 grid-margin">
                <div class="alert alert-<?php echo $message[0]; ?>"><?php echo htmlspecialchars($message[1]); ?></div>
              </div>
            </div>
          <?php } ?>

          <div class="row">
            <div class="col-md-8 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Minimum Monthly Savings</h5>
                  <p class="text-muted">
                    Applies to new membership applications, the amount you fix for each applicant,
                    and members' savings update requests. Existing members' savings are not changed.
                  </p>
                  <form method="POST" class="form-inline"
                        onsubmit="return confirm('Change the minimum monthly savings to ₦' + Number(this.min_monthly_savings.value).toLocaleString() + '?');">
                    <div class="input-group mr-2 mb-2">
                      <div class="input-group-prepend"><span class="input-group-text">₦</span></div>
                      <input type="number" name="min_monthly_savings" class="form-control" min="1" step="1" required
                             value="<?php echo (int) $minMonthlySavings; ?>">
                    </div>
                    <button type="submit" class="btn btn-primary mb-2">Save</button>
                  </form>
                  <?php if ($lastUpdate && $lastUpdate['updated_at']) { ?>
                    <p class="text-muted small mb-0 mt-2">
                      Last changed by <?php echo htmlspecialchars($lastUpdate['updated_by']); ?>
                      on <?php echo date('d/m/Y H:i', strtotime($lastUpdate['updated_at'])); ?>.
                    </p>
                  <?php } ?>
                  <p class="mt-3 mb-0"><a href="member_list.php">&larr; Back to Membership Applicants</a></p>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-8 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Loan and Share Rules</h5>
                  <form method="POST" onsubmit="return confirm('Save the loan and share rules?');">
                    <input type="hidden" name="rules_form" value="1">
                    <?php foreach ($rules as $key => $rule) { ?>
                      <div class="form-group">
                        <label for="<?php echo $key; ?>" class="mb-1"><strong><?php echo htmlspecialchars($rule[0]); ?></strong></label>
                        <input type="number" class="form-control col-md-4" id="<?php echo $key; ?>" name="<?php echo $key; ?>" min="0"
                               step="<?php echo $rule[3] === 'int' ? '1' : '0.1'; ?>" required
                               value="<?php echo htmlspecialchars(Settings::get($key, $rule[2])); ?>">
                        <small class="form-text text-muted"><?php echo htmlspecialchars($rule[1]); ?></small>
                      </div>
                    <?php } ?>
                    <button type="submit" class="btn btn-primary">Save Rules</button>
                  </form>
                </div>
              </div>
            </div>
          </div>

        </div>
        <!-- content-wrapper ends -->
        <?php
          echo $view->subFooter('../');
        ?>
