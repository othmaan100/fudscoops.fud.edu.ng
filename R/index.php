<?php 
require_once('../config/classes/View.php');
require_once('../config/classes/User.php');
require_once '../config/classes/MemberG3.php';
require_once '../config/classes/MemberG1.php';
require_once '../config/classes/MemberG2.php';

$view = new View();
$user = new User();
$member = new MemberG3();
$member1 = new MemberG1();
$member2 = new MemberG2();

$psavings = $member1->getProposedMonthlySavings($_SESSION['username']);
$cumsavings = $member1->getCumulativeMonthlySavings($_SESSION['username']);
$cumwithdrawal = $member1->getCumulativeSavingsWithdrawals($_SESSION['username']);
$cumulativeshares = $member1->getCumulativeShares($_SESSION['username']);
$cumulativeLoan = $member1->getCumulativeLoan($_SESSION['username']);
$currentsavings = $member1->getCurrentSavingsAmount($_SESSION['username']);
$lastSavings = $member1->getLastSavingsWithdrawals($_SESSION['username']);

$employeeid = $user->getEmployeeId($_SESSION['username']);
$memberId = $user->getMemberId($employeeid);

// Fetch recent withdrawals (last 5)
$recentWithdrawals = $member1->getRecentMemberWithdrawals($memberId);

$user->is_authenticated('../auth/');
$user->isStaff('../auth/logout.php');

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
                <h3 class="text-primary">WELCOME, <?php echo $user->getFullname($_SESSION['username'])." (".$_SESSION['username'].")"?></h3>
              </div>                  
            </div>
            <div class="row">
              <div class="d-flex justify-content-between align-items-end flex-wrap ml-n5">                  
                <span></span>
                <a href="payslip.php" class="btn btn-success mt-2 mt-xl-0">
                  <i class="mdi mdi-printer icon-lg mr-3"></i>
                  Print IPPIS Payslip
                </a>
              </div>
              
              <br/>
              <div class="d-flex justify-content-between align-items-end flex-wrap">                  
                <span></span>
                <a href="profile.php" style="color:#fff" class="btn btn-warning mt-2 mt-xl-0">
                  <i class="mdi mdi-account icon-lg mr-0"></i>
                  My Profile
                </a>
              </div>
            </div>
            <div class="row">
              <div class="d-flex justify-content-between align-items-end flex-wrap mt-2 mb-2 ml-5">                  
                <span></span>
                <?php echo $member2->getTotalShareTransfer($user->getEmployeeId($_SESSION['username']))?>
              </div>
              
              <div class="d-flex justify-content-between align-items-end flex-wrap mt-2 mb-2 ml-5">                  
                <span></span>
                <?php echo $member->getTotalGuarantee($user->getEmployeeId($_SESSION['username']))?>
              </div>
              
              <div class="d-flex justify-content-between align-items-end flex-wrap mt-2 mb-2 ml-5">                  
                <span></span>
                <?php echo $member2->notifyMemberShareApproval($memberId)?>
              </div>
              
              <div class="d-flex justify-content-between align-items-end flex-wrap mt-2 mb-2 ml-5">                  
                <span></span>
                <?php echo $member2->notifyMemberShareTransferApproval($memberId)?>
              </div>
            </div>
          </div>
          
          <!-- Stats Cards -->
          <div class="P-3 shadow mb-4">
            <div class="row P-3">  
              <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                  <div class="card-body">
                    <div class="row no-gutters align-items-center">
                      <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                          Current Monthly Savings
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                          ₦<?php echo number_format($psavings);?>
                        </div>
                      </div>
                      <div class="col-auto">
                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                  <div class="card-body">
                    <div class="row no-gutters align-items-center">
                      <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                          Current Cumulative Savings
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                          ₦<?php echo number_format($currentsavings);?>
                        </div>
                      </div>
                      <div class="col-auto">
                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                  <div class="card-body">
                    <div class="row no-gutters align-items-center">
                      <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                          Last Saving Withdrawal Amount
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                          ₦<?php echo number_format($lastSavings);?>
                        </div>
                      </div>
                      <div class="col-auto">
                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                  <div class="card-body">
                    <div class="row no-gutters align-items-center">
                      <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                          Your Current Material Supply Loan Value (₦)
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                          ₦<?php echo number_format($cumulativeLoan);?>
                        </div>
                      </div>
                      <div class="col-auto">
                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                  <div class="card-body">
                    <div class="row no-gutters align-items-center">
                      <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                          Total Unit of Shares
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                          <?php echo number_format($cumulativeshares);?> Units
                        </div>
                      </div>
                      <div class="col-auto">
                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                  <div class="card-body">
                    <div class="row no-gutters align-items-center">
                      <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                          Total Amount of Shares (₦)
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                          ₦<?php echo number_format($cumulativeshares);?>
                        </div>
                      </div>
                      <div class="col-auto">
                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- RECENT WITHDRAWALS SECTION -->
          <?php if (!empty($recentWithdrawals)): ?>
          <div class="P-3 shadow mb-4">
            <div class="card-header py-3">
              <h6 class="m-0 font-weight-bold text-primary">Your Recent Withdrawals</h6>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-bordered table-sm">
                  <thead class="thead-light">
                    <tr>
                      <th>Date </th>
                      <th>Amount (₦)</th>
                      <th>Bank</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($recentWithdrawals as $w): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($w['approved_date']); ?></td>
                      <td>₦<?php echo number_format($w['approved_withrawal_amount'], 2); ?></td>
                      <td><?php echo htmlspecialchars($w['bank_id']); ?></td>
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
                  </tbody>
                </table>
                <div class="text-right mt-2">
                  <a href="view_all_withdrawals.php" class="btn btn-sm btn-outline-primary">
                    View All Withdrawals
                  </a>
                </div>
              </div>
            </div>
          </div>
          <?php endif; ?>

        </div>
      </div>   
    </div>
    <!-- content-wrapper ends -->
    <?php echo $view->subFooter('../'); ?>
  </div>
</div>