<?php
require_once "../config/classes/DB.php";
require_once "../config/classes/User.php";
require_once "../config/classes/MemberG3.php";
$db = new DB();
$user = new User();
$member = new MemberG3();
if (empty($_POST['file_type']) || $_POST['file_type'] === '') {
    echo '<div class="alert alert-danger">Error: Please select a Loan Type!</div>';
    exit();
}
$loanTypeId = $_POST['file_type'];
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
                    $loand_updated = 0;
                    
                    $resultTable = '<table id="loanTable" class="display table table-striped table-bordered" width="100%">
                                    <thead>
                                        <tr>
                                            <th>SN</th>
                                            <th>Staff Number</th>
                                            <th>Loan Amount</th>
                                            <th>Repayment Amount</th>
                                            <th>Loan Tenor(in Month)</th>
                                            <th>Loan Status</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>';
                    
                    $con = $db->getConnection();
                    $currentMonth = date("n"); // Numeric month without leading zero
                    $currentYear = date("Y");  // Current year
                    
                    while(($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                        if($i > 0) { // Skip header row
                            $spNo = trim($data[0]); // Staff Number column
                            $loanAmount = trim($data[1]); // Loan Amount column
                            // $loanTypeName = trim($data[2]); // Loan Type ID column
                            $guarantorSpNo = trim($data[2]); // Guarantor column
                            $loanTenor = trim($data[3]); // Loan Tenor column
                            
                            $itermName = trim($data[4]); // Iterm Name column
                            $supplierName = trim($data[5]); // Supplier Name column
                            $supplierAddress = trim($data[6]); // Supplier Address column
                            $supplierGsm = trim($data[7]); // Supplier GSM column
                            $supplierBankName = trim($data[8]); // Supplier Bank column
                            $supplierBank = $member->getBankId($supplierBankName); // getting the bank ID
                            
                            $supplierAccountNumber = trim($data[9]); // Supplier Account Number column

                                $memberId = $user->getMemberId($user->getEmployeeId($spNo));
                                $guarantorId = $user->getEmployeeId($guarantorSpNo);
                                 // ========== calculate the loan repayment Amount start =======
                                    // to determine the monthly rate
                                        $monthly_rate = 0.06 / 12; // Assuming an annual interest rate of 6%
                                    // Apply the loan repayment formula (fixed-rate loan formula)
                                    if ($loanAmount > 0 && $loanTenor > 0) {
                                        
                                        // $repaymentAmount = $loanAmount * $monthly_rate * pow(1 + $monthly_rate, $loanTenor) / (pow(1 + $monthly_rate, $loanTenor) - 1);
                                        $repaymentAmount = $loanAmount / $loanTenor;
                                    }
                                    // echo $loanAmount.' repay'.$repaymentAmount;
                                    // die();
                                 // ========== calculating the laon repayment amount end ============
        
                            // Validate data (check if valid member ID, loan amount, etc.)
                            if(empty($memberId) || !is_numeric($loanAmount) || !is_numeric($repaymentAmount) || !is_numeric($loanTenor)) {
                                $resultTable .= "<tr>
                                    <td>$i</td>
                                    <td>$spNo</td>
                                    <td>$loanAmount</td>
                                    <td>$repaymentAmount</td>
                                    <td>$loanTenor</td>
                                    <td>Invalid data</td>
                                    <td class='text-danger'>Invalid data</td>
                                </tr>";
                                $skipped++;
                                $i++;
                                continue;
                            }

                            // Check if loan exists for the member
                            $loanDate = date('Y-m-d');
                            $loanStatus = 'Approved';
                            
                            $sqlCheck = "SELECT loan_id FROM fudscoops_loan WHERE member_id = :member_id";
                            $stmtCheck = $con->prepare($sqlCheck);
                            $stmtCheck->bindParam(':member_id', $memberId, PDO::PARAM_INT);
                            $stmtCheck->execute();

                            if ($stmtCheck->rowCount() > 0) {
                                // Loan exists, update it
                                $loanData = $stmtCheck->fetch(PDO::FETCH_ASSOC);
                                $loan_id = $loanData['loan_id'];

                                // Update loan
                                $sqlUpdate = "UPDATE fudscoops_loan 
                                              SET loan_amount = :loan_amount, loan_date = :loan_date, loan_status = :loan_status, 
                                                  loan_tenor = :loan_tenor, loan_type_id = :loan_type_id, guarantor_employee_id = :guarantor,amount_recommended=:amount_recommended  
                                              WHERE loan_id = :loan_id";
                                $stmtUpdate = $con->prepare($sqlUpdate);
                                $stmtUpdate->bindParam(':loan_amount', $loanAmount, PDO::PARAM_STR);
                                $stmtUpdate->bindParam(':loan_date', $loanDate, PDO::PARAM_STR);
                                $stmtUpdate->bindParam(':loan_status', $loanStatus, PDO::PARAM_STR);
                                $stmtUpdate->bindParam(':loan_tenor', $loanTenor, PDO::PARAM_STR);
                                $stmtUpdate->bindParam(':loan_type_id', $loanTypeId, PDO::PARAM_INT);
                                $stmtUpdate->bindParam(':guarantor', $guarantorId, PDO::PARAM_INT);
                                $stmtUpdate->bindParam(':loan_id', $loan_id, PDO::PARAM_INT);
                                $stmtUpdate->bindParam(':amount_recommended', $loanAmount, PDO::PARAM_INT);

                                if ($stmtUpdate->execute()) {
                                    $updated++;
                                    $status = "Loan updated successfully.";
                                    $statusClass = "text-success";
                                } else {
                                    $status = "Failed to update loan.";
                                    $statusClass = "text-danger";
                                }
                            } else {
                                $status =1;
                                $date=date('Y-m-d H:i:s');
                                // Loan does not exist, create a new loan
                                $sqlInsert = "INSERT INTO fudscoops_loan (loan_amount, loan_date, loan_status, loan_tenor, loan_type_id, member_id, guarantor_employee_id,iterm_name,guarantor_status,guarantor_approved_date,amount_recommended) 
                                              VALUES (:loan_amount, :loan_date, :loan_status, :loan_tenor, :loan_type_id, :member_id, :guarantor,:iterm_name,:status,:g_approve,:amount_recommended)";
                                $stmtInsert = $con->prepare($sqlInsert);
                                $stmtInsert->bindParam(':loan_amount', $loanAmount, PDO::PARAM_STR);
                                $stmtInsert->bindParam(':loan_date', $loanDate, PDO::PARAM_STR);
                                $stmtInsert->bindParam(':loan_status', $loanStatus, PDO::PARAM_STR);
                                $stmtInsert->bindParam(':loan_tenor', $loanTenor, PDO::PARAM_STR);
                                $stmtInsert->bindParam(':iterm_name', $itermName, PDO::PARAM_STR);
                                $stmtInsert->bindParam(':loan_type_id', $loanTypeId, PDO::PARAM_INT);
                                $stmtInsert->bindParam(':guarantor', $guarantorId, PDO::PARAM_INT);
                                $stmtInsert->bindParam(':member_id', $memberId, PDO::PARAM_INT);
                                $stmtInsert->bindParam(':status', $status, PDO::PARAM_INT);
                                $stmtInsert->bindParam(':g_approve', $date, PDO::PARAM_STR);
                                $stmtInsert->bindParam(':amount_recommended', $loanAmount, PDO::PARAM_INT);

                                if ($stmtInsert->execute()) {
                                    $lastId = $con->lastInsertId();

                                    // Insert supplier data for new loan
                                    $sqlInsertSupplier = "INSERT INTO fudscoops_suppliers (supplier_name, address, gsm, account_number, bank_id, loan_id,bank_name,date) 
                                                          VALUES (:supplier_name, :address, :gsm, :account_number, :bank_id, :loan_id,:bank_name,:date)";
                                    $stmtInsertSupplier = $con->prepare($sqlInsertSupplier);
                                    $stmtInsertSupplier->bindParam(':supplier_name', $supplierName, PDO::PARAM_STR); 
                                    $stmtInsertSupplier->bindParam(':address', $supplierAddress, PDO::PARAM_STR); 
                                    $stmtInsertSupplier->bindParam(':gsm', $supplierGsm, PDO::PARAM_STR); 
                                    $stmtInsertSupplier->bindParam(':account_number', $supplierAccountNumber, PDO::PARAM_STR); 
                                    $stmtInsertSupplier->bindParam(':bank_name', $supplierBankName, PDO::PARAM_STR); 
                                    $stmtInsertSupplier->bindParam(':bank_id', $supplierBank, PDO::PARAM_STR); 
                                    $stmtInsertSupplier->bindParam(':loan_id', $lastId, PDO::PARAM_INT);
                                    $stmtInsertSupplier->bindParam(':date', $date, PDO::PARAM_STR);
                                    $stmtInsertSupplier->execute();

                                    // Insert repayment record
                                    $sqlInsertRepayment = "INSERT INTO fudscoops_loan_repayments (member_id, loan_id, loan_repayment_amount, loan_repayment_date)
                                                           VALUES (:member_id, :loan_id, :repayment, NOW())";
                                    $stmtInsertRepayment = $con->prepare($sqlInsertRepayment);
                                    $stmtInsertRepayment->bindParam(':loan_id', $lastId, PDO::PARAM_INT);
                                    $stmtInsertRepayment->bindParam(':repayment', $repaymentAmount, PDO::PARAM_STR);
                                    $stmtInsertRepayment->bindParam(':member_id', $memberId, PDO::PARAM_INT);
                                    $stmtInsertRepayment->execute();

                                    $inserted++;
                                    $status = "Loan inserted successfully.";
                                    $statusClass = "text-success";
                                } else {
                                    $status = "Failed to insert loan.";
                                    $statusClass = "text-danger";
                                }
                            }

                            $resultTable .= "<tr><td>$i</td><td>$spNo</td><td>$loanAmount</td><td>$repaymentAmount</td><td>$loanTenor</td><td>$loanStatus</td><td class='$statusClass'>$status</td></tr>";
                        }
                        $i++;
                    }

                    fclose($handle);
                    $resultTable .= '</tbody></table>';

                    // Display the result table
                    echo "<div class='alert alert-info'>
                            <strong>Import Results:</strong><br>
                            $inserted new loans inserted<br>
                            $updated loans updated<br>
                            $skipped records skipped (either invalid or existing)
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
echo "<script>
            $(document).ready(function() {
                $('#loanTable').DataTable({
                    paging: true,
                    searching: true,
                    ordering: true,
                    responsive: true
                });
            });
          </script>";

?>