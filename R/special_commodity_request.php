<?php
require_once('../config/classes/View.php');
require_once('../config/classes/User.php');
require_once '../config/classes/Commodity.php';

$view = new View();
$user = new User();
$commodity = new Commodity();

$user->is_authenticated('../auth/');
$user->isStaff('../auth/logout.php');

$staff_info = $user->getStaffInformation($_SESSION['username']);
$employeeid = $user->getEmployeeId($_SESSION['username']);
$memberid = $user->getMemberId($employeeid);
$share_info = $user->getMemberShareInformation($memberid);
$date = date('Y-m-d', strtotime($share_info['date']));

$timeout = $commodity->getSchedulesTimeOut();
$timeoutid = $commodity->getSchedulesTimeOutId();

// Check if member already has a pending application
$pendingcheck = Commodity::hasPendingApplication($memberid, $timeoutid);

if ($pendingcheck == true) {
    $_SESSION['error'] = 'You already have a pending application for this commodity supply.';
}
?>

<?php echo $view->subHeader('../'); ?>
<!-- partial:partials/_navbar.html -->
<?php echo $view->subPartialNav('../')?>
<!-- partial -->
<div class="container-fluid page-body-wrapper">
  <?php echo $view->staffSideNav()?>
  
  <div class="main-panel">
    <div class="content-wrapper">
      <div class="row">
        <div class="col-md-12 grid-margin">
          <div class="d-flex2 justify-content-between flex-wrap2">
            <div class="d-flex2 align-items-end card flex-wrap2">
              <div class="container mt-5">
                <img src="../../images/logo-mini.png" style="display:block;margin-left:auto;margin-right:auto;margin-bottom:2px;margin-top:-30px; width:100px;" class="rounded mx-auto d-block" alt="logo">
                <h2 class="text-center">FUD STAFF COOPERATIVE SOCIETY LIMITED</h2>
                <h5 class="text-center">[An Interest Free Thrift and Loan Society]</h5>
                <h5 class="text-center mb-4" style="text-decoration: underline;">COMMODITY LOAN APPLICATION FORM</h5>

                <div class="container2 py-4">
                  <div class="card shadow">
                    <div class="card-body">
                      <!-- Manual Input Section -->
                      <div class="mb-4">
                        <h4 class="text-success mb-3"><i class="fa fa-plus"></i> Add New Item</h4>
                        <div class="row">
                          <div class="col-md-4">
                            <label>Commodity Name</label>
                            <input type="text" id="commodityName" class="form-control" placeholder="e.g. Rice" required>
                          </div>
                          <div class="col-md-4">
                            <label>Description</label>
                            <input type="text" id="commodityDesc" class="form-control" placeholder="e.g. 50kg bag" required>
                          </div>
                          <div class="col-md-3">
                            <label>Quantity Requested</label>
                            <input type="number" id="commodityQty" class="form-control" min="1" placeholder="e.g. 2" required>
                          </div>
                          <div class="col-md-1 d-flex align-items-end">
                            <button type="button" id="addItemBtn" class="btn btn-success">Add</button>
                          </div>
                        </div>
                      </div>

                      <!-- Items List Preview -->
                      <div class="preview-section bg-light p-3 rounded mt-4">
                        <h4 class="text-primary mb-3"><i class="fa fa-list"></i> Requested Items</h4>

                        <div id="noItemsMessage" class="text-center text-muted py-4">
                          <i class="fa fa-info-circle fa-2x mb-2"></i>
                          <p>No items added yet. Please add items above.</p>
                        </div>

                        <div id="itemsList" style="display: none;">
                          <table class="table table-bordered table-hover">
                            <thead class="table-success">
                              <tr>
                                <th>S/N</th>
                                <th>Commodity</th>
                                <th>Description</th>
                                <th>Quantity</th>
                                <th>Action</th>
                              </tr>
                            </thead>
                            <tbody id="itemsListBody">
                            </tbody>
                          </table>
                        </div>
                      </div>

                      <!-- Hidden form for submission -->
                      <form id="commodity-loan-form" method="post" action="process_commodity_loan.php" style="display:none;">
                        <input type="hidden" name="commodity_supply_id" value="<?php echo $timeoutid; ?>">
                        <input type="hidden" name="items_json" id="itemsJsonInput">
                        <input type="hidden" name="total_amount" id="hiddenTotalAmount" value="0">
                      </form>

                      <!-- Submit Button -->
                      <div class="text-center mt-4">
                        <button type="button" id="submitApplicationBtn" class="btn btn-primary btn-lg" disabled>
                          <i class="fa fa-save"></i> Save Application
                        </button>
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

    <?php echo $view->subFooter('../'); ?>
  </div>
</div>
</div>

<script>
$(document).ready(function() {
    let items = [];
    
    // Add Item Button
    $('#addItemBtn').on('click', function() {
        const name = $('#commodityName').val().trim();
        const desc = $('#commodityDesc').val().trim();
        const qty = parseInt($('#commodityQty').val());

        if (!name || !desc || isNaN(qty) || qty <= 0) {
            alert('Please fill all fields with valid values.');
            return;
        }

        // Add to list
        items.push({ name, desc, qty });
        renderItemsList();
        clearInputs();
    });

    function clearInputs() {
        $('#commodityName').val('');
        $('#commodityDesc').val('');
        $('#commodityQty').val('');
        $('#commodityName').focus();
    }

    function renderItemsList() {
        const tbody = $('#itemsListBody');
        const noMsg = $('#noItemsMessage');
        const listDiv = $('#itemsList');
        const submitBtn = $('#submitApplicationBtn');

        if (items.length === 0) {
            noMsg.show();
            listDiv.hide();
            submitBtn.prop('disabled', true);
            return;
        }

        noMsg.hide();
        listDiv.show();
        submitBtn.prop('disabled', false);

        tbody.empty();
        items.forEach((item, index) => {
            const row = `
                <tr>
                    <td>${index + 1}</td>
                    <td>${item.name}</td>
                    <td>${item.desc}</td>
                    <td>${item.qty}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger remove-item" data-index="${index}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    // Remove item
    $(document).on('click', '.remove-item', function() {
        const index = $(this).data('index');
        items.splice(index, 1);
        renderItemsList();
    });

    // Submit Application
// Submit Application via AJAX
$('#submitApplicationBtn').on('click', function () {
    if (items.length === 0) {
        alert('No items to submit. Please add at least one item.');
        return;
    }

    // Optional: Validate each item
    const invalidItem = items.find(item => 
        !item.name.trim() || !item.desc.trim() || item.qty <= 0
    );
    if (invalidItem) {
        alert('One or more items have missing or invalid data. Please review.');
        return;
    }

    const confirmed = confirm(`You are about to submit ${items.length} item(s) for commodity loan.\n\nProceed?`);
    if (!confirmed) return;

    // Disable button & show loading state
    const $btn = $('#submitApplicationBtn');
    const originalText = $btn.html();
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Submitting...');

    // Prepare data
    const formData = {
        commodity_supply_id: $('#commodity-loan-form input[name="commodity_supply_id"]').val(),
        items_json: JSON.stringify(items),
        total_amount: 0, // Update if you add pricing later
        action: 'submit_commodity_loan' // Optional: for routing in PHP
    };

console.log(formData);
    // AJAX request
    $.ajax({
        url: 'process_commodity_loan.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        timeout: 15000 // 15 seconds timeout
    })
    .done(function(response) {
        if (response.success) {
            // Show success message
            alert(response.message || 'Application submitted successfully!');
            
            // Optional: Reset form and items list
            items = [];
            renderItemsList();
            $('#commodityName, #commodityDesc, #commodityQty').val('');

            // Redirect or stay? Example: stay and allow new submission
        } else {
            alert('Error: ' + (response.message || 'Failed to submit application.'));
        }
    })
    .fail(function(jqXHR, textStatus, errorThrown) {
        console.error('AJAX Error:', textStatus, errorThrown);
        let msg = 'Unable to submit request. ';
        if (textStatus === 'timeout') {
            msg += 'Request timed out. Please try again.';
        } else if (jqXHR.status === 500) {
            msg += 'Server error. Contact administrator.';
        } else {
            msg += 'Please check your connection and try again.';
        }
        alert(msg);
    })
    .always(function() {
        // Re-enable button
        $('#submitApplicationBtn')
            .prop('disabled', false)
            .html('<i class="fa fa-save"></i> Save Application');
    });
});

});
</script>