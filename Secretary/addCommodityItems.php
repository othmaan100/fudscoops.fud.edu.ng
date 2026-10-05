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
    
    // Get commodity_name_id from URL parameter
    $commodity_name_id = $_GET['commodity_name_id'] ?? '';
    
    // Optional: Validate that commodity_name_id exists
    if (empty($commodity_name_id)) {
        die('Error: Commodity Name ID is required');
    }
    
    echo $view->subHeader('../');
?>

<!-- normalize data-table CSS -->
<link rel="stylesheet" href="../../css/data-table/bootstrap-table.css">
<link rel="stylesheet" href="../../css/data-table/bootstrap-editable.css">
<!-- Add Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- partial:partials/_navbar.html -->
<?php echo $view->subPartialNav('../')?>

<div class="container-fluid page-body-wrapper">
    <?php echo $view->SecretarySideNav()?>
    
    <div class="main-panel">
        <div class="content-wrapper">
            <div class="row">
                <div class="col-md-12 grid-margin">
                    <div class="d-flex1 justify-content-between flex-wrap1">
                        <div class="d-flex1 align-items-end1 card flex-wrap1">
                            <div class="mr-md-10 card-body mr-xl-10">
                                <h2>Secretary-General ACCOUNT</h2>
                                <div class="card-header py-3">
                                    <div class="d-flex justify-content-center align-items-center">
                                        <h6 class="m-0 font-weight-bold text-primary text-nowrap">Add Items to Commodity Supply</h6>
                                    </div>
                                </div>
                                
                                <div class="card-body">
                                    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                                        <!-- Form Card -->
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="card-title">Commodity Supply <?php echo htmlspecialchars($commodity_name_id); ?> - Add Items</h5>
                                            </div>
                                            <div class="card-body">
                                                <form action="" method="POST">
                                                    <!-- Item Selection -->
                                                    <div class="mb-3">
                                                        <label for="item_id" class="form-label">Item:</label>
                                                        <select name="item_id" id="item_id" class="form-control select2">
                                                            <option value="">-- Select Item --</option>
                                                            <?php 
                                                            $selectedProductId = $_POST['item_id'] ?? null;
                                                            echo $commodity->getAllItemsDropdown($selectedProductId); 
                                                            ?>
                                                        </select>
                                                    </div>

                                                    <!-- Quantity Input -->
                                                    <div class="mb-3">
                                                        <label for="quantity" class="form-label">Quantity</label>
                                                        <input type="number" class="form-control" name="quantity" id="quantity" placeholder="Enter quantity" min="1">
                                                    </div>

                                                    <!-- Button -->
                                                    <div class="mb-3">
                                                        <p class="text-danger" id="errorTxt"></p>
                                                        <button type="button" class="btn btn-primary w-100" id="addToSupply">
                                                            <i class="bi bi-plus-circle"></i> Add Item to Commodity Supply
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- Items Table Card -->
                                        <div class="card mt-3">
                                            <div class="card-header">
                                                <h5 class="card-title">Items for Commodity: <?php echo htmlspecialchars($commodity_name_id); ?></h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-striped table-hover">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>S/N</th>
                                                                <th>Item</th>
                                                                <th>Quantity</th>
                                                                <th>Unit Cost</th>
                                                                <th>Total</th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="tb"></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </main>

                                    <!-- Commodity Items Modal -->
                                    <div class="modal fade" id="commodityItemsModal" tabindex="-1" aria-labelledby="commodityItemsModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-xl">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="commodityItemsModalLabel">Commodity Items</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="table-responsive">
                                                        <table class="table table-hover" id="commodityItemsTable">
                                                            <thead>
                                                                <tr>
                                                                    <th>Commodity</th>
                                                                    <th>Available Qty</th>
                                                                    <th>Unit</th>
                                                                    <th>Price</th>
                                                                    <th>Min/Max per Member</th>
                                                                    <th>Actions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <!-- Items will be loaded via AJAX -->
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
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

<!-- Add jQuery first -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Add Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Data table scripts -->
<script src="../../js/data-table/bootstrap-table.js"></script>
<script src="../../js/data-table/data-table-active.js"></script>
<script src="../../js/data-table/tableExport.js"></script>
<script src="../../js/data-table/bootstrap-table-editable.js"></script>
<script src="../../js/data-table/bootstrap-editable.js"></script>
<script src="../../js/data-table/bootstrap-table-resizable.js"></script>
<script src="../../js/data-table/colResizable-1.5.source.js"></script>
<script src="../../js/data-table/bootstrap-table-export.js"></script>

<script>
$(document).ready(function() {
    // Initialize Select2
    $('#item_id').select2({
        placeholder: "-- Select Item --",
        allowClear: true
    });
});

// Global variables
let itemsListArray = [];

function reset() {
    itemsListArray = [];
    $("#tb").html("");
}

$(document).on("click", "#addToSupply", function() {
    $('#errorTxt').text(""); // Clear previous errors

    // Get values from form
    const selectedOption = $('#item_id').find(":selected");
    const itemId = $('#item_id').val();
    const quantity = $('#quantity').val();

    console.log('Selected Item ID:', itemId);

    // Get item details
    const itemNameFull = selectedOption.text();
    const itemName = itemNameFull.includes('(') ? itemNameFull.split(' (')[0] : itemNameFull;
    const unitPrice = selectedOption.data("unit-price");
    const stock = selectedOption.data("item-qty");

    console.log('Item Details:', {
        itemName: itemName,
        unitPrice: unitPrice,
        stock: stock
    });

    // Validate inputs
    const numQuantity = parseInt(quantity);
    const numStock = parseInt(stock);

    if (!itemId) {
        $('#errorTxt').text("Please select an item.");
        return;
    }

    const isExisting = itemsListArray.some(item => item.itemId === itemId);
    if (isExisting) {
        $('#errorTxt').text("This item has already been added to the commodity supply.");
        return;
    }

    if (isNaN(numQuantity) || numQuantity < 1) {
        $('#errorTxt').text("Quantity must be at least 1.");
        return;
    }

    if (numStock && numQuantity > numStock) {
        $('#errorTxt').text(`Quantity exceeds stock. Only ${numStock} available.`);
        return;
    }

    // Add item to array
    itemsListArray.push({
        itemId: itemId,
        itemName: itemName,
        quantity: numQuantity,
        unitPrice: parseFloat(unitPrice) || 0
    });

    console.log('Items Array:', itemsListArray);

    // Rebuild table
    renderTable();

    // Clear form
    $('#quantity').val('');
    $('#item_id').val('').trigger('change');
});

function renderTable() {
    let tableBody = "";
    let sn = 0;
    let totalAmount = 0;

    itemsListArray.forEach(item => {
        sn++;
        const itemTotal = item.unitPrice * item.quantity;
        totalAmount += itemTotal;
        
        tableBody += `
            <tr>
                <td>${sn}</td>
                <td>${item.itemName}</td>
                <td>${item.quantity}</td>
                <td>&#8358;${item.unitPrice.toLocaleString()}</td>
                <td>&#8358;${itemTotal.toLocaleString()}</td>
                <td><button class="btn btn-danger btn-sm removeSupplyItem" data-item-id="${item.itemId}" title="Remove Item">Remove</button></td>
            </tr>
        `;
    });
    
    if (itemsListArray.length > 0) {
        tableBody += `
            <tr class="table-info">
                <td colspan="4" class="text-end"><strong>Total Amount:</strong></td>
                <td><strong>&#8358;${totalAmount.toLocaleString()}</strong></td>
                <td></td>
            </tr>
            <tr>
                <td colspan="6" class="text-center">
                    <button class='btn btn-success btn-lg' id="btnSaveSupply">
                        <i class="bi bi-save"></i> Save Commodity Supply
                    </button>
                </td>
            </tr>
        `;
    }

    $("#tb").html(tableBody);
}

$(document).on("click", ".removeSupplyItem", function(e) {
    e.preventDefault();
    const itemId = $(this).attr("data-item-id");
    
    // Remove item from array
    itemsListArray = itemsListArray.filter((item) => item.itemId !== itemId);
    
    // Re-render table
    renderTable();
    
    console.log('Item removed. Remaining items:', itemsListArray);
});

$(document).on("click", "#btnSaveSupply", function(e) {
    e.preventDefault();
    
    if (itemsListArray.length === 0) {
        alert('Please add at least one item before saving.');
        return;
    }
    
    const button = $(this);
    const originalText = button.html();
    button.html('<i class="spinner-border spinner-border-sm me-2"></i>Saving, please wait...').prop('disabled', true);
    
    const commodityNameId = <?php echo json_encode($commodity_name_id); ?>;

    if (confirm("Are you sure you want to save these items to the commodity supply?")) {
        $.ajax({
            url: "../../ajax/commodity_ajax.php",
            method: "POST",
            dataType: "json",
            data: {
                "items": JSON.stringify(itemsListArray), 
                "commodity_name_id": commodityNameId, 
                "action": 'add_commodity_supply_items'
            },
            success: function (response) {
                console.log('AJAX Success Response:', response);
                
                try {
                    if (response.success === true || response.status === 'success') {
                        alert('Items saved successfully to commodity supply!');
                        reset();
                        $("#quantity").val("");
                        $('#item_id').val('').trigger('change');
                        
                        // Optional: Reload page or redirect
                        // location.reload();
                        // window.location.href = 'commodity_list.php';
                    } else {
                        alert('Error saving items: ' + (response.message || response.error || 'Unknown error occurred'));
                    }
                } catch (e) {
                    console.error('Error parsing response:', e);
                    alert('Error occurred while processing server response.');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error Details:');
                console.error('Status Code:', xhr.status);
                console.error('Status Text:', xhr.statusText);
                console.error('Error:', error);
               // console.error('Response Text:', xhr.responseText);
                
                let errorMessage = 'Error occurred while saving items.';
                
                if (xhr.status === 404) {
                    errorMessage = 'AJAX endpoint not found. Please check the file path.';
                } else if (xhr.status === 500) {
                    errorMessage = 'Server error occurred. Please check server logs.';
                } else if (xhr.status === 0) {
                    errorMessage = 'Network error. Please check your connection.';
                }
                
                alert(errorMessage + ' Please try again.');
            },
            complete: function() {
                button.html(originalText).prop('disabled', false);
            }
        });
    } else {
        button.html(originalText).prop('disabled', false);
    }
});

// Optional: Function to load existing commodity items
function loadExistingCommodityItems() {
    const commodityNameId = <?php echo json_encode($commodity_name_id); ?>;
    
    $.ajax({
        url: "../../ajax/commodity_ajax.php",
        method: "POST",
        data: {
            "commodity_name_id": commodityNameId,
            "action": 'get_commodity_items'
        },
        success: function(response) {
            console.log('Existing items loaded:', response);
            // Handle displaying existing items if needed
        },
        error: function(xhr, status, error) {
            console.log('Error loading existing items:', error);
        }
    });
}

// Load existing items on page load (optional)
// $(document).ready(function() {
//     loadExistingCommodityItems();
// });
</script>

<?php echo $view->subFooter('../'); ?>
</body> 
</html>