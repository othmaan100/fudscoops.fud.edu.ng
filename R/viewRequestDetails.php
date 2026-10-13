<?php
    // One of the member's commodity requests (CommodityLoan::detailsPage, member view: own requests only)
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    require_once('../config/classes/CommodityLoan.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../auth/') || !User::isStaff('../auth/logout.php')) {
        exit();
    }
    $memberId = $user->getMemberId($user->getEmployeeId($_SESSION['username']));
    echo $view->subHeader('../');
?>
    <?php echo $view->subPartialNav('../'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->staffSideNav(); ?>
      <div class="main-panel">
        <div class="content-wrapper">
          <?php CommodityLoan::detailsPage(isset($_GET['applicationId']) ? (int) $_GET['applicationId'] : 0, 'member', $memberId); ?>
        </div>
        <?php echo $view->subFooter('../'); ?>
