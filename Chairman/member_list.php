<?php 
require_once('../config/classes/View.php');
require_once('../config/classes/User.php');
require_once('../config/classes/MemberG1.php');

$view = new View();
$user = new User();
$members = new MemberG1();

// Fetch data
$registeredMembers = $members->getChairmanApprovedMembers();      // chairman_approval = 1
$applicants = $members->getMembershipApplicants();              // treasurer_approval = 1, chairman_approval = 0

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
                      <h6 class="m-0 font-weight-bold text-primary">Membership Applications Awaiting Authorization</h6>
                      <p class="mb-0 mt-2 text-muted">These applicants' monthly savings have been fixed by the Treasurer.</p>
                    </div>
                    <div class="card-body">
                      <!-- Plain table (no pagination) so "select all" covers every applicant -->
                      <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                        <div class="mb-2">
                          <button id="approveSelected" class="btn btn-success" disabled>
                            <i class="mdi mdi-check-all"></i> Approve Selected (<span id="selectedCount">0</span>)
                          </button>
                        </div>
                        <input class="form-control col-md-4 mb-2" type="search" id="searchApplicants" placeholder="Search by Staff No, Name, Department...">
                      </div>
                      <div class="approval-msg"></div>
                      <div class="table-responsive">
                        <table id="applicantsTable" class="table table-bordered table-hover">
                          <thead class="thead-dark">
                            <tr>
                              <th><input type="checkbox" id="selectAllApplicants" title="Select all"></th>
                              <th>#</th>
                              <th>Staff Number</th>
                              <th>Full Name</th>
                              <th>Cadre</th>
                              <th>Department</th>
                              <th>Monthly Savings (fixed by Treasurer)</th>
                              <th>Action</th>
                            </tr>
                          </thead>
                          <tbody id="applicantsBody">
                            <?php if (!empty($applicants)): ?>
                              <?php $sn = 1; ?>
                              <?php foreach ($applicants as $m): ?>
                                <tr>
                                  <td><input type="checkbox" class="applicant-checkbox" value="<?php echo htmlspecialchars($m['sp_no']); ?>"></td>
                                  <td><?php echo $sn++; ?></td>
                                  <td><?php echo htmlspecialchars($m['sp_no']); ?></td>
                                  <td><?php echo htmlspecialchars(trim($m['fname'] . ' ' . $m['lname'])); ?></td>
                                  <td><?php echo htmlspecialchars($m['cadre']); ?></td>
                                  <td><?php echo htmlspecialchars($m['dept']); ?></td>
                                  <td>₦<?php echo number_format((float) $m['proposed_monthly_savings'], 2); ?></td>
                                  <td>
                                    <button class="btn btn-sm btn-success approve-applicant"
                                            data-staff-no="<?php echo htmlspecialchars($m['sp_no']); ?>">
                                      Approve
                                    </button>
                                  </td>
                                </tr>
                              <?php endforeach; ?>
                            <?php else: ?>
                              <tr>
                                <td colspan="8" class="text-center text-muted">No applications awaiting authorization.</td>
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

<script>
$(document).ready(function() {
    function visibleCheckboxes() {
        return $('#applicantsBody tr:visible .applicant-checkbox');
    }

    function updateSelection() {
        const count = $('.applicant-checkbox:checked').length;
        $('#selectedCount').text(count);
        $('#approveSelected').prop('disabled', count === 0);
        const visible = visibleCheckboxes();
        $('#selectAllApplicants').prop('checked', visible.length > 0 && visible.filter(':checked').length === visible.length);
    }

    // Select all applicants currently shown (all of them unless a search filter is applied)
    $('#selectAllApplicants').on('change', function() {
        visibleCheckboxes().prop('checked', $(this).prop('checked'));
        updateSelection();
    });
    $(document).on('change', '.applicant-checkbox', updateSelection);

    $('#searchApplicants').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#applicantsBody tr').each(function() {
            $(this).toggle($(this).text().toLowerCase().includes(value));
        });
        updateSelection();
    });

    function approve(staffNos, buttons) {
        buttons.prop('disabled', true);
        $.post('../ajax/chairman_approve_memberships.php', { sp_nos: staffNos }, function(res) {
            const alertClass = res.success ? 'alert-success' : 'alert-warning';
            $('.approval-msg').html('<div class="alert ' + alertClass + '"></div>').find('.alert').text(res.message);
            if (res.approved > 0) {
                setTimeout(function() { location.reload(); }, 1500);
            } else {
                buttons.prop('disabled', false);
            }
        }, 'json').fail(function() {
            $('.approval-msg').html('<div class="alert alert-danger">Network error. Please try again.</div>');
            buttons.prop('disabled', false);
        });
    }

    $(document).on('click', '.approve-applicant', function() {
        const staffNo = $(this).data('staff-no');
        if (!confirm('Approve the membership application of ' + staffNo + '?')) return;
        approve([staffNo], $(this));
    });

    $('#approveSelected').on('click', function() {
        const staffNos = $('.applicant-checkbox:checked').map(function() { return $(this).val(); }).get();
        if (staffNos.length === 0) return;
        if (!confirm('Approve ' + staffNos.length + ' selected membership application(s)?')) return;
        approve(staffNos, $(this));
    });
});
</script>