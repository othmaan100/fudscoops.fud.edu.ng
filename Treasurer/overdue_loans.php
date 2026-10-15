<?php
    // Overdue loans: behind on their deductions (LoanControls::overdueLoans explains how this is worked out)
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    require_once('../config/classes/LoanControls.php');
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
        <div class="content-wrapper fud-dash">

          <div class="fud-hero">
            <div class="fud-hero-date">Loans</div>
            <h2><i class="mdi mdi-alert-circle"></i> Overdue Loans</h2>
            <p>Loans whose deductions are behind schedule. Only months for which deductions have been uploaded are counted.</p>
          </div>

          <div class="fud-section">
            <div class="fud-section-body">
              <?php echo LoanControls::overdueTable('view_loan_details.php'); ?>
            </div>
          </div>

        </div>
        <?php echo $view->subFooter('../'); ?>
