<?php
require_once "../config/classes/DB.php";
require_once "../config/classes/User.php";

$db = new DB();
$user = new User();

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

                $con = $db->getConnection();
                $month = $_POST['month'] ?? '';
                $year = $_POST['year'] ?? '';

                // Validate month and year
                if (!is_numeric($month) || !is_numeric($year) || $month < 1 || $month > 12 || $year < 2000 || $year > 2100) {
                    echo '<h3 class="text-danger">Error: Invalid Month or Year provided.</h3>';
                    fclose($handle);
                    exit;
                }

                while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                    if ($i > 0) {
                        $stafNo = isset($data[0]) ? trim($data[0]) : '';
                        $savingsAmount = isset($data[1]) ? trim($data[1]) : '';

                        // Validate Staff ID
                        if (empty($stafNo)) {
                            $resultTable .= "<tr><td>{$i}</td><td>N/A</td><td>{$savingsAmount}</td><td>{$month}</td><td>{$year}</td><td class='text-danger'>Missing Staff ID</td></tr>";
                            $skipped++;
                            $i++;
                            continue;
                        }

                        // Validate Savings Amount
                        if (!is_numeric($savingsAmount) || $savingsAmount <= 0) {
                            $resultTable .= "<tr><td>{$i}</td><td>{$stafNo}</td><td>{$savingsAmount}</td><td>{$month}</td><td>{$year}</td><td class='text-danger'>Invalid amount</td></tr>";
                            $skipped++;
                            $i++;
                            continue;
                        }

                        // Get Employee ID
                        $employeeid = $user->getEmployeeId($stafNo);
                        if (empty($employeeid)) {
                            $resultTable .= "<tr><td>{$i}</td><td>{$stafNo}</td><td>{$savingsAmount}</td><td>{$month}</td><td>{$year}</td><td class='text-danger'>Staff ID not found</td></tr>";
                            $skipped++;
                            $i++;
                            continue;
                        }

                        // Get Member ID
                        $staffid = $user->getMemberId($employeeid);
                        if (empty($staffid)) {
                            $resultTable .= "<tr><td>{$i}</td><td>{$stafNo}</td><td>{$savingsAmount}</td><td>{$month}</td><td>{$year}</td><td class='text-danger'>Not a registered member</td></tr>";
                            $skipped++;
                            $i++;
                            continue;
                        }

                        // Check for duplicate
                        $checkQuery = "SELECT 1 FROM fudscoops_savings 
                                       WHERE month = :month AND year = :year AND member_idmember = :staffid";
                        $checkStmt = $con->prepare($checkQuery);
                        $checkStmt->bindParam(':month', $month, PDO::PARAM_INT);
                        $checkStmt->bindParam(':year', $year, PDO::PARAM_INT);
                        $checkStmt->bindParam(':staffid', $staffid, PDO::PARAM_INT);
                        $checkStmt->execute();

                        if ($checkStmt->rowCount() > 0) {
                            $resultTable .= "<tr><td>{$i}</td><td>{$stafNo}</td><td>{$savingsAmount}</td><td>{$month}</td><td>{$year}</td><td class='text-warning'>Already exists</td></tr>";
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
                                $inserted++;
                                $resultTable .= "<tr><td>{$i}</td><td>{$stafNo}</td><td>{$savingsAmount}</td><td>{$month}</td><td>{$year}</td><td class='text-success'>Inserted</td></tr>";
                            } else {
                                $resultTable .= "<tr><td>{$i}</td><td>{$stafNo}</td><td>{$savingsAmount}</td><td>{$month}</td><td>{$year}</td><td class='text-danger'>Insert failed</td></tr>";
                                $skipped++;
                            }
                        }
                    }
                    $i++;
                }

                fclose($handle);
                $resultTable .= '</tbody></table>';

                // Final report
                echo "<div class='alert alert-info'>
                        <strong>Upload Summary:</strong><br>
                        $inserted record(s) inserted<br>
                        $skipped record(s) skipped
                      </div>";
                echo $resultTable;

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