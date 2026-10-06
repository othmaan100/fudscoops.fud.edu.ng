<?php 
require_once('../config/classes/View.php');
require_once('../config/classes/User.php');
require_once('../config/classes/MemberG1.php');

$view = new View();
$user = new User();
$members = new MemberG1();

// Fetch data
$recentWithdrawals = $members->getTreasurerUploadedWithdrawalsLast7Days(date('Y-m-d')); // Last 7 Days
$allWithdrawals = $members->getTreasurerUploadedWithdrawals(); // All

// Auth
$user->is_authenticated('auth/');
$user->isBur('../auth/logout.php');

echo $view->subHeader('../');
?>

<!-- Bootstrap Table CSS -->
<link rel="stylesheet" href="../../css/data-table/bootstrap-table.css">
<link rel="stylesheet" href="../../css/data-table/bootstrap-editable.css">

<!-- partial:partials/_navbar.html -->
<?php echo $view->subPartialNav('../')?>
<!-- partial -->
<div class="container-fluid page-body-wrapper">
  <?php echo $view->treasurerSideNav()?>
  
  <div class="main-panel">
    <div class="content-wrapper">
      <div class="row">
        <div class="col-md-12 grid-margin">
          <div class="d-flex1 justify-content-between flex-wrap1">
            <div class="d-flex1 align-items-end1 card flex-wrap1">
              <div class="mr-md-10 card-body mr-xl-10">
                <h2>TREASURER DASHBOARD</h2>
                
                <!-- Tabs -->
                <ul class="nav nav-tabs mt-3" id="approvalTabs" role="tablist">
                  <li class="nav-item">
                    <a class="nav-link active" id="recent-tab" data-toggle="tab" href="#recentApprovals" role="tab">
                      Recent Approvals (Last 7 Days)
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="all-tab" data-toggle="tab" href="#allApprovals" role="tab">
                      All Approvals
                    </a>
                  </li>
                </ul>

                <div class="tab-content mt-4" id="approvalTabContent">
                  
                  <!-- RECENT APPROVALS TAB -->
                  <div class="tab-pane fade show active" id="recentApprovals" role="tabpanel">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                      <h6 class="m-0 font-weight-bold text-primary">Withdrawals Approved in Last 7 Days</h6>
                    </div>
                    <div class="card-body">
                      <div class="table-responsive">
                        <table id="recentApprovedTable"
                               data-toggle="table"
                               data-pagination="true"
                               data-search="true"
                               data-show-columns="true"
                               data-show-export="true"
                               data-show-print="true"
                               data-page-size="10"
                               data-export-options='{"fileName": "recent_withdrawals_7days"}'>
                          <thead class="thead-dark">
                            <tr>
                              <th data-field="sn" data-sortable="true">S/N</th>
                              <th data-field="sp_no" data-sortable="true">Staff Number</th>
                              <th data-field="full_name" data-sortable="true">Full Name</th>
                              <th data-field="amount" data-sortable="true">Amount (₦)</th>
                              <th data-field="bank" data-sortable="true">Bank</th>
                              <th data-field="account_number" data-sortable="true">Account Number</th>
                              <th data-field="account_name" data-sortable="true">Account Name</th>
                              <th data-field="approved_date" data-sortable="true">Approved Date</th>
                              <th data-field="status" data-sortable="false">Status</th>
                              <th data-field="action" data-sortable="false">Action</th> <!-- ✅ NEW COLUMN -->
                            </tr>
                          </thead>
                          <tbody>
                            <?php if (!empty($recentWithdrawals)): ?>
                              <?php $sn = 1; ?>
                              <?php foreach ($recentWithdrawals as $w): ?>
                                <tr>
                                  <td><?php echo $sn++; ?></td>
                                  <td><?php echo htmlspecialchars($w['sp_no']); ?></td>
                                  <td><?php echo htmlspecialchars(trim($w['fname'] . ' ' . $w['lname'] . ' ' . $w['oname'])); ?></td>
                                  <td>₦<?php echo number_format($w['approved_withrawal_amount'], 2); ?></td>
                                  <td><?php echo htmlspecialchars($w['bank_id']); ?></td>
                                  <td><?php echo htmlspecialchars($w['acount_number']); ?></td>
                                  <td><?php echo htmlspecialchars($w['account_name']); ?></td>
                                  <td><?php echo htmlspecialchars($w['approved_date']); ?></td>
                                  <td><span class="badge badge-success">Approved</span></td>
                                  <td>
                                    <!-- ✅ DELETE BUTTON -->
                                    <button class="btn btn-sm btn-danger btn-delete-withdrawal" 
                                            data-id="<?php echo $w['withrawals_id']; ?>" 
                                            title="Delete Record">
                                      <i class="fa fa-trash"></i> Delete
                                    </button>
                                  </td>
                                </tr>
                              <?php endforeach; ?>
                            <?php else: ?>
                              <tr>
                                <!-- ✅ Updated colspan from 9 to 10 -->
                                <td colspan="10" class="text-center text-muted">No withdrawals approved in the last 7 days.</td>
                              </tr>
                            <?php endif; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>

                  <!-- ALL APPROVALS TAB -->
                  <div class="tab-pane fade" id="allApprovals" role="tabpanel">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                      <h6 class="m-0 font-weight-bold text-primary">All Approved Withdrawals</h6>
                      <input class="form-control ml-2" type="text" id="searchAll" placeholder="Search...">
                    </div>
                    <div class="card-body">
                      <div class="table-responsive">
                        <table id="allApprovedTable"
                               data-toggle="table"
                               data-pagination="true"
                               data-search="false"
                               data-show-columns="true"
                               data-show-export="true"
                               data-show-print="true"
                               data-page-size="10">
                          <thead class="thead-dark">
                            <tr>
                              <th>S/N</th>
                              <th>Staff Number</th>
                              <th>Full Name</th>
                              <th>Amount (₦)</th>
                              <th>Bank</th>
                              <th>Account Number</th>
                              <th>Account Name</th>
                              <th>Approved Date</th>
                              <th>Status</th>
                              <th>Action</th> <!-- ✅ NEW COLUMN -->
                            </tr>
                          </thead>
                          <tbody id="allTableBody">
                            <?php if (empty($allWithdrawals)): ?>
                              <tr>
                                <!-- ✅ Updated colspan from 9 to 10 -->
                                <td colspan="10" class="text-center text-muted">No approved withdrawals found.</td>
                              </tr>
                            <?php else: ?>
                              <?php $sn = 1; ?>
                              <?php foreach ($allWithdrawals as $w): ?>
                                <tr>
                                  <td><?php echo $sn++; ?></td>
                                  <td><?php echo htmlspecialchars($w['sp_no']); ?></td>
                                  <td><?php echo htmlspecialchars(trim($w['fname'] . ' ' . $w['lname'] . ' ' . $w['oname'])); ?></td>
                                  <td>₦<?php echo number_format($w['approved_withrawal_amount'], 2); ?></td>
                                  <td><?php echo htmlspecialchars($w['bank_id']); ?></td>
                                  <td><?php echo htmlspecialchars($w['acount_number']); ?></td>
                                  <td><?php echo htmlspecialchars($w['account_name']); ?></td>
                                  <td><?php echo htmlspecialchars($w['approved_date']); ?></td>
                                  <td><span class="badge badge-success">Approved</span></td>
                                  <td>
                                    <!-- ✅ DELETE BUTTON -->
                                    <button class="btn btn-sm btn-danger btn-delete-withdrawal" 
                                            data-id="<?php echo $w['withrawals_id']; ?>" 
                                            title="Delete Record">
                                      <i class="fa fa-trash"></i> Delete
                                    </button>
                                  </td>
                                </tr>
                              <?php endforeach; ?>
                            <?php endif; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>

                </div> <!-- tab-content -->
              </div>                  
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <?php echo $view->subFooter('../'); ?>
  </div>
</div>

<!-- Bootstrap Table JS -->
<script src="../../js/data-table/bootstrap-table.js"></script>
<script src="../../js/data-table/tableExport.js"></script>
<script src="../../js/data-table/bootstrap-table-export.js"></script>
<script src="../../js/data-table/data-table-active.js"></script>

<script>
$(document).ready(function() {
    // Search for All tab
    $('#searchAll').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#allTableBody tr').each(function() {
            const rowText = $(this).text().toLowerCase();
            $(this).toggle(rowText.includes(value));
        });
    });

    // Reinitialize Bootstrap Table when switching to "All" tab
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        if ($(e.target).attr('id') === 'all-tab') {
            $('#allApprovedTable').bootstrapTable('resetView');
        }
    });

    // ✅ DELETE WITHDRAWAL AJAX HANDLER
    $(document).on('click', '.btn-delete-withdrawal', function(e) {
        e.preventDefault();
        
        const withdrawalId = $(this).data('id');
        const row = $(this).closest('tr');
        const btn = $(this);
        
        if (confirm('Are you sure you want to permanently delete this withdrawal record? This action cannot be undone.')) {
            // Disable button to prevent double clicks
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Deleting...');
            
            $.ajax({
                url: '../ajax/delete_withdrawal.php', // 👈 Create this file (see step 2 below)
                type: 'POST',
                data: { id: withdrawalId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Fade out and remove the row from the table
                        row.fadeOut(300, function() {
                            $(this).remove();
                            
                            // Optional: Refresh bootstrap table to fix pagination/search
                            $('#recentApprovedTable').bootstrapTable('refresh');
                            $('#allApprovedTable').bootstrapTable('refresh');
                        });
                        alert('Withdrawal record deleted successfully.');
                    } else {
                        alert('Error: ' + (response.message || 'Failed to delete record.'));
                        btn.prop('disabled', false).html('<i class="fa fa-trash"></i> Delete');
                    }
                },
                error: function() {
                    alert('An error occurred while communicating with the server.');
                    btn.prop('disabled', false).html('<i class="fa fa-trash"></i> Delete');
                }
            });
        }
    });
});
</script>