<?php 
require_once('../config/classes/View.php');
require_once('../config/classes/User.php');
require_once('../config/classes/MemberG1.php');
require_once('../config/classes/Commodity.php');

$view = new View();
$user = new User();
$members = new MemberG1();
$commodity = new Commodity();

$proposed_members = $members->getRequesUpdateSavings();

// $user->is_authenticated('auth/');
// $user->isICT('../auth/logout.php');
// $payment = $putme->getPaymentInfoArray($_SESSION['username']);

echo $view->subHeader('../');
?>

<!-- normalize data-table CSS ============================================ -->
<link rel="stylesheet" href="../../css/data-table/bootstrap-table.css">
<link rel="stylesheet" href="../../css/data-table/bootstrap-editable.css">

<!-- partial:partials/_navbar.html -->
<?php echo $view->subPartialNav('../')?>
<!-- partial -->

<div class="container-fluid page-body-wrapper">
    <!-- partial:partials/_sidebar.html -->
    <?php echo $view->SecretarySideNav()?>
    <!-- partial -->

    <div class="main-panel">
        <div class="content-wrapper">
            <div class="row">
                <div class="col-md-12 grid-margin">
                    <div class="d-flex1 justify-content-between flex-wrap">
                        <div class="d-flex1 align-items-end1 card flex-wrap">
                            <div class="mr-md-10 card-body mr-xl-10">
                                <h2>Secretary-General ACCOUNT</h2>
                                <div class="card-header py-3">
                                    <div class="d-flex justify-content-center align-items-center">
                                        <h6 class="m-0 font-weight-bold text-primary text-nowrap">View Commodity Type</h6>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="responsive">
                                        <div class="msg"></div> 
                                        <?php echo $commodity->getItemTypesTable(); ?>
                                    </div>
                                </div>
                            </div>                  
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- content-wrapper ends -->
    </div>
</div>

<!-- DATA TABLE JS ============================================ -->
<script src="../../js/data-table/bootstrap-table.js"></script>
<script src="../../js/data-table/tableExport.js"></script>
<script src="../../js/data-table/bootstrap-table-editable.js"></script>
<script src="../../js/data-table/bootstrap-editable.js"></script>
<script src="../../js/data-table/bootstrap-table-resizable.js"></script>
<script src="../../js/data-table/colResizable-1.5.source.js"></script>
<script src="../../js/data-table/bootstrap-table-export.js"></script>
<script src="../../js/data-table/data-table-active.js"></script>

<!-- jQuery script to filter the table rows (if needed) -->
<!-- Your custom JS for modal goes below -->

<!-- Edit Item Type Modal (PLACE THIS JUST BEFORE </body>) -->
<div class="modal fade" id="type-modal" tabindex="-1" role="dialog" aria-labelledby="type-modal-title" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="type-modal-title">Edit Item Type</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="update-item-type">
        <div class="modal-body">
          <input type="hidden" name="type_id" id="type_id">
          <div class="form-group">
            <label for="type_name">Type Name</label>
            <input type="text" class="form-control" name="type_name" id="type_name" required>
          </div>
          <div class="form-group">
            <label for="description">Description</label>
            <textarea class="form-control" name="description" id="description" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="save-type-btn">
            <span class="spinner-border spinner-border-sm d-none" id="save-spinner" role="status" aria-hidden="true"></span>
            Update Item Type
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Custom JavaScript for Modal & AJAX -->
<script>
$(document).ready(function() {
    // Open modal and populate form on edit click
    $(document).on("click", ".edit-item-type", function(e) {
        var type_id = $(this).data("id");

        $.ajax({
            url: "../../ajax/commodity_ajax.php",
            method: "POST",
            data: {"action":"get_item_type", "type_id": type_id},
            dataType: "json",
            success: function(response) {
                if (response.success && response.data) {
                    var type = response.data;
                    $("#type_id").val(type.type_id);
                    $("#type_name").val(type.type_name);
                    $("#description").val(type.description);
                    $("#type-modal-title").text("Edit Item Type");
                    $("#type-modal").modal("show");
                } else {
                    $(".msg").html('<div class="alert alert-danger">Failed to load item type.</div>').fadeIn().delay(3000).fadeOut();
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                $(".msg").html('<div class="alert alert-danger">An error occurred while loading data.</div>').fadeIn();
            }
        });
    });

    // Handle form submission for updating item type
    $(document).on("submit", "#update-item-type", function(e) {
        e.preventDefault();

        var formData = $(this).serialize();
        var saveBtn = $("#save-type-btn");
        var spinner = $("#save-spinner");

        saveBtn.prop("disabled", true);
        spinner.removeClass("d-none");

        $.ajax({
            url: "../../ajax/commodity_ajax.php",
            method: "POST",
            data: formData + "&action=update_item_type",
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    $("#type-modal").modal("hide");
                    $(".msg").html('<div class="alert alert-success">' + response.message + '</div>').fadeIn().delay(3000).fadeOut();
                    
                    setTimeout((function(){  location.reload();  }), 1000);

                    // Optional: Refresh table if using bootstrap-table
                    // $('#item-types-table').bootstrapTable('refresh');
                } else {
                    $(".msg").html('<div class="alert alert-danger">' + response.message + '</div>').fadeIn();
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                $(".msg").html('<div class="alert alert-danger">An error occurred while saving. Please try again.</div>').fadeIn();
            },
            complete: function() {
                saveBtn.prop("disabled", false);
                spinner.addClass("d-none");
            }
        });
    });
});
</script>

<!-- Footer (includes closing body/html) -->
<?php echo $view->subFooter('../'); ?>