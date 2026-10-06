<?php
    // Treasurer reviews a membership applicant's details, then fixes the monthly savings
    // and sends the application to the Chairman for authorization.
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    require_once('../config/classes/MemberG1.php');
    require_once('../config/classes/SavingsG1.php');
    $view = new View();
    $user = new User();
    $members = new MemberG1();

    if (!User::is_authenticated('../auth/') || !User::isBur('../auth/logout.php')) {
        exit();
    }

    $employeeId = isset($_GET['employee_id']) ? (int) $_GET['employee_id'] : 0;
    $applicant = $employeeId ? $members->getApplicantDetails($employeeId) : null;
    $minMonthlySavings = SavingsG1::getMinMonthlySavings();

    // Payslips for the last three months of this year (Treasurer/slip.php only serves the current year)
    $payslipMonths = [];
    for ($k = 1; $k <= 3; $k++) {
        $time = strtotime("first day of -$k month");
        if (date('Y', $time) == date('Y')) {
            $payslipMonths[date('n', $time)] = date('F', $time);
        }
    }

    function detailRow($label, $value) {
        echo '<tr><th style="width: 35%">' . $label . '</th><td>' . htmlspecialchars((string) $value) . '</td></tr>';
    }

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
              <h4 class="text-primary">Membership Application</h4>
              <a href="member_list.php">&larr; Back to Membership Applicants</a>
            </div>
          </div>

          <?php if (!$applicant) { ?>
            <div class="alert alert-warning">Application not found.</div>
          <?php } else {
              $fullyApproved = $applicant['chairman_approval'] == 1 && $applicant['treasurer_approval'] == 1;
          ?>
          <div class="row">
            <div class="col-lg-7 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Applicant Information</h5>
                  <table class="table table-sm table-bordered">
                    <?php
                      detailRow('Name', preg_replace('/\s+/', ' ', trim($applicant['title'] . ' ' . $applicant['fname'] . ' ' . $applicant['oname'] . ' ' . $applicant['lname'])));
                      detailRow('Staff Number', $applicant['sp_no']);
                      detailRow('Department / Unit', $applicant['dept']);
                      detailRow('Cadre', $applicant['cadre']);
                      detailRow('Rank', $applicant['rank']);
                      detailRow('Grade Level / Step', trim($applicant['grade_level'] . ' / ' . $applicant['step'], ' /'));
                      detailRow('IPPIS Grade', $applicant['grade']);
                      detailRow('Type of Appointment', $applicant['nature_of_appo']);
                      detailRow('Phone', $applicant['phone_no']);
                      detailRow('Email', $applicant['email']);
                      detailRow('Bank', $applicant['bank_name']);
                      detailRow('Account Number', $applicant['acctno'] !== '' ? str_pad($applicant['acctno'], 10, '0', STR_PAD_LEFT) : '');
                      detailRow('Permanent Home Address', $applicant['permanent_address']);
                      detailRow('Date Applied', $applicant['reg_date'] ? date('d/m/Y', strtotime($applicant['reg_date'])) : '');
                    ?>
                  </table>

                  <h5 class="card-title mt-4">Next of Kin</h5>
                  <table class="table table-sm table-bordered">
                    <?php
                      detailRow('Name', $applicant['next_of_kin_name']);
                      detailRow('Phone', $applicant['next_of_kin_gsm']);
                      detailRow('Address', $applicant['next_of_kin_address']);
                    ?>
                  </table>

                  <?php if ($payslipMonths) { ?>
                    <p class="mt-3 mb-0"><strong>Payslips:</strong>
                      <?php foreach ($payslipMonths as $month => $monthName) { ?>
                        <a href="slip.php?legacy=<?php echo urlencode($applicant['sp_no']); ?>&s=<?php echo $month; ?>" class="ml-2"><?php echo $monthName; ?></a>
                      <?php } ?>
                    </p>
                  <?php } ?>
                </div>
              </div>
            </div>

            <div class="col-lg-5 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Monthly Savings</h5>
                  <?php if ($fullyApproved) { ?>
                    <div class="alert alert-success">Membership approved. Monthly savings: ₦<?php echo number_format((float) $applicant['proposed_monthly_savings'], 2); ?></div>
                  <?php } else { ?>
                    <p>
                      Status:
                      <?php if ($applicant['treasurer_approval'] == 1) { ?>
                        <span class="badge badge-info">Awaiting Chairman</span>
                        <br><small class="text-muted">You can still change the amount until the Chairman authorizes it.</small>
                      <?php } else { ?>
                        <span class="badge badge-warning">Awaiting Treasurer</span>
                      <?php } ?>
                    </p>
                    <p class="mb-1"><?php echo $applicant['treasurer_approval'] == 1 ? 'Amount fixed' : 'Amount proposed by applicant'; ?>:
                      <strong>₦<?php echo number_format((float) $applicant['proposed_monthly_savings'], 2); ?></strong></p>
                    <div class="form-group mt-3">
                      <label for="approvedAmount">Approved monthly savings (minimum ₦<?php echo number_format($minMonthlySavings); ?>)</label>
                      <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text">₦</span></div>
                        <input type="number" id="approvedAmount" class="form-control" min="<?php echo $minMonthlySavings; ?>" step="1"
                               value="<?php echo (int) $applicant['proposed_monthly_savings'] > 0 ? (int) $applicant['proposed_monthly_savings'] : ''; ?>">
                      </div>
                    </div>
                    <button type="button" id="approveApplicant" class="btn btn-success"
                            data-employee-id="<?php echo (int) $applicant['employee_id']; ?>"
                            data-staff-no="<?php echo htmlspecialchars($applicant['sp_no']); ?>">
                      <?php echo $applicant['treasurer_approval'] == 1 ? 'Change Amount' : 'Approve & Send to Chairman'; ?>
                    </button>
                    <div class="msg mt-3"></div>
                  <?php } ?>
                </div>
              </div>
            </div>
          </div>
          <?php } ?>

        </div>
        <!-- content-wrapper ends -->

        <script>
          $(document).ready(function() {
              $('#approveApplicant').on('click', function() {
                  const button = $(this);
                  const input = $('#approvedAmount');
                  const amount = parseFloat(input.val());
                  const minimum = parseFloat(input.attr('min'));

                  if (isNaN(amount) || amount < minimum) {
                      alert('Monthly savings must be at least ₦' + minimum.toLocaleString() + '.');
                      input.focus();
                      return;
                  }
                  if (!confirm('Fix ' + button.data('staff-no') + "'s monthly savings at ₦" + amount.toLocaleString() + ' and send the application to the Chairman for authorization?')) {
                      return;
                  }

                  button.prop('disabled', true);
                  $.post('../ajax/process_member_approval.php', {
                      employee_id: button.data('employee-id'),
                      amount: amount
                  }, function(res) {
                      const alertClass = res.success ? 'alert-success' : 'alert-danger';
                      $('.msg').html('<div class="alert ' + alertClass + '"></div>').find('.alert').text(res.message);
                      if (res.success) {
                          setTimeout(function() { window.location = 'member_list.php'; }, 1500);
                      } else {
                          button.prop('disabled', false);
                      }
                  }, 'json').fail(function() {
                      $('.msg').html('<div class="alert alert-danger">Network error. Please try again.</div>');
                      button.prop('disabled', false);
                  });
              });
          });
        </script>

        <?php
          echo $view->subFooter('../');
        ?>
