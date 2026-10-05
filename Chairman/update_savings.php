<?php 
require_once('../config/classes/View.php');
require_once('../config/classes/User.php');
require_once('../config/classes/MemberG1.php');

$view = new View();
$user = new User();
$members = new MemberG1();

$proposed_members = $members->getRequesUpdateSavings();

$user->is_authenticated('auth/');
$user->isChairman('../auth/logout.php');

echo $view->subHeader('../');
?>

<!-- normalize data-table CSS -->
<link rel="stylesheet" href="../../css/data-table/bootstrap-table.css">
<link rel="stylesheet" href="../../css/data-table/bootstrap-editable.css">

<!-- partial:partials/_navbar.html -->
<?php echo $view->subPartialNav('../')?>
<!-- partial -->
<div class="container-fluid page-body-wrapper">
  <!-- partial:partials/_sidebar.html -->
  <?php echo $view->subSideNav()?>
  
  <!-- partial -->
  <div class="main-panel">
    <div class="content-wrapper">
      <div class="row">
        <div class="col-md-12 grid-margin">
          <div class="d-flex justify-content-between flex-wrap">
            <div class="d-flex align-items-end card flex-wrap">
              <div class="mr-md-10 card-body mr-xl-10">
                <h2>COOPERATIVE CHAIRMAN ACCOUNT</h2>
                
                <!-- Bulk Action Buttons -->
                <div class="card-header py-3">
                  <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <h6 class="m-0 font-weight-bold text-primary text-nowrap">
                      Manage Savings Update Requests
                    </h6>
                    
                    <div class="mt-2 mt-md-0">
                      <button id="bulk_approve" class="btn btn-sm btn-success mr-2" disabled>
                        <i class="fa fa-check"></i> Approve Selected
                      </button>
                      <button id="bulk_reject" class="btn btn-sm btn-danger" disabled>
                        <i class="fa fa-times"></i> Reject Selected
                      </button>
                    </div>
                  </div>
                  
                  <div class="mt-2">
                    <input class="form-control" type="search" id="searchCompleteWithdrawal" placeholder="Search by Staff No, Name...">
                  </div>
                </div>

                <div class="card-body">
                  <div class="msg"></div>
                  <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="savingsTable">
                      <thead class="thead-dark">
                        <tr>
                          <th><input type="checkbox" id="master_checkbox"></th>
                          <th>S/N</th>
                          <th>Staff Number</th>
                          <th>Name</th>
                          <th>Department</th>
                          <th>Monthly Savings</th>
                          <th>Savings Update Amount</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody id="searchCompleteWithdrawalBody">
                        <?php
                        $sn = 1;
                        foreach ($proposed_members as $member) {
                            ?>
                            <tr>
                              <td>
                                <input type="checkbox" class="member-checkbox" 
                                       value="<?php echo htmlspecialchars($member['sp_no']); ?>">
                              </td>
                              <td><?php echo $sn; ?></td>
                              <td><?php echo htmlspecialchars($member['sp_no']); ?></td>
                              <td><?php echo htmlspecialchars($member['fname'] . ' ' . $member['lname']); ?></td>
                              <td><?php echo htmlspecialchars($member['dept']); ?></td>
                              <td>₦<?php echo number_format($member['proposed_monthly_savings'], 2); ?></td>
                              <td>₦<?php echo number_format($member['savings_update_amount'], 2); ?></td>
                              <td>
                                <button class="btn btn-sm btn-success approve-savings" 
                                        data-staff-no="<?php echo htmlspecialchars($member['sp_no']); ?>">
                                  Approve
                                </button>
                                <button class="btn btn-sm btn-danger reject-savings" 
                                        data-staff-no="<?php echo htmlspecialchars($member['sp_no']); ?>">
                                  Reject
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
      </div>
    </div>
    
    <?php echo $view->subFooter('../'); ?>
  </div>
</div>

<!-- jQuery (ensure it's loaded) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    // Master checkbox toggle
    $('#master_checkbox').on('change', function() {
        $('.member-checkbox').prop('checked', $(this).prop('checked'));
        updateBulkButtons();
    });

    // Individual checkbox change
    $(document).on('change', '.member-checkbox', function() {
        updateBulkButtons();
    });

    // Update bulk action buttons state
    function updateBulkButtons() {
        const anyChecked = $('.member-checkbox:checked').length > 0;
        $('#bulk_approve, #bulk_reject').prop('disabled', !anyChecked);
    }

    // Bulk Approve
    $('#bulk_approve').on('click', function() {
        const selectedStaffNos = $('.member-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedStaffNos.length === 0) {
            alert('Please select at least one request to approve.');
            return;
        }

        if (!confirm(`Approve ${selectedStaffNos.length} selected savings update request(s)?`)) {
            return;
        }

        $.ajax({
            url: '../ajax/bulk_approve_savings.php',
            type: 'POST',
            data: { staff_nos: selectedStaffNos, action: 'approve' },
            dataType: 'json',
            success: function(response) {
                showMessage(response);
            },
            error: function(xhr, status, error) {
                $('.msg').html('<div class="alert alert-danger">Network error: ' + error + '</div>');
            }
        });
    });

    // Bulk Reject
    $('#bulk_reject').on('click', function() {
        const selectedStaffNos = $('.member-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedStaffNos.length === 0) {
            alert('Please select at least one request to reject.');
            return;
        }

        if (!confirm(`Reject ${selectedStaffNos.length} selected savings update request(s)?`)) {
            return;
        }

        $.ajax({
            url: '../ajax/bulk_approve_savings.php',
            type: 'POST',
            data: { staff_nos: selectedStaffNos, action: 'reject' },
            dataType: 'json',
            success: function(response) {
                showMessage(response);
            },
            error: function(xhr, status, error) {
                $('.msg').html('<div class="alert alert-danger">Network error: ' + error + '</div>');
            }
        });
    });

    // Single Approve
    $(document).on('click', '.approve-savings', function() {
        const staffNo = $(this).data('staff-no');
        if (!confirm('Approve savings update for ' + staffNo + '?')) return;

        $.post('../ajax/approved_update_savings.php', { staff_no: staffNo }, showMessage, 'json')
         .fail(handleAjaxError);
    });

    // Single Reject
    $(document).on('click', '.reject-savings', function() {
        const staffNo = $(this).data('staff-no');
        if (!confirm('Reject savings update for ' + staffNo + '?')) return;

        $.post('../ajax/reject_update_savings.php', { staff_no: staffNo }, showMessage, 'json')
         .fail(handleAjaxError);
    });

    // Search functionality
    $('#searchCompleteWithdrawal').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#searchCompleteWithdrawalBody tr').each(function() {
            const rowText = $(this).text().toLowerCase();
            $(this).toggle(rowText.includes(value));
        });
    });

    // Helper functions
    function showMessage(response) {
        if (response.success) {
            $('.msg').html('<div class="alert alert-success">' + response.message + '</div>');
            setTimeout(() => location.reload(), 1500);
        } else {
            $('.msg').html('<div class="alert alert-danger">Error: ' + response.message + '</div>');
        }
    }

    function handleAjaxError(xhr, status, error) {
        $('.msg').html('<div class="alert alert-danger">AJAX Error: ' + error + '</div>');
    }
});
</script>