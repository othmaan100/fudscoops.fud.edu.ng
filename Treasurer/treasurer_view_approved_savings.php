<?php
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    require_once('../config/classes/SavingsG1.php');
    $view = new View();
    $user = new User();
    $savings = new SavingsG1();

    if (!User::is_authenticated('../auth/') || !User::isBur('../auth/logout.php')) {
        exit();
    }

    // Upload of the approved list (offline processing). Post/Redirect/Get so a page refresh does not re-submit.
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['approved_list'])) {
        $file = $_FILES['approved_list'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
            $summary = ['approved' => 0, 'skipped' => 0, 'errors' => ['No file uploaded or the upload failed.']];
        } elseif ($ext !== 'csv') {
            $summary = ['approved' => 0, 'skipped' => 0, 'errors' => ['Only CSV files are allowed. In Excel use File > Save As > CSV (Comma delimited).']];
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $summary = ['approved' => 0, 'skipped' => 0, 'errors' => ['File is too large (maximum 2MB).']];
        } else {
            $summary = $savings->processTreasurerSavingsUpload($file['tmp_name']);
        }

        $_SESSION['savings_upload_summary'] = $summary;
        header('location: treasurer_view_approved_savings.php');
        exit();
    }

    $uploadSummary = isset($_SESSION['savings_upload_summary']) ? $_SESSION['savings_upload_summary'] : null;
    unset($_SESSION['savings_upload_summary']);

    $requests = $savings->getChairmanApprovedSavingsUpdates();

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
              <h4 class="text-primary">Savings Update Requests</h4>
              <p class="text-muted mb-0">
                Requests approved by the Chairman and awaiting your approved amount.
                Approving a request updates the member's monthly savings.
                Minimum monthly savings: ₦<?php echo number_format(SavingsG1::getMinMonthlySavings()); ?>
                (<a href="savings_settings.php">change</a>).
              </p>
            </div>
          </div>

          <?php if ($uploadSummary) { ?>
            <div class="row">
              <div class="col-md-12 grid-margin">
                <div class="alert <?php echo empty($uploadSummary['errors']) ? 'alert-success' : 'alert-warning'; ?>">
                  <strong>Upload result:</strong>
                  <?php echo (int) $uploadSummary['approved']; ?> approved,
                  <?php echo (int) $uploadSummary['skipped']; ?> left pending (blank approved amount),
                  <?php echo count($uploadSummary['errors']); ?> not processed.
                  <?php if (!empty($uploadSummary['errors'])) { ?>
                    <ul class="mb-0 mt-2">
                      <?php foreach ($uploadSummary['errors'] as $error) { ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                      <?php } ?>
                    </ul>
                  <?php } ?>
                </div>
              </div>
            </div>
          <?php } ?>

          <div class="row">
            <!-- Offline processing -->
            <div class="col-md-12 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Offline processing</h5>
                  <ol class="pl-3">
                    <li>Download the list. The <em>Approved Amount</em> column is pre-filled with the amount each member requested.</li>
                    <li>Change the <em>Approved Amount</em> where a different amount is approved. Clear it to leave a request pending. Do not change the <em>Request ID</em> or <em>Staff Number</em> columns.</li>
                    <li>Save as CSV and upload it below.</li>
                    <li class="text-muted">Where a member applied more than once, only their latest request is listed; approving it closes the earlier ones.</li>
                  </ol>
                  <div class="d-flex flex-wrap align-items-center">
                    <a href="download_savings_updates.php" class="btn btn-primary mr-3 mb-2 <?php echo empty($requests) ? 'disabled' : ''; ?>">
                      <i class="mdi mdi-download"></i> Download List (CSV)
                    </a>
                    <form method="POST" enctype="multipart/form-data" class="form-inline mb-2"
                          onsubmit="return confirm('Approve all requests in this file with the approved amounts entered?');">
                      <input type="file" name="approved_list" accept=".csv" class="form-control-file mr-2" required>
                      <button type="submit" class="btn btn-success">
                        <i class="mdi mdi-upload"></i> Upload Approved List
                      </button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Individual processing -->
            <div class="col-md-12 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-2">
                      Pending requests (<span id="pendingCount"><?php echo count($requests); ?></span>)
                    </h5>
                    <input class="form-control col-md-4" type="search" id="searchSavingsUpdate" placeholder="Search by Staff No, Name, Department...">
                  </div>
                  <div class="msg"></div>
                  <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                      <thead class="thead-dark">
                        <tr>
                          <th>S/N</th>
                          <th>Staff Number</th>
                          <th>Name</th>
                          <th>Department</th>
                          <th>Date Requested</th>
                          <th>Current Monthly Savings</th>
                          <th>Requested Amount</th>
                          <th>Approved Amount (₦)</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody id="searchSavingsUpdateBody">
                        <?php if (empty($requests)) { ?>
                          <tr><td colspan="9" class="text-center text-muted">No savings update requests awaiting approval.</td></tr>
                        <?php } ?>
                        <?php
                          $sn = 1;
                          foreach ($requests as $request) {
                              $name = preg_replace('/\s+/', ' ', trim($request['fname'] . ' ' . $request['oname'] . ' ' . $request['lname']));
                        ?>
                          <tr>
                            <td><?php echo $sn; ?></td>
                            <td><?php echo htmlspecialchars($request['sp_no']); ?></td>
                            <td><?php echo htmlspecialchars($name); ?></td>
                            <td><?php echo htmlspecialchars($request['dept']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($request['update_proposed_date'])); ?></td>
                            <td>₦<?php echo number_format((float) $request['proposed_monthly_savings'], 2); ?></td>
                            <td>
                              ₦<?php echo number_format((float) $request['savings_update_amount'], 2); ?>
                              <?php if ($request['earlier_requests'] > 0) { ?>
                                <br><small class="text-muted" title="Earlier pending requests from this member are closed when this one is approved">
                                  replaces <?php echo (int) $request['earlier_requests']; ?> earlier request(s)
                                </small>
                              <?php } ?>
                            </td>
                            <td style="min-width: 150px">
                              <input type="number" class="form-control form-control-sm approved-amount"
                                     min="<?php echo SavingsG1::getMinMonthlySavings(); ?>" step="1"
                                     value="<?php echo htmlspecialchars($request['savings_update_amount']); ?>"
                                     data-requested="<?php echo htmlspecialchars($request['savings_update_amount']); ?>">
                            </td>
                            <td>
                              <button class="btn btn-sm btn-success approve-savings-update"
                                      data-update-id="<?php echo (int) $request['id']; ?>"
                                      data-staff-no="<?php echo htmlspecialchars($request['sp_no']); ?>">
                                Approve
                              </button>
                            </td>
                          </tr>
                        <?php
                              $sn++;
                          }
                        ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
        <!-- content-wrapper ends -->

        <script>
          $(document).ready(function() {
              $('#searchSavingsUpdate').on('keyup', function() {
                  const value = $(this).val().toLowerCase();
                  $('#searchSavingsUpdateBody tr').each(function() {
                      $(this).toggle($(this).text().toLowerCase().includes(value));
                  });
              });

              $(document).on('click', '.approve-savings-update', function() {
                  const button = $(this);
                  const row = button.closest('tr');
                  const input = row.find('.approved-amount');
                  const amount = parseFloat(input.val());
                  const requested = parseFloat(input.data('requested'));
                  const minimum = parseFloat(input.attr('min'));

                  if (isNaN(amount) || amount < minimum) {
                      alert('Enter a valid approved amount of at least ₦' + minimum.toLocaleString() + '.');
                      input.focus();
                      return;
                  }

                  let question = 'Approve ₦' + amount.toLocaleString() + ' as the new monthly savings for ' + button.data('staff-no') + '?';
                  if (amount !== requested) {
                      question = 'The member requested ₦' + requested.toLocaleString() + '.\n' + question;
                  }
                  if (!confirm(question)) {
                      return;
                  }

                  button.prop('disabled', true).text('Saving...');
                  $.post('../ajax/treasurer_approve_savings_update.php', {
                      update_id: button.data('update-id'),
                      amount: amount
                  }, function(response) {
                      const alertClass = response.success ? 'alert-success' : 'alert-danger';
                      $('.msg').html('<div class="alert ' + alertClass + '">' + $('<div>').text(response.message).html() + '</div>');
                      if (response.success) {
                          row.fadeOut(300, function() {
                              $(this).remove();
                              $('#pendingCount').text($('#searchSavingsUpdateBody .approve-savings-update').length);
                          });
                      } else {
                          button.prop('disabled', false).text('Approve');
                      }
                  }, 'json').fail(function() {
                      $('.msg').html('<div class="alert alert-danger">Network error. Please try again.</div>');
                      button.prop('disabled', false).text('Approve');
                  });
              });
          });
        </script>

        <?php
          echo $view->subFooter('../');
        ?>
