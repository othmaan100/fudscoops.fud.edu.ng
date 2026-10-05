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
                <!-- Schedule for All Staff -->
                <div class="col-lg-6 col-md-12 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Schedule Commodity for All Staff</h6>
                        </div>
                        <div class="card-body">
                            <form id="schedule-all">
                                <div class="form-group">
                                    <label for="commodity">Commodity Name</label>
                                    <?php echo $view->loadCommodityName(); ?>
                                </div>

                                <div class="form-group">
                                    <label for="startDate">Start Date</label>
                                    <input type="date" class="form-control" id="startDate" name="start_date" required>
                                </div>

                                <div class="form-group">
                                    <label for="endDate">End Date</label>
                                    <input type="date" class="form-control" id="endDate" name="end_date" required>
                                </div>

                                <button type="submit" class="btn btn-primary" rel="sform" id="schedule-all-btn">
                                    Schedule <i class="fa fa-history"></i>
                                </button>
                            </form>
                            <div id="msg-all" class="mt-3"></div>
                        </div>
                    </div>
                </div>

                <!-- Schedule for Individual Staff -->
                <div class="col-lg-6 col-md-12 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Schedule Commodity for Individual</h6>
                        </div>
                        <div class="card-body">
                            <form id="schedule-individual">
                                <div class="form-group">
                                    <label for="commodity-individual">Commodity Name</label>
                                    <?php echo $view->loadCommodityName(); ?>
                                </div>

                                <div class="form-group">
                                    <label for="startDate-individual">Start Date</label>
                                    <input type="date" class="form-control" id="startDate-individual" name="start_date" required>
                                </div>

                                <div class="form-group">
                                    <label for="endDate-individual">End Date</label>
                                    <input type="date" class="form-control" id="endDate-individual" name="end_date" required>
                                </div>

                                <div class="form-group">
                                    <label for="sfor">Schedule for:</label>
                                    <input type="text" placeholder="Staff Number" required name="sfor" id="sfor" class="form-control">
                                    <input type="hidden" value="individual" name="slevel">
                                </div>

                                <button type="submit" class="btn btn-primary" rel="sform2" id="schedule-individual-btn">
                                    Schedule <i class="fa fa-history"></i>
                                </button>
                            </form>
                            <div id="msg-individual" class="mt-3"></div>
                        </div>
                    </div>
                </div>
            </div> <!-- end row -->
        </div>
        <!-- content-wrapper ends -->
        <?php echo $view->subFooter('../'); ?>
    </div>
</div>