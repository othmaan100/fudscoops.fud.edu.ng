<?php
    // One commodity request (CommodityLoan::detailsPage)
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    require_once('../config/classes/CommodityLoan.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../auth/') || !User::isBur('../auth/logout.php')) {
        exit();
    }

    echo $view->subHeader('../');
?>
    <?php echo $view->subPartialNav('../'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->treasurerSideNav(); ?>
      <div class="main-panel">
        <div class="content-wrapper">
          <?php CommodityLoan::detailsPage(isset($_GET['applicationId']) ? (int) $_GET['applicationId'] : 0, 'treasurer'); ?>

        </div>
        <?php echo $view->subFooter('../'); ?>
