<?php
if(!isset($_SESSION)) 
    { 
        session_start(); 
    } 
    
require_once('User.php');
require_once('Payslip.php');

class MemberG3{
    private $db;
    
    public function addMember() {
        
    }
    
    public function editMember() {
        
    }
    
     public function deleteMember() {
        
    }
    
    //used in withdrawal  saving of member 
    public function saveWithdrawal($spNo,$bank_id,$acount_number,$withrawals_amount) {
            $member = new MemberG1();
    
            $db = new DB();
            $user = new User();
            $con = $db->getConnection();
            $employeeid = $user->getEmployeeId($spNo);
            
            
       
                    $sql = "INSERT INTO fudscoops_withrawals (member_id ,bank_id, acount_number, withrawals_amount, withrawals_date)
                            VALUES (:member_id, :bank_id, :acct_no, :withdrawal_amount, NOW())";
            
                    $stmt = $con->prepare($sql);
            
                    $stmt->bindParam(':bank_id', $bank_id);
                    $stmt->bindParam(':acct_no', $acount_number);
                    $stmt->bindParam(':member_id', $employeeid);
                    $stmt->bindParam(':withdrawal_amount', $withrawals_amount);
            
                    $stmt->execute();
            
                    if ($stmt) {
                        return 1;
                } else {
                    return -1;
                }
            }
            
            
    
    //Query to purchase a share 
  
    public function PurchaseShare($employeeid,$share_amount,$share_unit,$bank_name,$tellerno,$amount_paid,$share_amount_word){
        
            
            $member = new MemberG2();
            //return $next_of_kin_name. " ". $next_of_kin_gsm. " ". $next_of_kin_address." ". $employeeid." ". $monthly_savings;

    
            $db = new DB();
            $user = new User();
            $con = $db->getConnection();
            $memberid = $user->getMemberId($employeeid);
            
            $sql ="SELECT * FROM fudscoops_shares WHERE member_id='$memberid'";
            $stmt = $con->prepare($sql);
            $stmt->execute();
            $count = $stmt->rowCount();
            if($count>0){
                
              $sql ="UPDATE fudscoops_shares SET unit_price=:share_amount,shares_unit=:share_unit,
              bank_name=:bank_name,teller_no=:tellerno,amount_paid=:amount_paid,shares_amount_word=:share_amount_word WHERE member_id='$memberid'";
              $stmt = $con->prepare($sql);
              $stmt->bindParam(':share_amount', $share_amount,PDO::PARAM_STR);
              $stmt->bindParam(':share_unit', $share_unit,PDO::PARAM_STR);
              $stmt->bindParam(':bank_name', $bank_name,PDO::PARAM_STR);
              $stmt->bindParam(':tellerno', $tellerno,PDO::PARAM_STR);
              $stmt->bindParam(':amount_paid', $amount_paid,PDO::PARAM_STR);
              $stmt->bindParam(':share_amount_word', $share_amount_word,PDO::PARAM_STR);
              $stmt->execute();
              if ($stmt) {
                        return 1;
                } else {
                    return -1;
                }
              
            }else{
             
            
                $sql = "INSERT INTO fudscoops_shares(member_id, unit_price, shares_unit, bank_name, teller_no,amount_paid,shares_amount_word)
                VALUES (:memberid, :share_amount, :share_unit, :bank_name, :tellerno, :amount_paid, :share_amount_word)";
                // Create a prepared statement
                $stmt = $con->prepare($sql);
                // Bind parameters
                $stmt->bindParam(':memberid', $memberid,PDO::PARAM_STR);
                $stmt->bindParam(':share_amount', $share_amount,PDO::PARAM_STR);
                $stmt->bindParam(':share_unit', $share_unit,PDO::PARAM_STR);
                $stmt->bindParam(':bank_name', $bank_name,PDO::PARAM_STR);
                $stmt->bindParam(':tellerno', $tellerno,PDO::PARAM_STR);
                $stmt->bindParam(':amount_paid', $amount_paid,PDO::PARAM_STR);
                $stmt->bindParam(':share_amount_word', $share_amount_word,PDO::PARAM_STR);
                // Execute the query
                $stmt->execute();
    
                    if ($stmt) {
                        
                            return 1;
                    } else {
                        return -1;
                    }//end if
            }
    }
    
    public static function verifyBuyer($staffno)
    {
        
            $db = new DB();
            //$user = new User();
            $con = $db->getConnection();
            $q = "SELECT fname,lname,oname,sp_no,n.dept,phone_no,e.employee_id,f.member_id FROM employee e
                  JOIN ippis_static_record s ON e.employee_id=s.employee_id JOIN ippis_nonstatic_record n
                  ON s.row_id=n.ippis_static_id JOIN fudscoops_member f ON e.employee_id=f.employee_id WHERE e.sp_no='".$staffno."' ";
            $stm = $con->prepare($q);
            $stm->execute();
            $total_rows_found = $stm->rowCount();
            if ($total_rows_found > 0) {
                $row = $stm->fetch(PDO::FETCH_ASSOC);

                    $r ="<br/><div class='alert alert-success'>
                        <div class='form-group row'>
                           <div class='col-md-6'>
                            <b class='text-danger'>Name: <input type='text' disabled class='form-control' value='".$row['fname']." ".$row['lname']." ".$row['oname']."'/>
                            </b>
                            </div>
                           <div class='col-md-6'>
                            <b class='text-danger'>Staff No: <input type='text' disabled class='form-control' value='".$row['sp_no']."'/>
                            </b>
                            </div>    
                        
                        </div><br/>
                        <div class='form-group row'>
                           <div class='col-md-6'>
                            <b class='text-danger'>Department: <input type='text' disabled class='form-control' value='".$row['dept']."'/>
                            </b>
                            </div>
                           <div class='col-md-6'>
                            <b class='text-danger'>Phone No: <input type='text' disabled class='form-control' value='".$row['phone_no']."'/>
                            </b>
                            </div>    
                        
                        </div><br/>
                        <b class='text-primary'>AMOUNT (WORTH ₦): <input type='number' class='form-control' id='worth' name='worth' />
                        </b><br/>
                        <b class='text-primary'>AMOUNT IN WORDS: <input type='text' class='form-control' id='amount_word' name='amount_word' />
                        </b><br/>
                           
                            <div class='row container-fluid'>
                                <button id='add_share_transfer' rel='".$row['member_id']."' class='pull-right btn btn-sm btn-danger'><i class='fa fa-upload'></i> Submit</button>
                            </div>
                            <div id='msg'></div>
                        </div>";
                } else {
                $r = "<div class='alert alert-danger'><h4 class=''>No data found</h4></div>";
            }//end if ($total_rows_found == 1)
            //$con=null;
            return $r;
    }
    
     public function TransferShare($employeeid,$share_amount,$share_unit,$amount_paid,$share_amount_word){
        
            
            $member = new MemberG2();
            //return $next_of_kin_name. " ". $next_of_kin_gsm. " ". $next_of_kin_address." ". $employeeid." ". $monthly_savings;

    
            $db = new DB();
            $user = new User();
            $con = $db->getConnection();
            $memberid = $user->getMemberId($employeeid);
            $share_amount = $user->getShareAmount($memberid);
            
            $sql ="SELECT * FROM fudscoops_shares WHERE member_id='$memberid'";
            $stmt = $con->prepare($sql);
            $stmt->execute();
            $count = $stmt->rowCount();
            if($count>0){
                
              $sql ="UPDATE fudscoops_shares SET unit_price=:share_amount,shares_unit=:share_unit,
              bank_name=:bank_name,teller_no=:tellerno,amount_paid=:amount_paid,shares_amount_word=:share_amount_word WHERE member_id='$memberid'";
              $stmt = $con->prepare($sql);
              $stmt->bindParam(':share_amount', $share_amount,PDO::PARAM_STR);
              $stmt->bindParam(':share_unit', $share_unit,PDO::PARAM_STR);
              $stmt->bindParam(':bank_name', $bank_name,PDO::PARAM_STR);
              $stmt->bindParam(':tellerno', $tellerno,PDO::PARAM_STR);
              $stmt->bindParam(':amount_paid', $amount_paid,PDO::PARAM_STR);
              $stmt->bindParam(':share_amount_word', $share_amount_word,PDO::PARAM_STR);
              $stmt->execute();
              if ($stmt) {
                        return 1;
                } else {
                    return -1;
                }
              
            }else{
             
            
                $sql = "INSERT INTO fudscoops_shares(member_id, unit_price, shares_unit, bank_name, teller_no,amount_paid,shares_amount_word)
                VALUES (:memberid, :share_amount, :share_unit, :bank_name, :tellerno, :amount_paid, :share_amount_word)";
                // Create a prepared statement
                $stmt = $con->prepare($sql);
                // Bind parameters
                $stmt->bindParam(':memberid', $memberid,PDO::PARAM_STR);
                $stmt->bindParam(':share_amount', $share_amount,PDO::PARAM_STR);
                $stmt->bindParam(':share_unit', $share_unit,PDO::PARAM_STR);
                $stmt->bindParam(':bank_name', $bank_name,PDO::PARAM_STR);
                $stmt->bindParam(':tellerno', $tellerno,PDO::PARAM_STR);
                $stmt->bindParam(':amount_paid', $amount_paid,PDO::PARAM_STR);
                $stmt->bindParam(':share_amount_word', $share_amount_word,PDO::PARAM_STR);
                // Execute the query
                $stmt->execute();
    
                    if ($stmt) {
                        
                            return 1;
                    } else {
                        return -1;
                    }//end if
            }
    }
    
    

 public function scheduleCommoditySupply($commodityId, $memberId, $quantity, $supplyDate) {
        $con = $this->db->getConnection();
        $status = 0; // Assuming 0 means 'pending'

        $sql = "INSERT INTO fudscoops_commodity_supply 
                (commodity_supply_item, member_idmember, commodity_supplycol_qty, 
                 commodity_supply_date, commodity_supply_status, inventory_idinventory) 
                VALUES (:commodityId, :memberId, :quantity, :supplyDate, :status, :commodityId)";

        $stmt = $con->prepare($sql);
        $stmt->bindParam(':commodityId', $commodityId);
        $stmt->bindParam(':memberId', $memberId);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':supplyDate', $supplyDate);
        $stmt->bindParam(':status', $status);

        if ($stmt->execute()) {
            return 1;
        } else {
            return -1;
        }
    }
     public function updateCommoditySupplyStatus($supplyId, $status) {
        $con = $this->db->getConnection();

        $sql = "UPDATE fudscoops_commodity_supply 
                SET commodity_supply_status = :status 
                WHERE commodity_supply_id = :supplyId";

        $stmt = $con->prepare($sql);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':supplyId', $supplyId);

        if ($stmt->execute()) {
            return 1;
        } else {
            return -1;
        }
    }

    // Function to cancel a commodity supply
    public function cancelCommoditySupply($supplyId) {
        $con = $this->db->getConnection();

        $sql = "UPDATE fudscoops_commodity_supply 
                SET commodity_supply_is_deleted = 1 
                WHERE commodity_supply_id = :supplyId";

        $stmt = $con->prepare($sql);
        $stmt->bindParam(':supplyId', $supplyId);

        if ($stmt->execute()) {
            return 1;
        } else {
            return -1;
        }
    }

    // Function to retrieve scheduled supplies for a member
    public function getMemberScheduledSupplies($memberId) {
        $con = $this->db->getConnection();

        $sql = "SELECT * FROM fudscoops_commodity_supply 
                WHERE member_idmember = :memberId AND commodity_supply_is_deleted = 0";

        $stmt = $con->prepare($sql);
        $stmt->bindParam(':memberId', $memberId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
//     public function loadAvailableCommodities() {
//     $db = new DB();
//     $con = $db->getConnection();
    
//     $currentDate = date('Y-m-d');
    
//     $q = "SELECT i.inventory_id, i.inventory_item 
//           FROM fudscoops_inventory i
//           JOIN fudscoops_commodity_schedule s ON i.inventory_id = s.commodity_id
//           WHERE s.start_date <= :currentDate AND s.end_date >= :currentDate AND s.is_active = 1";
          
//     $stmt = $con->prepare($q);
//     $stmt->bindParam(':currentDate', $currentDate);
//     $stmt->execute();
    
//     $r_v = '';
//     while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
//         $r_v .= "<option value='" . $row['inventory_id'] . "'>" . $row['inventory_item'] . "</option>";
//     }
    
//     return $r_v;
// }
    
    public function loadAvailableCommodities() {
    $db = new DB();
    $con = $db->getConnection();
    
    $q = "SELECT inventory_id, inventory_item FROM fudscoops_inventory WHERE inventory_is_deleted = 0";
    $stmt = $con->prepare($q);
    $stmt->execute();
    
    $r_v = '';
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $r_v .= "<option value='" . $row['inventory_id'] . "'>" . $row['inventory_item'] . "</option>";
    }
    
    return $r_v;
}
//=======================Hussain methode start from here==========================================================================

    // ===================== Loans (workflow from 2026-10-13) =====================
    // Every application is a new fudscoops_loan record (a member can hold several loans over time).
    // 1. Member applies (loan_status 'pending')            -> saveLoanApplication, loanEligibilityError
    // 2. Guarantor accepts/declines (guarantor_status 1/-1) -> updateGuaranto
    // 3. Treasurer fixes amount, period, flat interest      -> treasurerReview ('Recommended' / 'Disapprove')
    // 4. Chairman authorizes/declines that amount           -> chairmanDecision ('Approve' / 'Disapprove',
    //    chairman_approval 1/-1); chairmanUnlockLoandDecision returns it to the Treasurer before payment
    // 5. Treasurer records the payment to the supplier      -> disburseLoan ('Disbursed'; deductions start
    //    the month after; schedule row created)
    // 6. Deductions recorded (single page or batch upload)  -> saveRepayment; updateRepaidStatus marks 'Repaid'
    // Amount owed = total_payable (approved amount + interest). Bulk-uploaded loans may still say 'Approved'
    // (treated like 'Disbursed'). Repayment rows: status 0 = monthly schedule, status 1 = actual deduction;
    // officer actions never change or delete status 1 rows. The Secretary only views loans.
    // These methods return 1 on success, otherwise a message explaining the refusal.
    public function saveLoanApplication($amountApplied, $loanRepayment, $loanRepaymentSpecify, $memberId, $loanTypeId, $guarantor) {
    $db = new DB();
    $post = function ($name) use ($db) { return $db->cleanData(isset($_POST[$name]) ? $_POST[$name] : ''); };
    $amount = preg_replace('/[^0-9.]/', '', (string) $amountApplied);
    $amount = ($amount === '' || !is_numeric($amount)) ? null : round((float) $amount, 2);
    $loanTenor = !empty($loanRepaymentSpecify) ? $loanRepaymentSpecify : $loanRepayment;

    if ($error = $this->loanEligibilityError($memberId, $loanTypeId, $amount, $loanTenor, $guarantor)) {
        return $error;
    }

    $con = $db->getConnection();
    try {
        $con->beginTransaction();
        $stmt = $con->prepare("INSERT INTO fudscoops_loan (loan_amount, loan_date, loan_status, loan_tenor, reason, iterm_name, loan_type_id, member_id,
                                   guarantor_employee_id, guarantor_status, chairman_approval, is_action_disabled)
                               VALUES (:loan_amount, CURDATE(), 'pending', :loan_tenor, :reason, :iterm_name, :loan_type_id, :member_id, :guarantor, 0, 0, 0)");
        $stmt->execute([':loan_amount' => (string) $amount, ':loan_tenor' => (string) (int) $loanTenor, ':reason' => $post('reason'),
                        ':iterm_name' => $post('meterials'), ':loan_type_id' => $loanTypeId, ':member_id' => $memberId, ':guarantor' => $guarantor]);
        $loanId = $con->lastInsertId();

        // The form names the supplier phone "supplier_gsm-no" and the sort code "supplier_sort-code"
        $stmt = $con->prepare("INSERT INTO fudscoops_suppliers (supplier_name, address, gsm, account_number, sort_code, bank_id, loan_id)
                               VALUES (:supplier_name, :address, :gsm, :account_number, :sort_code, :bank_id, :loan_id)");
        $stmt->execute([':supplier_name' => $post('supplier_name'), ':address' => $post('supplier_address'),
                        ':gsm' => $post('supplier_gsm-no') !== '' ? $post('supplier_gsm-no') : $post('supplier_gsm'),
                        ':account_number' => $post('account_number'), ':sort_code' => $post('supplier_sort-code'),
                        ':bank_id' => $post('bank'), ':loan_id' => $loanId]);
        $con->commit();
        return 1;
    } catch (Exception $e) {
        if ($con->inTransaction()) {
            $con->rollBack();
        }
        error_log('saveLoanApplication: ' . $e->getMessage());
        return 'Could not save the loan application. Contact system admin.';
    }
}
    public function saveLoanApplicationUpload($amountApplied, $loanRepayment, $loanTenor, $repayment,$memberId, $loanTypeId, $guarantor) {
    $db = new DB();
    $iterms = $db->cleanData($_POST['meterials']);
    $reason = $db->cleanData($_POST['reason']);
    $supplier_name = $db->cleanData($_POST['supplier_name']);
    $supplier_address = $db->cleanData($_POST['supplier_address']);
    $supplier_gsm = $db->cleanData($_POST['supplier_gsm']);
    $account_number = $db->cleanData($_POST['account_number']);
    $bank = $db->cleanData($_POST['bank']);
    $con = $db->getConnection();

    // Get the current date
    $loanDate = date('Y-m-d'); 
    $loanStatus = 'pending';
    $sqlCheck = "SELECT loan_id FROM fudscoops_loan WHERE member_id = :member_id";
    $stmtCheck = $con->prepare($sqlCheck);
    $stmtCheck->bindParam(':member_id', $memberId, PDO::PARAM_INT);
    $stmtCheck->execute();

    if ($stmtCheck->rowCount() > 0) {
        // Update existing loan
        $loanData = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        $loan_id = $loanData['loan_id'];

        $sqlUpdate = "UPDATE fudscoops_loan 
                      SET loan_amount = :loan_amount, loan_date = :loan_date, loan_status = :loan_status, 
                          loan_tenor = :loan_tenor, loan_type_id = :loan_type_id, iterm_name = :iterm_name, 
                          reason = :reason, guarantor_employee_id= :guarantor  
                      WHERE loan_id = :loan_id";
        $stmtUpdate = $con->prepare($sqlUpdate);
        $stmtUpdate->bindParam(':loan_amount', $amountApplied, PDO::PARAM_STR);
        $stmtUpdate->bindParam(':loan_date', $loanDate, PDO::PARAM_STR);
        $stmtUpdate->bindParam(':loan_status', $loanStatus, PDO::PARAM_STR);
        $stmtUpdate->bindParam(':loan_tenor', $loanTenor, PDO::PARAM_STR);
        $stmtUpdate->bindParam(':iterm_name', $iterms, PDO::PARAM_STR);
        $stmtUpdate->bindParam(':reason', $reason, PDO::PARAM_STR);
        $stmtUpdate->bindParam(':loan_type_id', $loanTypeId, PDO::PARAM_INT);
        $stmtUpdate->bindParam(':guarantor', $guarantor, PDO::PARAM_INT);
        $stmtUpdate->bindParam(':loan_id', $loan_id, PDO::PARAM_INT);

        if ($stmtUpdate->execute()) {
            // Update supplier information
            $sqlCheckSupplier = "SELECT * FROM fudscoops_suppliers WHERE loan_id = :loan_id";
            $stmtCheckSupplier = $con->prepare($sqlCheckSupplier);
            $stmtCheckSupplier->bindParam(':loan_id', $loan_id, PDO::PARAM_INT);
            $stmtCheckSupplier->execute();

            if ($stmtCheckSupplier->rowCount() > 0) {
                // Update existing supplier
                $sqlUpdateSupplier = "UPDATE fudscoops_suppliers 
                                      SET address = :address, gsm = :gsm, account_number = :account_number, bank_id = :bank_id WHERE loan_id = :loan_id";
                $stmtUpdateSupplier = $con->prepare($sqlUpdateSupplier);
                $stmtUpdateSupplier->bindParam(':address', $supplier_address, PDO::PARAM_STR);
                $stmtUpdateSupplier->bindParam(':gsm', $supplier_gsm, PDO::PARAM_STR);
                $stmtUpdateSupplier->bindParam(':account_number', $account_number, PDO::PARAM_STR);
                $stmtUpdateSupplier->bindParam(':bank_id', $bank, PDO::PARAM_STR);
                $stmtUpdateSupplier->bindParam(':loan_id', $loan_id, PDO::PARAM_INT);
                $stmtUpdateSupplier->execute();
            } 
            else {
                // Insert new supplier
                $sqlInsertSupplier = "INSERT INTO fudscoops_suppliers (supplier_name, address, gsm, account_number, bank_id, loan_id) 
                                      VALUES (:supplier_name, :address, :gsm, :account_number, :bank_id, :loan_id)";
                $stmtInsertSupplier = $con->prepare($sqlInsertSupplier);
                $stmtInsertSupplier->bindParam(':supplier_name', $supplier_name, PDO::PARAM_STR);
                $stmtInsertSupplier->bindParam(':address', $supplier_address, PDO::PARAM_STR);
                $stmtInsertSupplier->bindParam(':gsm', $supplier_gsm, PDO::PARAM_STR);
                $stmtInsertSupplier->bindParam(':account_number', $account_number, PDO::PARAM_STR);
                $stmtInsertSupplier->bindParam(':bank_id', $bank, PDO::PARAM_STR);
                $stmtInsertSupplier->bindParam(':loan_id', $loan_id, PDO::PARAM_INT);
                $stmtInsertSupplier->execute();
            }
            
            // repayment
                             $is_deleted = 0;
                        // Check if the record exists
                        $sql = "SELECT * FROM fudscoops_loan_repayments WHERE loan_id = :loan_id AND member_id = :member_id AND is_deleted = :is_deleted";
                        $stmt = $con->prepare($sql);
                        $stmt->bindParam(':loan_id', $loan_id, PDO::PARAM_INT);
                        $stmt->bindParam(':member_id', $memberId, PDO::PARAM_INT);
                        $stmt->bindParam(':is_deleted', $is_deleted, PDO::PARAM_INT);
                        $stmt->execute();
                        if ($stmt->rowCount() > 0) {
                            // Record exists, update it
                            $row = $stmt->fetch(PDO::FETCH_ASSOC);
                            $sql = "UPDATE fudscoops_loan_repayments 
                                    SET loan_repayment_amount = :repayment, loan_repayment_date = NOW() 
                                    WHERE loan_id = :loan_id AND member_id = :member_id AND loan_repayment_id = :loan_repayment_id";
                            $stmt = $con->prepare($sql);
                            $stmt->bindParam(':loan_id', $loan_id, PDO::PARAM_INT); 
                            $stmt->bindParam(':repayment', $repayment, PDO::PARAM_STR); 
                            $stmt->bindParam(':member_id', $memberId, PDO::PARAM_INT);
                            $stmt->bindParam(':loan_repayment_id', $row['loan_repayment_id'], PDO::PARAM_INT);
                            $stmt->execute();
                            if ($stmt) {
                                return 1; 
                            } else {
                                return -1;
                            }
                        } else {
                            // Record doesn't exist, insert a new one
                            $sql = "INSERT INTO fudscoops_loan_repayments (member_id, loan_id, loan_repayment_amount, loan_repayment_date)
                                    VALUES (:member_id, :loan_id, :repayment, NOW())";
                            $stmt = $con->prepare($sql);
                            $stmt->bindParam(':loan_id', $loan_id, PDO::PARAM_INT); 
                            $stmt->bindParam(':repayment', $repayment, PDO::PARAM_STR);
                            $stmt->bindParam(':member_id', $memberId, PDO::PARAM_INT);
                            $stmt->execute();
                            
                            if ($stmt->rowCount() > 0) {
                                return 1; // Successfully inserted
                            } else {
                                return -1; // Insertion failed
                            }
                        }
            return 1; 
        } else {
            return -1;
        }
    } else {
        // Insert new loan
        $sqlInsert = "INSERT INTO fudscoops_loan (loan_amount, loan_date, loan_status, loan_tenor, reason, iterm_name, loan_type_id, member_id, guarantor_employee_id) 
                      VALUES (:loan_amount, :loan_date, :loan_status, :loan_tenor, :reason, :iterm_name, :loan_type_id, :member_id, :guarantor)";
        $stmtInsert = $con->prepare($sqlInsert);
        $stmtInsert->bindParam(':loan_amount', $amountApplied, PDO::PARAM_STR);
        $stmtInsert->bindParam(':loan_date', $loanDate, PDO::PARAM_STR);
        $stmtInsert->bindParam(':loan_status', $loanStatus, PDO::PARAM_STR);
        $stmtInsert->bindParam(':loan_tenor', $loanTenor, PDO::PARAM_STR);
        $stmtInsert->bindParam(':reason', $reason, PDO::PARAM_STR);
        $stmtInsert->bindParam(':iterm_name', $iterms, PDO::PARAM_STR);
        $stmtInsert->bindParam(':loan_type_id', $loanTypeId, PDO::PARAM_INT);
        $stmtInsert->bindParam(':guarantor', $guarantor, PDO::PARAM_INT);
        $stmtInsert->bindParam(':member_id', $memberId, PDO::PARAM_INT);

        if ($stmtInsert->execute()) {
            $lastId = $con->lastInsertId();
            // Insert supplier information
            $sqlInsertSupplier = "INSERT INTO fudscoops_suppliers (supplier_name, address, gsm, account_number,bank_id, loan_id) 
                                  VALUES (:supplier_name, :address, :gsm, :account_number, :bank_id, :loan_id)";
            $stmtInsertSupplier = $con->prepare($sqlInsertSupplier);
            $stmtInsertSupplier->bindParam(':supplier_name', $supplier_name, PDO::PARAM_STR);
            $stmtInsertSupplier->bindParam(':address', $supplier_address, PDO::PARAM_STR);
            $stmtInsertSupplier->bindParam(':gsm', $supplier_gsm, PDO::PARAM_STR);
            $stmtInsertSupplier->bindParam(':account_number', $account_number, PDO::PARAM_STR);
            $stmtInsertSupplier->bindParam(':bank_id', $bank, PDO::PARAM_STR);
            $stmtInsertSupplier->bindParam(':loan_id', $lastId, PDO::PARAM_INT);
            if($stmtInsertSupplier->execute()){
                $sql = "INSERT INTO fudscoops_loan_repayments (member_id, loan_id, loan_repayment_amount, loan_repayment_date)
                         VALUES (:member_id, :loan_id, :repayment, NOW())";
                            $stmt = $con->prepare($sql);
                            $stmt->bindParam(':loan_id', $lastId, PDO::PARAM_INT); 
                            $stmt->bindParam(':repayment', $repayment, PDO::PARAM_STR);
                            $stmt->bindParam(':member_id', $memberId, PDO::PARAM_INT);
                            $stmt->execute();
                            
                            if ($stmt->rowCount() > 0) {
                                return 1; // Successfully inserted
                            } else {
                                return -1; // Insertion failed
                            }
            }
            return 1; 
        } else {
            return -1;
        }
    }
}

 public function loanInfoArray($member_id) {
    $db = new DB();
    $con = $db->getConnection();

    $sql = "SELECT 
                m.member_id, m.member_name, m.member_address, 
                l.loan_id, l.loan_amount, l.loan_date, l.loan_status, l.loan_tenor, 
                s.supplier_name, s.address AS supplier_address, s.gsm AS supplier_gsm
            FROM 
                fudscoops_member m
            JOIN 
                fudscoops_loan l ON m.member_id = l.member_id
            JOIN 
                fudscoops_suppliers s ON l.loan_id = s.loan_id
            WHERE 
                m.member_id = :member_id";

    $stmt = $con->prepare($sql);
    $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function checkGuarantor($guarantor_id) {
    $db = new DB();
    $con = $db->getConnection();

    // Get all loan details associated with the guarantor
    $sql = "SELECT 
                m.member_id, e.fname, e.lname,
                l.loan_id, l.loan_amount, l.loan_date, l.loan_status, l.guarantor_status, l.chairman_approval
            FROM 
                fudscoops_loan l
            JOIN 
                fudscoops_member m ON l.member_id = m.member_id
            JOIN 
                employee e ON e.employee_id = m.employee_id
            WHERE 
                l.guarantor_employee_id = :guarantor_id";

    $stmt = $con->prepare($sql);
    $stmt->bindParam(':guarantor_id', $guarantor_id, PDO::PARAM_INT);
    $stmt->execute();

    // Fetch all loan information for the guarantor
    $loanInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($loanInfo) {
        $sn = 0;
        echo "<h3>Loan Applicants Requesting You to Be Their Guarantor:</h3>";
        echo "<table id='loanTable' class='display table table-striped table-bordered'>";
        echo "<thead>
                <tr>
                    <th>S/N</th>
                    <th>Applicant Name</th>
                    <th>Loan Amount</th>
                    <th>Loan Date</th>
                    <th>Loan Status</th>
                    <th>Actions</th>
                </tr>
              </thead>";
        echo "<tbody>";
        // Display each loan applicant in a table row
        foreach ($loanInfo as $loan) {
            $sn++;
            echo "<tr>";
            echo "<td>" . $sn . "</td>";
            echo "<td>" . htmlspecialchars($loan['fname'] . ' ' . $loan['lname']) . "</td>";
            echo "<td>" . htmlspecialchars($loan['loan_amount']) . "</td>";
            echo "<td>" . htmlspecialchars($loan['loan_date']) . "</td>";
            echo "<td>" . htmlspecialchars(self::loanStageLabel($loan)) . "</td>";
            echo "<td>
                    <a href='undertaking.php?loan_id=" . htmlspecialchars($loan['loan_id']) . "' class='btn btn-primary'>Make Undertaking</a>
                  </td>";
            echo "</tr>";
        }

        echo "</tbody>";
        echo "</table>";
    } else {
        echo "<p>No applicants found.</p>";
    }
    
    // Initialize DataTable
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
}
    public function getMyloan($member_id) {
        $con = (new DB())->getConnection();
        $stmt = $con->prepare("
            SELECT l.*, t.loan_type_name,
                   (SELECT COALESCE(SUM(r.amount_paid), 0) FROM fudscoops_loan_repayments r
                     WHERE r.loan_id = l.loan_id AND r.status = 1 AND COALESCE(r.is_deleted, 0) = 0) AS repaid
            FROM fudscoops_loan l LEFT JOIN fudscoops_loan_type t ON t.loan_type_id = l.loan_type_id
            WHERE l.member_id = :member_id ORDER BY l.loan_id DESC");
        $stmt->execute([':member_id' => $member_id]);
        $loans = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$loans) {
            echo "<p>You have no loans.</p>";
            return;
        }
        echo "<table id='loanTable' class='display table table-striped table-bordered table-responsive'><thead><tr>
                <th>Applied</th><th>Type</th><th>Amount applied</th><th>Approved</th><th>Interest</th><th>Total to repay</th>
                <th>Monthly</th><th>Period</th><th>Repaid</th><th>Balance</th><th>Status</th></tr></thead><tbody>";
        foreach ($loans as $loan) {
            $fixed = !in_array($loan['loan_status'], ['pending'], true) && !empty($loan['amount_recommended']);
            $total = self::loanTotalPayable($loan);
            $isDebt = in_array($loan['loan_status'], ['Disbursed', 'Approved', 'Repaid'], true);
            echo "<tr><td>" . htmlspecialchars(date('d M Y', strtotime(substr($loan['loan_date'], 0, 10)))) . "</td>"
               . "<td>" . htmlspecialchars($loan['loan_type_name']) . "</td>"
               . "<td>₦" . number_format((float) $loan['loan_amount'], 2) . "</td>"
               . "<td>" . ($fixed ? '₦' . number_format((float) $loan['amount_recommended'], 2) : '-') . "</td>"
               . "<td>" . ($fixed ? '₦' . number_format((float) $loan['interest_amount'], 2) : '-') . "</td>"
               . "<td>" . ($fixed ? '₦' . number_format($total, 2) : '-') . "</td>"
               . "<td>" . ($fixed ? '₦' . number_format($isDebt ? $this->getRepaymentAmount($loan['loan_id'], $member_id) : $total / max(1, (int) $loan['loan_tenor']), 2) : '-') . "</td>"
               . "<td>" . (int) $loan['loan_tenor'] . " month(s)" . (!empty($loan['repayment_start']) ? "<br><small>from " . date('M Y', strtotime($loan['repayment_start'])) . "</small>" : '') . "</td>"
               . "<td>" . ($isDebt ? '₦' . number_format($loan['repaid'], 2) : '-') . "</td>"
               . "<td>" . ($isDebt ? '₦' . number_format(max(0, $total - $loan['repaid']), 2) : '-') . "</td>"
               . "<td>" . htmlspecialchars(self::loanStageLabel($loan)) . "</td></tr>";
        }
        echo "</tbody></table>";
    }

    public function getMyloanRepayment($member_id) {
        $con = (new DB())->getConnection();
        $stmt = $con->prepare("
            SELECT r.*, t.loan_type_name, l.loan_id
            FROM fudscoops_loan_repayments r
            JOIN fudscoops_loan l ON l.loan_id = r.loan_id
            LEFT JOIN fudscoops_loan_type t ON t.loan_type_id = l.loan_type_id
            WHERE l.member_id = :member_id AND r.status = 1 AND COALESCE(r.is_deleted, 0) = 0
            ORDER BY r.loan_repayment_date DESC, r.loan_repayment_id DESC");
        $stmt->execute([':member_id' => $member_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$rows) {
            echo "<p>No loan deductions have been recorded yet.</p>";
            return;
        }
        echo "<table id='loanTable' class='display table table-striped table-bordered table-responsive'><thead><tr>
                <th>S/N</th><th>Month</th><th>Loan</th><th>Amount deducted</th><th>Recorded on</th></tr></thead><tbody>";
        foreach ($rows as $i => $r) {
            echo "<tr><td>" . ($i + 1) . "</td><td>" . htmlspecialchars(date('F Y', strtotime($r['loan_repayment_date']))) . "</td>"
               . "<td>" . htmlspecialchars($r['loan_type_name']) . " #" . (int) $r['loan_id'] . "</td>"
               . "<td>₦" . number_format((float) $r['amount_paid'], 2) . "</td>"
               . "<td>" . htmlspecialchars((string) $r['date_paid']) . "</td></tr>";
        }
        echo "</tbody></table>";
    }

public function getLoansTable() {
    $db = new DB();
    $con = $db->getConnection();

    // Get all loan details associated with the guarantor
    $sql = "SELECT 
                m.member_id, e.fname, e.lname,
                l.loan_id, l.loan_amount,l.loan_date,l.loan_status,l.secretary_decision, l.guarantor_status, l.chairman_approval, (SELECT lt.loan_type_name FROM fudscoops_loan_type lt WHERE lt.loan_type_id = l.loan_type_id) AS loan_type_name
            FROM 
                fudscoops_loan l
            JOIN 
                fudscoops_member m ON l.member_id = m.member_id
            JOIN 
                employee e ON e.employee_id = m.employee_id
            WHERE 
                l.guarantor_status = :status ORDER BY l.loan_id DESC";
    $status = 1;
    $stmt = $con->prepare($sql);
    $stmt->bindParam(':status', $status, PDO::PARAM_INT);
    $stmt->execute();

    // Fetch all loan information for the guarantor
    $loanInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($loanInfo) {
        $sn = 0;
        echo "<h3>List of Loan Applicants</h3>";
        echo "<table id='example' class='display table table-striped table-bordered' width='100%'>";
        echo "<thead>
                <tr>
                    <th>S/N</th>
                    <th>Applicant Name</th>
                    <th>Loan Amount</th>
                    <th>Loan Date</th>
                    <th>Loan Status</th>
                    <th>Actions</th>
                </tr>
              </thead>";
        echo "<tbody>";
        // Display each loan applicant in a table row
        foreach ($loanInfo as $loan) {
            $sn++;
            if($loan['secretary_decision']==1){
                $status = 'Forwarded';
            }else{
                $status='Declined';
            } 
            echo "<tr>";
            echo "<td>" . $sn . "</td>";
            echo "<td>" . htmlspecialchars($loan['fname'] . ' ' . $loan['lname']) . "</td>";
            echo "<td>" . htmlspecialchars($loan['loan_amount']) . "<br><small class='text-muted'>" . htmlspecialchars($loan['loan_type_name'] ?? '') . "</small></td>";
            echo "<td>" . htmlspecialchars($loan['loan_date']) . "</td>";
            echo "<td>" . htmlspecialchars(self::loanStageLabel($loan)) . "</td>";
            echo "<td>
                    <a href='view_loan_details.php?loan_id=" . htmlspecialchars($loan['loan_id']) . "' class='btn btn-primary'>View Details</a>
                  </td>";
            echo "</tr>";
        }

        echo "</tbody>";
        echo "</table>";
    } else {
        echo "<p>No applicants found.</p>";
    }
    
    // Initialize DataTable
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

}
public function getProceedLoansTable() {
    $db = new DB();
    $con = $db->getConnection();
        $decision=1;
    // Get all loan details associated with the guarantor
    $sql = "SELECT 
                m.member_id, e.fname, e.lname,
                l.loan_id, l.loan_amount,l.loan_date,l.loan_status,l.secretary_decision, l.guarantor_status, l.chairman_approval, (SELECT lt.loan_type_name FROM fudscoops_loan_type lt WHERE lt.loan_type_id = l.loan_type_id) AS loan_type_name
            FROM 
                fudscoops_loan l
            JOIN 
                fudscoops_member m ON l.member_id = m.member_id
            JOIN 
                employee e ON e.employee_id = m.employee_id
            WHERE 
                l.guarantor_status = :status ORDER BY l.loan_id DESC";
    $status = 1;
    $stmt = $con->prepare($sql);
    // $stmt->bindParam(':decision', $decision, PDO::PARAM_INT);
    $stmt->bindParam(':status', $status, PDO::PARAM_INT);
    $stmt->execute();

    // Fetch all loan information for the guarantor
    $loanInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($loanInfo) {
        $sn = 0;
        echo "<h3>List of Loan Applicants</h3>";
        echo "<table id='example' class='display table table-striped table-bordered' width='100%'>";
        echo "<thead>
                <tr>
                    <th>S/N</th>
                    <th>Applicant Name</th>
                    <th>Loan Amount</th>
                    <th>Loan Date</th>
                    <th>Loan Status</th>
                    <th>Actions</th>
                </tr>
              </thead>";
        echo "<tbody>";
        // Display each loan applicant in a table row
        foreach ($loanInfo as $loan) {
            // if($loan['secretary_decision']==1){
            //     $status ='Forwarded';
            // }else{
            //     $status ='Declined';
            // }
            $sn++;
            echo "<tr>";
            echo "<td>" . $sn . "</td>";
            echo "<td>" . htmlspecialchars($loan['fname'] . ' ' . $loan['lname']) . "</td>";
            echo "<td>" . htmlspecialchars($loan['loan_amount']) . "<br><small class='text-muted'>" . htmlspecialchars($loan['loan_type_name'] ?? '') . "</small></td>";
            echo "<td>" . htmlspecialchars($loan['loan_date']) . "</td>";
           
            echo "<td>" . htmlspecialchars(self::loanStageLabel($loan)) . "</td>";
            echo "<td>
                    <a href='view_loan_details.php?loan_id=" . htmlspecialchars($loan['loan_id']) . "' class='btn btn-primary'>View Details</a>
                  </td>";
            echo "</tr>";
        }

        echo "</tbody>";
        echo "</table>";
    } else {
        echo "<p>No applicants found.</p>";
    }
    
    // Initialize DataTable
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

}

    public function getLoansDeductionTable() {
        $con = (new DB())->getConnection();
        $loans = $con->query("
            SELECT l.*, e.sp_no, e.fname, e.oname, e.lname, t.loan_type_name,
                   (SELECT COALESCE(SUM(r.amount_paid), 0) FROM fudscoops_loan_repayments r
                     WHERE r.loan_id = l.loan_id AND r.status = 1 AND COALESCE(r.is_deleted, 0) = 0) AS repaid
            FROM fudscoops_loan l
            JOIN fudscoops_member m ON m.member_id = l.member_id
            JOIN employee e ON e.employee_id = m.employee_id
            LEFT JOIN fudscoops_loan_type t ON t.loan_type_id = l.loan_type_id
            WHERE l.loan_status IN ('Disbursed', 'Approved', 'Repaid')
            ORDER BY (l.loan_status = 'Repaid'), e.fname, e.lname")->fetchAll(PDO::FETCH_ASSOC);
        if (!$loans) {
            echo "<p>No loans are being repaid.</p>";
            return;
        }
        echo "<h3>Loans Being Repaid</h3>";
        echo "<table id='example' class='display table table-striped table-bordered' width='100%'><thead><tr>
                <th>S/N</th><th>Staff No</th><th>Name</th><th>Type</th><th>Total to repay (₦)</th><th>Monthly (₦)</th>
                <th>Repaid (₦)</th><th>Balance (₦)</th><th>Status</th><th>Actions</th></tr></thead><tbody>";
        foreach ($loans as $i => $loan) {
            $total = self::loanTotalPayable($loan);
            echo "<tr><td>" . ($i + 1) . "</td><td>" . htmlspecialchars($loan['sp_no']) . "</td>"
               . "<td>" . htmlspecialchars(preg_replace('/\s+/', ' ', trim($loan['fname'] . ' ' . $loan['oname'] . ' ' . $loan['lname']))) . "</td>"
               . "<td>" . htmlspecialchars($loan['loan_type_name']) . "</td>"
               . "<td>" . number_format($total, 2) . "</td>"
               . "<td>" . number_format($this->getRepaymentAmount($loan['loan_id'], $loan['member_id']), 2) . "</td>"
               . "<td>" . number_format($loan['repaid'], 2) . "</td>"
               . "<td>" . number_format(max(0, $total - $loan['repaid']), 2) . "</td>"
               . "<td>" . htmlspecialchars(self::loanStageLabel($loan)) . "</td>"
               . "<td><a href='view_loan_deduction_details.php?loan_id=" . (int) $loan['loan_id'] . "' class='btn btn-primary btn-sm'>Schedule</a></td></tr>";
        }
        echo "</tbody></table>";
    }

    public function getProcessLoansDeductionTable($loan_id) {
        $loan = $this->loanDetails($loan_id);
        if (!$loan || !in_array($loan['loan_status'], ['Disbursed', 'Approved', 'Repaid'], true)) {
            echo "<p>This loan has not been paid out yet, so there is no repayment schedule.</p>";
            return;
        }
        $con = (new DB())->getConnection();
        $stmt = $con->prepare("SELECT DATE_FORMAT(loan_repayment_date, '%Y-%m') ym, SUM(amount_paid) paid, MAX(date_paid) date_paid
                               FROM fudscoops_loan_repayments WHERE loan_id = :id AND status = 1 AND COALESCE(is_deleted, 0) = 0
                               GROUP BY ym");
        $stmt->execute([':id' => $loan_id]);
        $paidByMonth = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $p) {
            $paidByMonth[$p['ym']] = $p;
        }
        $total = self::loanTotalPayable($loan);
        $monthly = $this->getRepaymentAmount($loan_id, $loan['member_id']);
        $balance = max(0, $total - $loan['repaid']);
        $start = new DateTime($loan['repayment_start'] ?: date('Y-m-01', strtotime(substr($loan['loan_date'], 0, 10))));
        $tenor = max(1, (int) $loan['loan_tenor']);
        // Show the agreed period, plus extra months while a balance remains after it
        $months = max($tenor, count($paidByMonth) + ($monthly > 0 ? (int) ceil(round($balance / $monthly, 4)) : 0));
        $fullName = preg_replace('/\s+/', ' ', trim($loan['fname'] . ' ' . $loan['oname'] . ' ' . $loan['lname']));

        echo "<div class='container' style='max-width:960px;margin:auto;padding:20px;border:1px solid #e6ebf4;border-radius:12px'>";
        echo "<h3 style='text-align:center;color:#3b6fe8;'>Loan Repayment Schedule</h3>";
        echo "<p><strong>Name:</strong> " . htmlspecialchars($fullName) . " (" . htmlspecialchars($loan['sp_no']) . ") · <strong>" . htmlspecialchars($loan['loan_type_name']) . "</strong><br>";
        echo "<strong>Approved:</strong> ₦" . number_format((float) ($loan['amount_recommended'] ?: $loan['loan_amount']), 2)
           . " · <strong>Interest:</strong> ₦" . number_format((float) $loan['interest_amount'], 2)
           . " · <strong>Total to repay:</strong> ₦" . number_format($total, 2) . "<br>";
        echo "<strong>Period:</strong> {$tenor} month(s) from " . $start->format('F Y') . " · <strong>Monthly:</strong> ₦" . number_format($monthly, 2) . "<br>";
        echo "<strong>Repaid:</strong> ₦" . number_format($loan['repaid'], 2) . " · <strong>Balance:</strong> ₦" . number_format($balance, 2)
           . " · <strong>Status:</strong> " . htmlspecialchars(self::loanStageLabel($loan)) . "</p>";
        echo "<table class='table table-striped table-bordered' style='width:100%'><thead style='background:#3b6fe8;color:#fff;'><tr>
                <th>S/N</th><th>Month</th><th>Expected</th><th>Paid</th><th>Recorded</th><th>Record deduction</th></tr></thead><tbody>";
        $remaining = $total;
        for ($i = 0; $i < $months; $i++) {
            $month = (clone $start)->modify("+$i month");
            $ym = $month->format('Y-m');
            $expected = $remaining > 0 ? min($monthly, $remaining) : 0;
            $paid = isset($paidByMonth[$ym]) ? (float) $paidByMonth[$ym]['paid'] : 0;
            $remaining = max(0, $remaining - max($paid, $expected));
            $canRecord = $paid <= 0 && $loan['loan_status'] !== 'Repaid' && $balance > 0;
            $default = min($monthly, $balance);
            echo "<tr><td>" . ($i + 1) . "</td><td>" . $month->format('F Y') . ($i >= $tenor ? " <small class='text-muted'>(extra)</small>" : '') . "</td>"
               . "<td>₦" . number_format($expected, 2) . "</td>"
               . "<td>" . ($paid > 0 ? '₦' . number_format($paid, 2) : '-') . "</td>"
               . "<td>" . ($paid > 0 ? htmlspecialchars((string) $paidByMonth[$ym]['date_paid']) : '-') . "</td><td>";
            if ($canRecord) {
                echo "<form class='repayment-form' style='margin:0;' onsubmit='submitRepayment(event, this)'>
                        <input type='hidden' name='loan_id' value='" . (int) $loan['loan_id'] . "'>
                        <input type='hidden' name='member_id' value='" . (int) $loan['member_id'] . "'>
                        <input type='hidden' name='loan_repayment_amount' value='" . $monthly . "'>
                        <input type='hidden' name='repayment_date' value='" . $month->format('Y-m-01') . "'>
                        <input type='number' name='amount_paid' step='0.01' min='0.01' max='" . $balance . "' value='" . $default . "' required style='width:130px'>
                        <button type='submit' class='btn btn-success btn-sm'>Save</button></form>";
            } else {
                echo $paid > 0 ? "<span class='fud-pill ok'>Paid</span>" : '-';
            }
            echo "</td></tr>";
        }
        echo "</tbody></table></div>";
        echo "<script>
        function submitRepayment(e, form) {
            e.preventDefault();
            const button = form.querySelector('button');
            if (!confirm('Record this deduction?')) return;
            button.disabled = true;
            button.textContent = 'Saving...';
            fetch('../ajax/save_repayment.php', { method: 'POST', body: new FormData(form), credentials: 'same-origin' })
                .then(res => res.json())
                .then(data => {
                    alert(data.message);
                    if (data.status === 'success') { window.location.reload(); }
                    else { button.disabled = false; button.textContent = 'Save'; }
                })
                .catch(() => { alert('Error saving repayment. Please try again.'); button.disabled = false; button.textContent = 'Save'; });
        }
        </script>";
    }


    public function updateGuaranto($loan_id, $accept = true, $guarantorEmployeeId = null){
        $con = (new DB())->getConnection();
        if ($guarantorEmployeeId === null) {
            $guarantorEmployeeId = (new User())->getEmployeeId(isset($_SESSION['username']) ? $_SESSION['username'] : '');
        }
        $stmt = $con->prepare("UPDATE fudscoops_loan SET guarantor_status = :status, guarantor_approved_date = :date
                               WHERE loan_id = :loan_id AND guarantor_employee_id = :guarantor AND guarantor_status = 0");
        $stmt->execute([':status' => $accept ? 1 : -1, ':date' => date('Y-m-d'), ':loan_id' => $loan_id, ':guarantor' => $guarantorEmployeeId]);
        return $stmt->rowCount() > 0 ? 1 : 'This loan is not awaiting your decision as guarantor.';
}

    public function processLoan($loan_id,$member_id,$repayment,$action,$amount=''){
        $action = strtolower(trim((string) $action));
        $stmt = (new DB())->getConnection()->prepare("SELECT loan_tenor FROM fudscoops_loan WHERE loan_id = :id");
        $stmt->execute([':id' => $loan_id]);
        $tenor = $stmt->fetchColumn();
        return $this->treasurerReview($loan_id, $member_id, $amount, $tenor, '', in_array($action, ['approve', 'approved', 'recommend'], true));
}

public function getUndertaking($loan_id) {
    $db = new DB();
    $con = $db->getConnection();
    $user = new User();
    
    // Fetch loan details based on loan_id
    $sql = "SELECT 
                m.member_id, e.fname, e.lname, l.guarantor_employee_id,l.guarantor_status,
                l.loan_id, l.loan_amount, l.loan_date, l.loan_status
            FROM 
                fudscoops_loan l
            JOIN 
                fudscoops_member m ON l.member_id = m.member_id
            JOIN 
                employee e ON e.employee_id = m.employee_id
            WHERE 
                l.loan_id = :loan_id";
    
    $stmt = $con->prepare($sql);
    $stmt->bindParam(':loan_id', $loan_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $loanDetails = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($loanDetails) {
        $info = $user->getStaffInformationByEmployeeId($loanDetails['guarantor_employee_id']);
        // Get today's date for the form
            if(!is_null($loanDetails['guarantor_approved_date'])){
                $date =$loanDetails['guarantor_approved_date'];
            }else{
              $date = date('Y-m-d');
            }
        
        // Prepare staff information
        $fullname = $info['fname'] . ' ' . $info['lname'] . ' ' . $info['oname'];
        $applicantName = htmlspecialchars($loanDetails['fname'] . ' ' . $loanDetails['lname']);
        $staffNo = htmlspecialchars($info['sp_no']);
        $department = htmlspecialchars($info['dept']);
        $gsmNo = htmlspecialchars($info['phone_no']);
        $bank = htmlspecialchars($info['bank']);
        $acctNo = htmlspecialchars($info['acct_no']);
        $address = htmlspecialchars($info['permanent_address']);
        $appointmentType = htmlspecialchars($info['nature_of_appo']);
            // The guarantor decides once: accepted (1) or declined (-1)
            if($loanDetails['guarantor_status']!=0){
                $button="disabled";
                $check="checked";
            }else{
                $button=" ";
                $check=" ";
            }
        // Output the professional header and layout
        echo "<html>
                <head>
                    <title>Guarantor Undertaking</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        .container { max-width: 800px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
                        h3 { text-align: center; color: #007bff; }
                        .header { text-align: center; }
                        .header img { width: 150px; }
                        .staff-info { margin-top: 20px; }
                        .staff-info p { margin: 5px 0; }
                        .undertaking-text { font-size: 14px; line-height: 1.5; }
                        .form-section { margin-top: 30px; }
                    </style>
                </head>
                <body>
                    
                    <div class=''>
                            <div class='header'>
                                <img src='../images/logo.png' alt='logo'>
                                  <h2>FUD STAFF COOPERATIVE PORTAL</h2>
                                  <h3>Guarantor Undertaking Form</h3>
                            </div>
                        <hr>
                        <div class='staff-info'>
                            <h4><strong>Guarantor's Information:</strong></h4>
                            <p><strong>Name:</strong> $fullname</p>
                            <p><strong>Staff No:</strong> $staffNo</p>
                            <p><strong>Department/Unit:</strong> $department</p>
                            <p><strong>GSM No:</strong> $gsmNo</p>
                            <p><strong>Bank:</strong> $bank</p>
                            <p><strong>Acct. No:</strong> $acctNo</p>
                            <p><strong>Permanent Home Address:</strong> $address</p>
                            <p><strong>Type of Appointment:</strong> $appointmentType</p>
                        </div>
                        <hr>
                        <p><strong>Loan Details:</strong></p>
                        <p><strong>Applicant Name:</strong> " . htmlspecialchars($loanDetails['fname'] . ' ' . $loanDetails['lname']) . "</p>
                        <p><strong>Loan Amount:</strong> " . htmlspecialchars($loanDetails['loan_amount']) . "</p>
                        <p><strong>Loan Date:</strong> " . htmlspecialchars($loanDetails['loan_date']) . "</p>
                        <p><strong>Loan Status:</strong> " . htmlspecialchars($loanDetails['loan_status']) . "</p>
                        
                        <div class='form-section'>
                           <form class='undertaking-form'>
                                <p class='undertaking-text'>
                                    <strong>UNDERTAKING:</strong><br>
                                    I, $fullname, hereby agree to serve as a guarantor to $applicantName and be held responsible in case of any default by the member.
                                </p>
                                <div>
                                    <label>
                                        <input type='checkbox' $check  name='undertaking_agreed' required> I agree to be the guarantor.
                                        <input type='hidden' name='loan_id' value='" . $loanDetails['loan_id'] . "'>
                                    </label>
                                    <p><strong>Date:</strong> $date</p>
                                </div>
                                <div class='msg'></div>
                                <br>
                                <button type='submit' class='btn btn-success' id='guarantorAccept' $button>Submit Undertaking</button>
                                <button type='submit' class='btn btn-danger' id='guarantorDecline' formnovalidate $button>Decline to Guarantee</button>
                          </form>    
                        </div>
                    </div>
                </body>
            </html>";
    } else {
        echo "<p>Loan details not found.</p>";
    }
}
public function getSecretaryLoanDecisionOld($loan_id) {
    $comment='';
    $terget1 = date('n')-2;
    $terget2 = date('n')-2;
    $terget3 = date('n')-1;
    if($terget1==0 || $terget1 == -1){
        $terget1 = date('n', strtotime('-3 months'));  
    }
    if($terget2==0 || $terget1 == -1){
        $terget2 = date('n', strtotime('-2 months'));  
    }
    if($terget3==0 || $terget1 == -1){
        $terget3 = date('n', strtotime('-1 months'));  
    }
    $payslip = new Payslip();
    $month_name1 = $payslip->getMonthName($terget1);
    $month_name2 = $payslip->getMonthName($terget2);
    $month_name3 = $payslip->getMonthName($terget3);
    $db = new DB();
    $con = $db->getConnection();
    $user = new User();
    // Fetch loan details based on loan_id
    $sql = "SELECT 
                m.member_id, e.fname, e.lname, l.guarantor_employee_id,l.guarantor_status,
                l.*
            FROM 
                fudscoops_loan l
            JOIN 
                fudscoops_member m ON l.member_id = m.member_id
            JOIN  
                employee e ON e.employee_id = m.employee_id
            WHERE 
                l.loan_id = :loan_id AND l.guarantor_status=:status";
    $status=1;
    $stmt = $con->prepare($sql);
    $stmt->bindParam(':loan_id', $loan_id, PDO::PARAM_INT);
    $stmt->bindParam(':status', $status, PDO::PARAM_INT);
    $stmt->execute();
    
    $loanDetails = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($loanDetails) {
        $info = $user->getStaffInformationByEmployeeId($loanDetails['guarantor_employee_id']);
        $applicant_info=$user->getStaffInformationByMemberId($loanDetails['member_id']);
            $date = $loanDetails['guarantor_approved_date'];
        
        // Prepare staff information
        $fullname = $info['fname'] . ' ' . $info['lname'] . ' ' . $info['oname'];
        $applicantName = htmlspecialchars($loanDetails['fname'] . ' ' . $loanDetails['lname']);
        $staffNo = htmlspecialchars($info['sp_no']);
        $department = htmlspecialchars($info['dept']);
        $gsmNo = htmlspecialchars($info['phone_no']);
        $bank = htmlspecialchars($info['bank']);
        $acctNo = htmlspecialchars($info['acct_no']);
        $address = htmlspecialchars($info['permanent_address']);
        $appointmentType = htmlspecialchars($info['nature_of_appo']);
         $shareAmount = $user->getShareAmountByMemberId($loanDetails['member_id']);
            if($loanDetails['loan_status']=='Approve'){
               $button ='disabled';
               $amount_recommended =$loanDetails['amount_recommended'];
            }else{
                $button='';
            }
        // Output the professional header and layout
        echo "<html>
                <head>
                    <title>Guarantor Undertaking</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        .container { max-width: 800px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
                        h3 { text-align: center; color: #007bff; }
                        .header { text-align: center; }
                        .header img { width: 150px; }
                        .staff-info { margin-top: 20px; }
                        .staff-info p { margin: 5px 0; }
                        .undertaking-text { font-size: 14px; line-height: 1.5; }
                        .form-section { margin-top: 30px; }
                    </style>
                </head>
                <body>
                    
                    <div class=''>
                            <div class='header'>
                                <img src='../images/logo.png' alt='logo'>
                                  <h2>FUD STAFF COOPERATIVE PORTAL</h2>
                                  <h3>Guarantor Undertaking Form</h3>
                            </div>
                        <hr>
                        <div class='staff-info'>
                            <h4><strong>Guarantor's Information:</strong></h4>
                            <p><strong>Name:</strong> $fullname</p>
                            <p><strong>Staff No:</strong> $staffNo</p>
                            <p><strong>Department/Unit:</strong> $department</p>
                            <p><strong>GSM No:</strong> $gsmNo</p>
                            <p><strong>Bank:</strong> $bank</p>
                            <p><strong>Acct. No:</strong> $acctNo</p>
                            <p><strong>Permanent Home Address:</strong> $address</p>
                            <p><strong>Type of Appointment:</strong> $appointmentType</p>
                        </div>
                        <hr>
                        <p><strong>Loan Details:</strong></p>
                        <p><strong>Applicant Name:</strong> " . htmlspecialchars($loanDetails['fname'] . ' ' . $loanDetails['lname']) . "</p>
                        <p><strong>Loan Amount :</strong> " . htmlspecialchars($loanDetails['loan_amount']) . "</p>
                        <p><strong>Loan Repayment Period:</strong> " . htmlspecialchars($loanDetails['loan_tenor']) . "  month(s) .&nbsp;<a data-toggle='modal' data-target='#updateRepayment' href =''>Update Repayment period</a></p>
                        <p><strong>Loan Date:</strong> " . htmlspecialchars($loanDetails['loan_date']) . "</p>
                        <p><strong>Loan Status:</strong> " . htmlspecialchars($loanDetails['loan_status']) . "</p>
                        <p><strong><a href='slip.php?legacy=".$applicant_info['sp_no']."&s=".$m1."'>Download Applicant $month_name1 Payslip</a> &nbsp;
                        <a href='slip.php?legacy=".$applicant_info['sp_no']."&s=".$m2."'>Download Applicant $month_name2 Payslip</a> &nbsp; 
                        <a href='slip.php?legacy=".$applicant_info['sp_no']."&s=".$m3."'>Download Applicant $month_name3 Payslip</a>
                        </strong> </p>
                        
                        <div class='form-section'>
                           <form class='loan-decision-form' method='POST'>
                                <p class='undertaking-text'>
                                    <strong>UNDERTAKING:</strong><br>
                                    I, $fullname, hereby agree to serve as a guarantor to $applicantName and be held responsible in case of any default by the member.
                                </p>
                                <div>
                                    <label>
                                        <input type='checkbox' checked  name='undertaking_agreed' required> I agree to be the guarantor.
                                        <input type='hidden' name='loan_id' value='" . $loanDetails['loan_id'] . "'>
                                    </label>
                                    <p><strong><i>Guarantor's Approved Date:</i></strong> $date</p>
                                </div>
                                <hr>
                                <p><strong>OFFICIAL SECTION</strong></p>
                                <div>
                                    <p><strong><i>Value of Member’s Share Capital:</i></strong> $shareAmount</p>
                                </div>
                                <div>
                                    <p><strong>Secretary’s Remarks:</strong></p>
                                </div> 
                                <div>
                                    <label> Remark:</label>
                                     <textarea class='form-control' row='2' name ='comment' required placeholder='Comment here'>".$loanDetails['secretary_comment']."</textarea>
                                     <input type='hidden' name='laon_tenor' id='loan_tenor' class='form-control' value=".$loanDetails['loan_tenor'].">
                                     <input type='hidden' name='member_id' id='member_id' class='form-control' value=".$loanDetails['member_id'].">
                                     <input type='hidden' name='decision' class='form-control' value='secretary'>
                                     <input type='hidden' name='type' id='type' class='form-control' value='decision'>
                                    <h4 class='amount_msg text-primary'></h4>
                                </div>
                                
                                <div class='msg'></div>
                                <br>
                                <button type='submit' class='btn btn-success' name='proceed' id='proceed' $button value='proceed' >Proceed</button>
                                <button type='submit' class='btn btn-danger' name='Decline' id='Decline' value='Decline'>Decline</button>
                          </form>    
                        </div>
                        
                    </div>
                    
                                        
        <div id='updateRepayment' data-backdrop='static' data-keyboard='false' class='modal modal-edu-general Customwidth-popup-WarningModal fade' role='dialog'>

        <div class='modal-dialog'>
          <div class='modal-content'>
            <div class='modal-close-area modal-close-df'>
              <a class='close' href=''><i class='fa fa-close'></i> .</a>
            </div><br />
            <div class='modal-heading'>
              <div class='container'>
                <h5>Update Repayment </h5>
              </div>

            </div>
            <div class='modal-body p-2'>
              <div id='content' class='pro-ad addcoursepro'>
                <div class='col-md-12 card p-2'>
                  <form action=''  id='frm-update-loan-tenor' method='POST'>
                    <div class='row'>
                     <div class='col-12 p-2'>
                        <div class='form-group'>
                          <label >Update Loan Tenor(in month):</label>
                          <input name='repayment' id='repayment' required type='number' value ='".$loanDetails['loan_tenor']."' class='form-control-sm  form-control-md  form-control form-control-sm 'placeholder='Write the Number in month'>
                          <input name='loan_id' id='loan_id' type='hidden' value ='".$loanDetails['loan_id']."'>
                        </div>
                      </div>  
                      
                    <div class='row'>
                      <div class='col-lg-4 col-md-6 col-sm-8 offset-lg-4 offset-md-3 offset-sm-2 text-center p-2'>
                        <button type='submit' class='btn btn-secondary'>Save</button>
                      </div>
                    </div>

                    <div class='msg'></div>
                  </form>
                </div>
              </div>
            </div>
            <div class='modal-footer warning-md'>
              <button class='btn btn-sm btn-danger' data-dismiss='modal'>Close</button>
            </div>
          </div>
        </div>
        
        </div>

                </body>
            </html>";
    } else {
        echo "<p>Loan details not found.</p>";
    }
}
    public function getSecretaryLoanDecision($loan_id) {
        $this->loanDetailsPage($loan_id, 'secretary');
    }
    public function getChairmanLoanDecision($loan_id) {
        $this->loanDetailsPage($loan_id, 'chairman');
    }

public function getLoanUndertakingOld($loan_id) {
    $terget1 = date('n')-2;
    $terget2 = date('n')-2;
    $terget3 = date('n')-1;
    // $m1 = date($terget1, strtotime('first day of last month'));
    // $m2 = date($terget2, strtotime('first day of last month'));
    // $m3 = date($terget3, strtotime('first day of last month'));
    
    if($terget1==0 || $terget1 == -1){
        $terget1 = date('n', strtotime('-3 months'));  
    }
    if($terget2==0 || $terget1 == -1){
        $terget2 = date('n', strtotime('-2 months'));  
    }
    if($terget3==0 || $terget1 == -1){
        $terget3 = date('n', strtotime('-1 months'));  
    }
   
    $payslip = new Payslip();
    $month_name1 = $payslip->getMonthName($terget1);
    $month_name2 = $payslip->getMonthName($terget2);
    $month_name3 = $payslip->getMonthName($terget3);
    $db = new DB();
    $con = $db->getConnection();
    $user = new User();
    $decision=1;
    // Fetch loan details based on loan_id
    $sql = "SELECT 
                m.member_id, e.fname, e.lname, l.guarantor_employee_id,l.guarantor_status,
                l.*
            FROM 
                fudscoops_loan l
            JOIN 
                fudscoops_member m ON l.member_id = m.member_id
            JOIN  
                employee e ON e.employee_id = m.employee_id
            WHERE 
                l.loan_id = :loan_id AND l.guarantor_status=:status";
    $status=1;
    $stmt = $con->prepare($sql);
    $stmt->bindParam(':loan_id', $loan_id, PDO::PARAM_INT);
    $stmt->bindParam(':status', $status, PDO::PARAM_INT);
    $stmt->execute();
    
    $loanDetails = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($loanDetails) {
        $info = $user->getStaffInformationByEmployeeId($loanDetails['guarantor_employee_id']);
        $applicant_info=$user->getStaffInformationByMemberId($loanDetails['member_id']);
            $date = $loanDetails['guarantor_approved_date'];
           
        // Prepare staff information
        $fullname = $info['fname'] . ' ' . $info['lname'] . ' ' . $info['oname'];
        $applicantName = htmlspecialchars($loanDetails['fname'] . ' ' . $loanDetails['lname']);
        $staffNo = htmlspecialchars($info['sp_no']);
        $department = htmlspecialchars($info['dept']);
        $gsmNo = htmlspecialchars($info['phone_no']);
        $bank = htmlspecialchars($info['bank']);
        $acctNo = htmlspecialchars($info['acct_no']);
        $address = htmlspecialchars($info['permanent_address']);
        $appointmentType = htmlspecialchars($info['nature_of_appo']);
         $shareAmount = $user->getShareAmountByMemberId($loanDetails['member_id']);
            if($loanDetails['is_action_disabled']==1){
               $button ='disabled';
               $amount_recommended =$loanDetails['amount_recommended'];
            }else{
               $amount_recommended =$loanDetails['amount_recommended'];
                $button='';
            }
            if($loanDetails['chairman_approval']==1){
                $buttons ="
                <button type='submit' class='btn btn-success' name='Approved' id='Approved' $button value='Approved' >Approve</button>
                <button type='submit' class='btn btn-danger' name='Deapproved' id='Deapproved' $button value='Deapproved'>Decline</button>
                ";
            }else{
                $buttons ="<h4>Waiting for the chairman's Approval</h4>";
            }
        // Output the professional header and layout
        echo "<html>
                <head>
                    <title>Guarantor Undertaking</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        .container { max-width: 800px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
                        h3 { text-align: center; color: #007bff; }
                        .header { text-align: center; }
                        .header img { width: 150px; }
                        .staff-info { margin-top: 20px; }
                        .staff-info p { margin: 5px 0; }
                        .undertaking-text { font-size: 14px; line-height: 1.5; }
                        .form-section { margin-top: 30px; }
                    </style>
                </head>
                <body>
                    
                    <div class=''>
                            <div class='header'>
                                <img src='../images/logo.png' alt='logo'>
                                  <h2>FUD STAFF COOPERATIVE PORTAL</h2>
                                  <h3>Guarantor Undertaking Form</h3>
                            </div>
                        <hr>
                        <div class='staff-info'>
                            <h4><strong>Guarantor's Information:</strong></h4>
                            <p><strong>Name:</strong> $fullname</p>
                            <p><strong>Staff No:</strong> $staffNo</p>
                            <p><strong>Department/Unit:</strong> $department</p>
                            <p><strong>GSM No:</strong> $gsmNo</p>
                            <p><strong>Bank:</strong> $bank</p>
                            <p><strong>Acct. No:</strong> $acctNo</p>
                            <p><strong>Permanent Home Address:</strong> $address</p>
                            <p><strong>Type of Appointment:</strong> $appointmentType</p>
                        </div>
                        <hr>
                        <p><strong>Loan Details:</strong></p>
                        <p><strong>Applicant Name:</strong> " . htmlspecialchars($loanDetails['fname'] . ' ' . $loanDetails['lname']) . "</p>
                        <p><strong>Loan Amount:</strong> " . htmlspecialchars($loanDetails['loan_amount']) . "</p>
                        <p><strong>Loan Repayment Period:</strong> " . htmlspecialchars($loanDetails['loan_tenor']) . "  month(s) .&nbsp;<a data-toggle='modal' data-target='#updateRepayment' href =''>Update Repayment period</a></p>
                        <p><strong>Loan Date:</strong> " . htmlspecialchars($loanDetails['loan_date']) . "</p>
                        <p><strong>Loan Status:</strong> " . htmlspecialchars($loanDetails['loan_status']) . "</p>
                        <p><strong><a href='slip.php?legacy=".$applicant_info['sp_no']."&s=".$m1."'>Download Applicant $month_name1 Payslip</a> &nbsp;
                        <a href='slip.php?legacy=".$applicant_info['sp_no']."&s=".$m2."'>Download Applicant $month_name2 Payslip</a> &nbsp; 
                        <a href='slip.php?legacy=".$applicant_info['sp_no']."&s=".$m3."'>Download Applicant $month_name3 Payslip</a>
                        </strong> </p>
                        
                        <div class='form-section'>
                           <form class='loan-decision-form' method='POST'>
                                <p class='undertaking-text'>
                                    <strong>UNDERTAKING:</strong><br>
                                    I, $fullname, hereby agree to serve as a guarantor to $applicantName and be held responsible in case of any default by the member.
                                </p>
                                <div>
                                    <label>
                                        <input type='checkbox' checked  name='undertaking_agreed' required> I agree to be the guarantor.
                                        <input type='hidden' name='loan_id' value='" . $loanDetails['loan_id'] . "'>
                                    </label>
                                    <p><strong><i>Guarantor's Approved Date:</i></strong> $date</p>
                                </div>
                                <hr>
                                  <p><h5> OFFICIAL </h5></p>
                                <p><strong>SECRETARY REMARK</strong></p>
                                <div>
                                     <textarea class='form-control' row='2' name ='comment' disabled required placeholder='Comment here'>".$loanDetails['secretary_comment']."</textarea>
                                </div>
                                <hr>
                                <p><strong>CHAIRMAN REMARK </strong></p>
                                <div>
                                     <textarea class='form-control' row='2' name ='comment' disabled required placeholder='Comment here'>".$loanDetails['chairman_comment']."</textarea>
                                </div
                                <hr>
                                <p><strong>TREASURER SECTION</strong></p>
                                <div>
                                    <p><strong><i>Value of Member’s Share Capital:</i></strong> $shareAmount</p>
                                </div>
                                <div>
                                    <label> Amount Recommended:</label>
                                     <input type='text' name='amount_recommended' id='amount_recommended' class='form-control' value='$amount_recommended' required placeholder='Recommended Amount here'>
                                     <input type='hidden' name='laon_tenor' id='loan_tenor' class='form-control' value=".$loanDetails['loan_tenor'].">
                                     <input type='hidden' name='member_id' id='member_id' class='form-control' value=".$loanDetails['member_id'].">
                                     <input type='hidden' name='decision'  class='form-control' value='treasurer'>
                                     <h4 class='amount_msg text-primary'></h4>
                                </div>
                                <div class='msg'></div>
                                <br>
                                $buttons
                          </form>    
                        </div>
                        
                    </div>
                    
                        
        <div id='updateRepayment' data-backdrop='static' data-keyboard='false' class='modal modal-edu-general Customwidth-popup-WarningModal fade' role='dialog'>

        <div class='modal-dialog'>
          <div class='modal-content'>
            <div class='modal-close-area modal-close-df'>
              <a class='close' href=''><i class='fa fa-close'></i> .</a>
            </div><br />
            <div class='modal-heading'>
              <div class='container'>
                <h5>Update Repayment </h5>
              </div>

            </div>
            <div class='modal-body p-2'>
              <div id='content' class='pro-ad addcoursepro'>
                <div class='col-md-12 card p-2'>
                  <form action=''  id='frm-update-loan-tenor' method='POST'>
                    <div class='row'>
                     <div class='col-12 p-2'>
                        <div class='form-group'>
                          <label >Update Loan Tenor(in month):</label>
                          <input name='repayment' id='repayment' required type='number' value ='".$loanDetails['loan_tenor']."' class='form-control-sm  form-control-md  form-control form-control-sm 'placeholder='Write the Number in month'>
                          <input name='loan_id' id='loan_id' type='hidden' value ='".$loanDetails['loan_id']."'>
                        </div>
                      </div>  
                      
                    <div class='row'>
                      <div class='col-lg-4 col-md-6 col-sm-8 offset-lg-4 offset-md-3 offset-sm-2 text-center p-2'>
                        <button type='submit' class='btn btn-secondary'>Save</button>
                      </div>
                    </div>

                    <div class='msg'></div>
                  </form>
                </div>
              </div>
            </div>
            <div class='modal-footer warning-md'>
              <button class='btn btn-sm btn-danger' data-dismiss='modal'>Close</button>
            </div>
          </div>
        </div>
        
        </div>

                    
                </body>
            </html>";
    } else {
        echo "<p>Loan details not found.</p>";
    }
}
    public function getLoanUndertaking($loan_id) {
        $this->loanDetailsPage($loan_id, 'treasurer');
    }



function getTotalGuarantee($guarantor) {
    $db = new DB();
    $con = $db->getConnection();

    $sql = "SELECT COUNT(loan_id) as total FROM fudscoops_loan WHERE guarantor_employee_id=:id";
    
    // Prepare the statement
    $stmt = $con->prepare($sql);
    
    // Bind parameters
    $stmt->bindParam(':id', $guarantor, PDO::PARAM_INT);
    
    // Execute the statement
    $stmt->execute();
    
    // Fetch the result
    
    // Check if the result is valid
    if ($stmt->rowCount() > 0) {
         $row = $stmt->fetch(PDO::FETCH_ASSOC);
        // Return the total with a link
        return "<a href='guarantee.php' class='btn btn-info mt-2 mt-xl-0'>Request for Guarantor: " . $row['total'] . "</a>";
    } else {
        return ''; 
    }
}
 
function getMonthlyRepayment($member_id,$loan_id) {
    $db = new DB();
    $con = $db->getConnection();

    $sql = "SELECT loan_repayment_amount FROM fudscoops_loan_repayments WHERE member_id=:member_id AND loan_id=:loan_id";
    
    $stmt = $con->prepare($sql);
    $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
    $stmt->bindParam(':loan_id', $loan_id, PDO::PARAM_INT);
    $stmt->execute();
    // Check if the result is valid
    if ($stmt) {
         $row = $stmt->fetch(PDO::FETCH_ASSOC);
         return $row['loan_repayment_amount'];
    } else {
        return 0; 
    }
}
public function updateLoanTenor($loan_id,$tenor) {
    $db = new DB();
    $con = $db->getConnection();

    $sql = "UPDATE fudscoops_loan SET loan_tenor =:tenor WHERE loan_id=:loan_id";
    
    $stmt = $con->prepare($sql);
    $stmt->bindParam(':tenor', $tenor, PDO::PARAM_INT);
    $stmt->bindParam(':loan_id', $loan_id, PDO::PARAM_INT);
    $stmt->execute();
    if ($stmt) {
         return 1;
    } else {
        return 0; 
    }
}
 
    public function toBeProceed($loan_id,$member_id,$action,$comment) {
        return 'The Secretary can view loans but not decide on them.';
}
    public function chairmanDecision($loan_id,$member_id,$action,$comment) {
        $action = strtolower(trim((string) $action));
        if (!in_array($action, ['proceed', 'decline'], true)) {
            return 'Please click Authorize or Decline.';
        }
        $con = (new DB())->getConnection();
        $stmt = $con->prepare("UPDATE fudscoops_loan
                               SET chairman_approval = :decision, chairman_approval_date = :date, chairman_comment = :comment,
                                   loan_status = :status, is_action_disabled = 1
                               WHERE loan_id = :loan_id AND member_id = :member_id AND loan_status = 'Recommended' AND chairman_approval = 0");
        $stmt->execute([':decision' => $action === 'proceed' ? 1 : -1, ':date' => date('Y-m-d'), ':comment' => (string) $comment,
                        ':status' => $action === 'proceed' ? 'Approve' : 'Disapprove', ':loan_id' => $loan_id, ':member_id' => $member_id]);
        if ($stmt->rowCount() > 0) {
            return 1;
        }
        $stmt = $con->prepare("SELECT loan_status FROM fudscoops_loan WHERE loan_id = :id");
        $stmt->execute([':id' => $loan_id]);
        $status = $stmt->fetchColumn();
        return $status === 'pending' ? 'The Treasurer has not yet fixed the amount for this loan.' : 'This loan has already been decided.';
}
    public function chairmanUnlockLoandDecision($loan_id,$member_id) {
        $con = (new DB())->getConnection();
        $stmt = $con->prepare("UPDATE fudscoops_loan SET loan_status = 'pending', chairman_approval = 0, is_action_disabled = 0
                               WHERE loan_id = :loan_id AND member_id = :member_id AND guarantor_status = 1
                                 AND loan_status IN ('Approve', 'Disapprove') AND disbursement_date IS NULL");
        $stmt->execute([':loan_id' => $loan_id, ':member_id' => $member_id]);
        return $stmt->rowCount() > 0 ? 1 : 'Only an authorized or declined loan that has not been paid out can be returned to the Treasurer.';
}
public function isTargetSaving($member_id) {
    $db = new DB();
    $con = $db->getConnection();
    $is_deleted =0;
        $sql = "SELECT * FROM fudscoops_Target_saving WHERE member_id =:member_id AND Target_saving_is_deleted=:deleted";
        $stmt = $con->prepare($sql);
        $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
        $stmt->bindParam(':deleted', $is_deleted, PDO::PARAM_INT);
        $stmt->execute();
        $no_of_rows = $stmt->rowCount();
        if ($no_of_rows > 0) {
             return 1;
        } else {
            return 0; 
        }

}
public function getLoanTypeId($name) {
    $db = new DB();
    $con = $db->getConnection();
    $is_deleted = 0;

    $sql = "SELECT loan_type_id FROM fudscoops_loan_type WHERE loan_type_name = :name AND is_deleted = :deleted";
    $stmt = $con->prepare($sql);
    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    $stmt->bindParam(':deleted', $is_deleted, PDO::PARAM_INT);
    $stmt->execute();
    if ($stmt->rowCount() > 0) { 
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['loan_type_id'];
    } else {
        return 0; 
    }
}
public function getBankId($bank) {
    $db = new DB();
    $con = $db->getConnection();
    $is_deleted = 0;

    $sql = "SELECT id FROM banks WHERE name LIKE '%$bank%' LIMIT 1";
    $stmt = $con->prepare($sql);
    $stmt->execute();
    if ($stmt->rowCount() > 0) { 
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['id'];
    } else {
        return 0; 
    }
}
public function saveRepayment2($loan_id,$member_id,$amount_paid,$repayment_date,$loan_repayment_amount) {
    $db = new DB();
    $con = $db->getConnection();
    $status =1;
    $date_paid=date('Y-m-d');
    // check record for the repayment data based on loan_id,member_id and loan_repayment_date
    // record exist update amount_paid,status,date_paid
    //else intsert
    $sql = "INSERT INTO fudscoops_loan_repayments (loan_id, member_id, amount_paid, loan_repayment_date,date_paid,status,loan_repayment_amount)
        VALUES (:loan_id, :member_id, :amount_paid, :repayment_date,:date_paid,:status,:loan_repayment_amount)";
    $stmt = $con->prepare($sql);
    $stmt->bindParam(':loan_id', $loan_id);
    $stmt->bindParam(':member_id', $member_id);
    $stmt->bindParam(':amount_paid', $amount_paid);
    $stmt->bindParam(':repayment_date', $repayment_date);
    $stmt->bindParam(':date_paid', $date_paid);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':loan_repayment_amount', $loan_repayment_amount);

    if ($stmt->execute()) {
        return 1;
    } else {
        return 0;
    }


    
}

    public function saveRepayment($loan_id, $member_id, $amount_paid, $repayment_date, $loan_repayment_amount) {
    $con = (new DB())->getConnection();
    $stmt = $con->prepare("SELECT 1 FROM fudscoops_loan WHERE loan_id = :loan_id AND member_id = :member_id AND loan_status IN ('Disbursed', 'Approved')");
    $stmt->execute([':loan_id' => $loan_id, ':member_id' => $member_id]);
    if (!$stmt->fetchColumn()) {
        return 0;
    }
    $date_paid = date('Y-m-d');
    $checkStmt = $con->prepare("SELECT loan_repayment_id FROM fudscoops_loan_repayments
                                WHERE loan_id = :loan_id AND member_id = :member_id AND status = 1 AND COALESCE(is_deleted, 0) = 0
                                  AND DATE_FORMAT(loan_repayment_date, '%Y-%m') = DATE_FORMAT(:repayment_date, '%Y-%m')");
    $checkStmt->execute([':loan_id' => $loan_id, ':member_id' => $member_id, ':repayment_date' => $repayment_date]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
    if ($existing) {
        $stmt = $con->prepare("UPDATE fudscoops_loan_repayments SET amount_paid = :amount_paid, date_paid = :date_paid, loan_repayment_amount = :loan_repayment_amount
                               WHERE loan_repayment_id = :repayment_id");
        $ok = $stmt->execute([':amount_paid' => $amount_paid, ':date_paid' => $date_paid, ':loan_repayment_amount' => $loan_repayment_amount,
                              ':repayment_id' => $existing['loan_repayment_id']]);
    } else {
        $stmt = $con->prepare("INSERT INTO fudscoops_loan_repayments (loan_id, member_id, amount_paid, loan_repayment_date, date_paid, status, loan_repayment_amount, is_deleted)
                               VALUES (:loan_id, :member_id, :amount_paid, :repayment_date, :date_paid, 1, :loan_repayment_amount, 0)");
        $ok = $stmt->execute([':loan_id' => $loan_id, ':member_id' => $member_id, ':amount_paid' => $amount_paid, ':repayment_date' => $repayment_date,
                              ':date_paid' => $date_paid, ':loan_repayment_amount' => $loan_repayment_amount]);
    }
    if ($ok) {
        $this->updateRepaidStatus($loan_id);
    }
    return $ok ? 1 : 0;
}

 // get total loan paid 
 public function getTotalAmountPaid($loan_id, $member_id) {
    $db = new DB();
    $con = $db->getConnection();

    $status = 1; 

    $sql = "SELECT 
                COALESCE(SUM(amount_paid), 0) AS total_paid
            FROM 
                fudscoops_loan_repayments
            WHERE 
                loan_id = :loan_id
                AND member_id = :member_id
                AND status = :status";

    $stmt = $con->prepare($sql);
    $stmt->bindParam(':loan_id', $loan_id, PDO::PARAM_INT);
    $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
    $stmt->bindParam(':status', $status, PDO::PARAM_INT);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Return numeric value (not string)
    return $result ? (float)$result['total_paid'] : 0.0;
}

// number of unpaid month

    public function getUnpaidMonths($loan_id, $member_id)
    {
        $con = (new DB())->getConnection();
        $stmt = $con->prepare("SELECT * FROM fudscoops_loan WHERE loan_id = :id");
        $stmt->execute([':id' => $loan_id]);
        $loan = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$loan) {
            return 0;
        }
        $balance = self::loanTotalPayable($loan) - $this->getTotalAmountPaid($loan_id, $member_id);
        $monthly = $this->getRepaymentAmount($loan_id, $member_id);
        return ($balance > 0 && $monthly > 0) ? (int) ceil(round($balance / $monthly, 4)) : 0;
    }
// get repyament amount
    public function getRepaymentAmount($loan_id, $member_id)
    {
        $con = (new DB())->getConnection();
        $stmt = $con->prepare("SELECT loan_repayment_amount FROM fudscoops_loan_repayments
                               WHERE loan_id = :loan_id AND status = 0 AND COALESCE(is_deleted, 0) = 0
                               ORDER BY loan_repayment_id DESC LIMIT 1");
        $stmt->execute([':loan_id' => $loan_id]);
        $monthly = $stmt->fetchColumn();
        if ($monthly !== false && (float) str_replace(',', '', $monthly) > 0) {
            return (float) str_replace(',', '', $monthly);
        }
        $stmt = $con->prepare("SELECT * FROM fudscoops_loan WHERE loan_id = :id");
        $stmt->execute([':id' => $loan_id]);
        $loan = $stmt->fetch(PDO::FETCH_ASSOC);
        return $loan ? round(self::loanTotalPayable($loan) / max(1, (int) $loan['loan_tenor']), 2) : 0;
    }





//=======================Hussain methode end from here============================================================================





    public function loanEligibilityError($memberId, $loanTypeId, $amount, $tenor, $guarantorEmployeeId)
    {
        require_once('Settings.php');
        $con = (new DB())->getConnection();

        $stmt = $con->prepare("SELECT employee_id, treasurer_approval, chairman_approval FROM fudscoops_member WHERE member_id = :id AND is_deleted = 0");
        $stmt->execute([':id' => $memberId]);
        $member = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$member || $member['treasurer_approval'] != 1 || $member['chairman_approval'] != 1) {
            return 'Only approved members can apply for a loan.';
        }

        $stmt = $con->prepare("SELECT loan_type_name, loan_type_max_tenor FROM fudscoops_loan_type WHERE loan_type_id = :id AND is_deleted = 0");
        $stmt->execute([':id' => $loanTypeId]);
        $type = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$type) {
            return 'Unknown loan type.';
        }
        if (stripos($type['loan_type_name'], 'soft') !== false && !$this->isTargetSaving($memberId)) {
            return 'Soft loans are only available to members on target savings.';
        }
        if ($amount === null || $amount <= 0) {
            return 'Enter a valid loan amount.';
        }
        $maxTenor = (int) $type['loan_type_max_tenor'];
        if (!ctype_digit((string) $tenor) || (int) $tenor < 1 || ($maxTenor > 0 && (int) $tenor > $maxTenor)) {
            return 'Choose a repayment period of 1 to ' . $maxTenor . ' months for a ' . $type['loan_type_name'] . '.';
        }

        $live = self::liveLoans($con, 'member_id', $memberId);
        foreach ($live as $loan) {
            if ($loan['loan_type_id'] == $loanTypeId) {
                return 'You already have a ' . $type['loan_type_name'] . ' that is pending or not fully repaid.';
            }
        }

        $multiple = (float) Settings::get('loan_max_savings_multiple', 2);
        if ($multiple > 0) {
            $balance = self::memberSavingsBalance($con, $memberId);
            $owing = array_sum(array_map(function ($l) { return $l['amount'] - $l['repaid']; }, $live));
            $limit = $multiple * $balance - $owing;
            if ($amount > $limit) {
                return 'You can borrow at most ₦' . number_format(max(0, $limit)) . ' (' . rtrim(rtrim(number_format($multiple, 2), '0'), '.')
                     . ' times your savings balance of ₦' . number_format($balance) . ', less loans you still owe).';
            }
        }

        return $this->guarantorError($guarantorEmployeeId, $member['employee_id']);
    }

    public function guarantorError($guarantorEmployeeId, $applicantEmployeeId)
    {
        require_once('Settings.php');
        $con = (new DB())->getConnection();
        if (empty($guarantorEmployeeId)) {
            return 'Guarantor not found. Check the staff number.';
        }
        if ((int) $guarantorEmployeeId === (int) $applicantEmployeeId) {
            return 'You cannot be your own guarantor.';
        }
        $stmt = $con->prepare("SELECT e.sp_no, m.member_id, m.treasurer_approval, m.chairman_approval
                               FROM employee e LEFT JOIN fudscoops_member m ON m.employee_id = e.employee_id AND m.is_deleted = 0
                               WHERE e.employee_id = :id");
        $stmt->execute([':id' => $guarantorEmployeeId]);
        $g = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$g) {
            return 'Guarantor not found. Check the staff number.';
        }
        if (strtoupper(substr($g['sp_no'], 0, 2)) === 'JP') {
            return 'Junior staff (JP) cannot be guarantors.';
        }
        if (!$g['member_id'] || $g['treasurer_approval'] != 1 || $g['chairman_approval'] != 1) {
            return 'The guarantor must be an approved member of the cooperative.';
        }
        $max = (int) Settings::get('guarantor_max_open_loans', 2);
        if ($max > 0 && count(self::liveLoans($con, 'guarantor_employee_id', $guarantorEmployeeId)) >= $max) {
            return 'This member is already guaranteeing ' . $max . ' loans and cannot guarantee another.';
        }
        return null;
    }

    private static function memberSavingsBalance($con, $memberId)
    {
        $stmt = $con->prepare("SELECT
                (SELECT COALESCE(SUM(savings_amount), 0) FROM fudscoops_savings WHERE member_idmember = :m1)
              - (SELECT COALESCE(SUM(approved_withrawal_amount), 0) FROM fudscoops_withrawals WHERE member_id = :m2 AND chairman_approval = 1)");
        $stmt->execute([':m1' => $memberId, ':m2' => $memberId]);
        return (float) $stmt->fetchColumn();
    }

    private static function liveLoans($con, $column, $value, $excludeLoanId = 0)
    {
        $stmt = $con->prepare("
            SELECT l.loan_id, l.loan_type_id, l.loan_status,
                   CASE WHEN l.loan_status IN ('Disbursed', 'Approved') THEN COALESCE(l.total_payable, NULLIF(l.amount_recommended, 0), l.loan_amount)
                        WHEN l.loan_status IN ('Recommended', 'Approve') THEN COALESCE(l.total_payable, NULLIF(l.amount_recommended, 0), l.loan_amount)
                        ELSE l.loan_amount END + 0 AS amount,
                   (SELECT COALESCE(SUM(r.amount_paid), 0) FROM fudscoops_loan_repayments r
                     WHERE r.loan_id = l.loan_id AND r.status = 1 AND COALESCE(r.is_deleted, 0) = 0) AS repaid
            FROM fudscoops_loan l
            WHERE l.$column = :value AND l.loan_id <> :exclude AND l.guarantor_status <> -1
              AND COALESCE(l.loan_status, '') NOT IN ('Disapprove', 'Repaid')");
        $stmt->execute([':value' => $value, ':exclude' => (int) $excludeLoanId]);
        return array_values(array_filter($stmt->fetchAll(PDO::FETCH_ASSOC), function ($l) {
            return in_array($l['loan_status'], ['pending', 'Recommended', 'Approve'], true) || $l['amount'] - $l['repaid'] > 0;
        }));
    }

    public static function loanStageLabel($loan)
    {
        $status = isset($loan['loan_status']) ? $loan['loan_status'] : '';
        if (isset($loan['guarantor_status']) && $loan['guarantor_status'] == -1) {
            return 'Declined by guarantor';
        }
        if ($status === 'Disapprove') {
            return (isset($loan['chairman_approval']) && $loan['chairman_approval'] == -1) ? 'Declined by Chairman' : 'Declined by Treasurer';
        }
        if ($status === 'Repaid') {
            return 'Fully repaid';
        }
        if ($status === 'Disbursed' || $status === 'Approved') {
            return 'Repaying';
        }
        if ($status === 'Approve') {
            return 'Awaiting disbursement';
        }
        if ($status === 'Recommended') {
            return 'Awaiting Chairman';
        }
        if (isset($loan['guarantor_status']) && $loan['guarantor_status'] != 1) {
            return 'Awaiting guarantor';
        }
        return 'Awaiting Treasurer';
    }

    public function disburseLoan($loan_id, $date, $reference, $disbursedBy)
    {
        $d = DateTime::createFromFormat('Y-m-d', (string) $date);
        if (!$d || $d->format('Y-m-d') !== $date) {
            return 'Enter the date the loan was paid out.';
        }
        if ($date > date('Y-m-d')) {
            return 'The payment date cannot be in the future.';
        }
        $reference = trim((string) $reference);
        if ($reference === '') {
            return 'Enter the payment reference (e.g. transfer or cheque number).';
        }
        $con = (new DB())->getConnection();
        try {
            $con->beginTransaction();
            $stmt = $con->prepare("SELECT * FROM fudscoops_loan WHERE loan_id = :id FOR UPDATE");
            $stmt->execute([':id' => $loan_id]);
            $loan = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$loan || $loan['loan_status'] !== 'Approve' || $loan['chairman_approval'] != 1) {
                throw new RuntimeException('Only a loan the Chairman has authorized can be paid out.');
            }
            $start = (clone $d)->modify('first day of next month')->format('Y-m-d');
            $total = (float) $loan['total_payable'];
            $tenor = max(1, (int) $loan['loan_tenor']);
            $monthly = round($total / $tenor, 2);

            $stmt = $con->prepare("UPDATE fudscoops_loan SET loan_status = 'Disbursed', disbursed_amount = amount_recommended, disbursement_date = :date,
                                       disbursement_reference = :ref, disbursed_by = :by, repayment_start = :start, update_at = :today
                                   WHERE loan_id = :id");
            $stmt->execute([':date' => $date, ':ref' => $reference, ':by' => (string) $disbursedBy, ':start' => $start, ':today' => date('Y-m-d'), ':id' => $loan_id]);

            // Repayment schedule row (status 0): monthly amount and first deduction month
            $con->prepare("UPDATE fudscoops_loan_repayments SET is_deleted = 1 WHERE loan_id = :id AND status = 0")->execute([':id' => $loan_id]);
            $stmt = $con->prepare("INSERT INTO fudscoops_loan_repayments (member_id, loan_id, loan_repayment_amount, loan_repayment_date, amount_paid, status, is_deleted)
                                   VALUES (:member_id, :loan_id, :monthly, :start, 0, 0, 0)");
            $stmt->execute([':member_id' => $loan['member_id'], ':loan_id' => $loan_id, ':monthly' => (string) $monthly, ':start' => $start]);
            $con->commit();
            return 1;
        } catch (Exception $e) {
            if ($con->inTransaction()) {
                $con->rollBack();
            }
            if ($e instanceof RuntimeException) {
                return $e->getMessage();
            }
            error_log('disburseLoan: ' . $e->getMessage());
            return 'Could not record the disbursement. Contact system admin.';
        }
    }

    // Figures shown on the member's loan application page (R/business_loan.php, R/soft_loan.php);
    // the same rules as loanEligibilityError, which still decides when the form is submitted.
    public function loanApplicationContext($memberId, $loanTypeName)
    {
        require_once('Settings.php');
        $con = (new DB())->getConnection();
        $stmt = $con->prepare("SELECT loan_type_id, loan_type_max_tenor FROM fudscoops_loan_type WHERE loan_type_name = :name AND is_deleted = 0");
        $stmt->execute([':name' => $loanTypeName]);
        $type = $stmt->fetch(PDO::FETCH_ASSOC);
        $typeId = $type ? (int) $type['loan_type_id'] : 0;

        $openLoan = null;
        if ($typeId) {
            foreach (self::liveLoans($con, 'member_id', $memberId) as $live) {
                if ((int) $live['loan_type_id'] === $typeId) {
                    $stmt = $con->prepare("SELECT * FROM fudscoops_loan WHERE loan_id = :id");
                    $stmt->execute([':id' => $live['loan_id']]);
                    $openLoan = $stmt->fetch(PDO::FETCH_ASSOC);
                    $openLoan['stage'] = self::loanStageLabel($openLoan);
                    $openLoan['balance'] = $live['amount'] - $live['repaid'];
                    break;
                }
            }
        }

        return [
            'type_id'   => $typeId,
            'max_tenor' => $type ? (int) $type['loan_type_max_tenor'] : 0,
            'savings'   => self::memberSavingsBalance($con, $memberId),
            'multiple'  => (float) Settings::get('loan_max_savings_multiple', 2),
            'limit'     => $this->loanLimit($memberId),
            'rate'      => self::loanInterestRate(),
            'open_loan' => $openLoan,
            'is_target' => (bool) $this->isTargetSaving($memberId),
        ];
    }

    public function loanLimit($memberId, $excludeLoanId = 0)
    {
        require_once('Settings.php');
        $con = (new DB())->getConnection();
        $multiple = (float) Settings::get('loan_max_savings_multiple', 2);
        if ($multiple <= 0) {
            return null; // no limit
        }
        $owing = array_sum(array_map(function ($l) { return $l['amount'] - $l['repaid']; }, self::liveLoans($con, 'member_id', $memberId, $excludeLoanId)));
        return max(0, $multiple * self::memberSavingsBalance($con, $memberId) - $owing);
    }

    private static function loanTotalPayable($loan)
    {
        if (!empty($loan['total_payable'])) {
            return (float) $loan['total_payable'];
        }
        return (float) (!empty($loan['amount_recommended']) ? $loan['amount_recommended'] : $loan['loan_amount']);
    }

    public static function loanInterestRate()
    {
        require_once('Settings.php');
        return (float) Settings::get('loan_interest_rate', 10);
    }

    public function updateRepaidStatus($loan_id)
    {
        $con = (new DB())->getConnection();
        $stmt = $con->prepare("UPDATE fudscoops_loan l SET l.loan_status = 'Repaid', l.repaid_date = CURDATE()
                               WHERE l.loan_id = :id AND l.loan_status IN ('Disbursed', 'Approved')
                                 AND COALESCE(l.total_payable, NULLIF(l.amount_recommended, 0), l.loan_amount) <=
                                     (SELECT COALESCE(SUM(r.amount_paid), 0) FROM fudscoops_loan_repayments r
                                       WHERE r.loan_id = l.loan_id AND r.status = 1 AND COALESCE(r.is_deleted, 0) = 0)");
        $stmt->execute([':id' => $loan_id]);
        return $stmt->rowCount() > 0;
    }

    public function loanDetailsPage($loan_id, $role)
    {
        $loan = $this->loanDetails($loan_id);
        if (!$loan) {
            echo "<div class='alert alert-warning'>Loan not found.</div>";
            return;
        }
        $con = (new DB())->getConnection();
        $stage = self::loanStageLabel($loan);
        $name = function ($t, $f, $o, $l) { return preg_replace('/\s+/', ' ', trim("$t $f $o $l")); };
        $money = function ($v) { return '₦' . number_format((float) $v, 2); };
        $savings = self::memberSavingsBalance($con, $loan['member_id']);
        require_once('MemberG2.php');
        $shares = MemberG2::getShareHolding($loan['member_id']);
        $limit = $this->loanLimit($loan['member_id'], $loan['loan_id']);
        $rate = $loan['loan_status'] === 'pending' ? self::loanInterestRate() : (float) $loan['interest_rate'];
        $total = self::loanTotalPayable($loan);
        $monthly = $this->getRepaymentAmount($loan['loan_id'], $loan['member_id']);
        $isDebt = in_array($loan['loan_status'], ['Disbursed', 'Approved', 'Repaid'], true);
        $steps = ['Applied', 'Guarantor', 'Treasurer', 'Chairman', 'Paid out', 'Repaid'];
        $stepIndex = ['Awaiting guarantor' => 1, 'Awaiting Treasurer' => 2, 'Awaiting Chairman' => 3, 'Awaiting disbursement' => 4, 'Repaying' => 5, 'Fully repaid' => 6];
        $current = isset($stepIndex[$stage]) ? $stepIndex[$stage] : -1;
        $row = function ($label, $value) { echo "<tr><th style='width:42%'>" . $label . "</th><td>" . htmlspecialchars((string) $value) . "</td></tr>"; };
        ?>
        <div class="fud-dash">
          <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div>
              <h3 class="mb-1"><?php echo htmlspecialchars($loan['loan_type_name'] ?: 'Loan'); ?> #<?php echo (int) $loan['loan_id']; ?></h3>
              <div class="text-muted"><?php echo htmlspecialchars($name($loan['title'], $loan['fname'], $loan['oname'], $loan['lname'])); ?> (<?php echo htmlspecialchars($loan['sp_no']); ?>) · applied <?php echo htmlspecialchars(date('d M Y', strtotime(substr($loan['loan_date'], 0, 10)))); ?></div>
            </div>
            <?php $pill = in_array($stage, ['Repaying', 'Fully repaid'], true) ? 'ok' : (strpos($stage, 'Declined') === 0 ? 'no' : 'wait'); ?>
            <span class="fud-pill <?php echo $pill; ?>" style="font-size:.85rem"><?php echo htmlspecialchars($stage); ?></span>
          </div>

          <?php if ($current >= 0) { ?>
          <div class="fud-section"><div class="fud-section-body">
            <div class="fud-steps">
              <?php foreach ($steps as $i => $s) {
                  $class = $i < $current ? 'done' : ($i === $current ? 'current' : '');
                  if ($stage === 'Fully repaid') { $class = 'done'; } ?>
                <div class="fud-step <?php echo $class; ?>"><div class="fud-step-dot"><i class="mdi <?php echo $class === 'done' ? 'mdi-check' : 'mdi-circle-small'; ?>"></i></div><?php echo $s; ?></div>
              <?php } ?>
            </div>
          </div></div>
          <?php } ?>

          <div class="fud-stats">
            <div class="fud-stat"><div class="fud-stat-icon fud-blue"><i class="mdi mdi-cash"></i></div>
              <div><div class="fud-stat-label">Amount applied</div><div class="fud-stat-value"><?php echo $money($loan['loan_amount']); ?></div>
              <div class="fud-stat-sub"><?php echo (int) $loan['loan_tenor']; ?> month(s)</div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-purple"><i class="mdi mdi-check-decagram"></i></div>
              <div><div class="fud-stat-label">Approved amount</div><div class="fud-stat-value"><?php echo $loan['loan_status'] === 'pending' ? '—' : $money($loan['amount_recommended']); ?></div>
              <div class="fud-stat-sub"><?php echo $loan['loan_status'] === 'pending' ? 'not fixed yet' : '+ ' . rtrim(rtrim(number_format($rate, 2), '0'), '.') . '% interest = ' . $money($total); ?></div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-amber"><i class="mdi mdi-calendar-clock"></i></div>
              <div><div class="fud-stat-label">Monthly deduction</div><div class="fud-stat-value"><?php echo $loan['loan_status'] === 'pending' ? '—' : $money($isDebt ? $monthly : $total / max(1, (int) $loan['loan_tenor'])); ?></div>
              <div class="fud-stat-sub"><?php echo !empty($loan['repayment_start']) ? 'from ' . date('F Y', strtotime($loan['repayment_start'])) : 'starts the month after payment'; ?></div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-green"><i class="mdi mdi-wallet"></i></div>
              <div><div class="fud-stat-label"><?php echo $isDebt ? 'Repaid / balance' : 'Savings / loan limit'; ?></div>
              <div class="fud-stat-value"><?php echo $isDebt ? $money($loan['repaid']) : $money($savings); ?></div>
              <div class="fud-stat-sub"><?php echo $isDebt ? 'balance ' . $money(max(0, $total - $loan['repaid'])) : ($limit === null ? 'no limit set' : 'can borrow up to ' . $money($limit)); ?></div></div></div>
          </div>

          <div class="row">
            <div class="col-lg-6">
              <div class="fud-section"><div class="fud-section-head"><h5>Application</h5></div><div class="fud-section-body">
                <table class="table table-sm fud-table">
                  <?php
                    $row('Staff number', $loan['sp_no']);
                    $row('Department', $loan['dept']);
                    $row('Phone', $loan['phone_no']);
                    $row('Material(s) needed', $loan['iterm_name']);
                    $row('Reason', $loan['reason']);
                    $row('Savings balance', $money($savings));
                    $row('Share capital', $money($shares));
                  ?>
                </table>
                <h6 class="mt-3">Supplier</h6>
                <table class="table table-sm fud-table">
                  <?php
                    $row('Name', $loan['supplier_name']);
                    $row('Address', $loan['s_address']);
                    $row('Phone', $loan['s_gsm']);
                    $row('Bank', $loan['s_bank']);
                    $row('Account number', $loan['s_account']);
                    $row('Sort code', $loan['s_sort_code']);
                  ?>
                </table>
              </div></div>
            </div>
            <div class="col-lg-6">
              <div class="fud-section"><div class="fud-section-head"><h5>Guarantor</h5></div><div class="fud-section-body">
                <table class="table table-sm fud-table">
                  <?php
                    $row('Name', $name($loan['g_title'], $loan['g_fname'], $loan['g_oname'], $loan['g_lname']));
                    $row('Staff number', $loan['g_sp_no']);
                    $row('Department', $loan['g_dept']);
                    $row('Phone', $loan['g_phone']);
                    $row('Decision', $loan['guarantor_status'] == 1 ? 'Accepted' . ($loan['guarantor_approved_date'] ? ' on ' . $loan['guarantor_approved_date'] : '') : ($loan['guarantor_status'] == -1 ? 'Declined' : 'Not yet decided'));
                  ?>
                </table>
              </div></div>
              <div class="fud-section"><div class="fud-section-head"><h5>Decisions</h5></div><div class="fud-section-body">
                <table class="table table-sm fud-table">
                  <?php
                    $row('Treasurer', $loan['treasurer_date'] ? date('d M Y', strtotime($loan['treasurer_date'])) . ($loan['treasurer_comment'] ? ' – ' . $loan['treasurer_comment'] : '') : '—');
                    $row('Chairman', $loan['chairman_approval'] != 0 ? ($loan['chairman_approval'] == 1 ? 'Authorized' : 'Declined') . ($loan['chairman_approval_date'] ? ' on ' . $loan['chairman_approval_date'] : '') . ($loan['chairman_comment'] ? ' – ' . $loan['chairman_comment'] : '') : '—');
                    $row('Paid out', $loan['disbursement_date'] ? date('d M Y', strtotime($loan['disbursement_date'])) . ' · ' . $money($loan['disbursed_amount']) . ' · ref ' . $loan['disbursement_reference'] . ($loan['disbursed_by'] ? ' · by ' . $loan['disbursed_by'] : '') : '—');
                    $row('Fully repaid', $loan['repaid_date'] ? date('d M Y', strtotime($loan['repaid_date'])) : '—');
                  ?>
                </table>
              </div></div>
            </div>
          </div>

          <div class="fud-section"><div class="fud-section-head"><h5>Action</h5></div><div class="fud-section-body loan-action" data-loan="<?php echo (int) $loan['loan_id']; ?>" data-member="<?php echo (int) $loan['member_id']; ?>">
            <?php if ($role === 'treasurer' && $loan['guarantor_status'] == 1 && in_array($loan['loan_status'], ['pending', 'Recommended'], true) && $loan['chairman_approval'] == 0) {
                $maxAmount = $limit === null ? (float) $loan['loan_amount'] : min((float) $loan['loan_amount'], $limit);
                $maxTenor = (int) $loan['loan_type_max_tenor']; ?>
              <p class="text-muted mb-2"><?php echo $loan['loan_status'] === 'Recommended' ? 'You sent this loan to the Chairman. You can still change it until the Chairman decides.' : 'Fix the approved amount and repayment period. A flat ' . rtrim(rtrim(number_format(self::loanInterestRate(), 2), '0'), '.') . '% interest is added and the loan goes to the Chairman.'; ?></p>
              <div class="form-row">
                <div class="col-md-4 mb-2"><label class="small mb-1">Approved amount (₦, max <?php echo number_format($maxAmount, 2); ?>)</label>
                  <input type="number" class="form-control" id="loanAmount" min="1" max="<?php echo $maxAmount; ?>" step="0.01" value="<?php echo htmlspecialchars($loan['amount_recommended'] ?: min((float) $loan['loan_amount'], $maxAmount)); ?>"></div>
                <div class="col-md-3 mb-2"><label class="small mb-1">Repayment period</label>
                  <select class="form-control" id="loanTenor"><?php for ($t = 1; $t <= max(1, $maxTenor); $t++) { echo "<option value='$t'" . ($t == $loan['loan_tenor'] ? ' selected' : '') . ">$t month" . ($t > 1 ? 's' : '') . "</option>"; } ?></select></div>
                <div class="col-md-5 mb-2"><label class="small mb-1">Comment</label>
                  <input type="text" class="form-control" id="loanComment" value="<?php echo htmlspecialchars((string) $loan['treasurer_comment']); ?>"></div>
              </div>
              <p class="mb-3" id="loanPreview" data-rate="<?php echo self::loanInterestRate(); ?>"></p>
              <button class="btn btn-success loan-btn" data-action="recommend">Send to Chairman</button>
              <button class="btn btn-outline-danger loan-btn" data-action="treasurer_decline">Decline Loan</button>
            <?php } elseif ($role === 'treasurer' && $loan['loan_status'] === 'Approve') { ?>
              <p class="text-muted mb-2">The Chairman authorized <?php echo $money($loan['amount_recommended']); ?>. Record the payment to the supplier; deductions start the month after.</p>
              <div class="form-row">
                <div class="col-md-3 mb-2"><label class="small mb-1">Date paid</label><input type="date" class="form-control" id="disburseDate" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>"></div>
                <div class="col-md-5 mb-2"><label class="small mb-1">Payment reference (transfer / cheque no.)</label><input type="text" class="form-control" id="disburseRef"></div>
              </div>
              <button class="btn btn-success loan-btn" data-action="disburse">Record Payment of <?php echo $money($loan['amount_recommended']); ?></button>
            <?php } elseif ($role === 'treasurer' && $isDebt) { ?>
              <a class="btn btn-primary" href="view_loan_deduction_details.php?loan_id=<?php echo (int) $loan['loan_id']; ?>">Open repayment schedule</a>
            <?php } elseif ($role === 'chairman' && $loan['loan_status'] === 'Recommended') { ?>
              <p class="mb-2">The Treasurer fixed <strong><?php echo $money($loan['amount_recommended']); ?></strong> over <strong><?php echo (int) $loan['loan_tenor']; ?> month(s)</strong>
                + <?php echo rtrim(rtrim(number_format($rate, 2), '0'), '.'); ?>% interest (<?php echo $money($loan['interest_amount']); ?>) = <strong><?php echo $money($total); ?></strong>,
                about <?php echo $money($total / max(1, (int) $loan['loan_tenor'])); ?> a month.</p>
              <div class="form-group"><label class="small mb-1">Comment</label><input type="text" class="form-control" id="loanComment"></div>
              <button class="btn btn-success loan-btn" data-action="authorize">Authorize Loan</button>
              <button class="btn btn-outline-danger loan-btn" data-action="chairman_decline">Decline Loan</button>
            <?php } elseif ($role === 'chairman' && in_array($loan['loan_status'], ['Approve', 'Disapprove'], true) && $loan['guarantor_status'] == 1 && empty($loan['disbursement_date'])) { ?>
              <p class="text-muted mb-2">Decided. You can return it to the Treasurer to review again (it will then need your authorization again).</p>
              <button class="btn btn-outline-secondary loan-btn" data-action="unlock">Return to Treasurer</button>
            <?php } else { ?>
              <p class="fud-empty mb-0"><?php echo $role === 'secretary' ? 'The Secretary can view loans; decisions are taken by the Treasurer and the Chairman.' : 'No action needed from you at this stage (' . htmlspecialchars($stage) . ').'; ?></p>
            <?php } ?>
            <div class="loan-msg mt-3"></div>
          </div></div>
        </div>

        <script>
        (function () {
            var box = document.querySelector('.loan-action');
            var preview = document.getElementById('loanPreview');
            function money(v) { return '₦' + Number(v).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}); }
            function updatePreview() {
                if (!preview) return;
                var amount = parseFloat(document.getElementById('loanAmount').value) || 0;
                var tenor = parseInt(document.getElementById('loanTenor').value, 10) || 1;
                var rate = parseFloat(preview.getAttribute('data-rate')) || 0;
                var interest = Math.round(amount * rate) / 100;
                preview.innerHTML = 'Interest ' + rate + '%: <strong>' + money(interest) + '</strong> · Total to repay: <strong>' + money(amount + interest) +
                    '</strong> · Monthly: <strong>' + money((amount + interest) / tenor) + '</strong>';
            }
            if (preview) {
                document.getElementById('loanAmount').addEventListener('input', updatePreview);
                document.getElementById('loanTenor').addEventListener('change', updatePreview);
                updatePreview();
            }
            var labels = {recommend: 'send this loan to the Chairman', treasurer_decline: 'DECLINE this loan', authorize: 'AUTHORIZE this loan',
                          chairman_decline: 'DECLINE this loan', unlock: 'return this loan to the Treasurer', disburse: 'record this payment'};
            document.querySelectorAll('.loan-btn').forEach(function (button) {
                button.addEventListener('click', function () {
                    var action = button.getAttribute('data-action');
                    if (!confirm('Are you sure you want to ' + labels[action] + '?')) return;
                    var data = new FormData();
                    data.append('action', action);
                    data.append('loan_id', box.getAttribute('data-loan'));
                    data.append('member_id', box.getAttribute('data-member'));
                    ['loanAmount:amount', 'loanTenor:tenor', 'loanComment:comment', 'disburseDate:date', 'disburseRef:reference'].forEach(function (pair) {
                        var p = pair.split(':'), el = document.getElementById(p[0]);
                        if (el) data.append(p[1], el.value);
                    });
                    button.disabled = true;
                    fetch('../ajax/process_loan_decision.php', {method: 'POST', body: data, credentials: 'same-origin'})
                        .then(function (r) { return r.text(); })
                        .then(function (text) {
                            var msg = box.querySelector('.loan-msg');
                            if (text.trim() === '1') {
                                msg.innerHTML = '<div class="alert alert-success">Saved.</div>';
                                setTimeout(function () { location.reload(); }, 1000);
                            } else {
                                msg.innerHTML = '<div class="alert alert-danger"></div>';
                                msg.firstChild.textContent = text.trim() || 'Could not save. Contact system admin.';
                                button.disabled = false;
                            }
                        })
                        .catch(function () { button.disabled = false; alert('Network error. Please try again.'); });
                });
            });
        })();
        </script>
        <?php
    }

    private function loanDetails($loan_id)
    {
        $con = (new DB())->getConnection();
        $stmt = $con->prepare("
            SELECT l.*, t.loan_type_name, t.loan_type_max_tenor,
                   e.sp_no, e.title, e.fname, e.oname, e.lname, e.dept, e.phone_no,
                   g.sp_no AS g_sp_no, g.title AS g_title, g.fname AS g_fname, g.oname AS g_oname, g.lname AS g_lname, g.dept AS g_dept, g.phone_no AS g_phone,
                   s.supplier_name, s.address AS s_address, s.gsm AS s_gsm, s.account_number AS s_account, s.sort_code AS s_sort_code,
                   COALESCE(b.name, s.bank_name, s.bank_id) AS s_bank,
                   (SELECT COALESCE(SUM(r.amount_paid), 0) FROM fudscoops_loan_repayments r
                     WHERE r.loan_id = l.loan_id AND r.status = 1 AND COALESCE(r.is_deleted, 0) = 0) AS repaid
            FROM fudscoops_loan l
            JOIN fudscoops_member m ON m.member_id = l.member_id
            JOIN employee e ON e.employee_id = m.employee_id
            LEFT JOIN employee g ON g.employee_id = l.guarantor_employee_id
            LEFT JOIN fudscoops_loan_type t ON t.loan_type_id = l.loan_type_id
            LEFT JOIN fudscoops_suppliers s ON s.loan_id = l.loan_id
            LEFT JOIN banks b ON b.id = s.bank_id
            WHERE l.loan_id = :id
            LIMIT 1");
        $stmt->execute([':id' => $loan_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function treasurerReview($loan_id, $member_id, $amount, $tenor, $comment, $recommend)
    {
        $con = (new DB())->getConnection();
        $stmt = $con->prepare("SELECT l.*, t.loan_type_max_tenor FROM fudscoops_loan l LEFT JOIN fudscoops_loan_type t ON t.loan_type_id = l.loan_type_id
                               WHERE l.loan_id = :id AND l.member_id = :member_id");
        $stmt->execute([':id' => $loan_id, ':member_id' => $member_id]);
        $loan = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$loan) {
            return 'Loan not found.';
        }
        if ($loan['guarantor_status'] != 1) {
            return 'The guarantor has not accepted this loan.';
        }
        if (!in_array($loan['loan_status'], ['pending', 'Recommended'], true) || $loan['chairman_approval'] != 0) {
            return 'This loan has already been decided. The Chairman can return it to the Treasurer if it must be changed.';
        }

        if (!$recommend) {
            $stmt = $con->prepare("UPDATE fudscoops_loan SET loan_status = 'Disapprove', treasurer_comment = :comment, treasurer_date = CURDATE(),
                                   update_at = :date, is_action_disabled = 1 WHERE loan_id = :id");
            $stmt->execute([':comment' => (string) $comment, ':date' => date('Y-m-d'), ':id' => $loan_id]);
            return 1;
        }

        $amount = preg_replace('/[^0-9.]/', '', (string) $amount);
        $amount = ($amount === '' || !is_numeric($amount)) ? 0 : round((float) $amount, 2);
        if ($amount <= 0) {
            return 'Enter the approved loan amount.';
        }
        if ($amount > (float) $loan['loan_amount']) {
            return 'The approved amount cannot be more than the amount applied for (₦' . number_format((float) $loan['loan_amount'], 2) . ').';
        }
        $limit = $this->loanLimit($member_id, $loan_id);
        if ($limit !== null && $amount > $limit) {
            return 'The approved amount cannot be more than the member\'s loan limit (₦' . number_format($limit, 2) . ').';
        }
        $maxTenor = (int) $loan['loan_type_max_tenor'];
        if (!ctype_digit((string) $tenor) || (int) $tenor < 1 || ($maxTenor > 0 && (int) $tenor > $maxTenor)) {
            return 'Choose a repayment period of 1 to ' . $maxTenor . ' months.';
        }

        $rate = self::loanInterestRate();
        $interest = round($amount * $rate / 100, 2);
        $stmt = $con->prepare("UPDATE fudscoops_loan
                               SET amount_recommended = :amount, loan_tenor = :tenor, interest_rate = :rate, interest_amount = :interest,
                                   total_payable = :total, treasurer_comment = :comment, treasurer_date = CURDATE(), update_at = :date,
                                   loan_status = 'Recommended'
                               WHERE loan_id = :id");
        $stmt->execute([':amount' => (int) round($amount), ':tenor' => (string) (int) $tenor, ':rate' => $rate, ':interest' => $interest,
                        ':total' => $amount + $interest, ':comment' => (string) $comment, ':date' => date('Y-m-d'), ':id' => $loan_id]);
        return 1;
    }
}