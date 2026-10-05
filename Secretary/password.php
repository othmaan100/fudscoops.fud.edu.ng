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
$user->isSecretary('../auth/logout.php');     // ensure the user is secretary (redirect if not)

// --- Handle Form Submission -----------------------------------------
$msg = '';
if (isset($_POST['update'])) {
    $username = trim($_POST['sp']);  // SP number input (username in `user` table)

    if (!empty($username)) {
        $result = $members->resetPasswordByUsername($username);

        if ($result === 1) {
            $msg = "<div class='alert alert-success'>Password for <b>$username</b> has been reset successfully.</div>";
        } elseif ($result === -1) {
            $msg = "<div class='alert alert-danger'>Error: Could not reset password. Username may not exist.</div>";
        } else {
            $msg = "<div class='alert alert-warning'>Unexpected error occurred. Please try again.</div>";
        }
    } else {
        $msg = "<div class='alert alert-warning'>Please enter a valid SP Number.</div>";
    }
}

// --- Render header (assumes subHeader outputs <head> and opening <body>) ---
echo $view->subHeader('../');
?>

<!-- Navigation / Sidebar -->
<?php echo $view->subPartialNav('../'); ?>
<div class="container-fluid page-body-wrapper">
  <?php echo $view->SecretarySideNav(); ?>

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
              <?php if (!empty($msg)) echo $msg; ?>

              <form method="post" action="password.php" class="my-form" autocomplete="off" novalidate>
                <!-- Label -->
                <div class="form-group mb-2">
                  <label for="sp" class="form-label">SP Number</label>
                </div>

                <!-- Input -->
                <div class="form-group mb-3">
                  <div style="max-width:420px;">
                    <input
                      type="text"
                      name="sp"
                      id="sp"
                      class="form-control form-control-sm"
                      placeholder="Enter SP number"
                      required
                      maxlength="50"
                      aria-describedby="spHelp"
                    >
                  </div>
                  <small id="spHelp" class="form-text text-muted">Enter the staff SP number to reset password.</small>
                </div>

                <!-- Button -->
                <div class="form-group">
                  <button
                    type="submit"
                    name="update"
                    id="update"
                    class="btn btn-danger"
                  >
                    Reset
                  </button>
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
