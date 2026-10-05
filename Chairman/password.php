<?php
// --- Includes & setup ------------------------------------------------
require_once('../config/classes/View.php');
require_once('../config/classes/User.php');
require_once('../config/classes/MemberG2.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$view    = new View();
$user    = new User();
$members = new MemberG2();

// --- Authentication / role checks -----------------------------------
$user->is_authenticated('auth/');              // ensure logged in
$employeeid = isset($_SESSION['username']) ? $user->getEmployeeId($_SESSION['username']) : null;
$user->isChairman('../auth/logout.php');     // ensure the user is secretary (redirect if not)

// --- Handle Form Submission -----------------------------------------


// --- Render header (assumes subHeader outputs <head> and opening <body>) ---
echo $view->subHeader('../');
?>

<!-- Navigation / Sidebar -->
<?php echo $view->subPartialNav('../'); ?>
<div class="container-fluid page-body-wrapper">
  <?php echo $view->subSideNav(); ?>

  <div class="main-panel">
    <div class="content-wrapper">

      <!-- Page header -->
      <div class="row mb-3">
        <div class="col-md-12">
          <div class="card">
            <div class="card-body d-flex align-items-center justify-content-between">
              <h4 class="mb-0">Password Reset</h4>
              <?php if (!empty($employeeid)): ?>
                <small class="text-muted">Employee ID: <?php echo htmlspecialchars($employeeid, ENT_QUOTES); ?></small>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Form card -->
      <div class="row">
        <div class="col-md-6">
          <div class="card">
            <div class="card-body">

              <!-- Server message -->

              <form class="" role="form" id="password-reset" enctype="multipart/form-data">
                <!-- Label -->
                 
        
                    <div class="form-group mb-2">
                      <label for="sp" class="form-label">Enter the staff SP Number to reset password.</label>
                    </div>
    
                    <!-- Input -->
                    <div class="form-group mb-3">
                      <div style="max-width:420px;">
                        <input type="text" name="username" id="username" class="form-control form-control-sm" placeholder="Enter SP number" required>
                      </div>
                      
                    </div>
    
                    <!-- Button -->
                    <div class="form-group">
                      <button type="submit" name="reset" id="reset" class="btn btn-danger"> Reset </button>
                    </div>
               
                <br/>
                <div id="msg" class="row text-center">
                  <h3 id="loading" hidden><img src="../../img/loading.gif" alt=""> <i>Resetting password please wait...</i></h3>
                </div>
              </form>

            </div>
          </div>
        </div>
      </div>

    </div> <!-- content-wrapper ends -->

    <?php echo $view->subFooter('../'); ?>
  </div> <!-- main-panel ends -->
</div> <!-- page-body-wrapper ends -->
