<?php 
require_once('../config/classes/View.php');
require_once('../config/classes/User.php');
require_once('../config/classes/MemberG1.php');
require_once('../config/classes/Commodity.php');

$view = new View();
$user = new User();
$members = new MemberG1();
$commodity = new Commodity();

// Get application ID from URL
$application_id = isset($_GET['batch']) ? (int)$_GET['batch'] : 0;

if (!$application_id) {
    header('Location: ViewRequests.php');
    exit;
}

// Get application details and items
$applicationDetails = $commodity->getApplicationDetails($application_id);
$applicationItems = $commodity->getApplicationItems($application_id);

echo $view->subHeader('../');
?>

<!-- normalize data-table CSS -->
<link rel="stylesheet" href="../../css/data-table/bootstrap-table.css">
<link rel="stylesheet" href="../../css/data-table/bootstrap-editable.css">

<style>
.counter-offer-section {
    border: 2px solid #28a745;
    border-radius: 10px;
    padding: 20px;
    margin: 20px 0;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.original-request {
    background-color: #f8f9fa;
    border-left: 4px solid #007bff;
    padding: 15px;
    margin: 15px 0;
}

.counter-offer {
    background-color: #fff3cd;
    border-left: 4px solid #ffc107;
    padding: 15px;
    margin: 15px 0;
}

.approval-actions {
    background-color: #d1ecf1;
    border-left: 4px solid #17a2b8;
    padding: 20px;
    margin: 20px 0;
    border-radius: 5px;
}

.item-comparison {
    display: flex;
    justify-content: space-between;
    margin: 10px 0;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
}

.quantity-input {
    width: 80px;
}

.status-badge {
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: bold;
}

.status-pending { background-color: #fff3cd; color: #856404; }
.status-approved { background-color: #d4edda; color: #155724; }
.status-rejected { background-color: #f8d7da; color: #721c24; }
.status-counter-offered { background-color: #d1ecf1; color: #0c5460; }
</style>

<!-- partial:partials/_navbar.html -->
<?php echo $view->subPartialNav('../') ?>

<div class="container-fluid page-body-wrapper">
    <!-- partial:partials/_sidebar.html -->
    <?php echo $view->SecretarySideNav() ?>
    
    <div class="main-panel">
        <div class="content-wrapper">
            <div class="row">
                <div class="col-md-12 grid-margin">
                    <div class="card">
                        <div class="card-header py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    Commodity Loan Application Details - #<?php echo $application_id; ?>
                                </h6>
                                <a href="view_commodity_request.php" class="btn btn-secondary btn-sm">
                                    <i class="fa fa-arrow-left"></i> Back to Applications
                                </a>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <div class="msg"></div>
                            
                            <?php if ($applicationDetails): ?>
                                <!-- Application Information -->
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="original-request">
                                            <h5><i class="fa fa-user"></i> Applicant Information</h5>
                                            <p><strong>Name:</strong> <?php echo htmlspecialchars($applicationDetails['title'] . ' ' . $applicationDetails['fname'] . ' ' . $applicationDetails['lname'] . ' ' . $applicationDetails['oname']); ?></p>
                                            <p><strong>Staff ID:</strong> <?php echo htmlspecialchars($applicationDetails['sp_no']); ?></p>
                                            <p><strong>Application Date:</strong> <?php echo htmlspecialchars($applicationDetails['application_date']); ?></p>
                                            <p><strong>Supply Schedule:</strong> <?php echo htmlspecialchars($applicationDetails['commodity_name']); ?></p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="original-request">
                                            <h5><i class="fa fa-info-circle"></i> Application Status</h5>
                                            <p><strong>Current Status:</strong> 
                                                <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $applicationDetails['status'])); ?>">
                                                    <?php echo htmlspecialchars($applicationDetails['status']); ?>
                                                </span>
                                            </p>
                                            <p><strong>Original Total:</strong> ₦<?php echo number_format($applicationDetails['total_amount'], 2); ?></p>
                                            <p><strong>Application ID:</strong> #<?php echo $applicationDetails['application_id']; ?></p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Counter Offer Form -->
                                <div class="counter-offer-section">
                                    <h4 class="text-success mb-4">
                                        <i class="fa fa-balance-scale"></i> Review & Counter Offer
                                    </h4>
                                    
                                    <form id="counter-offer-form" method="post">
                                        <input type="hidden" name="application_id" value="<?php echo $application_id; ?>">
                                        
                                        <!-- Items Review Table -->
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th width="5%">S/N</th>
                                                        <th width="25%">Item</th>
                                                        <th width="12%">Unit Price (₦)</th>
                                                        <th width="12%">Requested Qty</th>
                                                        <th width="12%">Approved Qty</th>
                                                        <th width="12%">Original Subtotal</th>
                                                        <th width="12%">Approved Subtotal</th>
                                                        <th width="10%">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="itemsReviewBody">
                                                    <?php 
                                                    $sn = 1;
                                                    $originalTotal = 0;
                                                    foreach ($applicationItems as $item): 
                                                        $originalSubtotal = $item['quantity_requested'] * $item['unit_price'];
                                                        $originalTotal += $originalSubtotal;
                                                        $approvedQty = $item['quantity_approved'] ?? $item['quantity ']; // Use approved quantity if available
                                                    ?>
                                                        <tr class="item-row" data-item-id="<?php echo $item['commodity_supply_item_id']; ?>">
                                                            <td><?php echo $sn++; ?></td>
                                                            <td><?php echo htmlspecialchars($item['commodity_item']); ?></td>
                                                            <td class="unit-price">₦<?php echo number_format($item['unit_price'], 2); ?></td>
                                                            <td class="original-quantity"><?php echo $item['quantity_requested']; ?></td>
                                                            <td>
                                                                <input type="number" 
                                                                       name="approved_quantities[<?php echo $item['commodity_supply_item_id']; ?>]"
                                                                       class="form-control quantity-input approved-quantity" 
                                                                       value="<?php echo $approvedQty; ?>" 
                                                                       min="0" 
                                                                       max="<?php echo $item['quantity_requested']; ?>"
                                                                       data-unit-price="<?php echo $item['unit_price']; ?>"
                                                                       data-item-id="<?php echo $item['commodity_supply_item_id']; ?>">
                                                            </td>
                                                            <td class="original-subtotal">₦<?php echo number_format($originalSubtotal, 2); ?></td>
                                                            <td class="approved-subtotal">₦<?php echo number_format($approvedQty * $item['unit_price'], 2); ?></td>
                                                            <td>
                                                                <div class="btn-group btn-group-sm">
                                                                    <button type="button" class="btn btn-success approve-item" data-item-id="<?php echo $item['commodity_supply_item_id']; ?>">
                                                                        <i class="fa fa-check"></i>
                                                                    </button>
                                                                    <button type="button" class="btn btn-warning partial-approve" data-item-id="<?php echo $item['commodity_supply_item_id']; ?>">
                                                                        <i class="fa fa-edit"></i>
                                                                    </button>
                                                                    <button type="button" class="btn btn-danger reject-item" data-item-id="<?php echo $item['commodity_supply_item_id']; ?>">
                                                                        <i class="fa fa-times"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Totals Summary -->
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <div class="card bg-info text-white">
                                                    <div class="card-body text-center">
                                                        <h5>Original Total</h5>
                                                        <h3 id="originalTotal">₦<?php echo number_format($originalTotal, 2); ?></h3>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="card bg-success text-white">
                                                    <div class="card-body text-center">
                                                        <h5>Approved Total</h5>
                                                        <h3 id="approvedTotal">₦<?php echo number_format($originalTotal, 2); ?></h3>
                                                        <input type="hidden" name="approved_total" id="hiddenApprovedTotal" value="<?php echo $originalTotal; ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Comments Section -->
                                        <div class="mt-4">
                                            <label for="admin_comments" class="form-label">
                                                <strong>Administrator Comments/Justification:</strong>
                                            </label>
                                            <textarea name="admin_comments" id="admin_comments" class="form-control" rows="4" 
                                                    placeholder="Enter comments explaining the approval decision, counter offer, or rejection reasons..."></textarea>
                                        </div>

                                        <!-- Action Buttons -->
                                        <div class="approval-actions mt-4">
                                            <div class="row">
                                                <div class="col-md-12 text-center">
                                                    <h5 class="mb-3">Final Decision:</h5>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-success btn-lg" id="approveBtn">
                                                            <i class="fa fa-check-circle"></i> Reserve Application Items
                                                        </button>
                                                        <button type="button" class="btn btn-warning btn-lg" id="counterOfferBtn">
                                                            <i class="fa fa-balance-scale"></i> Make Counter Reservation Offer
                                                        </button>
                                                        <button type="button" class="btn btn-danger btn-lg" id="rejectBtn">
                                                            <i class="fa fa-times-circle"></i> Reject Application
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="mt-3 text-center">
                                                <div id="loadingIndicator" style="display: none;">
                                                    <img src="../img/loading.gif" alt="Processing..."> Processing request...
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="fa fa-exclamation-triangle"></i>
                                    Application not found or you don't have permission to view it.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Calculate and update totals
    function updateTotals() {
        let approvedTotal = 0;
        
        document.querySelectorAll('.item-row').forEach(function(row) {
            const approvedQtyInput = row.querySelector('.approved-quantity');
            const unitPrice = parseFloat(approvedQtyInput.dataset.unitPrice);
            const approvedQty = parseInt(approvedQtyInput.value) || 0;
            const approvedSubtotal = approvedQty * unitPrice;
            
            // Update the approved subtotal display
            row.querySelector('.approved-subtotal').textContent = '₦' + approvedSubtotal.toLocaleString('en-NG', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            
            approvedTotal += approvedSubtotal;
        });
        
        // Update approved total display
        document.getElementById('approvedTotal').textContent = '₦' + approvedTotal.toLocaleString('en-NG', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        document.getElementById('hiddenApprovedTotal').value = approvedTotal.toFixed(2);
    }

    // Handle quantity input changes
    document.querySelectorAll('.approved-quantity').forEach(function(input) {
        input.addEventListener('input', updateTotals);
        input.addEventListener('change', updateTotals);
    });

    // Quick action buttons
    document.querySelectorAll('.approve-item').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const itemId = this.dataset.itemId;
            const row = document.querySelector(`.item-row[data-item-id="${itemId}"]`);
            const originalQty = parseInt(row.querySelector('.original-quantity').textContent);
            const approvedQtyInput = row.querySelector('.approved-quantity');
            
            approvedQtyInput.value = originalQty;
            updateTotals();
            
            // Visual feedback
            row.style.backgroundColor = '#d4edda';
            setTimeout(() => { row.style.backgroundColor = ''; }, 1000);
        });
    });

    document.querySelectorAll('.partial-approve').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const itemId = this.dataset.itemId;
            const row = document.querySelector(`.item-row[data-item-id="${itemId}"]`);
            const originalQty = parseInt(row.querySelector('.original-quantity').textContent);
            const approvedQtyInput = row.querySelector('.approved-quantity');
            
            // Set to half of original quantity or prompt user
            const suggestedQty = Math.floor(originalQty / 2);
            const userQty = prompt(`Enter approved quantity for this item (Max: ${originalQty}):`, suggestedQty);
            
            if (userQty !== null && !isNaN(userQty) && userQty >= 0 && userQty <= originalQty) {
                approvedQtyInput.value = parseInt(userQty);
                updateTotals();
                
                // Visual feedback
                row.style.backgroundColor = '#fff3cd';
                setTimeout(() => { row.style.backgroundColor = ''; }, 1000);
            }
        });
    });

    document.querySelectorAll('.reject-item').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const itemId = this.dataset.itemId;
            const row = document.querySelector(`.item-row[data-item-id="${itemId}"]`);
            const approvedQtyInput = row.querySelector('.approved-quantity');
            
            approvedQtyInput.value = 0;
            updateTotals();
            
            // Visual feedback
            row.style.backgroundColor = '#f8d7da';
            setTimeout(() => { row.style.backgroundColor = ''; }, 1000);
        });
    });

    // Final decision buttons
    document.getElementById('approveBtn').addEventListener('click', function() {
        if (confirm('Are you sure you want to approve this application as is?')) {
            processDecision('approved');
        }
    });

    document.getElementById('counterOfferBtn').addEventListener('click', function() {
        const originalTotal = parseFloat(document.getElementById('originalTotal').textContent.replace(/[₦,]/g, ''));
        const approvedTotal = parseFloat(document.getElementById('hiddenApprovedTotal').value);
        
        if (approvedTotal >= originalTotal) {
            alert('Counter offer total should be less than the original amount. Use "Approve" instead.');
            return;
        }
        
        if (confirm(`Are you sure you want to make a counter offer of ₦${approvedTotal.toLocaleString('en-NG', {minimumFractionDigits: 2})}?`)) {
            processDecision('counter_offered');
        }
    });

    document.getElementById('rejectBtn').addEventListener('click', function() {
        const comments = document.getElementById('admin_comments').value.trim();
        if (!comments) {
            alert('Please provide comments explaining the reason for rejection.');
            document.getElementById('admin_comments').focus();
            return;
        }
        
        if (confirm('Are you sure you want to reject this application? This action cannot be undone.')) {
            processDecision('rejected');
        }
    });

    function processDecision(decision) {
        const formData = new FormData(document.getElementById('counter-offer-form'));
        formData.append('action', 'process_counter_offer');
        formData.append('decision', decision);
        
        // Show loading indicator
        document.getElementById('loadingIndicator').style.display = 'block';
        document.querySelectorAll('#approveBtn, #counterOfferBtn, #rejectBtn').forEach(btn => {
            btn.disabled = true;
        });
        
        $.ajax({
            url: '../../ajax/commodity_ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                document.getElementById('loadingIndicator').style.display = 'none';
                
                if (response.success) {
                    $('.msg').html('<div class="alert alert-success"><i class="fa fa-check-circle"></i> ' + response.message + '</div>');
                    
                    // Redirect after success
                    setTimeout(function() {
                        window.location.href = 'view_commodity_request.php';
                    }, 2000);
                } else {
                    $('.msg').html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> Error: ' + response.message + '</div>');
                    // Re-enable buttons
                    document.querySelectorAll('#approveBtn, #counterOfferBtn, #rejectBtn').forEach(btn => {
                        btn.disabled = false;
                    });
                }
            },
            error: function(xhr, status, error) {
                document.getElementById('loadingIndicator').style.display = 'none';
                $('.msg').html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> Network error. Please try again.</div>');
                // Re-enable buttons
                document.querySelectorAll('#approveBtn, #counterOfferBtn, #rejectBtn').forEach(btn => {
                    btn.disabled = false;
                });
                console.error('Ajax error:', error);
            }
        });
    }
});
</script>

<!-- data table JS -->
<script src="../../js/data-table/bootstrap-table.js"></script>
<script src="../../js/data-table/data-table-active.js"></script>

<?php echo $view->subFooter('../'); ?>
</body>
</html>