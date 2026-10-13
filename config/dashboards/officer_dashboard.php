<?php
// Dashboard shared by Chairman/index.php, Treasurer/index.php and Secretary/index.php.
// The including page checks the login and sets $dashRole ('chairman', 'treasurer' or 'secretary').
require_once(__DIR__ . '/../classes/View.php');
require_once(__DIR__ . '/../classes/User.php');
require_once(__DIR__ . '/../classes/Dashboard.php');

$roles = [
    'chairman'  => ['title' => 'Chairman', 'sidebar' => 'subSideNav', 'actions' => [
                        ['member_list.php', 'mdi-account-plus', 'Membership Registrations'],
                        ['loans.php', 'mdi-cash-multiple', 'Loans'],
                        ['share_holders_view.php', 'mdi-chart-pie', 'Share Holders'],
                        ['reports.php', 'mdi-file-chart', 'Generate Report']]],
    'treasurer' => ['title' => 'Treasurer', 'sidebar' => 'treasurerSideNav', 'actions' => [
                        ['uploadDeposit.php', 'mdi-cloud-upload', 'Upload Savings'],
                        ['member_list.php', 'mdi-account-plus', 'Membership Registrations'],
                        ['savings_settings.php', 'mdi-tune', 'Savings Settings'],
                        ['reports.php', 'mdi-file-chart', 'Generate Report']]],
    'secretary' => ['title' => 'Secretary-General', 'sidebar' => 'SecretarySideNav', 'actions' => [
                        ['manage_share_purchase.php', 'mdi-chart-pie', 'Share Purchases'],
                        ['view_staff.php', 'mdi-account-multiple', 'View Staff'],
                        ['view_commodity_request.php', 'mdi-cart-outline', 'Commodity Requests'],
                        ['reports.php', 'mdi-file-chart', 'Generate Report']]],
];
if (!isset($dashRole, $roles[$dashRole])) {
    exit();
}
$role = $roles[$dashRole];

$view = new View();
$user = new User();
$dashboard = new Dashboard();
$username = $_SESSION['username'];
$name = Dashboard::displayName($user, $username, $role['title']);
$tasks = $dashboard->pendingFor($dashRole);
$waiting = array_sum(array_column($tasks, 1));
$requests = $dashboard->personalRequests($user->getEmployeeId($username));
$totals = $dashboard->cooperativeTotals();
list($greeting, $today) = Dashboard::greeting();
$sidebar = $role['sidebar'];

echo $view->subHeader('../');
?>
    <!-- partial:partials/_navbar.html -->
    <?php echo $view->subPartialNav('../'); ?>
    <!-- partial -->
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->$sidebar(); ?>
      <div class="main-panel">
        <div class="content-wrapper fud-dash">

          <!-- Welcome -->
          <div class="fud-hero">
            <div class="fud-hero-date"><?php echo $today; ?></div>
            <h2><?php echo $greeting . ', ' . htmlspecialchars($name); ?></h2>
            <p><?php echo htmlspecialchars($username); ?> <span class="fud-hero-chip"><?php echo $role['title']; ?></span>
              &nbsp;·&nbsp; <?php echo $waiting > 0 ? number_format($waiting) . ' item(s) waiting for you' : 'Nothing waiting for you'; ?></p>
            <div class="fud-hero-actions">
              <?php foreach ($role['actions'] as $action) { ?>
                <a href="<?php echo $action[0]; ?>"><i class="mdi <?php echo $action[1]; ?>"></i> <?php echo $action[2]; ?></a>
              <?php } ?>
            </div>
          </div>

          <!-- Personal requests (officers can also be guarantors or receive shares) -->
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

          <!-- Work queue -->
          <div class="fud-section">
            <div class="fud-section-head"><h5>Needs Your Attention</h5></div>
            <div class="fud-section-body">
              <?php if ($waiting === 0) { ?>
                <div class="fud-all-clear"><i class="mdi mdi-check-circle"></i> All clear. Nothing is waiting for your decision.</div>
              <?php } ?>
              <div class="fud-tasks">
                <?php foreach ($tasks as $task) { ?>
                  <a class="fud-task <?php echo $task[1] > 0 ? 'has-items' : ''; ?>" href="<?php echo $task[3]; ?>">
                    <i class="mdi <?php echo $task[2]; ?>"></i>
                    <span class="fud-task-label"><?php echo htmlspecialchars($task[0]); ?></span>
                    <span class="fud-task-count"><?php echo number_format($task[1]); ?></span>
                  </a>
                <?php } ?>
              </div>
            </div>
          </div>

          <!-- Cooperative overview -->
          <div class="fud-section">
            <div class="fud-section-head"><h5>Cooperative Overview</h5><a href="reports.php">Full reports</a></div>
            <div class="fud-section-body">
              <div class="fud-stats mb-0">
                <div class="fud-stat">
                  <div class="fud-stat-icon fud-blue"><i class="mdi mdi-account-group"></i></div>
                  <div><div class="fud-stat-label">Members</div>
                    <div class="fud-stat-value"><?php echo number_format($totals['members']); ?></div></div>
                </div>
                <div class="fud-stat">
                  <div class="fud-stat-icon fud-green"><i class="mdi mdi-wallet"></i></div>
                  <div><div class="fud-stat-label">Savings Balance</div>
                    <div class="fud-stat-value">₦<?php echo number_format($totals['savings_balance']); ?></div>
                    <div class="fud-stat-sub">all members, after withdrawals</div></div>
                </div>
                <div class="fud-stat">
                  <div class="fud-stat-icon fud-purple"><i class="mdi mdi-chart-pie"></i></div>
                  <div><div class="fud-stat-label">Share Capital</div>
                    <div class="fud-stat-value">₦<?php echo number_format($totals['share_capital']); ?></div></div>
                </div>
                <div class="fud-stat">
                  <div class="fud-stat-icon fud-amber"><i class="mdi mdi-cash-multiple"></i></div>
                  <div><div class="fud-stat-label">Loans Outstanding</div>
                    <div class="fud-stat-value">₦<?php echo number_format($totals['loans_outstanding']); ?></div>
                    <div class="fud-stat-sub"><?php echo number_format($totals['open_loans']); ?> approved loan(s)</div></div>
                </div>
                <?php if ($totals['last_month_label'] !== '') { ?>
                  <div class="fud-stat">
                    <div class="fud-stat-icon fud-teal"><i class="mdi mdi-calendar-check"></i></div>
                    <div><div class="fud-stat-label">Savings Deducted, <?php echo $totals['last_month_label']; ?></div>
                      <div class="fud-stat-value">₦<?php echo number_format($totals['last_month_total']); ?></div>
                      <div class="fud-stat-sub">latest deduction month</div></div>
                  </div>
                <?php } ?>
              </div>
            </div>
          </div>

        </div>
        <!-- content-wrapper ends -->
        <?php echo $view->subFooter('../'); ?>
