<?php
require_once('../config/classes/View.php');
require_once('../config/classes/User.php');
require_once '../config/classes/MemberG3.php';
require_once '../config/classes/MemberG1.php';
require_once '../config/classes/MemberG2.php';
require_once '../config/classes/Dashboard.php';

$view = new View();
$user = new User();
$member1 = new MemberG1();
$member2 = new MemberG2();
$dashboard = new Dashboard();

if (!User::is_authenticated('../auth/') || !User::isStaff('../auth/logout.php')) {
    exit();
}

$spNo = $_SESSION['username'];
$employeeid = $user->getEmployeeId($spNo);
$memberId = $user->getMemberId($employeeid);

$psavings = $member1->getProposedMonthlySavings($spNo);
$currentsavings = $member1->getCurrentSavingsAmount($spNo);
$cumwithdrawal = $member1->getCumulativeSavingsWithdrawals($spNo);
$lastSavings = $member1->getLastSavingsWithdrawals($spNo);
$shareHolding = MemberG2::getShareHolding($memberId);
$unitPrice = MemberG2::shareUnitPrice();
$loans = $dashboard->memberLoans($memberId);
$loansOutstanding = array_sum(array_column($loans, 'outstanding'));
$openLoans = count(array_filter($loans, function ($l) { return $l['outstanding'] > 0; }));
$pending = $dashboard->memberPending($memberId);
$requests = $dashboard->personalRequests($employeeid);
$repaid = $dashboard->recentlyRepaid($memberId, $employeeid);
require_once('../config/classes/LoanControls.php');
$overdueLoans = LoanControls::overdueLoans($memberId);
$overdueGuaranteed = LoanControls::overdueGuaranteed($employeeid);
// Loans this member guarantees: active = accepted and not finished; waiting = not decided yet (same as R/guarantee.php)
require_once('../config/classes/MemberG3.php');
require_once('../config/classes/Settings.php');
$guaranteeActive = $guaranteeWaiting = 0;
foreach ((new MemberG3())->guaranteeRequests($employeeid) as $g) {
    if (in_array($g['loan_status'], ['Disapprove', 'Repaid'], true)) {
        continue;
    }
    if ((int) $g['guarantor_status'] === 1) {
        $guaranteeActive++;
    } elseif ((int) $g['guarantor_status'] === 0) {
        $guaranteeWaiting++;
    }
}
$guaranteeMax = (int) Settings::get('guarantor_max_open_loans', 2);
$overpayments = LoanControls::overpayments(null, $memberId);
$transferNotice = $member2->notifyMemberShareTransferApproval($memberId);
$recentWithdrawals = $member1->getRecentMemberWithdrawals($memberId);
$banks = (new DB())->getConnection()->query("SELECT id, name FROM banks")->fetchAll(PDO::FETCH_KEY_PAIR);
list($greeting, $today) = Dashboard::greeting();

echo $view->subHeader('../');
?>
<!-- partial:partials/_navbar.html -->
<?php echo $view->subPartialNav('../')?>
<!-- partial -->
<div class="container-fluid page-body-wrapper">
  <?php echo $view->staffSideNav()?>

  <div class="main-panel">
    <div class="content-wrapper fud-dash">

      <!-- Welcome -->
      <div class="fud-hero">
        <div class="fud-hero-date"><?php echo $today; ?></div>
        <h2><?php echo $greeting . ', ' . htmlspecialchars(Dashboard::displayName($user, $spNo, 'Member')); ?></h2>
        <p><?php echo htmlspecialchars($spNo); ?> <span class="fud-hero-chip">Member</span></p>
        <div class="fud-hero-actions">
          <a href="payslip.php"><i class="mdi mdi-printer"></i> Print IPPIS Payslip</a>
          <a href="profile.php"><i class="mdi mdi-account"></i> My Profile</a>
          <a href="savings/update_savings.php"><i class="mdi mdi-cash-refund"></i> Change Savings</a>
          <a href="share_purchase.php"><i class="mdi mdi-chart-pie"></i> Buy Shares</a>
          <a href="business_loan.php"><i class="mdi mdi-cash-multiple"></i> Apply for a Loan</a>
        </div>
      </div>

      <!-- Things that need the member's action -->
      <?php foreach ($overdueLoans as $od) { ?>
        <div class="fud-notice"><i class="mdi mdi-alert-circle"></i>
          <span>Your <?php echo htmlspecialchars($od['loan_type_name']); ?> is overdue: deductions are ₦<?php echo number_format($od['arrears'], 2); ?> behind
            (<?php echo count($od['missed']); ?> month(s) missed or short). Please contact the Treasurer.</span>
          <a href="view_loan_approval.php">My loans</a></div>
      <?php } ?>
      <?php foreach ($overdueGuaranteed as $od) { ?>
        <div class="fud-notice"><i class="mdi mdi-account-alert"></i>
          <span>The <?php echo htmlspecialchars($od['loan_type_name']); ?> you guaranteed for <?php echo htmlspecialchars(trim($od['fname'] . ' ' . $od['lname'])); ?>
            is overdue: ₦<?php echo number_format($od['arrears'], 2); ?> behind. As guarantor you may be held responsible.</span></div>
      <?php } ?>
      <?php foreach ($dashboard->recentWithdrawalRejections($memberId) as $rj) { ?>
        <div class="fud-notice"><i class="mdi mdi-close-circle"></i>
          <span>Your savings withdrawal request of ₦<?php echo number_format((float) $rj['proposed_withrawal_amount'], 2); ?> was rejected<?php echo $rj['treasurer_comment'] ? ': ' . htmlspecialchars($rj['treasurer_comment']) : '.'; ?></span>
          <a href="savings/savings_withdrawal.php">Withdraw Savings</a></div>
      <?php } ?>
      <?php foreach ($overpayments as $op) { ?>
        <div class="fud-notice info"><i class="mdi mdi-cash-refund"></i>
          <span>Your <?php echo htmlspecialchars($op['loan_type_name']); ?> was overpaid by ₦<?php echo number_format($op['amount'], 2); ?>.
            <?php echo $op['status'] === 'open' ? 'Tell the Treasurer what to do with it.' : 'Your choice is with the Treasurer.'; ?></span>
          <a href="overpayment.php"><?php echo $op['status'] === 'open' ? 'Choose' : 'View'; ?></a></div>
      <?php } ?>
      <?php if ($requests['guarantees'] > 0) { ?>
        <div class="fud-notice"><i class="mdi mdi-account-check"></i>
          <span><?php echo $requests['guarantees']; ?> member(s) asked you to guarantee a loan.</span>
          <a href="guarantee.php">Respond</a></div>
      <?php } ?>
      <?php if ($requests['transfers'] > 0) { ?>
        <div class="fud-notice"><i class="mdi mdi-swap-horizontal"></i>
          <span><?php echo $requests['transfers']; ?> share transfer(s) are waiting for you to accept.</span>
          <a href="transfer.php">Accept</a></div>
      <?php } ?>
      <?php foreach ($repaid['own'] as $r) { ?>
        <div class="fud-notice success"><i class="mdi mdi-check-decagram"></i><span>Your <?php echo htmlspecialchars($r['loan_type_name']); ?> was fully repaid on <?php echo date('d M Y', strtotime($r['repaid_date'])); ?>.</span></div>
      <?php } ?>
      <?php foreach ($repaid['guaranteed'] as $r) { ?>
        <div class="fud-notice success"><i class="mdi mdi-account-check"></i><span>The loan you guaranteed for <?php echo htmlspecialchars($r['fname'] . ' ' . $r['lname']); ?> has been fully repaid. You are released as guarantor.</span></div>
      <?php } ?>
      <?php if (strpos($transferNotice, 'Approved') !== false) { ?>
        <div class="fud-notice success"><i class="mdi mdi-check-circle"></i><span>A share transfer to you has been approved.</span></div>
      <?php } ?>

      <!-- Key figures -->
      <div class="fud-stats">
        <div class="fud-stat">
          <div class="fud-stat-icon fud-blue"><i class="mdi mdi-calendar-clock"></i></div>
          <div><div class="fud-stat-label">Monthly Savings</div>
            <div class="fud-stat-value">₦<?php echo number_format($psavings); ?></div>
            <div class="fud-stat-sub">deducted from salary</div></div>
        </div>
        <a class="fud-stat fud-stat-link" href="savings/savings_history.php" title="Savings History">
          <div class="fud-stat-icon fud-green"><i class="mdi mdi-wallet"></i></div>
          <div><div class="fud-stat-label">Savings Balance</div>
            <div class="fud-stat-value">₦<?php echo number_format($currentsavings); ?></div>
            <div class="fud-stat-sub">after withdrawals</div></div>
        </a>
        <div class="fud-stat">
          <div class="fud-stat-icon fud-purple"><i class="mdi mdi-chart-pie"></i></div>
          <div><div class="fud-stat-label">My Shares</div>
            <div class="fud-stat-value">₦<?php echo number_format($shareHolding); ?></div>
            <div class="fud-stat-sub"><?php echo $unitPrice > 0 ? number_format(floor($shareHolding / $unitPrice)) . ' units at ₦' . number_format($unitPrice) : ''; ?></div></div>
        </div>
        <div class="fud-stat">
          <div class="fud-stat-icon fud-amber"><i class="mdi mdi-cash-multiple"></i></div>
          <div><div class="fud-stat-label">Loans Outstanding</div>
            <div class="fud-stat-value">₦<?php echo number_format($loansOutstanding); ?></div>
            <div class="fud-stat-sub"><?php echo $openLoans; ?> loan(s) being repaid</div></div>
        </div>
        <a class="fud-stat fud-stat-link" href="guarantee.php">
          <div class="fud-stat-icon <?php echo $overdueGuaranteed ? 'fud-rose' : 'fud-blue'; ?>"><i class="mdi mdi-account-check"></i></div>
          <div><div class="fud-stat-label">Loans I Guarantee</div>
            <div class="fud-stat-value"><?php echo $guaranteeActive; ?> active</div>
            <div class="fud-stat-sub"><?php
              $bits = [];
              if ($guaranteeWaiting) { $bits[] = $guaranteeWaiting . ' waiting for you'; }
              if ($overdueGuaranteed) { $bits[] = count($overdueGuaranteed) . ' overdue'; }
              if ($guaranteeMax > 0) { $bits[] = 'at most ' . $guaranteeMax . ' at a time'; }
              echo implode(' · ', $bits);
            ?></div></div>
        </a>
        <div class="fud-stat">
          <div class="fud-stat-icon fud-teal"><i class="mdi mdi-bank-transfer-out"></i></div>
          <div><div class="fud-stat-label">Total Withdrawn</div>
            <div class="fud-stat-value">₦<?php echo number_format($cumwithdrawal); ?></div>
            <div class="fud-stat-sub">last withdrawal ₦<?php echo number_format($lastSavings); ?></div></div>
        </div>
      </div>

      <div class="row">
        <!-- Pending requests -->
        <div class="col-lg-5">
          <div class="fud-section">
            <div class="fud-section-head"><h5>My Pending Requests</h5></div>
            <div class="fud-section-body">
              <?php if ($pending) { ?>
                <?php foreach ($pending as $item) { ?>
                  <div class="fud-notice info"><i class="mdi <?php echo $item[1]; ?>"></i><span><?php echo htmlspecialchars($item[0]); ?></span></div>
                <?php } ?>
              <?php } else { ?>
                <div class="fud-all-clear"><i class="mdi mdi-check-circle"></i> Nothing awaiting approval.</div>
              <?php } ?>
            </div>
          </div>
        </div>

        <!-- Loans -->
        <div class="col-lg-7">
          <div class="fud-section">
            <div class="fud-section-head"><h5>My Loans</h5><a href="view_loan_approval.php">View all</a></div>
            <div class="fud-section-body">
              <?php if ($loans) { ?>
                <div class="table-responsive">
                  <table class="table fud-table">
                    <thead><tr><th>Date</th><th>Type</th><th class="num">To repay (₦)</th><th class="num">Balance (₦)</th><th>Status</th></tr></thead>
                    <tbody>
                      <?php foreach (array_slice($loans, 0, 5) as $loan) {
                          $pill = in_array($loan['stage'], ['Repaying', 'Fully repaid'], true) ? 'ok' : (strpos($loan['stage'], 'Declined') === 0 ? 'no' : 'wait'); ?>
                        <tr>
                          <td><?php echo htmlspecialchars(date('d M Y', strtotime(substr($loan['loan_date'], 0, 10)))); ?></td>
                          <td><?php echo htmlspecialchars($loan['loan_type_name']); ?></td>
                          <td class="num"><?php echo number_format($loan['amount'], 2); ?></td>
                          <td class="num"><?php echo $loan['stage'] === 'Repaying' ? number_format($loan['outstanding'], 2) : ''; ?></td>
                          <td><span class="fud-pill <?php echo $pill; ?>"><?php echo htmlspecialchars($loan['stage']); ?></span></td>
                        </tr>
                      <?php } ?>
                    </tbody>
                  </table>
                </div>
              <?php } else { ?>
                <p class="fud-empty">You have no loans. <a href="business_loan.php">Apply for a loan</a></p>
              <?php } ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent withdrawals -->
      <div class="fud-section">
        <div class="fud-section-head"><h5>Recent Withdrawals</h5><a href="view_all_withdrawals.php">View all</a></div>
        <div class="fud-section-body">
          <?php if (!empty($recentWithdrawals)) { ?>
            <div class="table-responsive">
              <table class="table fud-table">
                <thead><tr><th>Date</th><th class="num">Amount (₦)</th><th>Bank</th><th>Status</th></tr></thead>
                <tbody>
                  <?php foreach ($recentWithdrawals as $w) {
                      $date = !empty($w['approved_date']) ? $w['approved_date'] : (isset($w['proposed_date']) ? $w['proposed_date'] : '');
                      $amount = $w['chairman_approval'] == 1 ? $w['approved_withrawal_amount'] : (isset($w['proposed_withrawal_amount']) ? $w['proposed_withrawal_amount'] : $w['approved_withrawal_amount']); ?>
                    <tr>
                      <td><?php echo $date ? htmlspecialchars(date('d M Y', strtotime($date))) : ''; ?></td>
                      <td class="num"><?php echo number_format((float) $amount, 2); ?></td>
                      <td><?php echo htmlspecialchars(isset($banks[$w['bank_id']]) ? $banks[$w['bank_id']] : $w['bank_id']); ?></td>
                      <td>
                        <?php if ($w['chairman_approval'] == 1) { ?><span class="fud-pill ok">Approved</span>
                        <?php } elseif ($w['chairman_approval'] == 0) { ?><span class="fud-pill wait">Pending</span>
                        <?php } else { ?><span class="fud-pill no">Rejected</span><?php } ?>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          <?php } else { ?>
            <p class="fud-empty">No withdrawals yet.</p>
          <?php } ?>
        </div>
      </div>

    </div>
    <!-- content-wrapper ends -->
    <?php echo $view->subFooter('../'); ?>
  </div>
</div>
