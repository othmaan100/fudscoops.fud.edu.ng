<?php
require_once "../config/classes/DB.php";
require_once "../config/classes/User.php";

$db = new DB();
$user = new User();

// Simple bank name to ID mapping (update as per your bank table)
$bankNameToId = [
    'ZENITH BANK' => 1,
    'FIRST BANK' => 2,
    'GTB' => 3,
    'ACCESS BANK' => 4,
    'UBA' => 5,
    'FCMB' => 6,
    'STANBIC IBTC' => 7,
    'ECOBANK' => 8,
    'FIDELITY BANK' => 9,
    'KESTER BANK' => 10, // Fixed "KETIC" → likely meant "KESTER" or another; adjust as needed
    // Add more as needed
];

if (isset($_FILES['uploadFile'])) {
    if (!empty($_FILES['uploadFile']['name'])) {
        $allowedExtensions = ["csv"];
        $ext = strtolower(pathinfo($_FILES['uploadFile']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowedExtensions)) {
            $file_size = $_FILES['uploadFile']['size'] / 1024; // KB

            if ($file_size < 500) {
                $uploadPath = "../resources/uploads/" . $_FILES['uploadFile']['name'];
                $isUploaded = move_uploaded_file($_FILES['uploadFile']['tmp_name'], $uploadPath);

                if ($isUploaded) {
                    $handle = fopen($uploadPath, 'r');
                    $i = 0;
                    $inserted = 0;
                    $updated = 0;
                    $skipped = 0;

                    $resultTable = '<table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>SN</th>
                                <th>Withdrawal ID</th>
                                <th>Staff Number</th>
                                <th>Full Name</th>
                                <th>Bank</th>
                                <th>Account Number</th>
                                <th>Account Name</th>
                                <th>Approved Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>';

                    $con = $db->getConnection();
                    $currentDate = date('Y-m-d'); // For new records

                    while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                        if ($i > 0) { // Skip header row
                            // CSV Columns:
                            // 0: Withdrawal ID
                            // 1: Staff Number
                            // 2: Full Name
                            // 3: Bank
                            // 4: Account Number
                            // 5: Account Name
                            // 6: Approved Withdrawal Amount

                            $withdrawalId = isset($data[0]) ? trim($data[0]) : '';
                            $staffNumber = isset($data[1]) ? trim($data[1]) : '';
                            $fullName = isset($data[2]) ? trim($data[2]) : '';
                            $bankName = isset($data[3]) ? trim($data[3]) : '';
                            $accountNumber = isset($data[4]) ? trim($data[4]) : '';
                            $accountName = isset($data[5]) ? trim($data[5]) : '';
                            $approvedAmount = isset($data[6]) ? trim($data[6]) : '';

                            // Validate required fields (even for updates, amount is needed)
                            if (empty($approvedAmount)) {
                                $resultTable .= renderRow($i, $withdrawalId, $staffNumber, $fullName, $bankName, $accountNumber, $accountName, $approvedAmount, 'text-danger', 'Approved amount is required');
                                $skipped++;
                                $i++;
                                continue;
                            }

                            if (!is_numeric($approvedAmount) || $approvedAmount <= 0) {
                                $resultTable .= renderRow($i, $withdrawalId, $staffNumber, $fullName, $bankName, $accountNumber, $accountName, $approvedAmount, 'text-danger', 'Invalid approved amount');
                                $skipped++;
                                $i++;
                                continue;
                            }

                            // Map bank name to ID
                            $bank_id = strtoupper($bankName);
                           // $bank_id = $bankNameToId[$bankNameUpper] ?? null;

                            if ($bank_id === null && !empty($bankName)) {
                                $resultTable .= renderRow($i, $withdrawalId, $staffNumber, $fullName, $bankName, $accountNumber, $accountName, $approvedAmount, 'text-danger', "Unknown Bank: '$bankName'");
                                $skipped++;
                                $i++;
                                continue;
                            }

                            // Case 1: Withdrawal ID provided → UPDATE
                            if (!empty($withdrawalId)) {
                                // Validate Withdrawal ID is numeric
                                if (!is_numeric($withdrawalId)) {
                                    $resultTable .= renderRow($i, $withdrawalId, $staffNumber, $fullName, $bankName, $accountNumber, $accountName, $approvedAmount, 'text-danger', 'Invalid Withdrawal ID');
                                    $skipped++;
                                    $i++;
                                    continue;
                                }

                                // Check if record exists 
                                $checkQuery = "SELECT withrawals_id FROM fudscoops_withrawals WHERE withrawals_id = :id";
                                $checkStmt = $con->prepare($checkQuery);
                                $checkStmt->bindParam(':id', $withdrawalId, PDO::PARAM_INT);
                                $checkStmt->execute();

                                if ($checkStmt->rowCount() == 0) {
                                    $resultTable .= renderRow($i, $withdrawalId, $staffNumber, $fullName, $bankName, $accountNumber, $accountName, $approvedAmount, 'text-danger', 'Withdrawal ID not found');
                                    $skipped++;
                                    $i++;
                                    continue;
                                }

                                // Update approved_withdrawal_amount only
                                $updateQuery = "UPDATE fudscoops_withrawals 
                                                SET approved_withrawal_amount = :amount 
                                                WHERE withrawals_id = :id";
                                $updateStmt = $con->prepare($updateQuery);
                                $updateStmt->bindParam(':amount', $approvedAmount, PDO::PARAM_STR);
                                $updateStmt->bindParam(':id', $withdrawalId, PDO::PARAM_INT);

                                if ($updateStmt->execute()) {
                                    $updated++;
                                    $status = "Record updated successfully";
                                    $statusClass = "text-success";
                                } else {
                                    $status = "Update failed";
                                    $statusClass = "text-danger";
                                    $skipped++;
                                }

                                $resultTable .= renderRow($i, $withdrawalId, $staffNumber, $fullName, $bankName, $accountNumber, $accountName, $approvedAmount, $statusClass, $status);
                            }
                            // Case 2: No Withdrawal ID → INSERT new record
                            else {
                                if (empty($staffNumber) || empty($bankName) || empty($accountNumber) || empty($accountName)) {
                                    $resultTable .= renderRow($i, $withdrawalId, $staffNumber, $fullName, $bankName, $accountNumber, $accountName, $approvedAmount, 'text-danger', 'Staff Number, Bank, Account Number, and Account Name are required for new records');
                                    $skipped++;
                                    $i++;
                                    continue;
                                }

                                $employeeid = $user->getEmployeeId($staffNumber);
                                if (empty($employeeid)) {
                                    $resultTable .= renderRow($i, $withdrawalId, $staffNumber, $fullName, $bankName, $accountNumber, $accountName, $approvedAmount, 'text-danger', 'Invalid Staff Number');
                                    $skipped++;
                                    $i++;
                                    continue;
                                }

                                $member_id = $user->getMemberId($employeeid);
                                if (!$member_id) {
                                    $resultTable .= renderRow($i, $withdrawalId, $staffNumber, $fullName, $bankName, $accountNumber, $accountName, $approvedAmount, 'text-danger', 'Member not found');
                                    $skipped++;
                                    $i++;
                                    continue;
                                }

                                // Insert new withdrawal
                                $insertQuery = "INSERT INTO fudscoops_withrawals 
                                    (member_id, approved_withrawal_amount, approved_date, bank_id, account_name, acount_number)
                                    VALUES (:member_id, :amount, :date, :bank_id, :account_name, :account_number)";

                                $stmt = $con->prepare($insertQuery);
                                $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
                                $stmt->bindParam(':amount', $approvedAmount, PDO::PARAM_STR);
                                $stmt->bindParam(':date', $currentDate, PDO::PARAM_STR);
                                $stmt->bindParam(':bank_id', $bank_id, PDO::PARAM_INT);
                                $stmt->bindParam(':account_name', $accountName, PDO::PARAM_STR);
                                $stmt->bindParam(':account_number', $accountNumber, PDO::PARAM_STR);

                                if ($stmt->execute()) {
                                    $inserted++;
                                    $status = "New record inserted successfully";
                                    $statusClass = "text-success";
                                } else {
                                    $status = "Insert failed";
                                    $statusClass = "text-danger";
                                    $skipped++;
                                }

                                $resultTable .= renderRow($i, $withdrawalId, $staffNumber, $fullName, $bankName, $accountNumber, $accountName, $approvedAmount, $statusClass, $status);
                            }
                        }
                        $i++;
                    }

                    fclose($handle);
                    $resultTable .= '</tbody></table>';

                    echo "<div class='alert alert-info'>
                            <strong>Withdrawal Import Results:</strong><br>
                            $inserted new record(s) inserted<br>
                            $updated record(s) updated<br>
                            $skipped record(s) skipped
                          </div>";
                    echo $resultTable;

                } else {
                    echo '<div class="alert alert-danger">Error: File could not be uploaded!</div>';
                }
            } else {
                echo '<div class="alert alert-danger">Error: File size must be less than 500KB!</div>';
            }
        } else {
            echo '<div class="alert alert-danger">Error: Only CSV files are allowed!</div>';
        }
    } else {
        echo '<div class="alert alert-danger">Error: Please select a CSV file!</div>';
    }
} else {
    echo '<div class="alert alert-danger">Error: No file was uploaded!</div>'; 
}

// Helper function to render table rows cleanly
function renderRow($sn, $wid, $staff, $name, $bank, $accNo, $accName, $amount, $class, $status) {
    return "<tr>
        <td>$sn</td>
        <td>" . htmlspecialchars($wid ?: '&mdash;') . "</td>
        <td>" . htmlspecialchars($staff) . "</td>
        <td>" . htmlspecialchars($name) . "</td>
        <td>" . htmlspecialchars($bank) . "</td>
        <td>" . htmlspecialchars($accNo) . "</td>
        <td>" . htmlspecialchars($accName) . "</td>
        <td>" . htmlspecialchars($amount) . "</td>
        <td class='$class'>$status</td>
    </tr>";
}
?>