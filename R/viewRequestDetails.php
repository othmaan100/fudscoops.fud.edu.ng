<?php 
require_once('../config/classes/View.php');
require_once('../config/classes/User.php');
require_once('../config/classes/MemberG1.php');
require_once('../config/classes/Commodity.php');

$view = new View();
$user = new User();
$members = new MemberG1();
$commodity = new Commodity();
   
$employeeid = $user->getEmployeeId($_SESSION['username']);
$memberId = $user->getMemberId($employeeid); 

// Get application ID from URL
$application_id = isset($_GET['applicationId']) ? (int)$_GET['applicationId'] : 0;

if (!$application_id) {
    header('Location: view_commodity_request.php');
    exit;
}

// Get application details and items
$applicationDetails = $commodity->getApplicationDetails($application_id, $memberId);
$applicationItems = $commodity->getApplicationItems($application_id);

$user->is_authenticated('../auth/');
$user->isStaff('../auth/logout.php');

echo $view->subHeader('../');
?>

<link rel="stylesheet" href="../../css/data-table/bootstrap-table.css">
<link rel="stylesheet" href="../../css/data-table/bootstrap-editable.css">

<style>
/* Screen styles */
.loan-approval-container {
    max-width: 900px;
    margin: 0 auto;
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.header-section {
    text-align: center;
    border-bottom: 3px solid #036823;
    padding-bottom: 20px;
    margin-bottom: 30px;
}

.header-section h1 {
    /*color: #007bff;*/
    color:#f3bb69;
    font-size: 28px;
    font-weight: bold;
    margin-bottom: 5px;
}

.header-section h2 {
    color: #6c757d;
    font-size: 18px;
    margin-bottom: 0;
}

.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 30px;
}

.info-card {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 20px;
    border-left: 4px solid #036823;
}

.info-card h5 {
    color: #ba8875;
    font-weight: bold;
    margin-bottom: 15px;
    font-size: 16px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    padding: 5px 0;
    border-bottom: 1px dotted #dee2e6;
}

.info-row:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.info-label {
    font-weight: 600;
    color: #495057;
}

.info-value {
    color: #212529;
}

.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
    text-transform: uppercase;
}

.status-pending { background-color: #fff3cd; color: #856404; }
.status-approved { background-color: #d4edda; color: #155724; }
.status-rejected { background-color: #f8d7da; color: #721c24; }
.status-counter-offered { background-color: #d1ecf1; color: #0c5460; }

.items-section {
    margin: 30px 0;
}

.items-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
    font-size: 14px;
}

.items-table th,
.items-table td {
    border: 1px solid #dee2e6;
    padding: 12px 8px;
    text-align: left;
}

.items-table th {
    background-color: #036823;
    color: white;
    font-weight: bold;
    text-align: center;
}

.items-table tbody tr:nth-child(even) {
    background-color: #f8f9fa;
}

.items-table tbody tr:hover {
    background-color: #e9ecef;
}

.totals-section {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin: 30px 0;
}

.total-card {
    text-align: center;
    padding: 20px;
    border-radius: 8px;
    color: white;
    font-weight: bold;
}

.total-card.original {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
}

.total-card.approved {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
}

.total-card h5 {
    margin-bottom: 10px;
    font-size: 16px;
}

.total-card h3 {
    margin: 0;
    font-size: 24px;
}

.comments-section {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 8px;
    padding: 20px;
    margin: 20px 0;
}

.comments-section h5 {
    color: #856404;
    margin-bottom: 15px;
}

.comments-content {
    color: #212529;
    line-height: 1.6;
    font-style: italic;
}

.no-break {
    page-break-inside: avoid;
}

.signature-section {
    margin-top: 40px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    padding-top: 20px;
    border-top: 1px solid #dee2e6;
}

.signature-box {
    text-align: center;
}

.signature-line {
    border-bottom: 2px solid #000;
    width: 200px;
    margin: 30px auto 10px;
}

.action-buttons {
    text-align: center;
    margin: 30px 0;
    page-break-inside: avoid;
}

/* Print styles */
@media print {
    body * {
        visibility: hidden;
    }
    
    .loan-approval-container,
    .loan-approval-container * {
        visibility: visible;
    }
    
    .loan-approval-container {
        position: absolute;
        left: 0;
        top: 0;
        width: 100% !important;
        max-width: none !important;
        margin: 0 !important;
        padding: 20px !important;
        box-shadow: none !important;
        border-radius: 0 !important;
    }
    
    .action-buttons,
    .btn {
        display: none !important;
    }
    
    .info-grid,
    .totals-section {
        grid-template-columns: 1fr !important;
        gap: 15px !important;
    }
    
    .items-table {
        font-size: 12px;
    }
    
    .items-table th,
    .items-table td {
        padding: 8px 6px;
    }
    
    .total-card {
        background: #f8f9fa !important;
        color: #000 !important;
        border: 2px solid #007bff !important;
    }
    
    .page-break {
        page-break-before: always;
    }
}

@media screen and (max-width: 768px) {
    .info-grid,
    .totals-section {
        grid-template-columns: 1fr;
    }
    
    .signature-section {
        grid-template-columns: 1fr;
    }
    
    .items-table {
        font-size: 12px;
    }
    
    .items-table th,
    .items-table td {
        padding: 8px 4px;
    }
}
</style>

<?php echo $view->subPartialNav('../') ?>

<div class="container-fluid page-body-wrapper">
    <?php echo $view->staffSideNav() ?>
    
    <div class="main-panel">
        <div class="content-wrapper">
            <?php if ($applicationDetails): ?>
                <div class="loan-approval-container">
                    <!-- Header Section -->
                    <div class="header-section no-break">
                        <h1>Commodity Loan Application</h1>
                        <h2>Approval Details & Summary</h2>
                        <p><strong>Application ID:</strong> #<?php echo $applicationDetails['application_id']; ?></p>
                        <p><strong>Generated on:</strong> <?php echo date('F d, Y \a\t g:i A'); ?></p>
                    </div>

                    <!-- Application Information Grid -->
                    <div class="info-grid no-break">
                        <div class="info-card">
                            <h5><i class="fa fa-user"></i> Applicant Information</h5>
                            <div class="info-row">
                                <span class="info-label">Full Name:</span>
                                <span class="info-value"><?php echo htmlspecialchars($applicationDetails['title'] . ' ' . $applicationDetails['fname'] . ' ' . $applicationDetails['lname'] . ' ' . $applicationDetails['oname']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Staff ID:</span>
                                <span class="info-value"><?php echo htmlspecialchars($applicationDetails['sp_no']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Employee ID:</span>
                                <span class="info-value"><?php echo htmlspecialchars($applicationDetails['employee_id']); ?></span>
                            </div>
                        </div>

                        <div class="info-card">
                            <h5><i class="fa fa-calendar"></i> Application Details</h5>
                            <div class="info-row">
                                <span class="info-label">Application Date:</span>
                                <span class="info-value"><?php echo date('F d, Y', strtotime($applicationDetails['application_date'])); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Supply Schedule:</span>
                                <span class="info-value"><?php echo htmlspecialchars($applicationDetails['commodity_name']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Current Status:</span>
                                <span class="info-value">
                                    <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $applicationDetails['status'])); ?>">
                                        <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $applicationDetails['status']))); ?>
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Items Section -->
                    <div class="items-section">
                        <h4 style="color: #ba8875; border-bottom: 2px solid #036823; padding-bottom: 10px; margin-bottom: 20px;">
                            <i class="fa fa-list-alt"></i> Commodity Items Details
                        </h4>
                        
                        <div class="table-responsive">
                            <table class="items-table">
                                <thead>
                                    <tr>
                                        <th width="5%">S/N</th>
                                        <th width="30%">Item Description</th>
                                        <th width="13%">Unit Price (₦)</th>
                                        <th width="13%">Requested Qty</th>
                                        <th width="13%">Approved Qty</th>
                                        <th width="13%">Original Amount (₦)</th>
                                        <th width="13%">Approved Amount (₦)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $sn = 1;
                                    $originalTotal = 0;
                                    $approvedTotal = 0;
                                    
                                    foreach ($applicationItems as $item): 
                                        $originalSubtotal = $item['quantity_requested'] * $item['unit_price'];
                                        $originalTotal += $originalSubtotal;
                                        $approvedQty = $item['quantity_approved'] ?? $item['quantity_approved ']; // Use approved quantity if available
                                    ?>
                                    <tr>
                                        <td style="text-align: center;"><?php echo $sn++; ?></td>
                                        <td><?php echo htmlspecialchars($item['commodity_item']); ?></td>
                                        <td style="text-align: right;">₦<?php echo number_format($item['unit_price'], 2); ?></td>
                                        <td style="text-align: center;"><?php echo number_format($item['quantity_requested']); ?></td>
                                       <td style="text-align: right; font-weight: bold; color: <?php echo ($approvedQty < $item['quantity_requested']) ? '#dc3545' : '#28a745'; ?>;">
                                            <?php echo number_format($approvedQty); ?>  
                                        </td>

                                        <td style="text-align: right;">₦<?php echo number_format($originalSubtotal, 2); ?></td>
                                        <td style="text-align: right; font-weight: bold;
                                            <?php echo ($approvedSubtotal < $originalSubtotal) ? 'color: #dc3545;' : 'color: #28a745;'; ?>">
                                            ₦<?php echo number_format($approvedSubtotal, 2); ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

 

                    <!-- Signature Section -->
                    <div class="signature-section no-break">
                        <div class="signature-box">
                            <p><strong>Member Signature</strong></p>
                            <div class="signature-line"></div>
                            <p>Date: _________________</p>
                        </div>
                        <div class="signature-box">
                            <p><strong>Issuer  Signature</strong></p>
                            <div class="signature-line"></div>
                            <p>Date: _________________</p>
                        </div>
                    </div>

                    <!-- Action Buttons (Hidden in print) -->
                    <div class="action-buttons">
                        <button onclick="window.print()" class="btn btn-primary btn-lg">
                            <i class="fa fa-print"></i> Print
                        </button>
                        
                        
                        <a href="view_commodity_request.php" class="btn btn-info btn-lg">
                            <i class="fa fa-list"></i> View All Applications
                        </a>
                    </div>
                </div>

            <?php else: ?>
                <div class="alert alert-warning">
                    <i class="fa fa-exclamation-triangle"></i>
                    Application not found or you don't have permission to view it.
                    <br><br>
                    <a href="view_commodity_request.php" class="btn btn-primary">
                        <i class="fa fa-arrow-left"></i> Back to Applications
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-focus on print button when page loads (optional)
    // document.querySelector('.btn-primary').focus();
    
    // Add keyboard shortcut for printing (Ctrl+P)
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'p') {
            e.preventDefault();
            window.print();
        }
    });
    
    // Calculate and display additional statistics
    const originalTotal = <?php echo $originalTotal; ?>;
    const approvedTotal = <?php echo $approvedTotal; ?>;
    
    if (originalTotal > approvedTotal) {
        console.log('Savings achieved: ₦' + (originalTotal - approvedTotal).toFixed(2));
    }
});
</script>

<?php echo $view->subFooter('../'); ?>
</body>
</html>