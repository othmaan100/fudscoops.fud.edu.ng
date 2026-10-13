<?php
    // View Commodity Requests (member). Workflow: config/classes/CommodityLoan.php
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    require_once('../config/classes/CommodityLoan.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../auth/') || !User::isStaff('../auth/logout.php')) {
        exit();
    }
    $rows = CommodityLoan::memberRequests($user->getMemberId($user->getEmployeeId($_SESSION['username'])));
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
            <h2><i class="mdi mdi-cart-outline"></i> My Commodity Requests</h2>
            <p>Follow each request from the Treasurer and Chairman to supply and repayment.</p>
            <div class="fud-hero-actions"><a href="commodity_request.php"><i class="mdi mdi-plus"></i> New request</a></div>
          </div>

          <div class="fud-section">
            <div class="fud-section-body">
              <?php if (!$rows) { ?>
                <p class="fud-empty">You have not requested any commodity yet.</p>
              <?php } else { ?>
                <div class="table-responsive">
                  <table class="table fud-table">
                    <thead><tr><th>Supply</th><th>Requested</th><th class="num">Items (₦)</th><th class="num">To repay (₦)</th>
                      <th class="num">Monthly (₦)</th><th class="num">Balance (₦)</th><th>Stage</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($rows as $r) { $repaying = in_array($r['status'], ['supplied', 'repaid'], true); ?>
                      <tr>
                        <td><?php echo $h($r['commodity_name']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($r['application_date'])); ?></td>
                        <td class="num"><?php echo number_format($r['total_amount'], 2); ?></td>
                        <td class="num"><?php echo $r['total_payable'] !== null ? number_format($r['payable'], 2) : '–'; ?></td>
                        <td class="num"><?php echo $r['monthly_amount'] !== null ? number_format($r['monthly_amount'], 2) . ' × ' . (int) $r['tenor'] : '–'; ?></td>
                        <td class="num"><?php echo $repaying ? number_format($r['balance'], 2) : '–'; ?></td>
                        <td><?php echo CommodityLoan::stagePill($r); ?></td>
                        <td><a class="btn btn-sm btn-outline-primary" href="viewRequestDetails.php?applicationId=<?php echo (int) $r['application_id']; ?>">Open</a></td>
                      </tr>
                    <?php } ?>
                    </tbody>
                  </table>
                </div>
              <?php } ?>
            </div>
          </div>

        </div>
        <?php echo $view->subFooter('../'); ?>
