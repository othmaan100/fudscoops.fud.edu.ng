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
    'KETIC BANK' => 10,
    // Add more as needed — or query from a `banks` table if available
];

if (isset($_FILES['uploadFile'])) {
    if (!empty($_FILES['uploadFile']['name'])) {
        $allowedExtensions = ["csv"];
        $ext = strtolower(pathinfo($_FILES['uploadFile']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowedExtensions)) {
            $file_size = $_FILES['uploadFile']['size'] / 1024; // KB

            if ($file_size < 500) {
                $isUploaded = copy($_FILES['uploadFile']['tmp_name'], "../resources/uploads/" . $_FILES['uploadFile']['name']);

                if ($isUploaded) {
                    $handle = fopen($_FILES['uploadFile']['tmp_name'], 'r');
                    $i = 0;
                    $inserted = 0;
                    $skipped = 0;

                    $resultTable = '<table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>SN</th>
                                <th>Staff Number</th>
                                <th>Withdrawal Amount</th>
                                <th>Date</th>
                                <th>Bank</th>
                                <th>Account Name</th>
                                <th>Account Number</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>';

                    $con = $db->getConnection();

                    while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                        if ($i > 0) { // Skip header row
                            // Expected CSV columns (0-based index):
                            // 0: Staff Number
                            // 1: Withdrawal Amount
                            // 2: Date (YYYY-MM-DD)
                            // 3: Bank
                            // 4: Account Name
                            // 5: Account Number

                            $staffNumber = isset($data[0]) ? trim($data[0]) : '';
                            $withdrawalAmount = isset($data[1]) ? trim($data[1]) : '';
                            $date = isset($data[2]) ? trim($data[2]) : '';
                            $bankName = isset($data[3]) ? trim($data[3]) : '';
                            $accountName = isset($data[4]) ? trim($data[4]) : '';
                            $accountNumber = isset($data[5]) ? trim($data[5]) : '';

                            // Validate required fields
                            if (empty($staffNumber) || empty($withdrawalAmount) || empty($date) || 
                                empty($bankName) || empty($accountName) || empty($accountNumber)) {
                                $resultTable .= "<tr>
                                    <td>$i</td>
                                    <td>$staffNumber</td>
                                    <td>$withdrawalAmount</td>
                                    <td>$date</td>
                                    <td>$bankName</td>
                                    <td>$accountName</td>
                                    <td>$accountNumber</td>
                                    <td class='text-danger'>Missing required field(s)</td>
                                </tr>";
                                $skipped++;
                                $i++;
                                continue;
                            }

                            // Validate numeric amount
                            if (!is_numeric($withdrawalAmount) || $withdrawalAmount <= 0) {
                                $resultTable .= "<tr>
                                    <td>$i</td>
                                    <td>$staffNumber</td>
                                    <td>$withdrawalAmount</td>
                                    <td>$date</td>
                                    <td>$bankName</td>
                                    <td>$accountName</td>
                                    <td>$accountNumber</td>
                                    <td class='text-danger'>Invalid withdrawal amount</td>
                                </tr>";
                                $skipped++;
                                $i++;
                                continue;
                            }

                            // Validate date format (YYYY-MM-DD)
                            if (!DateTime::createFromFormat('Y-m-d', $date)) {
                                $resultTable .= "<tr>
                                    <td>$i</td>
                                    <td>$staffNumber</td>
                                    <td>$withdrawalAmount</td>
                                    <td>$date</td>
                                    <td>$bankName</td>
                                    <td>$accountName</td>
                                    <td>$accountNumber</td>
                                    <td class='text-danger'>Invalid date format (use YYYY-MM-DD)</td>
                                </tr>";
                                $skipped++;
                                $i++;
                                continue;
                            }

                            // Get employee ID from staff number
                            $employeeid = $user->getEmployeeId($staffNumber);
                            if (empty($employeeid)) {
                                $resultTable .= "<tr>
                                    <td>$i</td>
                                    <td>$staffNumber</td>
                                    <td>$withdrawalAmount</td>
                                    <td>$date</td>
                                    <td>$bankName</td>
                                    <td>$accountName</td>
                                    <td>$accountNumber</td>
                                    <td class='text-danger'>Invalid Staff Number (employee not found)</td>
                                </tr>";
                                $skipped++;
                                $i++;
                                continue;
                            }

                            // Map bank name to ID (case-insensitive)
                            $bankNameUpper = strtoupper($bankName);
                            $bank_id = $bankNameToId[$bankNameUpper] ?? null;

                            if ($bank_id === null) {
                                $resultTable .= "<tr>
                                    <td>$i</td>
                                    <td>$staffNumber</td>
                                    <td>$withdrawalAmount</td>
                                    <td>$date</td>
                                    <td>$bankName</td>
                                    <td>$accountName</td>
                                    <td>$accountNumber</td>
                                    <td class='text-danger'>Unknown Bank: '$bankName' (not in approved list)</td>
                                </tr>";
                                $skipped++;
                                $i++;
                                continue;
                            }

                            // Get member_id from employee_id
                            $member_id = $user->getMemberId($employeeid);
                            if (!$member_id) {
                                $resultTable .= "<tr>
                                    <td>$i</td>
                                    <td>$staffNumber</td>
                                    <td>$withdrawalAmount</td>
                                    <td>$date</td>
                                    <td>$bankName</td>
                                    <td>$accountName</td>
                                    <td>$accountNumber</td>
                                    <td class='text-danger'>Member not found for this employee</td>
                                </tr>";
                                $skipped++;
                                $i++;
                                continue;
                            }

                            // Insert into fudscoops_withdrawals
                            $insertQuery = "INSERT INTO fudscoops_withrawals 
                                (member_id, approved_withrawal_amount, approved_date, bank_id, account_name, acount_number)
                                VALUES (:member_id, :amount, :date, :bank_id, :account_name, :account_number)";

                            $stmt = $con->prepare($insertQuery);
                            $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
                            $stmt->bindParam(':amount', $withdrawalAmount, PDO::PARAM_STR);
                            $stmt->bindParam(':date', $date, PDO::PARAM_STR);
                            $stmt->bindParam(':bank_id', $bank_id, PDO::PARAM_INT);
                            $stmt->bindParam(':account_name', $accountName, PDO::PARAM_STR);
                            $stmt->bindParam(':account_number', $accountNumber, PDO::PARAM_STR);

                            if ($stmt->execute()) {
                                $inserted++;
                                $status = "Withdrawal record added successfully";
                                $statusClass = "text-success";
                            } else {
                                $status = "Database insert failed";
                                $statusClass = "text-danger";
                                $skipped++;
                            }

                            $resultTable .= "<tr>
                                <td>$i</td>
                                <td>$staffNumber</td>
                                <td>$withdrawalAmount</td>
                                <td>$date</td>
                                <td>$bankName</td>
                                <td>$accountName</td>
                                <td>$accountNumber</td>
                                <td class='$statusClass'>$status</td>
                            </tr>";
                        }
                        $i++;
                    }

                    fclose($handle);
                    $resultTable .= '</tbody></table>';

                    echo "<div class='alert alert-info'>
                            <strong>Withdrawal Import Results:</strong><br>
                            $inserted withdrawal record(s) inserted<br>
                            $skipped record(s) skipped (invalid data)
                          </div>";
                    echo $resultTable;

                } else {
                    echo '<div class="alert alert-danger">Error: File could not be saved!</div>';
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
?>