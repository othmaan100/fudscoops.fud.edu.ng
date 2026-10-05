<?php
// File: disburse_commodity_loan.php
require_once('../config/classes/View.php');
require_once('../config/classes/User.php');
require_once('../config/classes/MemberG1.php');
require_once('../config/classes/Commodity.php');

$view = new View();
$user = new User();
$members = new MemberG1();
$commodity = new Commodity();

// Get application ID from URL
$application_id = isset($_GET['application_id']) ? (int)$_GET['application_id'] : 0;

if (!$application_id) {
    // Redirect to the list of approved applications pending disbursement
    header('Location: approved_for_disbursement.php');
    exit;
}

// Assumes getApplicationDetails now joins with fudscoops_commodity_Items to get stock levels
$applicationDetails = $commodity->getApplicationDetails($application_id);
$applicationItems = $commodity->getApplicationItems($application_id); // Use a new dedicated method

echo $view->subHeader('../');
?>

<style>
/* Simplified styles for disbursement */
.disbursement-section {
    border: 2px solid #17a2b8; /* Info color */
    border-radius: 10px;
    padding: 20px;
    margin: 20px 0;
    background: #f1f8ff;
}
.applicant-info {
    background-color: #f8f9fa;
    border-left: 4px solid #007bff;
    padding: 15px;
    margin: 15px 0;
}
.quantity-input {
    width: 100px;
}
.status-badge {
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: bold;
}
.status-approved { background-color: #d4edda; color: #155724; }
/* Add other statuses as needed */
.text-danger {
    font-size: 0.8em;
}
</style>

<?php echo $view->subPartialNav('../') ?>

<div class="container-fluid page-body-wrapper">
    <?php echo $view->subSideNav() ?>
    
    <div class="main-panel">
        <div class="content-wrapper">
            <div class="row">
                <div class="col-md-12 grid-margin">
                    <div class="card">
                        <div class="card-header py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="fa fa-truck"></i> Commodity Loan Disbursement - #<?php echo $application_id; ?>
                                </h6>
                                <a href="approved_commodity_Request.php" class="btn btn-secondary btn-sm">
                                    <i class="fa fa-arrow-left"></i> Back to Disbursement List
                                </a>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <div class="msg"></div>
                            
                            <?php if ($applicationDetails && $applicationDetails['status'] === 'approved'): ?>
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="applicant-info">
                                            <h5><i class="fa fa-user"></i> Applicant Information</h5>
                                            <p><strong>Name:</strong> <?php echo htmlspecialchars($applicationDetails['title'] . ' ' . $applicationDetails['fname'] . ' ' . $applicationDetails['lname']); ?></p>
                                            <p><strong>Staff ID:</strong> <?php echo htmlspecialchars($applicationDetails['sp_no']); ?></p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="applicant-info">
                                            <h5><i class="fa fa-info-circle"></i> Application Status</h5>
                                            <p><strong>Current Status:</strong> 
                                                <span class="status-badge status-approved">
                                                    <?php echo htmlspecialchars($applicationDetails['status']); ?>
                                                </span>
                                            </p>
                                            <p><strong>Approved Total:</strong> ₦<?php echo number_format($applicationDetails['total_amount'], 2); ?></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="disbursement-section">
                                    <h4 class="text-info mb-4">
                                        <i class="fa fa-dolly-flatbed"></i> Record Item Disbursement
                                    </h4>
                                    
                                    <form id="disbursement-form" method="post">
                                        <input type="hidden" name="application_id" value="<?php echo $application_id; ?>">
                                        
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>S/N</th>
                                                        <th>Item</th>
                                                        <th>Unit Price (₦)</th>
                                                        <th>Approved Qty</th>
                                                        <th>Available Stock</th>
                                                        <th>Disbursed Qty</th>
                                                        <th>Disbursed Subtotal</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $sn = 1;
                                                    $totalApprovedAmount = 0;
                                                    foreach ($applicationItems as $item):
                                                        $approvedQty = $item['quantity_approved'] ?? 0;
                                                        $stockQty = $item['stock_quantity'] ?? 0; // Assuming this comes from the new query
                                                        $maxDisburse = min($approvedQty, $stockQty);
                                                        $totalApprovedAmount += $approvedQty * $item['unit_price'];
                                                    ?>
                                                        <tr class="item-row">
                                                            <td><?php echo $sn++; ?></td>
                                                            <td><?php echo htmlspecialchars($item['commodity_item']); ?></td>
                                                            <td class="unit-price" data-price="<?php echo $item['unit_price']; ?>">
                                                                <?php echo number_format($item['unit_price'], 2); ?>
                                                            </td>
                                                            <td><?php echo $approvedQty; ?></td>
                                                            <td style="color: <?php echo ($stockQty < $approvedQty) ? 'red' : 'green'; ?>;">
                                                                <?php echo $stockQty; ?>
                                                            </td>
                                                            <td>
                                                                <input type="number" 
                                                                    name="disbursed_quantities[<?php echo $item['commodity_supply_item_id']; ?>]"
                                                                    class="form-control quantity-input disbursed-quantity" 
                                                                    value="<?php echo $maxDisburse; ?>" 
                                                                    min="0" 
                                                                    max="<?php echo $maxDisburse; ?>"
                                                                    data-item-inventory-id="<?php echo $item['fudscoops_commodity_item_id']; ?>"
                                                                    required>
                                                                <?php if ($stockQty < $approvedQty): ?>
                                                                    <small class="text-danger">Stock limited!</small>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="disbursed-subtotal">
                                                                ₦<?php echo number_format($maxDisburse * $item['unit_price'], 2); ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="row mt-3">
                                            <div class="col-md-6 offset-md-6">
                                                <div class="card bg-info text-white">
                                                    <div class="card-body text-center">
                                                        <h5>Total Disbursed Amount</h5>
                                                        <h3 id="disbursedTotal">₦0.00</h3>
                                                        <input type="hidden" name="disbursed_total" id="hiddenDisbursedTotal">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-4">
                                            <label for="admin_comments" class="form-label"><strong>Disbursement Notes:</strong></label>
                                            <textarea name="admin_comments" id="admin_comments" class="form-control" rows="3" 
                                                placeholder="Enter any notes about this disbursement (e.g., reason for partial disbursement)..."></textarea>
                                        </div>

                                        <div class="mt-4 text-center">
                                            <button type="button" class="btn btn-success btn-lg" id="disburseBtn">
                                                <i class="fa fa-check-circle"></i> Confirm & Record Disbursement
                                            </button>
                                            <div id="loadingIndicator" style="display: none;" class="mt-2">
                                                <img src="../img/loading.gif" alt="Processing..."> Processing...
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="fa fa-exclamation-triangle"></i>
                                    Application not found or is not in 'approved' status for disbursement.
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
    function formatCurrency(amount) {
        return '₦' + amount.toLocaleString('en-NG', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // Calculate and update disbursement totals
    function updateDisbursementTotals() {
        let totalDisbursed = 0;
        
        document.querySelectorAll('.item-row').forEach(function(row) {
            const disbursedQtyInput = row.querySelector('.disbursed-quantity');
            const unitPrice = parseFloat(row.querySelector('.unit-price').dataset.price);
            const disbursedQty = parseInt(disbursedQtyInput.value) || 0;
            const maxQty = parseInt(disbursedQtyInput.max);

            // Enforce max quantity
            if (disbursedQty > maxQty) {
                disbursedQtyInput.value = maxQty;
                alert(`Cannot disburse more than the approved quantity of ${maxQty}.`);
            }

            const disbursedSubtotal = (disbursedQty > maxQty ? maxQty : disbursedQty) * unitPrice;
            
            row.querySelector('.disbursed-subtotal').textContent = formatCurrency(disbursedSubtotal);
            totalDisbursed += disbursedSubtotal;
        });
        
        document.getElementById('disbursedTotal').textContent = formatCurrency(totalDisbursed);
        document.getElementById('hiddenDisbursedTotal').value = totalDisbursed.toFixed(2);
    }

    // Initial calculation on page load
    updateDisbursementTotals();

    // Add event listeners to all quantity inputs
    document.querySelectorAll('.disbursed-quantity').forEach(function(input) {
        input.addEventListener('input', updateDisbursementTotals);
    });

    // Handle the final disbursement confirmation
    document.getElementById('disburseBtn').addEventListener('click', function() {
        if (confirm('Are you sure you want to record this disbursement? This will update inventory and cannot be undone.')) {
            processDisbursement();
        }
    });

    function processDisbursement() {
        const form = document.getElementById('disbursement-form');
        const formData = new FormData(form);
        formData.append('action', 'process_disbursement');
        
        const loadingIndicator = document.getElementById('loadingIndicator');
        const disburseBtn = document.getElementById('disburseBtn');
        
        loadingIndicator.style.display = 'block';
        disburseBtn.disabled = true;
        
        $.ajax({
            url: '../../ajax/commodity_ajax.php', // Your AJAX handler file
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                loadingIndicator.style.display = 'none';
                
                if (response.success) {
                    $('.msg').html('<div class="alert alert-success"><i class="fa fa-check-circle"></i> ' + response.message + '</div>');
                    setTimeout(() => window.location.href = 'approved_for_disbursement.php', 2000);
                } else {
                    $('.msg').html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> Error: ' + response.message + '</div>');
                    disburseBtn.disabled = false;
                }
            },
            error: function(xhr, status, error) {
                loadingIndicator.style.display = 'none';
                disburseBtn.disabled = false;
                $('.msg').html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> A network error occurred. Please try again.</div>');
                console.error('AJAX Error:', error);
            }
        });
    }
});
</script>

<?php echo $view->subFooter('../'); ?>