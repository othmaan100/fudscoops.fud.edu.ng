<?php 
require_once('../config/classes/View.php');
require_once('../config/classes/User.php');
require_once('../config/classes/MemberG1.php');

$view = new View();
$user = new User();
$members = new MemberG1();

$ListWithdrawalEndorsments = $members->getchairmanWithdrawalEndorsment();

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
  <?php echo $view->subSideNav()?>
  
  <div class="main-panel">
    <div class="content-wrapper">
      <div class="row">
        <div class="col-md-12 grid-margin">
          <div class="d-flex1 justify-content-between flex-wrap1">
            <div class="d-flex1 align-items-end1 card flex-wrap1">
              <div class="mr-md-10 card-body mr-xl-10">
                <h2>COOPERATIVE CHAIRMAN ACCOUNT</h2>
                
                <div class="card-header py-3">
                  <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <h6 class="m-0 font-weight-bold text-primary text-nowrap">Manage Members Withdrawal Endorsement</h6>
                    
                    <!-- Bulk Action Buttons -->
                    <div class="mt-2 mt-md-0">
                      <button id="approve_selected" class="btn btn-sm btn-success mr-2" disabled>
                        <i class="fa fa-check"></i> Approve Selected
                      </button>
                      <button id="reject_selected" class="btn btn-sm btn-danger" disabled>
                        <i class="fa fa-times"></i> Reject Selected
                      </button>
                    </div>
                  </div>
                  <div class="mt-2">
                    <input class="form-control" type="search" id="searchEndorsment" placeholder="Search by Staff No, Name, etc...">
                  </div>
                </div>

                <div class="card-body">
                  <div class="msg"></div>
                  
                  <div class="table-responsive">
                    <table id="withdrawalTable" class="table table-bordered table-hover">
                      <thead class="thead-dark">
                        <tr>
                          <th><input type="checkbox" id="master_checkbox"></th>
                          <th>S/N</th>
                          <th>Staff Number</th>
                          <th>Full Name</th>
                          <th>Total Savings</th>
                          <th>Amount Requested</th>
                          <th>Treasurer Recommends</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody id="endorsementBody">
                        <?php
                        $sn = 1;
                        foreach ($ListWithdrawalEndorsments as $endorsements) {
                            $wid = $endorsements['withrawals_id'];
                            ?>
                            <tr>
                              <td>
                                <input type="checkbox" class="withdrawal_checkbox" 
                                       value="<?php echo $wid; ?>" 
                                       data-wid="<?php echo $wid; ?>">
                              </td>
                              <td><?php echo $sn; ?></td>
                              <td><?php echo htmlspecialchars($endorsements['sp_no']); ?></td>
                              <td><?php echo htmlspecialchars($endorsements['fname'].' '.$endorsements['lname'].' '.$endorsements['oname']); ?></td>
                              <td>₦<?php echo number_format($endorsements['total_savings'], 2); ?></td>
                              <td>₦<?php echo number_format($endorsements['proposed_withrawal_amount'], 2); ?></td>
                              <td>₦<?php echo number_format($endorsements['approved_withrawal_amount'], 2); ?></td>
                              <td class="d-flex">
                                <button class="btn btn-sm btn-success chairman_approve_withdrawal me-1" 
                                        data-withrawal="<?php echo $wid; ?>" 
                                        data-approve="approveWithdrawal"
                                        onclick="return confirm('Approve this single request?');">
                                  Approve
                                </button>
                                <form class="d-inline" method="POST" action="reject_withdrawal.php" onsubmit="return confirm('Reject this request?');">
                                  <input type="hidden" name="withdrawal_id" value="<?php echo $wid; ?>">
                                  <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                </form>
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
  </div>
</div>

<?php echo $view->subFooter('../'); ?>

<script>
$(document).ready(function() {
    // Master checkbox toggle
    $('#master_checkbox').on('change', function() {
        $('.withdrawal_checkbox').prop('checked', $(this).prop('checked'));
        updateBulkButtons();
    });

    // Individual checkbox change
    $(document).on('change', '.withdrawal_checkbox', function() {
        updateBulkButtons();
    });

    // Update bulk action buttons state
    function updateBulkButtons() {
        const anyChecked = $('.withdrawal_checkbox:checked').length > 0;
        $('#approve_selected, #reject_selected').prop('disabled', !anyChecked);
    }

    // Bulk Approve
    $('#approve_selected').on('click', function() {
        const selectedIds = $('.withdrawal_checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) {
            alert('Please select at least one request to approve.');
            return;
        }

        if (!confirm(`Approve ${selectedIds.length} selected withdrawal request(s)?`)) {
            return;
        }

        $.ajax({
            url: '../ajax/bulk_approve_withdrawal.php',
            type: 'POST',
            data: { withdrawal_ids: selectedIds, action: 'approve' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('.msg').html('<div class="alert alert-success">' + response.message + '</div>');
                    // Optionally reload or remove approved rows
                    location.reload();
                } else {
                    $('.msg').html('<div class="alert alert-danger">Error: ' + response.message + '</div>');
                }
            },
            error: function() {
                $('.msg').html('<div class="alert alert-danger">Network error. Please try again.</div>');
            }
        });
    });

    // Bulk Reject
    $('#reject_selected').on('click', function() {
        const selectedIds = $('.withdrawal_checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) {
            alert('Please select at least one request to reject.');
            return;
        }

        if (!confirm(`Reject ${selectedIds.length} selected withdrawal request(s)?`)) {
            return;
        }

        $.ajax({
            url: '../ajax/bulk_approve_withdrawal.php',
            type: 'POST',
            data: { withdrawal_ids: selectedIds, action: 'reject' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('.msg').html('<div class="alert alert-success">' + response.message + '</div>');
                    location.reload();
                } else {
                    $('.msg').html('<div class="alert alert-danger">Error: ' + response.message + '</div>');
                }
            },
            error: function() {
                $('.msg').html('<div class="alert alert-danger">Network error. Please try again.</div>');
            }
        });
    });

    // Search functionality
    $('#searchEndorsment').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#endorsementBody tr').each(function() {
            const rowText = $(this).text().toLowerCase();
            $(this).toggle(rowText.includes(value));
        });
    });
});
</script>