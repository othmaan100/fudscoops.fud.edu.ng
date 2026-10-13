<?php
    // View Commodity Deduction (member): requests supplied to the member, what is repaid and what is left
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    require_once('../config/classes/CommodityLoan.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../auth/') || !User::isStaff('../auth/logout.php')) {
        exit();
    }
    $rows = array_values(array_filter(CommodityLoan::memberRequests($user->getMemberId($user->getEmployeeId($_SESSION['username']))), function ($r) {
        return in_array($r['status'], ['supplied', 'repaid', 'completed'], true);
    }));
    $owing = array_sum(array_column($rows, 'balance'));
    $repaid = array_sum(array_column($rows, 'repaid'));
    $monthly = array_sum(array_map(function ($r) { return $r['status'] === 'supplied' ? min($r['monthly_amount'], $r['balance']) : 0; }, $rows));
    $h = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };

    echo $view->subHeader('../');
?>
    <?php echo $view->subPartialNav('../'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->staffSideNav(); ?>
      <div class="main-panel">
        <div class="content-wrapper fud-dash">

          <div class="fud-hero">
            <div class="fud-hero-date">Commodity</div>
            <h2><i class="mdi mdi-cart-outline"></i> Commodity Deductions</h2>
            <p>Commodity supplied to you, the monthly deduction and what is left to repay.</p>
          </div>

          <div class="fud-stats">
            <div class="fud-stat"><div class="fud-stat-icon fud-rose"><i class="mdi mdi-cash-multiple"></i></div>
              <div><div class="fud-stat-label">Still to repay</div><div class="fud-stat-value">₦<?php echo number_format($owing, 2); ?></div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-purple"><i class="mdi mdi-calendar-clock"></i></div>
              <div><div class="fud-stat-label">Monthly deduction</div><div class="fud-stat-value">₦<?php echo number_format($monthly, 2); ?></div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-green"><i class="mdi mdi-check-circle"></i></div>
              <div><div class="fud-stat-label">Repaid so far</div><div class="fud-stat-value">₦<?php echo number_format($repaid, 2); ?></div></div></div>
          </div>

          <?php if (!$rows) { ?>
            <div class="fud-section"><div class="fud-section-body"><p class="fud-empty">No commodity has been supplied to you yet.
              <a href="view_commodity_request.php">See my requests</a></p></div></div>
          <?php } ?>
          <?php foreach ($rows as $r) { ?>
            <div class="fud-section">
              <div class="fud-section-head"><h5><?php echo $h($r['commodity_name']); ?> <?php echo CommodityLoan::stagePill($r); ?></h5>
                <a href="viewRequestDetails.php?applicationId=<?php echo (int) $r['application_id']; ?>">Details <i class="mdi mdi-arrow-right"></i></a></div>
              <div class="fud-section-body">
                <div class="fud-facts mb-3">
                  <div><span>Total to repay</span>₦<?php echo number_format($r['payable'], 2); ?></div>
                  <div><span>Monthly</span>₦<?php echo number_format($r['monthly_amount'], 2); ?> × <?php echo (int) $r['tenor']; ?></div>
                  <div><span>Repaid</span>₦<?php echo number_format($r['repaid'], 2); ?></div>
                  <div><span>Balance</span>₦<?php echo number_format($r['balance'], 2); ?></div>
                </div>
                <?php $reps = CommodityLoan::repayments($r['application_id']);
                if (!$reps) { ?>
                  <p class="fud-empty">No deductions yet. The first is due in <?php echo $r['repayment_start'] ? date('F Y', strtotime($r['repayment_start'])) : 'the month after supply'; ?>.</p>
                <?php } else { ?>
                  <table class="table fud-table"><thead><tr><th>Month</th><th class="num">Deducted (₦)</th></tr></thead><tbody>
                    <?php foreach ($reps as $p) { ?>
                      <tr><td><?php echo date('F Y', strtotime($p['repayment_month'])); ?></td><td class="num"><?php echo number_format($p['amount_paid'], 2); ?></td></tr>
                    <?php } ?>
                  </tbody></table>
                <?php } ?>
              </div>
            </div>
          <?php } ?>

        </div>
        <?php echo $view->subFooter('../'); ?>
