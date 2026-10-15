<?php
    // Guarantor undertaking: the member asked to guarantee a loan accepts or declines it
    // (MemberG3::guarantorUndertakingPage; only the loan's guarantor can open it)
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    require_once('../config/classes/MemberG3.php');
    $view = new View();
    $user = new User();
    $member = new MemberG3();
    if (!User::is_authenticated('../auth/') || !User::isStaff('../auth/logout.php')) {
        exit();
    }
    $employeeId = $user->getEmployeeId($_SESSION['username']);
    echo $view->subHeader('../');
?>
    <?php echo $view->subPartialNav('../'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->staffSideNav(); ?>
      <div class="main-panel">
        <div class="content-wrapper">
          <?php $member->guarantorUndertakingPage(isset($_GET['loan_id']) ? (int) $_GET['loan_id'] : 0, $employeeId); ?>
        </div>
        <?php echo $view->subFooter('../'); ?>
