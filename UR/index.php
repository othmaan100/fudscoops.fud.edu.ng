<?php
        require_once('../config/classes/View.php');
        require_once('../config/classes/User.php');
        require_once '../config/classes/MemberG3.php';
        require_once '../config/classes/Dashboard.php';
        $view = new View();
        $user = new User();
        $dashboard = new Dashboard();

        if (!User::is_authenticated('../auth/') || !User::isNonM('../auth/logout.php')) {
            exit();
        }

        $spNo = $_SESSION['username'];
        $employee_id = $user->getEmployeeId($spNo);
        $MembershipDecision = $user->getMembershipDecision($employee_id);
        list($stage, $application) = $dashboard->applicationStage($employee_id);
        list($greeting, $today) = Dashboard::greeting();

        // Steps of a membership application (Dashboard::applicationStage)
        $steps = [
            ['Applied', 'mdi-file-document-box'],
            ['Treasurer fixes savings', 'mdi-bank'],
            ['Chairman authorizes', 'mdi-account-tie'],
            ['Member', 'mdi-account-check'],
        ];
        $stageText = [
            0 => 'You have not applied for membership yet.',
            1 => 'Your application is with the Treasurer, who will confirm your monthly savings.',
            2 => 'The Treasurer has fixed your monthly savings. Your application is awaiting the Chairman.',
            3 => 'Your membership has been approved. Log out and log in again to open your member account.',
        ];

        echo $view->subHeader('../');
    ?>
    <!-- partial:partials/_navbar.html -->
    <?php  echo $view->subPartialNav('../')?>
    <!-- partial -->
    <div class="container-fluid page-body-wrapper">
      <!-- partial:partials/_sidebar.html -->
      <?php  echo $view->nonMemberfSideNav()?>

      <!-- partial -->
      <div class="main-panel">
        <div class="content-wrapper fud-dash">

          <div class="fud-hero">
            <div class="fud-hero-date"><?php echo $today; ?></div>
            <h2><?php echo $greeting . ', ' . htmlspecialchars(Dashboard::displayName($user, $spNo, 'Applicant')); ?></h2>
            <p><?php echo htmlspecialchars($spNo); ?> <span class="fud-hero-chip">Applicant</span></p>
            <div class="fud-hero-actions">
              <a href="register.php"><i class="mdi mdi-account-plus"></i> <?php echo $stage === 0 ? 'Apply for Membership' : 'Update My Application'; ?></a>
              <a href="payslip.php"><i class="mdi mdi-printer"></i> Print IPPIS Payslip</a>
              <a href="profile.php"><i class="mdi mdi-account"></i> My Profile</a>
            </div>
          </div>

          <div class="fud-section">
            <div class="fud-section-head"><h5>Membership Application</h5></div>
            <div class="fud-section-body">
              <div class="fud-steps">
                <?php foreach ($steps as $i => $step) {
                    $class = ($stage > $i || $stage === 3) ? 'done' : ($stage === $i ? 'current' : '');
                    if ($stage === 0) { $class = ''; } ?>
                  <div class="fud-step <?php echo $class; ?>">
                    <div class="fud-step-dot"><i class="mdi <?php echo $class === 'done' ? 'mdi-check' : $step[1]; ?>"></i></div>
                    <?php echo $step[0]; ?>
                  </div>
                <?php } ?>
              </div>

              <div class="fud-notice <?php echo $stage === 3 ? 'success' : 'info'; ?> mt-3"><i class="mdi mdi-information"></i>
                <span><?php echo $stageText[$stage]; ?></span>
                <?php if ($stage === 0) { ?><a href="register.php">Apply now</a><?php } ?>
              </div>

              <?php if ($application) { ?>
                <div class="fud-stats mt-3 mb-0">
                  <div class="fud-stat">
                    <div class="fud-stat-icon fud-blue"><i class="mdi mdi-calendar-clock"></i></div>
                    <div><div class="fud-stat-label"><?php echo $stage >= 2 ? 'Monthly savings fixed by Treasurer' : 'Monthly savings you proposed'; ?></div>
                      <div class="fud-stat-value">₦<?php echo number_format((float) $application['proposed_monthly_savings']); ?></div></div>
                  </div>
                  <div class="fud-stat">
                    <div class="fud-stat-icon fud-purple"><i class="mdi mdi-calendar"></i></div>
                    <div><div class="fud-stat-label">Date applied</div>
                      <div class="fud-stat-value"><?php echo $application['reg_date'] ? date('d M Y', strtotime($application['reg_date'])) : '—'; ?></div></div>
                  </div>
                </div>
              <?php } ?>

              <?php if (strpos($MembershipDecision, 'Latest Decision') !== false) { ?>
                <div class="fud-notice mt-3"><i class="mdi mdi-gavel"></i><span><?php echo htmlspecialchars(strip_tags(str_replace('<br>', ' · ', $MembershipDecision))); ?></span></div>
              <?php } ?>
            </div>
          </div>

        </div>
        <!-- content-wrapper ends -->
        <?php
          echo $view->subFooter('../');
        ?>
