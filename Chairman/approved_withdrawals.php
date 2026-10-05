<?php 
require_once('../config/classes/View.php');
require_once('../config/classes/User.php');
require_once('../config/classes/MemberG1.php');

$view = new View();
$user = new User();
$members = new MemberG1();

// Fetch data
$recentWithdrawals = $members->getChairmanApprovedWithdrawalsLast7Days(date('Y-m-d')); // Today only
$allWithdrawals = $members->getChairmanApprovedWithdrawals(); // All

// Auth
$user->is_authenticated('auth/');
$user->isChairman('../auth/logout.php');

echo $view->subHeader('../');
?>

<!-- Bootstrap Table CSS -->
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
                                  <!-- RECENT APPROVALS TAB -->
                <div class="tab-pane fade show active" id="recentApprovals" role="tabpanel">
                  <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Withdrawals Approved in Last 7 Days</h6>
                    <!-- Search will be handled by Bootstrap Table -->
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
                              </tr>
                            <?php endforeach; ?>
                          <?php else: ?>
                            <tr>
                              <td colspan="9" class="text-center text-muted">No withdrawals approved in the last 7 days.</td>
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
                            </tr>
                          </thead>
                          <tbody id="allTableBody">
                            <?php if (empty($allWithdrawals)): ?>
                              <tr>
                                <td colspan="9" class="text-center text-muted">No approved withdrawals found.</td>
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
    // Search for Recent tab
    $('#searchRecent').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#recentTableBody tr').each(function() {
            const rowText = $(this).text().toLowerCase();
            $(this).toggle(rowText.includes(value));
        });
    });

    // Search for All tab (if not using Bootstrap Table search)
    $('#searchAll').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#allTableBody tr').each(function() {
            const rowText = $(this).text().toLowerCase();
            $(this).toggle(rowText.includes(value));
        });
    });

    // Optional: Reinitialize Bootstrap Table when switching to "All" tab
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        if ($(e.target).attr('id') === 'all-tab') {
            $('#allApprovedTable').bootstrapTable('resetView');
        }
    });
});
</script>