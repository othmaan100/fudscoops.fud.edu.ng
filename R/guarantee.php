<?php
    // Loans the member was asked to guarantee: waiting for a decision, being guaranteed now, and past ones
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    require_once('../config/classes/MemberG3.php');
    require_once('../config/classes/LoanControls.php');
    require_once('../config/classes/Settings.php');
    $view = new View();
    $user = new User();
    $member = new MemberG3();
    if (!User::is_authenticated('../auth/') || !User::isStaff('../auth/logout.php')) {
        exit();
    }
    $employeeId = $user->getEmployeeId($_SESSION['username']);
    $rows = $member->guaranteeRequests($employeeId);
    $overdue = [];
    foreach (LoanControls::overdueGuaranteed($employeeId) as $od) {
        $overdue[(int) $od['loan_id']] = $od;
    }
    // Waiting: the guarantor has not decided. Current: accepted and not finished. Past: the rest.
    $waiting = $current = $past = [];
    foreach ($rows as $r) {
        if ((int) $r['guarantor_status'] === 0 && !in_array($r['loan_status'], ['Disapprove', 'Repaid'], true)) {
            $waiting[] = $r;
        } elseif ((int) $r['guarantor_status'] === 1 && !in_array($r['loan_status'], ['Disapprove', 'Repaid'], true)) {
            $current[] = $r;
        } else {
            $past[] = $r;
        }
    }
    $maxOpen = (int) Settings::get('guarantor_max_open_loans', 2);
    $h = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
    $who = function ($r) { return preg_replace('/\s+/', ' ', trim($r['title'] . ' ' . $r['fname'] . ' ' . $r['lname'])); };
    $pill = function ($stage) { return in_array($stage, ['Repaying', 'Fully repaid'], true) ? 'ok' : (strpos($stage, 'Declined') === 0 ? 'no' : 'wait'); };

    echo $view->subHeader('../');
?>
    <?php echo $view->subPartialNav('../'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->staffSideNav(); ?>
      <div class="main-panel">
        <div class="content-wrapper fud-dash">

          <div class="fud-hero">
            <div class="fud-hero-date">Loans</div>
            <h2><i class="mdi mdi-account-check"></i> Loans I Guarantee</h2>
            <p>Members who asked you to be their guarantor, and the loans you are guaranteeing.</p>
          </div>

          <div class="fud-stats">
            <div class="fud-stat"><div class="fud-stat-icon fud-amber"><i class="mdi mdi-clock-outline"></i></div>
              <div><div class="fud-stat-label">Waiting for your decision</div><div class="fud-stat-value"><?php echo count($waiting); ?></div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-blue"><i class="mdi mdi-account-check"></i></div>
              <div><div class="fud-stat-label">Guaranteeing now</div><div class="fud-stat-value"><?php echo count($current); ?></div>
                <?php if ($maxOpen > 0) { ?><div class="fud-stat-sub">at most <?php echo $maxOpen; ?> at a time</div><?php } ?></div></div>
            <div class="fud-stat"><div class="fud-stat-icon <?php echo $overdue ? 'fud-rose' : 'fud-green'; ?>"><i class="mdi <?php echo $overdue ? 'mdi-alert-circle' : 'mdi-check-circle'; ?>"></i></div>
              <div><div class="fud-stat-label">Overdue</div><div class="fud-stat-value"><?php echo count($overdue); ?></div></div></div>
          </div>

          <div class="fud-section">
            <div class="fud-section-head"><h5>Waiting for your decision</h5></div>
            <div class="fud-section-body">
              <?php if (!$waiting) { ?>
                <p class="fud-empty">Nobody is waiting for you to guarantee a loan.</p>
              <?php } else { ?>
                <div class="fud-guarantee-list">
                  <?php foreach ($waiting as $r) { ?>
                    <div class="fud-guarantee-item">
                      <div class="fud-guarantee-who">
                        <strong><?php echo $h($who($r)); ?></strong>
                        <div class="text-muted small"><?php echo $h($r['sp_no']); ?> · <?php echo $h($r['dept']); ?></div>
                      </div>
                      <div class="fud-guarantee-loan">
                        <?php echo $h($r['loan_type_name']); ?> of <strong>₦<?php echo number_format((float) $r['loan_amount'], 2); ?></strong>
                        over <?php echo (int) $r['loan_tenor']; ?> months
                        <div class="text-muted small">asked on <?php echo date('d M Y', strtotime(substr($r['loan_date'], 0, 10))); ?></div>
                      </div>
                      <a class="btn btn-primary" href="undertaking.php?loan_id=<?php echo (int) $r['loan_id']; ?>"><i class="mdi mdi-pencil"></i> Review and respond</a>
                    </div>
                  <?php } ?>
                </div>
              <?php } ?>
            </div>
          </div>

          <div class="fud-section">
            <div class="fud-section-head"><h5>Loans you are guaranteeing</h5></div>
            <div class="fud-section-body">
              <?php if (!$current) { ?>
                <p class="fud-empty">You are not guaranteeing any loan at the moment.</p>
              <?php } else { ?>
                <div class="table-responsive"><table class="table fud-table">
                  <thead><tr><th>Member</th><th>Loan</th><th class="num">To repay (₦)</th><th class="num">Repaid (₦)</th><th class="num">Balance (₦)</th><th>Stage</th><th></th></tr></thead>
                  <tbody>
                  <?php foreach ($current as $r) { $od = isset($overdue[(int) $r['loan_id']]) ? $overdue[(int) $r['loan_id']] : null; ?>
                    <tr>
                      <td><?php echo $h($who($r)); ?><div class="text-muted small"><?php echo $h($r['sp_no']); ?></div></td>
                      <td><?php echo $h($r['loan_type_name']); ?></td>
                      <td class="num"><?php echo number_format($r['total'], 2); ?></td>
                      <td class="num"><?php echo number_format((float) $r['repaid'], 2); ?></td>
                      <td class="num"><?php echo number_format($r['balance'], 2); ?></td>
                      <td><span class="fud-pill <?php echo $pill($r['stage']); ?>"><?php echo $h($r['stage']); ?></span>
                        <?php if ($od) { ?><div><span class="fud-pill no">Overdue: ₦<?php echo number_format($od['arrears'], 2); ?> behind</span></div><?php } ?></td>
                      <td><a href="undertaking.php?loan_id=<?php echo (int) $r['loan_id']; ?>">Undertaking</a></td>
                    </tr>
                  <?php } ?>
                  </tbody>
                </table></div>
              <?php } ?>
            </div>
          </div>

          <?php if ($past) { ?>
          <div class="fud-section">
            <div class="fud-section-head"><h5>Past requests</h5></div>
            <div class="fud-section-body">
              <div class="table-responsive"><table class="table fud-table">
                <thead><tr><th>Member</th><th>Loan</th><th class="num">Amount (₦)</th><th>Asked</th><th>Outcome</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($past as $r) {
                    $outcome = (int) $r['guarantor_status'] === -1 ? 'You declined' : ($r['loan_status'] === 'Repaid' ? 'Fully repaid – you are released' : $r['stage']); ?>
                  <tr>
                    <td><?php echo $h($who($r)); ?><div class="text-muted small"><?php echo $h($r['sp_no']); ?></div></td>
                    <td><?php echo $h($r['loan_type_name']); ?></td>
                    <td class="num"><?php echo number_format((float) $r['loan_amount'], 2); ?></td>
                    <td><?php echo date('d M Y', strtotime(substr($r['loan_date'], 0, 10))); ?></td>
                    <td><span class="fud-pill <?php echo $r['loan_status'] === 'Repaid' ? 'ok' : 'no'; ?>"><?php echo $h($outcome); ?></span></td>
                    <td><a href="undertaking.php?loan_id=<?php echo (int) $r['loan_id']; ?>">Undertaking</a></td>
                  </tr>
                <?php } ?>
                </tbody>
              </table></div>
            </div>
          </div>
          <?php } ?>

        </div>
        <?php echo $view->subFooter('../'); ?>
