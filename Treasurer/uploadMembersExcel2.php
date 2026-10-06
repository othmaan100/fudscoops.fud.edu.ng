<?php
require_once "../config/classes/DB.php";
require_once "../config/classes/User.php";
$db = new DB();
$user = new User();

if(isset($_FILES['uploadFile'])) {
    if(isset($_FILES['uploadFile']['name']) && $_FILES['uploadFile']['name'] != "") {
        $allowedExtensions = array("csv");
        $ext = pathinfo($_FILES['uploadFile']['name'], PATHINFO_EXTENSION);
        
        if(in_array($ext, $allowedExtensions)) {
            $file_size = $_FILES['uploadFile']['size'] / 1024; // KB
            
            if($file_size < 500) { // 500KB size limit
                $file = "../resources/uploads/".$_FILES['uploadFile']['name'];
                $isUploaded = copy($_FILES['uploadFile']['tmp_name'], $file);
                
                if($isUploaded) {
                    $handle = fopen($_FILES['uploadFile']['tmp_name'], 'r');
                    $i = 0;
                    $inserted = 0;
                    $skipped = 0;
                    $savings_updated = 0;
                    $members_updated = 0;
                    
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
                    $currentMonth = date("n"); // Numeric month without leading zero
                    $currentYear = date("Y");  // Current year
                    $currentDate = date('Y-m-d H:i:s'); // Current timestamp
                    
                    while(($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                        if($i > 0) { // Skip header row
                            $staffNumber = trim($data[0]); // Staff Number column
                            $monthlySavings = trim($data[1]); // Monthly Savings column
                            $consolidatedSavings = isset($data[2]) ? trim($data[2]) : 0; // Consolidated Savings column
                            
                            $employeeid = $user->getEmployeeId($staffNumber);
                            $IPPIS_no = $user->getIPPIS_no($employeeid);
                            
                            // Validate data
                            if(empty($employeeid) || !is_numeric($monthlySavings) || !is_numeric($consolidatedSavings)) {
                                $resultTable .= "<tr>
                                    <td>$i</td>
                                    <td>$staffNumber</td>
                                    <td>$IPPIS_no</td>
                                    <td>$monthlySavings</td>
                                    <td>$consolidatedSavings</td>
                                    <td>$currentMonth</td>
                                    <td>$currentYear</td>
                                    <td class='text-danger'>Invalid data</td>
                                </tr>";
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
                            $chairman_approval=1;
                            
                            if($checkStmt->rowCount() == 0) {
                                // Insert new member
                                $insertQuery = "INSERT INTO fudscoops_member 
                                              (employee_id, proposed_monthly_savings,chairman_approval, treasurer_approval) 
                                              VALUES (:employeeid, :savings,:chairman_approval, 1)";
                                
                                $insertStmt = $con->prepare($insertQuery);
                                $insertStmt->bindParam(':employeeid', $employeeid, PDO::PARAM_STR);
                                $insertStmt->bindParam(':savings', $monthlySavings, PDO::PARAM_STR);
                                $insertStmt->bindParam(':chairman_approval', $chairman_approval, PDO::PARAM_INT);

                                
                                if($insertStmt->execute()) {
                                    $member_id = $con->lastInsertId();
                                    $action = 'New Member Record Inserted';
                                    $inserted++;
                                } else {
                                    $resultTable .= "<tr>
                                        <td>$i</td>
                                        <td>$staffNumber</td>
                                        <td>$IPPIS_no</td>
                                        <td>$monthlySavings</td>
                                        <td>$consolidatedSavings</td>
                                        <td>$currentMonth</td>
                                        <td>$currentYear</td>
                                        <td class='text-danger'>Member creation failed</td>
                                    </tr>";
                                    $skipped++;
                                    $i++;
                                    continue;
                                }
                            } else {
                                // Get existing member ID and update proposed monthly savings
                                $row = $checkStmt->fetch(PDO::FETCH_ASSOC);
                                $member_id = $row['member_id'];
                                $action = 'Existing member';
                                
                                // Update member's proposed monthly savings
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
                                
                                if($updateMemberStmt->execute()) {
                                    $members_updated++;
                                    $action .= ' (savings updated)';
                                } else {
                                    $action .= ' (savings update failed)';
                                }
                            }
                            
                            // Check if savings record exists for current month/year
                            $savingsQuery = "SELECT savings_id FROM fudscoops_savings 
                                             WHERE month = :month AND year = :year 
                                             AND member_idmember = :member_id";
                            $savingsStmt = $con->prepare($savingsQuery);
                            $savingsStmt->bindParam(':month', $currentMonth, PDO::PARAM_INT);
                            $savingsStmt->bindParam(':year', $currentYear, PDO::PARAM_INT);
                            $savingsStmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
                            $savingsStmt->execute();
                            
                            if($savingsStmt->rowCount() == 0) {
                                // Insert new savings record
                                $insertSavingsQuery = "INSERT INTO fudscoops_savings 
                                                     (member_idmember, month, year, savings_amount, savings_date) 
                                                     VALUES (:member_id, :month, :year, :consolidated, NOW())";
                                
                                $insertSavingsStmt = $con->prepare($insertSavingsQuery);
                                $insertSavingsStmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
                                $insertSavingsStmt->bindParam(':month', $currentMonth, PDO::PARAM_INT);
                                $insertSavingsStmt->bindParam(':year', $currentYear, PDO::PARAM_INT);
                                $insertSavingsStmt->bindParam(':consolidated', $consolidatedSavings, PDO::PARAM_STR);
                              
                                
                                if($insertSavingsStmt->execute()) {
                                    $savings_updated++;
                                    $status = "Savings Record updated - $action";
                                    $statusClass = "text-success";
                                } else {
                                    $status = "Savings Record update failed - $action";
                                    $statusClass = "text-danger";
                                }
                            } else {
                                // Update existing savings record
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
                                
                                if($updateSavingsStmt->execute()) {
                                    $savings_updated++;
                                    $status = "Savings updated - $action";
                                    $statusClass = "text-success";
                                } else {
                                    $status = "Savings update failed - $action";
                                    $statusClass = "text-danger";
                                }
                            }
                            
                            $resultTable .= "<tr>
                                <td>$i</td>
                                <td>$staffNumber</td>
                                <td>$IPPIS_no</td>
                                <td>$monthlySavings</td>
                                <td>$consolidatedSavings</td>
                                <td>$currentMonth</td>
                                <td>$currentYear</td>
                                <td class='$statusClass'>$status</td>
                            </tr>";
                        }
                        $i++;
                    }
                    
                    fclose($handle);
                    
                    $resultTable .= '</tbody></table>';
                    
                    // Display results
                    echo "<div class='alert alert-info'>
                            <strong>Import Results:</strong><br>
                            $inserted new members inserted<br>
                            $members_updated existing members updated<br>
                            $savings_updated savings records updated/created<br>
                            $skipped records skipped (invalid data)
                          </div>";
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
?>