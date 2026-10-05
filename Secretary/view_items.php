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
                                        <?php echo $commodity->getItemsTable(); ?>
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
<!-- Edit Item Modal -->
<div class="modal fade" id="item-modal" tabindex="-1" role="dialog" aria-labelledby="item-modal-title" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="item-modal-title">Edit Commodity Item</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="update-item-form">
        <div class="modal-body">
          <input type="hidden" name="commodity_item_id" id="commodity_item_id">

          <div class="form-group">
            <label for="commodity_item">Item Name</label>
            <input type="text" class="form-control" name="commodity_item" id="commodity_item" required>
          </div>

          <div class="form-group">
            <label for="type_id">Item Type</label>
            <select class="form-control" name="type_id" id="type_id" required>
                <!-- Will be populated dynamically or preloaded -->
                <option value="">-- Select Type --</option>
                <!-- Example: <option value="1">Food</option> -->
            </select>
          </div>

          <div class="form-group">
            <label for="commodity_description">Description</label>
            <textarea class="form-control" name="commodity_description" id="commodity_description" rows="3"></textarea>
          </div>

          <div class="form-group">
            <label for="quantity">Available Quantity</label>
            <input type="number" class="form-control" name="quantity" id="quantity" min="0" step="1" required>
          </div>

          <div class="form-group">
            <label for="price_unit">Price per Unit (₦)</label>
            <input type="number" class="form-control" name="price_unit" id="price_unit" min="0" step="0.01" required>
          </div>

          <div class="form-group">
            <label for="max_senior_quantity">Max Senior Qty</label>
            <input type="number" class="form-control" name="max_senior_quantity" id="max_senior_quantity" min="0" step="1" required>
          </div>

          <div class="form-group">
            <label for="max_junior_quantity">Max Junior Qty</label>
            <input type="number" class="form-control" name="max_junior_quantity" id="max_junior_quantity" min="0" step="1" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="save-item-btn">
            <span class="spinner-border spinner-border-sm d-none" id="save-spinner" role="status" aria-hidden="true"></span>
            Update Item
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Add Quantity Modal -->
<div class="modal fade" id="add-quantity-modal" tabindex="-1" role="dialog" aria-labelledby="add-quantity-modal-title" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="add-quantity-modal-title">Add Quantity to <span id="item-name-placeholder">Item</span></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="add-quantity-form">
        <div class="modal-body">
          <input type="hidden" name="commodity_item_id" id="add_quantity_item_id">

          <div class="form-group">
            <label for="add_quantity">Quantity to Add</label>
            <input type="number" class="form-control" name="add_quantity" id="add_quantity" min="1" step="1" required>
            <small class="form-text text-muted">Enter the number of units to add to current stock.</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="add-quantity-btn">
            <span class="spinner-border spinner-border-sm d-none" id="add-quantity-spinner" role="status" aria-hidden="true"></span>
            Add Quantity
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Custom JavaScript for Modal & AJAX -->
<script>
$(document).ready(function() {

    // ======================
    // LOAD ITEM TYPES INTO DROPDOWN (optional enhancement)
    // ======================
    function loadItemTypes() {
        $.ajax({
            url: "../../ajax/commodity_ajax.php",
            method: "POST",
            data: { "action": "get_types" },
            dataType: "json",
            success: function(response) {
                if (response.success && Array.isArray(response.data)) {
                    let select = $("#type_id");
                    select.empty().append('<option value="">-- Select Type --</option>');
                    response.data.forEach(type => {
                        select.append(`<option value="${type.type_id}">${type.type_name}</option>`);
                    });
                }
            },
            error: function() {
                console.warn("Could not load item types.");
            }
        });
    }

    // Load types when page loads
    loadItemTypes();

    // ======================
    // OPEN MODAL & POPULATE FORM
    // ======================
    $(document).on("click", ".edit-item", function(e) {
        e.preventDefault();

        // Get data from button
        let itemId = $(this).data("id");
        let typeName = $(this).data("name");
        let description = $(this).data("description");
        let quantity = $(this).data("quantity");
        let priceUnit = $(this).data("price-unit");
        let maxSenior = $(this).data("max-senior");
        let maxJunior = $(this).data("max-junior");
        let typeId = $(this).data("type-id");

        // Populate form
        $("#commodity_item_id").val(itemId);
        $("#commodity_item").val($(this).data("item-name"));
        $("#commodity_description").val(description);
        $("#quantity").val(quantity);
        $("#price_unit").val(priceUnit);
        $("#max_senior_quantity").val(maxSenior);
        $("#max_junior_quantity").val(maxJunior);
        $("#type_id").val(typeId);

        // Set modal title
        $("#item-modal-title").text("Edit Item: " + typeName);

        // Show modal
        $("#item-modal").modal("show");
    });

    // ======================
    // HANDLE FORM SUBMISSION
    // ======================
    $(document).on("submit", "#update-item-form", function(e) {
        e.preventDefault();

        let formData = $(this).serialize() + "&action=update_item";
        let saveBtn = $("#save-item-btn");
        let spinner = $("#save-spinner");

        saveBtn.prop("disabled", true);
        spinner.removeClass("d-none");

        $.ajax({
            url: "../../ajax/commodity_ajax.php",
            method: "POST",
            data: formData,
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    $("#item-modal").modal("hide");
                    $(".msg").html('<div class="alert alert-success">' + response.message + '</div>').fadeIn().delay(3000).fadeOut();
                    
                    // Reload page after 1 second to reflect changes
                    setTimeout(function() { location.reload(); }, 1000);
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
    
    // ======================
// OPEN ADD QUANTITY MODAL
// ======================
$(document).on("click", ".add-quantity", function(e) {
    e.preventDefault();

    let itemId = $(this).data("id");
    let itemName = $(this).data("item-name");

    $("#add_quantity_item_id").val(itemId);
    $("#item-name-placeholder").text(itemName);
    $("#add-quantity-modal-title").text("Add Quantity to " + itemName);
    $("#add_quantity").val("").focus(); // Clear and focus input

    $("#add-quantity-modal").modal("show");
});

// ======================
// HANDLE ADD QUANTITY FORM SUBMISSION
// ======================
$(document).on("submit", "#add-quantity-form", function(e) {
    e.preventDefault();

    let formData = $(this).serialize() + "&action=add_quantity";
    let btn = $("#add-quantity-btn");
    let spinner = $("#add-quantity-spinner");

    btn.prop("disabled", true);
    spinner.removeClass("d-none");

    $.ajax({
        url: "../../ajax/commodity_ajax.php",
        method: "POST",
        data: formData,
        dataType: "json",
        success: function(response) {
            if (response.success) {
                $("#add-quantity-modal").modal("hide");
                $(".msg").html('<div class="alert alert-success">' + response.message + '</div>').fadeIn().delay(3000).fadeOut();

                // ✅ Update quantity in table without reload
                let itemId = $("#add_quantity_item_id").val();
                let newQty = response.new_quantity;
                $('td.quantity-td[data-item-id="' + itemId + '"]').text(newQty);

            } else {
                $(".msg").html('<div class="alert alert-danger">' + response.message + '</div>').fadeIn();
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            $(".msg").html('<div class="alert alert-danger">An error occurred while adding quantity.</div>').fadeIn();
        },
        complete: function() {
            btn.prop("disabled", false);
            spinner.addClass("d-none");
        }
    });
});

});
</script>

<!-- Footer (includes closing body/html) -->
<?php echo $view->subFooter('../'); ?>