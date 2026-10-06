<?php
require_once "../config/classes/DB.php";
require_once "../config/classes/User.php";

$db = new DB();
$user = new User();

if (isset($_FILES['uploadFile'])) {
    if (!empty($_FILES['uploadFile']['name'])) {
        $allowedExtensions = ["csv"];
        $ext = strtolower(pathinfo($_FILES['uploadFile']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowedExtensions)) {
            $file_size = $_FILES['uploadFile']['size'] / 1024; // KB

            if ($file_size < 500) {
                $file = "../resources/uploads/" . $_FILES['uploadFile']['name'];
                $isUploaded = copy($_FILES['uploadFile']['tmp_name'], $file);

                if ($isUploaded) {
                    $handle = fopen($_FILES['uploadFile']['tmp_name'], 'r');
                    $i = 0;
                    $inserted = 0;
                    $skipped = 0;
                    $savings_updated = 0;
                    $members_updated = 0;

                    // Collect failed / skipped rows here so we can export them as CSV
                    $failedRows = [];
                    $failedHeader = [
                        'SN', 'Staff Number', 'IPPIS Number', 'Monthly Savings',
                        'Consolidated Savings Amount', 'Month', 'Year', 'Reason'
                    ];

                    $resultTable = '<table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>SN</th>
                                <th>Staff Number</th>
                                <th>IPPIS Number</th>
                                <th>Monthly Savings</th>
                                <th>Consolidated Savings Amount</th>
                                <th>Month</th>
                                <th>Year</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>';

                    $con = $db->getConnection();
                    $currentMonth = date("n");
                    $currentYear = date("Y");
                    $currentDate = date('Y-m-d H:i:s');

                    while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                        if ($i > 0) {
                            $staffNumber = trim($data[0]);
                            $monthlySavings = trim($data[1]);
                            $consolidatedSavings = isset($data[2]) ? trim($data[2]) : 0;

                            $employeeid = $user->getEmployeeId($staffNumber);
                            $IPPIS_no = $user->getIPPIS_no($employeeid);

                            // Escape for safe HTML output
                            $safeStaffNumber = htmlspecialchars($staffNumber, ENT_QUOTES);
                            $safeIPPIS_no = htmlspecialchars((string)$IPPIS_no, ENT_QUOTES);
                            $safeMonthlySavings = htmlspecialchars((string)$monthlySavings, ENT_QUOTES);
                            $safeConsolidatedSavings = htmlspecialchars((string)$consolidatedSavings, ENT_QUOTES);

                            if (empty($employeeid) || !is_numeric($monthlySavings) || !is_numeric($consolidatedSavings)) {
                                $reason = 'Invalid data';
                                $resultTable .= "<tr>
                                    <td>$i</td>
                                    <td>$safeStaffNumber</td>
                                    <td>$safeIPPIS_no</td>
                                    <td>$safeMonthlySavings</td>
                                    <td>$safeConsolidatedSavings</td>
                                    <td>$currentMonth</td>
                                    <td>$currentYear</td>
                                    <td class='text-danger'>$reason</td>
                                </tr>";

                                $failedRows[] = [
                                    $i, $staffNumber, $IPPIS_no, $monthlySavings,
                                    $consolidatedSavings, $currentMonth, $currentYear, $reason
                                ];

                                $skipped++;
                                $i++;
                                continue;
                            }

                            // Check if member exists
                            $checkQuery = "SELECT member_id FROM fudscoops_member WHERE employee_id = :employeeid";
                            $checkStmt = $con->prepare($checkQuery);
                            $checkStmt->bindParam(':employeeid', $employeeid, PDO::PARAM_STR);
                            $checkStmt->execute();

                            $member_id = null;
                            $action = '';
                            $chairman_approval = 1;

                            if ($checkStmt->rowCount() == 0) {
                                // Insert new member
                                $insertQuery = "INSERT INTO fudscoops_member 
                                              (employee_id, proposed_monthly_savings, chairman_approval,is_active, treasurer_approval) 
                                              VALUES (:employeeid, :savings, :chairman_approval,:is_active, 1)";

                                $insertStmt = $con->prepare($insertQuery);
                                $insertStmt->bindParam(':employeeid', $employeeid, PDO::PARAM_STR);
                                $insertStmt->bindParam(':savings', $monthlySavings, PDO::PARAM_STR);
                                $insertStmt->bindParam(':chairman_approval', $chairman_approval, PDO::PARAM_INT);
                                $insertStmt->bindParam(':is_active', $chairman_approval, PDO::PARAM_INT);

                                if ($insertStmt->execute()) {
                                    $member_id = $con->lastInsertId();
                                    $action = 'New Member Record Inserted';
                                    $inserted++;

                                    // Update user access for new member
                                    $sqlUpdateUser = "UPDATE user 
                                                      SET access_level = '1' 
                                                      WHERE username = :username";
                                    $stmtUpdateUser = $con->prepare($sqlUpdateUser);
                                    $stmtUpdateUser->bindParam(':username', $staffNumber, PDO::PARAM_STR);
                                    $stmtUpdateUser->execute();
                                } else {
                                    $reason = 'Member creation failed';
                                    $resultTable .= "<tr>
                                        <td>$i</td>
                                        <td>$safeStaffNumber</td>
                                        <td>$safeIPPIS_no</td>
                                        <td>$safeMonthlySavings</td>
                                        <td>$safeConsolidatedSavings</td>
                                        <td>$currentMonth</td>
                                        <td>$currentYear</td>
                                        <td class='text-danger'>$reason</td>
                                    </tr>";

                                    $failedRows[] = [
                                        $i, $staffNumber, $IPPIS_no, $monthlySavings,
                                        $consolidatedSavings, $currentMonth, $currentYear, $reason
                                    ];

                                    $skipped++;
                                    $i++;
                                    continue;
                                }
                            } else {
                                // Update existing member
                                $row = $checkStmt->fetch(PDO::FETCH_ASSOC);
                                $member_id = $row['member_id'];
                                $action = 'Existing member';

                                $updateMemberQuery = "UPDATE fudscoops_member 
                                                    SET proposed_monthly_savings = :savings,
                                                        chairman_approval = 1,
                                                        treasurer_approval = 1,
                                                        updated_at = :updated_at
                                                    WHERE member_id = :member_id";

                                $updateMemberStmt = $con->prepare($updateMemberQuery);
                                $updateMemberStmt->bindParam(':savings', $monthlySavings, PDO::PARAM_STR);
                                $updateMemberStmt->bindParam(':updated_at', $currentDate);
                                $updateMemberStmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);

                                if ($updateMemberStmt->execute()) {
                                    $members_updated++;
                                    $action .= ' (savings updated)';

                                    // Update user access for existing member
                                    $sqlUpdateUser = "UPDATE user 
                                                      SET access_level = '1' 
                                                      WHERE username = :username";
                                    $stmtUpdateUser = $con->prepare($sqlUpdateUser);
                                    $stmtUpdateUser->bindParam(':username', $staffNumber, PDO::PARAM_STR);
                                    $stmtUpdateUser->execute();
                                } else {
                                    $action .= ' (savings update failed)';
                                }
                            }

                            // Handle savings record (insert/update)
                            $savingsQuery = "SELECT savings_id FROM fudscoops_savings 
                                             WHERE month = :month AND year = :year 
                                             AND member_idmember = :member_id";
                            $savingsStmt = $con->prepare($savingsQuery);
                            $savingsStmt->bindParam(':month', $currentMonth, PDO::PARAM_INT);
                            $savingsStmt->bindParam(':year', $currentYear, PDO::PARAM_INT);
                            $savingsStmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
                            $savingsStmt->execute();

                            if ($savingsStmt->rowCount() == 0) {
                                $insertSavingsQuery = "INSERT INTO fudscoops_savings 
                                                     (member_idmember, month, year, savings_amount, savings_date, updated_at) 
                                                     VALUES (:member_id, :month, :year, :consolidated, NOW(), :updated_at)";
                                $insertSavingsStmt = $con->prepare($insertSavingsQuery);
                                $insertSavingsStmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
                                $insertSavingsStmt->bindParam(':month', $currentMonth, PDO::PARAM_INT);
                                $insertSavingsStmt->bindParam(':year', $currentYear, PDO::PARAM_INT);
                                $insertSavingsStmt->bindParam(':consolidated', $consolidatedSavings, PDO::PARAM_STR);
                                $insertSavingsStmt->bindParam(':updated_at', $currentDate);

                                if ($insertSavingsStmt->execute()) {
                                    $savings_updated++;
                                    $status = "Savings Record updated - $action";
                                    $statusClass = "text-success";
                                } else {
                                    $status = "Savings Record update failed - $action";
                                    $statusClass = "text-danger";

                                    $failedRows[] = [
                                        $i, $staffNumber, $IPPIS_no, $monthlySavings,
                                        $consolidatedSavings, $currentMonth, $currentYear, $status
                                    ];
                                    $skipped++;
                                }
                            } else {
                                $row = $savingsStmt->fetch(PDO::FETCH_ASSOC);
                                $savings_id = $row['savings_id'];

                                $updateSavingsQuery = "UPDATE fudscoops_savings 
                                                      SET savings_amount = :consolidated,
                                                          updated_at = :updated_at
                                                      WHERE savings_id = :savings_id";
                                $updateSavingsStmt = $con->prepare($updateSavingsQuery);
                                $updateSavingsStmt->bindParam(':consolidated', $consolidatedSavings, PDO::PARAM_STR);
                                $updateSavingsStmt->bindParam(':updated_at', $currentDate);
                                $updateSavingsStmt->bindParam(':savings_id', $savings_id, PDO::PARAM_INT);

                                if ($updateSavingsStmt->execute()) {
                                    $savings_updated++;
                                    $status = "Savings updated - $action";
                                    $statusClass = "text-success";
                                } else {
                                    $status = "Savings update failed - $action";
                                    $statusClass = "text-danger";

                                    $failedRows[] = [
                                        $i, $staffNumber, $IPPIS_no, $monthlySavings,
                                        $consolidatedSavings, $currentMonth, $currentYear, $status
                                    ];
                                    $skipped++;
                                }
                            }

                            $safeStatus = htmlspecialchars($status, ENT_QUOTES);
                            $resultTable .= "<tr>
                                <td>$i</td>
                                <td>$safeStaffNumber</td>
                                <td>$safeIPPIS_no</td>
                                <td>$safeMonthlySavings</td>
                                <td>$safeConsolidatedSavings</td>
                                <td>$currentMonth</td>
                                <td>$currentYear</td>
                                <td class='$statusClass'>$safeStatus</td>
                            </tr>";
                        }
                        $i++;
                    }

                    fclose($handle);
                    $resultTable .= '</tbody></table>';

                    echo "<div class='alert alert-info'>
                            <strong>Import Results:</strong><br>
                            $inserted new members inserted<br>
                            $members_updated existing members updated<br>
                            $savings_updated savings records updated/created<br>
                            $skipped records skipped (invalid data)
                          </div>";

                    // ---- Build a downloadable CSV of failed rows, if any ----
                    if (!empty($failedRows)) {
                        $downloadDir = "../resources/uploads/failed_imports/";
                        if (!is_dir($downloadDir)) {
                            mkdir($downloadDir, 0755, true);
                        }

                        // Unique, non-guessable-ish filename tied to this run
                        $failedFileName = "failed_records_" . date('Ymd_His') . "_" . bin2hex(random_bytes(4)) . ".csv";
                        $failedFilePath = $downloadDir . $failedFileName;

                        $fh = fopen($failedFilePath, 'w');
                        fputcsv($fh, $failedHeader);
                        foreach ($failedRows as $row) {
                            fputcsv($fh, $row);
                        }
                        fclose($fh);

                        // Store just the filename in session so the download handler
                        // can validate it belongs to this session rather than
                        // trusting an arbitrary path from the browser.
                        if (session_status() === PHP_SESSION_NONE) {
                            session_start();
                        }
                        $_SESSION['failed_import_file'] = $failedFileName;

                        echo "<div class='alert alert-warning d-flex justify-content-between align-items-center'>
                                <span><strong>" . count($failedRows) . "</strong> record(s) failed and were not saved.</span>
                                <a href='download_failed_records.php?file=" . urlencode($failedFileName) . "' 
                                   class='btn btn-sm btn-warning'>
                                   Download Failed Records (CSV)
                                </a>
                              </div>";
                    }

                    echo $resultTable;

                } else {
                    echo '<div class="alert alert-danger">Error: File not uploaded!</div>';
                }
            } else {
                echo '<div class="alert alert-danger">Error: Maximum file size should not exceed 500KB!</div>';
            }
        } else {
            echo '<div class="alert alert-danger">Error: Only CSV files are allowed!</div>';
        }
    } else {
        echo '<div class="alert alert-danger">Error: Please select a file first!</div>';
    }
} else {
    echo '<div class="alert alert-danger">Error: No file uploaded!</div>';
}