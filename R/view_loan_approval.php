<?php
    // My Loans (member menu: View Loan Approval): every loan with its progress, figures and repayments
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    require_once('../config/classes/MemberG3.php');
    require_once('../config/classes/LoanControls.php');
    $view = new View();
    $user = new User();
    $member = new MemberG3();
    if (!User::is_authenticated('../auth/') || !User::isStaff('../auth/logout.php')) {
        exit();
    }
    $memberId = $user->getMemberId($user->getEmployeeId($_SESSION['username']));
    $loans = $member->myLoans($memberId);
    $overdue = [];
    foreach (LoanControls::overdueLoans($memberId) as $od) {
        $overdue[(int) $od['loan_id']] = $od;
    }
    $overpayments = LoanControls::overpayments(null, $memberId);

    $repaying = array_filter($loans, function ($l) { return $l['stage'] === 'Repaying'; });
    $inProgress = array_filter($loans, function ($l) { return in_array($l['stage'], ['Awaiting guarantor', 'Awaiting Treasurer', 'Awaiting Chairman', 'Awaiting disbursement'], true); });
    $owed = array_sum(array_column($repaying, 'balance'));
    $monthly = array_sum(array_map(function ($l) { return min($l['monthly'], $l['balance']); }, $repaying));

    $h = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
    $money = function ($v) { return '₦' . number_format((float) $v, 2); };
    $steps = ['Applied', 'Guarantor', 'Treasurer', 'Chairman', 'Paid out', 'Repaid'];
    $stepIndex = ['Awaiting guarantor' => 1, 'Awaiting Treasurer' => 2, 'Awaiting Chairman' => 3, 'Awaiting disbursement' => 4, 'Repaying' => 5, 'Fully repaid' => 6];
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
            <h2><i class="mdi mdi-cash-multiple"></i> My Loans</h2>
            <p>Follow each loan from your application to the last deduction.</p>
            <div class="fud-hero-actions">
              <a href="business_loan.php"><i class="mdi mdi-plus"></i> Apply for a Business Loan</a>
              <a href="view_laon_deduction.php"><i class="mdi mdi-file-document-box"></i> My deductions</a>
            </div>
          </div>

          <?php foreach ($overdue as $od) { ?>
            <div class="fud-notice"><i class="mdi mdi-alert-circle"></i>
              <span>Your <?php echo $h($od['loan_type_name']); ?> is overdue: deductions are <?php echo $money($od['arrears']); ?> behind. Please contact the Treasurer.</span></div>
          <?php } ?>
          <?php foreach ($overpayments as $op) { ?>
            <div class="fud-notice info"><i class="mdi mdi-cash-refund"></i>
              <span>Your <?php echo $h($op['loan_type_name']); ?> was overpaid by <?php echo $money($op['amount']); ?>.</span>
              <a href="overpayment.php"><?php echo $op['status'] === 'open' ? 'Choose what to do' : 'View'; ?> <i class="mdi mdi-arrow-right"></i></a></div>
          <?php } ?>

          <div class="fud-stats">
            <div class="fud-stat"><div class="fud-stat-icon fud-rose"><i class="mdi mdi-cash-multiple"></i></div>
              <div><div class="fud-stat-label">Still to repay</div><div class="fud-stat-value"><?php echo $money($owed); ?></div>
                <div class="fud-stat-sub"><?php echo count($repaying); ?> loan(s) being repaid</div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-purple"><i class="mdi mdi-calendar-clock"></i></div>
              <div><div class="fud-stat-label">Monthly deductions</div><div class="fud-stat-value"><?php echo $money($monthly); ?></div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-amber"><i class="mdi mdi-clock-outline"></i></div>
              <div><div class="fud-stat-label">Applications in progress</div><div class="fud-stat-value"><?php echo count($inProgress); ?></div></div></div>
          </div>

          <?php if (!$loans) { ?>
            <div class="fud-section"><div class="fud-section-body">
              <p class="fud-empty mb-3">You have not applied for a loan yet.</p>
              <a href="business_loan.php" class="btn btn-primary"><i class="mdi mdi-plus"></i> Apply for a Business Loan</a>
            </div></div>
          <?php } ?>

          <?php foreach ($loans as $l) {
              $current = isset($stepIndex[$l['stage']]) ? $stepIndex[$l['stage']] : -1;
              $od = isset($overdue[(int) $l['loan_id']]) ? $overdue[(int) $l['loan_id']] : null;
              $percent = $l['is_debt'] && $l['total'] > 0 ? min(100, round($l['repaid'] / $l['total'] * 100)) : 0; ?>
            <div class="fud-section fud-loan-card">
              <div class="fud-section-head">
                <h5><?php echo $h($l['loan_type_name'] ?: 'Loan'); ?> <span class="text-muted font-weight-normal">#<?php echo (int) $l['loan_id']; ?></span></h5>
                <div>
                  <?php if ($od) { ?><span class="fud-pill no mr-1">Overdue</span><?php } ?>
                  <span class="fud-pill <?php echo $pill($l['stage']); ?>"><?php echo $h($l['stage']); ?></span>
                </div>
              </div>
              <div class="fud-section-body">
                <?php if ($current >= 0 && $l['stage'] !== 'Fully repaid') { ?>
                  <div class="fud-steps mb-3">
                    <?php foreach ($steps as $i => $s) { $class = $i < $current ? 'done' : ($i === $current ? 'current' : ''); ?>
                      <div class="fud-step <?php echo $class; ?>"><div class="fud-step-dot"><i class="mdi <?php echo $class === 'done' ? 'mdi-check' : 'mdi-circle-small'; ?>"></i></div><?php echo $s; ?></div>
                    <?php } ?>
                  </div>
                <?php } ?>

                <div class="fud-facts">
                  <div><span>Applied</span><?php echo $money($l['loan_amount']); ?><small class="d-block text-muted font-weight-normal"><?php echo date('d M Y', strtotime(substr($l['loan_date'], 0, 10))); ?></small></div>
                  <div><span>Approved</span><?php echo $l['fixed'] ? $money($l['amount_recommended']) : '–'; ?></div>
                  <div><span>Interest</span><?php echo $l['fixed'] ? $money($l['interest_amount']) : '–'; ?></div>
                  <div><span>Total to repay</span><?php echo $l['fixed'] || $l['is_debt'] ? $money($l['total']) : '–'; ?></div>
                  <div><span>Monthly</span><?php echo $l['monthly'] > 0 ? $money($l['monthly']) . ' × ' . (int) $l['loan_tenor'] : (int) $l['loan_tenor'] . ' months'; ?>
                    <?php if (!empty($l['repayment_start'])) { ?><small class="d-block text-muted font-weight-normal">from <?php echo date('M Y', strtotime($l['repayment_start'])); ?></small><?php } ?></div>
                  <div><span>Guarantor</span><?php echo $l['g_sp_no'] ? $h(trim($l['g_fname'] . ' ' . $l['g_lname'])) : '–'; ?></div>
                </div>

                <?php if ($l['is_debt']) { ?>
                  <div class="fud-repay mt-3">
                    <div class="d-flex justify-content-between small mb-1">
                      <span>Repaid <strong><?php echo $money($l['repaid']); ?></strong></span>
                      <span><?php echo $l['balance'] > 0 ? 'Balance <strong>' . $money($l['balance']) . '</strong>' : '<strong>Fully repaid</strong>' . ($l['repaid_date'] ? ' on ' . date('d M Y', strtotime($l['repaid_date'])) : ''); ?></span>
                    </div>
                    <div class="fud-bar"><div style="width: <?php echo $percent; ?>%"></div></div>
                    <?php if ($od) { ?><div class="small text-danger mt-1"><?php echo $money($od['arrears']); ?> behind schedule.</div><?php } ?>
                  </div>
                <?php } ?>

                <?php if ($l['decline_reason'] !== '') { ?>
                  <div class="fud-notice mt-3 mb-0"><i class="mdi mdi-information-outline"></i><span>Reason: <?php echo $h($l['decline_reason']); ?></span></div>
                <?php } elseif ($l['stage'] === 'Awaiting guarantor') { ?>
                  <p class="small text-muted mt-3 mb-0">Waiting for <?php echo $h(trim($l['g_fname'] . ' ' . $l['g_lname'])); ?> to accept being your guarantor.</p>
                <?php } elseif ($l['stage'] === 'Awaiting disbursement') { ?>
                  <p class="small text-muted mt-3 mb-0">Authorized. The Treasurer will pay the supplier; deductions start the month after.</p>
                <?php } ?>
              </div>
            </div>
          <?php } ?>

        </div>
        <?php echo $view->subFooter('../'); ?>
