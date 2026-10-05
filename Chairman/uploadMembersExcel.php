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
                    
                    $resultTable = '<table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>SN</th>
                                            <th>Staff Number</th>
                                            <th>Monthly Savings</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>';
                    
                    $con = $db->getConnection();
                    
                    while(($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                        if($i > 0) { // Skip header row
                            $staffNumber = trim($data[0]); // Staff Number column
                            $monthlySavings = trim($data[1]); // Monthly Savings column
                            
                            $employeeid = $user->getEmployeeId($staffNumber);
                            
                            // Validate data
                            if(empty($employeeid) || !is_numeric($monthlySavings)) {
                                $resultTable .= "<tr>
                                    <td>$i</td>
                                    <td>$staffNumber</td>
                                    <td>$monthlySavings</td>
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
                            
                            if($checkStmt->rowCount() == 0) {
                                // Insert new member only if doesn't exist
                                $insertQuery = "INSERT INTO fudscoops_member 
                                              (employee_id, proposed_monthly_savings) 
                                              VALUES (:employeeid, :savings)";
                                
                                $insertStmt = $con->prepare($insertQuery);
                                $insertStmt->bindParam(':employeeid', $employeeid, PDO::PARAM_STR);
                                $insertStmt->bindParam(':savings', $monthlySavings, PDO::PARAM_STR);
                                
                                if($insertStmt->execute()) {
                                    $resultTable .= "<tr>
                                        <td>$i</td>
                                        <td>$staffNumber</td>
                                        <td>$monthlySavings</td>
                                        <td class='text-success'>Inserted</td>
                                    </tr>";
                                    $inserted++;
                                } else {
                                    $resultTable .= "<tr>
                                        <td>$i</td>
                                        <td>$staffNumber</td>
                                        <td>$monthlySavings</td>
                                        <td class='text-danger'>Insert failed</td>
                                    </tr>";
                                    $skipped++;
                                }
                            } else {
                                // Skip existing members
                                $resultTable .= "<tr>
                                    <td>$i</td>
                                    <td>$staffNumber</td>
                                    <td>$monthlySavings</td>
                                    <td class='text-warning'>Member exists (not updated)</td>
                                </tr>";
                                $skipped++;
                            }
                        }
                        $i++;
                    }
                    
                    fclose($handle);
                    
                    $resultTable .= '</tbody></table>';
                    
                    // Display results
                    echo "<div class='alert alert-info'>
                            <strong>Import Results:</strong><br>
                            $inserted new records inserted<br>
                            $skipped records skipped (either existing or invalid)
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