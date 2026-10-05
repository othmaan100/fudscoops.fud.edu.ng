<?php
require_once('config/classes/User.php');
require_once('config/classes/DB.php');

$user = new User();
$db = new DB();


$user->is_authenticated('auth/');
?>
<!doctype html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang=""> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" lang=""> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" lang=""> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang=""> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>STAFF PORTAL - FUD | Change Password</title>
    <meta name="description" content="JISMA Project FUD">
    <meta name="viewport" content="width=device-width, initial-scale=1">


    <link rel="stylesheet" href="vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="vendors/base/vendor.bundle.base.css">
    <!-- endinject -->
    <!-- plugin css for this page -->
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/login.css">
    <!-- endinject -->
    <link rel="shortcut icon" href="../images/favicon.png" />



</head>

<body>
    <!-- Left Panel -->
    <!-- /#left-panel -->
    <!-- Right Panel -->
    <div  class="container">
        <!-- Header-->
        <!-- /#header -->
        <!-- Content -->
        <br/><br/>
        <div class="row ch">
            <div class="col-lg-3 col-md-3"></div>
            <div class="col-lg-6 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="stat-widget-five">
                            <code><b>FUD STAFF COOPERATIVE PORTAL | CHANGE PASSWORD</b></code>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                    <div class="error-pagewrap">
                        <div class="error-page-int">
                            <div class="text-center m-b-md custom-login">
                            <img src="images/logo.png" style="display:block;margin-left:auto;margin-right:auto;width:25%;" class="col-" alt="logo">
                            </div>
                            <div class="content-error">
                                <div class="hpanel">
                                    <div class="panel-body">
                                    <div class="stat-widget-five">
                            <div class="login-wrap">
                                <div class="err_msg">
                                    <?php 
                                        if (isset($_POST['saveChanges'])) {
                                            $user_id = $db->cleanData($_POST['id']);
                                             $current = $db->cleanData($_POST['cpass']);
                                            $new = $db->cleanData($_POST['npass']);
                                            $confirm = $db->cleanData($_POST['cnpass']);
                                            echo $user->changePassword($user_id,$current,$new,$confirm);

                                        }
                                    ?>
                                </div>
                                <form action="change_password.php" method="POST" name="form-nature" id="changePassword">
                                    <div class="form-group">
                                        <label for="class_name" class="control-label mb-1">Sp. No/Jp. No/</label>
                                        <input id="cpass" name="cpass" required type="password" class="form-control" aria-required="true" aria-invalid="false"/>
                                    </div>
                                    <div class="form-group">
                                        <label for="class_name" class="control-label mb-1">New Password</label>
                                        <input id="npass" name="npass" required type="password" class="form-control" aria-required="true" aria-invalid="false"/>
                                    </div>
                                    <div class="form-group">
                                        <label for="cnpass" class="control-label mb-1">Confirm New Password</label>
                                        <input id="cnpass" name="cnpass" required type="password" class="form-control" aria-required="true" aria-invalid="false"/>
                                        <input id="id" name="id" hidden="hidden" type="text" value="<?php echo $_SESSION['user_id']?>" class="form-control" aria-required="true" aria-invalid="false"/>
                                    </div>
                                    <div>
                                        <button id="saveChanges" name="saveChanges" type="submit" class="btn btn-lg btn-info btn-block">
                                            <span id="payment-button-amount">Save</span>
                                        </button>
                                        <span hidden="hidden" class="text-center">Please wait...</span>
                                    </div>
                                </form>
                            </div>
                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center login-footer">
                            </div>
                        </div>   
                    </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-3"></div>
        </div>



        <div class="clearfix"></div>
        <!-- Footer -->
        <footer class="site-footer text-center">
            <div class="footer-inner bg-white">
                <div class="row">
                    

                </div>
            </div>
        </footer>    <!-- /#add-category -->
    </div>
    
    <!-- jquery
		============================================ -->
        <script src="../vendors/base/vendor.bundle.base.js"></script>
        <!-- endinject -->
        <!-- inject:js -->
        <script src="../js/off-canvas.js"></script>
        <script src="../js/hoverable-collapse.js"></script>
        <script src="../js/custom.js"></script>
        <!-- endinject -->


</body>
</html>
