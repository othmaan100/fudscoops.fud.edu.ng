<?php
    // One membership application for the Chairman: the applicant's details and the monthly savings the Treasurer fixed,
    // with Authorize (ajax/chairman_decisions.php, type membership). ?employee_id= (from member_list.php); ?member_id= also works.
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    require_once('../config/classes/MemberG1.php');
    require_once('../config/chairman/decisions.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../auth/') || !User::isChairman('../auth/logout.php')) {
        exit();
    }
    $members = new MemberG1();
    $h = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
    $employeeId = isset($_GET['employee_id']) ? (int) $_GET['employee_id'] : 0;
    if (!$employeeId && isset($_GET['member_id'])) {
        $stmt = (new DB())->getConnection()->prepare("SELECT employee_id FROM fudscoops_member WHERE member_id = :m");
        $stmt->execute([':m' => (int) $_GET['member_id']]);
        $employeeId = (int) $stmt->fetchColumn();
    }
    $a = $employeeId ? $members->getApplicantDetails($employeeId) : null;
    $payslips = [];
    for ($k = 1; $k <= 3; $k++) {
        $time = strtotime("first day of -$k month");
        if (date('Y', $time) == date('Y')) {
            $payslips[date('n', $time)] = date('F', $time);
        }
    }
    echo $view->subHeader('../');
?>
    <?php echo $view->subPartialNav('../'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->subSideNav(); ?>
      <div class="main-panel">
        <div class="content-wrapper fud-dash fud-apply">
          <?php if (!$a) { ?>
            <div class="fud-notice"><i class="mdi mdi-alert-circle"></i><span>Application not found.</span>
              <a href="member_list.php?tab=applicants">Applicants <i class="mdi mdi-arrow-right"></i></a></div>
          <?php } else {
              $name = preg_replace('/\s+/', ' ', trim($a['title'] . ' ' . $a['fname'] . ' ' . $a['oname'] . ' ' . $a['lname']));
              $waiting = $a['treasurer_approval'] == 1 && $a['chairman_approval'] == 0;
              $stage = $a['chairman_approval'] == 1 ? ['Approved', 'ok'] : ($waiting ? ['Awaiting your authorization', 'wait'] : ['Awaiting the Treasurer', 'wait']); ?>

          <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div>
              <h3 class="mb-1">Membership application <span class="fud-pill <?php echo $stage[1]; ?>" style="font-size:.8rem"><?php echo $stage[0]; ?></span></h3>
              <div class="text-muted"><?php echo $h($name); ?> (<?php echo $h($a['sp_no']); ?>)<?php echo $a['reg_date'] ? ' · applied ' . date('d M Y', strtotime($a['reg_date'])) : ''; ?></div>
            </div>
            <a href="member_list.php?tab=applicants" class="btn btn-light btn-sm"><i class="mdi mdi-arrow-left"></i> All applicants</a>
          </div>

          <div class="row">
            <div class="col-lg-7">
              <div class="fud-section">
                <div class="fud-section-head"><h5><span class="fud-num">1</span> Applicant</h5></div>
                <div class="fud-section-body">
                  <div class="fud-facts">
                    <div><span>Name</span><?php echo $h($name); ?></div>
                    <div><span>Staff number</span><?php echo $h($a['sp_no']); ?></div>
                    <div><span>Department / unit</span><?php echo $h($a['dept'] ?: '–'); ?></div>
                    <div><span>Cadre</span><?php echo $h($a['cadre'] ?: '–'); ?></div>
                    <div><span>Rank</span><?php echo $h($a['rank'] ?: '–'); ?></div>
                    <div><span>Grade level / step</span><?php echo $h(trim($a['grade_level'] . ' / ' . $a['step'], ' /') ?: '–'); ?></div>
                    <div><span>IPPIS grade</span><?php echo $h($a['grade'] ?: '–'); ?></div>
                    <div><span>Appointment</span><?php echo $h($a['nature_of_appo'] ?: '–'); ?></div>
                    <div><span>Phone</span><?php echo $h($a['phone_no'] ?: '–'); ?></div>
                    <div><span>Email</span><?php echo $h($a['email'] ?: '–'); ?></div>
                    <div><span>Bank</span><?php echo $h($a['bank_name'] ?: '–'); ?></div>
                    <div><span>Account number</span><?php echo $a['acctno'] !== '' ? $h(str_pad($a['acctno'], 10, '0', STR_PAD_LEFT)) : '–'; ?></div>
                    <div><span>Home address</span><?php echo $h($a['permanent_address'] ?: '–'); ?></div>
                  </div>
                  <?php if ($payslips) { ?>
                    <p class="mt-3 mb-0"><strong>Payslips:</strong>
                      <?php foreach ($payslips as $mo => $mn) { ?><a class="ml-2" href="slip.php?legacy=<?php echo urlencode($a['sp_no']); ?>&s=<?php echo $mo; ?>"><?php echo $mn; ?></a><?php } ?></p>
                  <?php } ?>
                </div>
              </div>
              <div class="fud-section">
                <div class="fud-section-head"><h5><span class="fud-num">2</span> Next of kin</h5></div>
                <div class="fud-section-body">
                  <div class="fud-facts">
                    <div><span>Name</span><?php echo $h($a['next_of_kin_name'] ?: '–'); ?></div>
                    <div><span>Phone</span><?php echo $h($a['next_of_kin_gsm'] ?: '–'); ?></div>
                    <div><span>Address</span><?php echo $h($a['next_of_kin_address'] ?: '–'); ?></div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-5">
              <div class="fud-section">
                <div class="fud-section-head"><h5><i class="mdi mdi-calendar-clock"></i> Monthly savings</h5></div>
                <div class="fud-section-body">
                  <div class="fud-facts">
                    <div><span><?php echo $a['treasurer_approval'] == 1 ? 'Fixed by the Treasurer' : 'Proposed by the applicant'; ?></span>
                      <strong style="font-size:1.3rem">₦<?php echo number_format((float) $a['proposed_monthly_savings'], 2); ?></strong></div>
                  </div>
                </div>
              </div>
              <?php echo chairmanDecisionPanel('membership', $a['sp_no'], $name . ' (' . $a['sp_no'] . ')', 'member_list.php?tab=applicants', $waiting,
                  $a['chairman_approval'] == 1 ? 'This membership is approved.' : 'The Treasurer has not fixed the monthly savings yet. You can authorize it after that.',
                  [], false, 'Authorizing makes the applicant a member, with the monthly savings above deducted from their salary.'); ?>
            </div>
          </div>
          <?php } ?>
        </div>
        <?php echo $view->subFooter('../'); ?>
