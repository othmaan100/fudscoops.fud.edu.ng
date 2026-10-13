<?php
    // Commodity requests (workflow: config/classes/CommodityLoan.php)
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    require_once('../config/classes/CommodityLoan.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../auth/') || !User::isBur('../auth/logout.php')) {
        exit();
    }
    $stages = ['', 'pending', 'recommended', 'authorized', 'supplied', 'repaid', 'declined'];
    $stage = isset($_GET['stage']) && in_array($_GET['stage'], $stages, true) ? $_GET['stage'] : '';

    echo $view->subHeader('../');
?>
    <?php echo $view->subPartialNav('../'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->treasurerSideNav(); ?>
      <div class="main-panel">
        <div class="content-wrapper">
          <div class="fud-dash">
            <div class="fud-hero">
              <div class="fud-hero-date">Commodity</div>
              <h2><i class="mdi mdi-cart-outline"></i> Commodity Requests</h2>
              <p>Review requests, record supply and deductions.</p>
            </div>
            <div class="fud-section"><div class="fud-section-body">
              <?php echo CommodityLoan::requestsTable($stage); ?>
            </div></div>
          </div>
        </div>
        <?php echo $view->subFooter('../'); ?>
