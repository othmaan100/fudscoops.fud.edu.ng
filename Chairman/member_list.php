<?php 
require_once('../config/classes/View.php');
require_once('../config/classes/User.php');
require_once('../config/classes/MemberG1.php');

$view = new View();
$user = new User();
$members = new MemberG1();

// Fetch data
$registeredMembers = $members->getChairmanApprovedMembers();      // chairman_approval = 1
$applicants = $members->getMembershipApplicants();              // chairman_approval = 0

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
                <h2>COOPERATIVE CHAIRMAN ACCOUNT</h2>
                
                <!-- Tabs -->
                <ul class="nav nav-tabs mt-3" id="membershipTabs" role="tablist">
                  <li class="nav-item">
                    <a class="nav-link active" id="registered-tab" data-toggle="tab" href="#registeredMembers" role="tab">
                      Registered Members
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="applicants-tab" data-toggle="tab" href="#applicants" role="tab">
                      Membership Applicants
                    </a>
                  </li>
                </ul>

                <div class="tab-content mt-4" id="membershipTabContent">
                  
                  <!-- REGISTERED MEMBERS TAB -->
                  <div class="tab-pane fade show active" id="registeredMembers" role="tabpanel">
                    <div class="card-header py-3">
                      <h6 class="m-0 font-weight-bold text-primary">Approved & Registered Members</h6>
                    </div>
                    <div class="card-body">
                      <div class="table-responsive">
                        <table id="registeredTable"
                               data-toggle="table"
                               data-pagination="true"
                               data-search="true"
                               data-show-columns="true"
                               data-show-export="true"
                               data-show-print="true"
                               data-page-size="10"
                               data-export-options='{"fileName": "registered_members"}'>
                          <thead class="thead-dark">
                            <tr>
                              <th data-field="sn">#</th>
                              <th data-field="sp_no">Staff Number</th>
                              <th data-field="name">Full Name</th>
                              <th data-field="cadre">Cadre</th>
                              <th data-field="dept">Department</th>
                              <th data-field="savings">Proposed Monthly Savings</th>
                              
                            </tr>
                          </thead>
                          <tbody>
                            <?php if (!empty($registeredMembers)): ?>
                              <?php $sn = 1; ?>
                              <?php foreach ($registeredMembers as $m): ?>
                                <tr>
                                  <td><?php echo $sn++; ?></td> 
                                  <td><?php echo htmlspecialchars($m['sp_no']); ?></td>
                                  <td><?php echo htmlspecialchars(trim($m['fname'] . ' ' . $m['lname'])); ?></td>
                                  <td><?php echo htmlspecialchars($m['cadre']); ?></td>
                                  <td><?php echo htmlspecialchars($m['dept']); ?></td>
                                  <td>₦<?php echo number_format($m['proposed_monthly_savings'], 2); ?></td>
                                  
                                </tr>
                              <?php endforeach; ?>
                            <?php else: ?>
                              <tr>
                                <td colspan="7" class="text-center text-muted">No registered members found.</td>
                              </tr>
                            <?php endif; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>

                  <!-- APPLICANTS TAB -->
                  <div class="tab-pane fade" id="applicants" role="tabpanel">
                    <div class="card-header py-3">
                      <h6 class="m-0 font-weight-bold text-primary">Pending Membership Applications</h6>
                    </div>
                    <div class="card-body">
                      <div class="table-responsive">
                        <table id="applicantsTable"
                               data-toggle="table"
                               data-pagination="true"
                               data-search="true"
                               data-show-columns="true"
                               data-show-export="true"
                               data-show-print="true"
                               data-page-size="10"
                               data-export-options='{"fileName": "membership_applicants"}'>
                          <thead class="thead-dark">
                            <tr>
                              <th data-field="sn">#</th>
                              <th data-field="sp_no">Staff Number</th>
                              <th data-field="name">Full Name</th>
                              <th data-field="cadre">Cadre</th>
                              <th data-field="dept">Department</th>
                              <th data-field="savings">Proposed Monthly Savings</th>
                              <th data-field="action">Action</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php if (!empty($applicants)): ?>
                              <?php $sn = 1; ?>
                              <?php foreach ($applicants as $m): ?>
                                <tr>
                                  <td><?php echo $sn++; ?></td>
                                  <td><?php echo htmlspecialchars($m['sp_no']); ?></td>
                                  <td><?php echo htmlspecialchars(trim($m['fname'] . ' ' . $m['lname'])); ?></td>
                                  <td><?php echo htmlspecialchars($m['cadre']); ?></td>
                                  <td><?php echo htmlspecialchars($m['dept']); ?></td>
                                  <td>₦<?php echo number_format($m['proposed_monthly_savings'], 2); ?></td>
                                  <td>
                                    <a href="view_proposed_member_details.php?member_id=<?php echo urlencode($m['employee_id']); ?>" 
                                       class="btn btn-sm btn-primary">
                                      Review Application
                                    </a>
                                  </td>
                                </tr>
                              <?php endforeach; ?>
                            <?php else: ?>
                              <tr>
                                <td colspan="7" class="text-center text-muted">No pending applications.</td>
                              </tr>
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