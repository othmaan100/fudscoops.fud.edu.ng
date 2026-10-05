<?php 
require_once('../config/classes/View.php');
require_once('../config/classes/User.php');
require_once('../config/classes/MemberG1.php');

$view = new View();
$user = new User();
$member1 = new MemberG1();

// Auth check
$user->is_authenticated('../auth/');
$user->isStaff('../auth/logout.php');

// Get member ID
$employeeid = $user->getEmployeeId($_SESSION['username']);
$memberId = $user->getMemberId($employeeid);

if (!$memberId) {
    $_SESSION['error'] = 'Member record not found.';
    header('Location: dashboard.php');
    exit();
}

// Fetch all withdrawals for this member
$withdrawals = $member1->getAllMemberWithdrawals($memberId);

echo $view->subHeader('../');
?>
<!-- partial:partials/_navbar.html -->
<?php echo $view->subPartialNav('../')?>
<!-- partial -->
<div class="container-fluid page-body-wrapper">
  <?php echo $view->staffSideNav()?>
  
  <div class="main-panel">
    <div class="content-wrapper">
      <div class="row">
        <div class="col-md-12 grid-margin">
          <div class="d-flex justify-content-between flex-wrap">
            <div class="d-flex align-items-end card flex-wrap">
              <div class="mr-md-3 card-body mr-xl-5">
                <h3 class="text-primary">My Withdrawal History</h3>
              </div>                  
            </div>
          </div>

          <div class="card shadow mb-4">
            <div class="card-header py-3">
              <h6 class="m-0 font-weight-bold text-primary">All Withdrawal Requests</h6>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table id="withdrawalsTable"
                       data-toggle="table"
                       data-pagination="true"
                       data-search="true"
                       data-show-columns="true"
                       data-show-export="true"
                       data-show-print="true"
                       data-page-size="10"
                       data-export-options='{"fileName": "my_withdrawals"}'>
                  <thead class="thead-dark">
                    <tr>
                      <th data-field="date" data-sortable="true">Date</th>
                      <th data-field="amount" data-sortable="true">Amount (₦)</th>
                      <th data-field="bank" data-sortable="true">Bank</th>
                      <th data-field="account_number" data-sortable="true">Account Number</th>
                      <th data-field="account_name" data-sortable="true">Account Name</th>
                      <th data-field="status" data-sortable="false">Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (!empty($withdrawals)): ?>
                      <?php foreach ($withdrawals as $w): ?>
                        <tr>
                          <td><?php echo htmlspecialchars($w['approved_date']); ?></td>
                          <td>₦<?php echo number_format($w['approved_withrawal_amount'], 2); ?></td>
                          <td><?php echo htmlspecialchars($w['bank_id']); ?></td>
                          <td><?php echo htmlspecialchars($w['acount_number']); ?></td>
                          <td><?php echo htmlspecialchars($w['account_name']); ?></td>
                          <td>
                            <?php if ($w['chairman_approval'] == 1): ?>
                              <span class="badge badge-success">Approved</span>
                            <?php elseif ($w['chairman_approval'] == 0): ?>
                              <span class="badge badge-warning">Pending</span>
                            <?php else: ?>
                              <span class="badge badge-danger">Rejected</span>
                            <?php endif; ?>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr>
                        <td colspan="6" class="text-center text-muted">No withdrawal requests found.</td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
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
<link rel="stylesheet" href="../../css/data-table/bootstrap-table.css">
<script src="../../js/data-table/bootstrap-table.js"></script>
<script src="../../js/data-table/tableExport.js"></script>
<script src="../../js/data-table/bootstrap-table-export.js"></script>
<script src="../../js/data-table/data-table-active.js"></script>