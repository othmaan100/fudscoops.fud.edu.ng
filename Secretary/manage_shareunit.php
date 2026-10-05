<?php 
require_once('../config/classes/View.php');
require_once('../config/classes/User.php');
require_once('../config/classes/MemberG2.php');

$view = new View();
$user = new User();
$member = new MemberG2();

$user->is_authenticated('auth/');
$user->isSecretary('../auth/logout.php');

$message = ''; // Initialize message variable

if (isset($_POST['add'])) {
    $message = $member->updateShareUnitAmount($_POST['share'], $_POST['price']);
}

echo $view->subHeader('../');
?>
<!-- partial:partials/_navbar.html -->
<?php echo $view->subPartialNav('../') ?>
<!-- partial -->
<div class="container-fluid page-body-wrapper">
    <!-- partial:partials/_sidebar.html -->
    <?php echo $view->SecretarySideNav() ?>
    <!-- partial -->
    <div class="main-panel">
        <div class="content-wrapper">
            <div class="row">
                <div class="col-md-12 grid-margin">
                    <div class="d-flex justify-content-between flex-wrap">
                        <div class="d-flex align-items-end card stretch-card flex-wrap">
                            <div class="mr-md-5 card-body mr-xl-5">
                                <h2>MANAGE SHARE UNIT AMOUNT</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <?php if (!empty($message)): ?>
                                    <div class="alert alert-info">
                                        <?php echo htmlspecialchars($message); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 card">
                                <form class="my-form form-vertical" method="post" action="manage_shareunit.php">
                                    <div class="row">
                                        <div class="col-md-12 col-sm-6 col-sx-12">
                                            <div class="form-group">
                                                <label for="exampleInputUsername1">SHARE NAME:</label>
                                                <input type="text" required name="share" class="form-control form-control-sm" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 col-sm-6 col-sx-12">
                                            <div class="form-group">
                                                <label for="exampleInputUsername1">AMOUNT:</label>
                                                <input type="number" required name="price" class="form-control form-control-sm" />
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-6 col-sx-12">
                                            <div class="form-group"><br/>
                                                <button type="submit" class="btn btn-md btn-danger" name="add" id="add">
                                                    ADD
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- content-wrapper ends -->
        <?php echo $view->subFooter('../'); ?>
    </div>
</div>
