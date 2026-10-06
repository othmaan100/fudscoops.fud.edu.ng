<?php
session_start();
require_once('../config/classes/View.php');
require_once('../config/classes/User.php');
require_once('../config/classes/SavingsG1.php');

$view = new View();
$user = new User();
$savings = new SavingsG1();

// Auth check
$user->is_authenticated('auth/');
$user->isBur('../auth/logout.php'); // Ensure this method exists

echo $view->subHeader('../');
?>

<!-- partial:partials/_navbar.html -->
<?php echo $view->subPartialNav('../')?>
<div class="container-fluid page-body-wrapper">
  <?php echo $view->treasurerSideNav()?>
  
  <div class="main-panel">
    <div class="content-wrapper">
      <h2>Upload Approved Savings Updates</h2>
      
      <!-- Upload Form -->
      <div class="card mb-4">
        <div class="card-body">
          <form method="POST" enctype="multipart/form-data" id="uploadForm">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>CSV File (Columns: Staff No, Final Amount)</label>
                  <input type="file" name="savings_file" class="form-control" accept=".csv" required>
                  <small class="form-text text-muted">
                    Max 500KB. Example: <code>STF001,5000</code>
                  </small>
                </div>
              </div>
              <div class="col-md-6 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Upload & Process</button>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- Results -->
      <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['savings_file'])): ?>
        <?php
        $result = processSavingsUpload($_FILES['savings_file'], $user, $savings);
        $_SESSION['savings_upload_report'] = $result['report'];
        ?>
        
        <div class="alert alert-info">
          <strong>Upload Summary:</strong><br>
          <?php echo $result['inserted']; ?> updated<br>
          <?php echo $result['skipped']; ?> skipped
        </div>
        
        <?php if (!empty($result['report'])): ?>
          <div class="card">
            <div class="card-body">
              <table class="table table-bordered">
                <thead class="thead-light">
                  <tr>
                    <th>Row</th>
                    <th>Staff No</th>
                    <th>Amount</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($result['report'] as $row): ?>
                    <tr>
                      <td><?php echo $row[0]; ?></td>
                      <td><?php echo htmlspecialchars($row[1]); ?></td>
                      <td>₦<?php echo number_format($row[2], 2); ?></td>
                      <td class="<?php 
                        if (strpos($row[3], 'Updated') !== false) echo 'text-success';
                        elseif (strpos($row[3], 'Error') !== false) echo 'text-danger';
                        else echo 'text-warning';
                      ?>"><?php echo htmlspecialchars($row[3]); ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
              
              <!-- Download Report Button -->
              <div class="mt-3">
                <a href="?download_report=1" class="btn btn-success">
                  <i class="fa fa-download"></i> Download Report as CSV
                </a>
              </div>
            </div>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php echo $view->subFooter('../'); ?>

<?php
// Handle CSV download
if (isset($_GET['download_report']) && $_GET['download_report'] === '1') {
    if (!isset($_SESSION['savings_upload_report'])) {
        exit('No report available.');
    }
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="savings_upload_report_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Row', 'Staff No', 'Amount', 'Status']);
    
    foreach ($_SESSION['savings_upload_report'] as $row) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}

// Processing function
function processSavingsUpload($file, $user, $savings) {
    $report = [];
    $inserted = 0;
    $skipped = 0;
    
    // Validate file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['inserted' => 0, 'skipped' => 1, 'report' => [['1', 'N/A', '0', 'File upload error']]];
    }
    
    if ($file['size'] > 500 * 1024) {
        return ['inserted' => 0, 'skipped' => 1, 'report' => [['1', 'N/A', '0', 'File too large (max 500KB)']]];
    }
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'csv') {
        return ['inserted' => 0, 'skipped' => 1, 'report' => [['1', 'N/A', '0', 'Invalid file type (CSV only)']]];
    }
    
    // Process CSV
    $handle = fopen($file['tmp_name'], 'r');
    if (!$handle) {
        return ['inserted' => 0, 'skipped' => 1, 'report' => [['1', 'N/A', '0', 'Could not read file']]];
    }
    
    $rowNum = 1;
    while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
        if ($rowNum === 1) { $rowNum++; continue; } // Skip header
        
        $staffNo = isset($data[0]) ? trim($data[0]) : '';
        $amount = isset($data[1]) ? trim($data[1]) : '';
        $status = '';
        
        try {
            // Validate inputs
            if (empty($staffNo)) {
                $status = 'Missing Staff No';
            } elseif (!is_numeric($amount) || $amount <= 0) {
                $status = 'Invalid amount';
            } else {
                // Get employee ID
                $employeeId = $user->getEmployeeId($staffNo);
                if (!$employeeId) {
                    $status = 'Staff not found';
                } else {
                    // Update savings record
                    $result = $savings->updateTreasurerApprovedAmount($employeeId, $amount);
                    if ($result) {
                        $status = 'Updated';
                        $inserted++;
                    } else {
                        $status = 'Update failed (not approved by chairman or No Changes in monthly savings amount)';
                       //$status = $result;
                    }
                }
            }
        } catch (Exception $e) {
            $status = 'System error: ' . $e->getMessage();
        }
        
        if (empty($status)) $status = 'Unknown error';
        if ($status !== 'Updated') $skipped++;
        
        $report[] = [$rowNum - 1, $staffNo, $amount, $status];
        $rowNum++;
    }
    
    fclose($handle);
    return compact('inserted', 'skipped', 'report');
}
?>