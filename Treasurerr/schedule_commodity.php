<?php
require_once('../config/classes/View.php');
require_once('../config/classes/DB.php');
$view = new View();
?>

<?php echo $view->subHeader('../'); ?>
<!-- partial:partials/_navbar.html -->
<?php echo $view->subPartialNav('../'); ?>
<!-- partial -->
<div class="container-fluid page-body-wrapper">
    <!-- partial:partials/_sidebar.html -->
    <?php echo $view->SecretarySideNav(); ?>
    
    <!-- partial -->
    <div class="main-panel">
        <div class="content-wrapper">
            <div class="row">
                <div class="col-md-12 grid-margin">
                    <div class="d-flex justify-content-between flex-wrap">
                        <div class="d-flex align-items-end card flex-wrap">
                            <div class="mr-md-10 card-body mr-xl-10">
                                <h2>Commodity Supply Schedule</h2>
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Create New Commodity Schedule</h6>
                                </div>
                                <div class="card-body">
                                    <!-- Commodity Scheduling Form -->
                                    <form action="commodity_schedule_insert.php" method="POST">
                                        <div class="form-group">
                                            <label for="commodity">Select Commodity</label>
                                            <select class="form-control" id="commodity" name="commodity_id">
                                                <?php echo $view->loadAvailableCommodities(); ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="startDate">Start Date</label>
                                            <input type="date" class="form-control" id="startDate" name="start_date" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="endDate">End Date</label>
                                            <input type="date" class="form-control" id="endDate" name="end_date" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="status">Is Active</label>
                                            <select class="form-control" id="status" name="is_active">
                                                <option value="1">Active</option>
                                                <option value="0">Inactive</option>
                                            </select>
                                        </div>

                                        <button type="submit" class="btn btn-primary">Create Schedule</button>
                                    </form>
                                </div>
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
