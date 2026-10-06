<?php
require_once "../config/classes/DB.php";
require_once "../config/classes/User.php";

$db = new DB();
$user = new User();

// Handle CSV download request
if (isset($_GET['download_report']) && $_GET['download_report'] === '1') {
    if (!isset($_SESSION['upload_report_data'])) {
        exit('No report data available.');
    }

    $reportData = $_SESSION['upload_report_data'];
    $month = $_SESSION['upload_month'] ?? 'N/A';
    $year = $_SESSION['upload_year'] ?? 'N/A';

    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="savings_upload_report_' . date('Y-m-d') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');

    // Write BOM for Excel compatibility (optional)
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Header row
    fputcsv($output, ['SN', 'Staff ID', 'Savings Amount', 'Month', 'Year', 'Status']);

    // Data rows
    foreach ($reportData as $row) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit;
}

// Regular upload processing
if (isset($_FILES['uploadFile']) && !empty($_FILES['uploadFile']['name'])) {
    $allowedExtensions = ["csv"];
    $ext = strtolower(pathinfo($_FILES['uploadFile']['name'], PATHINFO_EXTENSION));

    if (in_array($ext, $allowedExtensions)) {
        $file_size = $_FILES['uploadFile']['size'] / 1024; // KB

        if ($file_size < 500) {
            $uploadPath = "../resources/uploads/" . $_FILES['uploadFile']['name'];
            $isUploaded = move_uploaded_file($_FILES['uploadFile']['tmp_name'], $uploadPath);

            if ($isUploaded) {
                $handle = fopen($uploadPath, 'r');
                if (!$handle) {
                    echo '<h3 class="text-danger">Error: Could not open uploaded file.</h3>';
                    exit;
                }

                $i = 0;
                $inserted = 0;
                $skipped = 0;
                $reportData = []; // Store for CSV download

                $con = $db->getConnection();
                $month = $_POST['month'] ?? '';
                $year = $_POST['year'] ?? '';

                // Validate month and year
                if (!is_numeric($month) || !is_numeric($year) || $month < 1 || $month > 12 || $year < 2000 || $year > 2100) {
                    echo '<h3 class="text-danger">Error: Invalid Month or Year provided.</h3>';
                    fclose($handle);
                    exit;
                }

                // Store in session for download
                $_SESSION['upload_month'] = $month;
                $_SESSION['upload_year'] = $year;

                while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                    if ($i > 0) {
                        $stafNo = isset($data[0]) ? trim($data[0]) : '';
                        $savingsAmount = isset($data[1]) ? trim($data[1]) : '';

                        $status = '';
                        if (empty($stafNo)) {
                            $status = 'Missing Staff ID';
                        } elseif (!is_numeric($savingsAmount) || $savingsAmount <= 0) {
                            $status = 'Invalid amount';
                        } else {
                            $employeeid = $user->getEmployeeId($stafNo);
                            if (empty($employeeid)) {
                                $status = 'Staff ID not found';
                            } else {
                                $staffid = $user->getMemberId($employeeid);
                                if (empty($staffid)) {
                                    $status = 'Not a registered member';
                                } else {
                                    // Check for duplicate
                                    $checkQuery = "SELECT 1 FROM fudscoops_savings 
                                                   WHERE month = :month AND year = :year AND member_idmember = :staffid";
                                    $checkStmt = $con->prepare($checkQuery);
                                    $checkStmt->bindParam(':month', $month, PDO::PARAM_INT);
                                    $checkStmt->bindParam(':year', $year, PDO::PARAM_INT);
                                    $checkStmt->bindParam(':staffid', $staffid, PDO::PARAM_INT);
                                    $checkStmt->execute();

                                    if ($checkStmt->rowCount() > 0) {
                                        $status = 'Already exists';
                                        $skipped++;
                                    } else {
                                        // Insert new record
                                        $insertQuery = "INSERT INTO fudscoops_savings (savings_amount, month, year, member_idmember) 
                                                        VALUES (:savings_amount, :month, :year, :member_idmember)";
                                        $insertStmt = $con->prepare($insertQuery);
                                        $insertStmt->bindParam(':savings_amount', $savingsAmount, PDO::PARAM_STR);
                                        $insertStmt->bindParam(':month', $month, PDO::PARAM_INT);
                                        $insertStmt->bindParam(':year', $year, PDO::PARAM_INT);
                                        $insertStmt->bindParam(':member_idmember', $staffid, PDO::PARAM_INT);

                                        if ($insertStmt->execute()) {
                                            $status = 'Inserted';
                                            $inserted++;
                                        } else {
                                            $status = 'Insert failed';
                                            $skipped++;
                                        }
                                    }
                                }
                            }
                        }

                        // Record for report
                        $reportData[] = [$i, $stafNo, $savingsAmount, $month, $year, $status];
                    }
                    $i++;
                }

                fclose($handle);

                // Save to session for download
                $_SESSION['upload_report_data'] = $reportData;

                // Build HTML table
                $resultTable = '
                <table id="example1" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>SN</th>
                            <th>Staff ID</th>
                            <th>Savings Amount</th>
                            <th>Month</th>
                            <th>Year</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>';

                foreach ($reportData as $row) {
                    $class = '';
                    if (strpos($row[5], 'Inserted') !== false) {
                        $class = 'class="text-success"';
                    } elseif (strpos($row[5], 'Error') !== false || strpos($row[5], 'failed') !== false) {
                        $class = 'class="text-danger"';
                    } elseif (strpos($row[5], 'exists') !== false) {
                        $class = 'class="text-warning"';
                    }

                    $resultTable .= "<tr><td>{$row[0]}</td><td>{$row[1]}</td><td>{$row[2]}</td><td>{$row[3]}</td><td>{$row[4]}</td><td $class>{$row[5]}</td></tr>";
                }

                $resultTable .= '</tbody></table>';

                // Final report
                echo "<div class='alert alert-info'>
                        <strong>Upload Summary:</strong><br>
                        $inserted record(s) inserted<br>
                        $skipped record(s) skipped
                      </div>";
                echo $resultTable;

                // Download button
                echo '<div class="mt-3 text-center">
                        <a href="?download_report=1" class="btn btn-success">
                            <i class="fa fa-download"></i> Download This Report as CSV
                        </a>
                      </div>';

            } else {
                echo '<h3 class="text-danger">Error: File could not be uploaded!</h3>';
            }
        } else {
            echo '<h3 class="text-danger">Error: File size must be less than 500KB!</h3>';
        }
    } else {
        echo '<h3 class="text-danger">Error: Only CSV files are allowed!</h3>';
    }
} else {
    echo '<h3 class="text-danger">Error: Please select a CSV file and provide Month/Year!</h3>';
}
?>