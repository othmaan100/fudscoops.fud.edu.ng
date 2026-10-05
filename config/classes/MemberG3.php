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

    public function saveLoanApplication($amountApplied, $loanRepayment, $loanRepaymentSpecify, $memberId, $loanTypeId, $guarantor) {
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
    $loanTenor = !empty($loanRepaymentSpecify) ? $loanRepaymentSpecify : $loanRepayment;

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
            } else {
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
            $stmtInsertSupplier->execute();

            return 1; 
        } else {
            return -1;
        }
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
                l.loan_id, l.loan_amount, l.loan_date, l.loan_status
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
            echo "<td>" . htmlspecialchars($loan['loan_status']) . "</td>";
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
    $db = new DB();
    $con = $db->getConnection();

    // Get all loan details associated with the guarantor
    $sql = "SELECT 
                m.member_id, e.fname, e.lname,
                l.*
            FROM 
                fudscoops_loan l
            JOIN 
                fudscoops_member m ON l.member_id = m.member_id
            JOIN 
                employee e ON e.employee_id = m.employee_id
            WHERE 
                l.member_id = :member_id";

    $stmt = $con->prepare($sql);
    $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
    $stmt->execute();

    // Fetch all loan information for the guarantor
    $loanInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($loanInfo) {
        $loan_type='';
        $sn = 0;
        echo "<h3> List of Loans</h3>";
        echo "<table id='loanTable' class='display table table-striped table-bordered table-responsive'>";
        echo "<thead>
                <tr>
                    <th>S/N</th>
                    <th>Applicant Name</th>
                    <th>Loan Amount</th>
                    <th>Loan Type</th>
                    <th>Loan Status</th>
                    <th>Amount Recommended</th>
                    <th>Approved Date</th>
                    <th>Monthly Repayment</th>
                    <th>Repayment Priode</th>
                </tr>
              </thead>";
        echo "<tbody>";
        // Display each loan applicant in a table row
        foreach ($loanInfo as $loan) {
            $repayment=$this->getMonthlyRepayment($loan['member_id'],$loan['loan_id']);
            $sn++;
            echo "<tr>";
            echo "<td>" . $sn . "</td>";
            echo "<td>" . htmlspecialchars($loan['fname'] . ' ' . $loan['lname']) . "</td>";
            echo "<td>" . htmlspecialchars($loan['loan_amount']) . "</td>";
            echo "<td>" . $loan_type . "</td>";
            echo "<td>" . htmlspecialchars($loan['loan_status']) . "</td>";
            echo "<td>" . htmlspecialchars($loan['amount_recommended']) . "</td>";
            echo "<td>". htmlspecialchars($loan['update_at'])."</td>";
            echo "<td>".$repayment."</td>";
            echo "<td>".htmlspecialchars($loan['loan_tenor']). "month</td>";
            echo "</tr>";
        }

        echo "</tbody>";
        echo "</table>";
    } else {
        echo "<p> Record not found.</p>";
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

public function getMyloanRepayment($member_id) {
    $db = new DB();
    $con = $db->getConnection();

    // Get all loan details associated with the guarantor
    $sql = "SELECT 
                m.member_id, e.fname, e.lname,
                l.*,lr.*
            FROM 
                fudscoops_loan l
            JOIN 
                fudscoops_member m ON l.member_id = m.member_id
            JOIN 
                employee e ON e.employee_id = m.employee_id
                INNER JOIN fudscoops_loan_repayments lr ON l.member_id =lr.member_id
            WHERE 
                l.member_id = :member_id AND lr.is_deleted =0";

    $stmt = $con->prepare($sql);
    $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
    $stmt->execute();

    // Fetch all loan information for the guarantor
    $loanInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($loanInfo) {
        // checking if the loan deduction is paid
        $loan_type='';
        $sn = 0;
        echo "<h3> List of Loans Deduction</h3>";
        echo "<table id='loanTable' class='display table table-striped table-bordered table-responsive'>";
        echo "<thead>
                <tr>
                    <th>S/N</th>
                    <th>Name</th>
                    <th>Amount Deducted</th>
                    <th>Loan Type</th>
                    <th>Deduction Date</th>
                    <th>Approved Date</th>
                    <th>Monthly Repayment</th>
                    <th>Repayment Priode</th>
                    <th>Status</th>
                </tr>
              </thead>";
        echo "<tbody>";
        // Display each loan applicant in a table row
        foreach ($loanInfo as $loan) {
                if($loan['status']==1){
                    $status ='PAID';
                }
                 else{
                    $status ='NOT-PAID';
                }
            $repayment=$this->getMonthlyRepayment($loan['member_id'],$loan['loan_id']);
            $sn++;
            echo "<tr>";
            echo "<td>" . $sn . "</td>";
            echo "<td>" . htmlspecialchars($loan['fname'] . ' ' . $loan['lname']) . "</td>";
            echo "<td>" . htmlspecialchars($loan['amount_paid']) . "</td>";
            echo "<td>" . $loan_type . "</td>";
            echo "<td>" . htmlspecialchars($loan['date_paid']) . "</td>";
            echo "<td>". htmlspecialchars($loan['update_at'])."</td>";
            echo "<td>".$repayment."</td>";
            echo "<td>".htmlspecialchars($loan['loan_tenor']). "month</td>";
            echo "<td>".$status. "</td>";
            echo "</tr>";
        }

        echo "</tbody>";
        echo "</table>";
    } else {
        echo "<p> Record not found.</p>";
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

public function getLoansTable() {
    $db = new DB();
    $con = $db->getConnection();

    // Get all loan details associated with the guarantor
    $sql = "SELECT 
                m.member_id, e.fname, e.lname,
                l.loan_id, l.loan_amount,l.loan_date,l.loan_status,l.secretary_decision
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
            echo "<td>" . htmlspecialchars($loan['loan_amount']) . "</td>";
            echo "<td>" . htmlspecialchars($loan['loan_date']) . "</td>";
            echo "<td>" . $status . "</td>";
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
                l.loan_id, l.loan_amount,l.loan_date,l.loan_status,l.secretary_decision
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
            echo "<td>" . htmlspecialchars($loan['loan_amount']) . "</td>";
            echo "<td>" . htmlspecialchars($loan['loan_date']) . "</td>";
           
            echo "<td>" . htmlspecialchars($loan['loan_status']) . "</td>";
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
    $db = new DB();
    $user = new User();
    $con = $db->getConnection();
        $decision=1;
    // Get all loan details associated with the guarantor
    $sql = "SELECT 
                m.member_id, e.fname, e.lname,e.oname,
                l.loan_id, l.loan_amount,l.loan_date,l.loan_status,l.secretary_decision,lr.*
            FROM 
                fudscoops_loan l
            JOIN 
                fudscoops_member m ON l.member_id = m.member_id
            JOIN 
                employee e ON e.employee_id = m.employee_id
                INNER JOIN 
                fudscoops_loan_repayments lr ON lr.member_id=l.member_id 
            WHERE 
                l.guarantor_status = :status GROUP BY l.loan_id ORDER BY lr.loan_repayment_id DESC";
    $status = 1;
    $stmt = $con->prepare($sql);
    // $stmt->bindParam(':decision', $decision, PDO::PARAM_INT);
    $stmt->bindParam(':status', $status, PDO::PARAM_INT);
    $stmt->execute();

    // Fetch all loan information for the guarantor
    $loanInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($loanInfo) {
        $sn = 0;
        echo "<h3>List of Loan Deduction</h3>";
        echo "<table id='example' class='display table table-striped table-bordered' width='100%'>";
        echo "<thead>
                <tr>
                    <th>S/N</th>
                    <th>Staff.No</th>
                    <th>Name</th>
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
            $staff_ino =$user->getStaffInformationByMemberId($loan['member_id']);
            $sn++;
            echo "<tr>";
            echo "<td>" . $sn . "</td>";
            echo "<td>" . $staff_ino['sp_no'] . "</td>";
            echo "<td>" . htmlspecialchars($loan['fname'] . ' ' . $loan['lname'].$loan['oname']) . "</td>";
            echo "<td>" . htmlspecialchars($loan['loan_amount']) . "</td>";
            echo "<td>" . htmlspecialchars($loan['loan_date']) . "</td>";
            echo "<td>" . htmlspecialchars($loan['loan_status']) . "</td>";
            echo "<td>
                    <a href='view_loan_deduction_details.php?loan_id=" . htmlspecialchars($loan['loan_id']) . "' class='btn btn-primary'>View Details</a>
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

public function getProcessLoansDeductionTable($loan_id) {
    $db = new DB();
    $con = $db->getConnection();

    // Only approved loans with guarantor confirmed
    $status = 1;

    // Fetch loan and repayment details
    $sql = "
        SELECT 
            m.member_id, e.fname, e.lname,e.oname,lr.status,
            l.loan_id, l.loan_amount, l.loan_date, l.loan_status, l.loan_tenor,
            lr.loan_repayment_id, lr.amount_paid, lr.loan_repayment_date,lr.status,lr.loan_repayment_amount,lr.date_paid
        FROM 
            fudscoops_loan l
        JOIN 
            fudscoops_member m ON l.member_id = m.member_id
        JOIN 
            employee e ON e.employee_id = m.employee_id
        LEFT JOIN 
            fudscoops_loan_repayments lr ON lr.loan_id = l.loan_id
        WHERE 
            l.guarantor_status = :status 
            AND l.loan_id = :loan_id
        ORDER BY 
            lr.loan_repayment_id ASC
    ";

    $stmt = $con->prepare($sql);
    $stmt->bindParam(':status', $status, PDO::PARAM_INT);
    $stmt->bindParam(':loan_id', $loan_id, PDO::PARAM_INT);
    $stmt->execute();
    $loanInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$loanInfo) {
        echo "<p>No record found.</p>";
        return;
    }

    // Extract loan details from first row
    $loan = $loanInfo[0];
    //$monthlyPayment = round($loan['loan_amount'] / $loan['loan_tenor'], 2);
    $monthlyPayment = $loan['loan_repayment_amount'];
    $startDate = new DateTime($loan['loan_date']);
    $total_paid = self::getTotalAmountPaid($loan['loan_id'],$loan['member_id']);
    $unpaid_paid_month = self::getUnpaidMonths($loan['loan_id'],$loan['member_id']);
    echo "<div class='container' style='max-width:900px;margin:auto;padding:20px;border:1px solid #ccc;border-radius:10px'>";
    echo "<h3 style='text-align:center;color:#007bff;'>Loan Repayment Deduction Schedule</h3>";
    echo "<p><strong>Name:</strong> {$loan['fname']} {$loan['lname']} {$loan['oname']}<br>";
    echo "<strong>Loan Amount:</strong> ₦" . number_format($loan['loan_amount'], 2) . "<br>";
    echo "<strong>Tenor:</strong> {$loan['loan_tenor']} months<br>";
    echo "<strong>Monthly Repayment:</strong> ₦" . number_format($monthlyPayment, 2) . "</p>";
    echo "<strong>Total Amount Paid:</strong> ₦" . number_format($total_paid, 2) . "</p>";
    echo "<strong>month remanin:</strong>" .$unpaid_paid_month . " Month Remain</p>";

    echo "<table class='table display table table-striped table-bordered' id='#' cellspacing='0' cellpadding='8' style='width:100%;border-collapse:collapse;text-align:left;'>";
    echo "<thead style='background:#007bff;color:#fff;'>
            <tr>
                <th>S/N</th>
                <th>Month</th>
                <th>Expected Amount</th>
                <th>Amount Paid</th>
                <th>Payment Date</th>
                <th>Action</th>
            </tr>
          </thead>";
    echo "<tbody>";

    // Loop through loan tenor months
    for ($i = 0; $i < $loan['loan_tenor']; $i++) {
        $monthDate = (clone $startDate)->modify("+$i month")->format('Y-m-d');

        // Check if repayment already exists for this month
        $paidRow = null;
        foreach ($loanInfo as $repay) {
            if (substr($repay['loan_repayment_date'], 0, 7) == substr($monthDate, 0, 7)) {
                $paidRow = $repay;
                break;
            }
            
        }
        //  var_dump($paidRow);
        //   die();
        $isPaid = ($paidRow && $paidRow['amount_paid'] > 0);
        // if($isPaid){
        //     $amountPaid =  number_format($paidRow['amount_paid'], 2);
        //     $repayDate = htmlspecialchars($paidRow['loan_repayment_date']);
        //     $disabled = $isPaid ? 'disabled' : '';
        // }
        // else{
        $amountPaid = $isPaid ? number_format($paidRow['amount_paid'], 2) : '-';
        $repayDate = $isPaid ? htmlspecialchars($paidRow['date_paid']) : '-';
        $disabled = $isPaid ? 'disabled' : '';
        
        
 
        echo "<tr>
                <td>" . ($i + 1) . "</td>
                <td>" . date('F Y', strtotime($monthDate)) . "</td>
                <td>₦" . number_format($monthlyPayment, 2) . "</td>
                <td>{$amountPaid}</td>
                <td>{$repayDate}</td>
                <td>
                    <form class='repayment-form' style='margin:0;' onsubmit='submitRepayment(event, this)'>
                        <input type='hidden' name='loan_id' value='{$loan['loan_id']}'>
                        <input type='hidden' name='member_id' value='{$loan['member_id']}'>
                        <input type='hidden' name='loan_repayment_amount' value='{$loan['loan_repayment_amount']}'>
                        <input type='hidden' name='repayment_date' value='{$monthDate}'>
                        <input type='number' name='amount_paid' step='0.01' min='0' value='{$monthlyPayment}' {$disabled} required>
                        <button type='submit' class='btn btn-success btn-sm' {$disabled}>Submit</button>
                    </form>
                </td>
              </tr>";
    }

    echo "</tbody></table></div>";

    // JS for handling submission (AJAX)
    echo "
    <script>
   
    function submitRepayment(e, form) {
        e.preventDefault();
        const formData = new FormData(form);
        const button = form.querySelector('button');
        button.disabled = true;
        button.textContent = 'Submitting...';

        fetch('../../ajax/save_repayment.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.status === 'success') {
                form.querySelector('input[name=\"amount_paid\"]').disabled = true;
                button.textContent = 'Submitted';
             setTimeout(() => {
                window.location.reload();
            }, 1500);
                
            } else {
                button.disabled = false;
                button.textContent = 'Submit';
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error saving repayment. Please try again.');
            button.disabled = false;
            button.textContent = 'Submit';
        });
    }
    </script>
    ";
}


public function updateGuaranto($loan_id){
        $db = new DB();
        $con = $db->getConnection();
            $status = 1; 
            $date=date('Y-m-d');
            // Update loan status in the database
            $updateSql = "UPDATE fudscoops_loan SET guarantor_status = :status ,guarantor_approved_date=:date WHERE loan_id = :loan_id";
            $updateStmt = $con->prepare($updateSql);
            $updateStmt->bindParam(':status', $status);
            $updateStmt->bindParam(':loan_id', $loan_id);
            $updateStmt->bindParam(':date', $date);
            $updateStmt->execute();
            if($updateStmt){
                return 1;
            }else{
                return -1;
            }
            

}

public function processLoan($loan_id,$member_id,$repayment,$action,$amount=''){
        $db = new DB();
        $con = $db->getConnection();
            $date=date('Y-m-d');
            // Update loan status in the database
            $is_action_disabled=1;
            $updateSql = "UPDATE fudscoops_loan SET loan_status = :status ,amount_recommended=:amount,update_at=:date ,is_action_disabled =:disabled WHERE loan_id = :loan_id";
            $updateStmt = $con->prepare($updateSql);
            $updateStmt->bindParam(':status', $action);
            $updateStmt->bindParam(':loan_id', $loan_id);
            $updateStmt->bindParam(':amount', $amount);
            $updateStmt->bindParam(':date', $date);
            $updateStmt->bindParam(':disabled', $is_action_disabled);
            $updateStmt->execute();
            if($updateStmt){
                //insert only if the loan is approved
                    if($action=='Approve'){
                        $is_deleted = 0;
                        // Check if the record exists
                        $sql = "SELECT * FROM fudscoops_loan_repayments WHERE loan_id = :loan_id AND member_id = :member_id AND is_deleted = :is_deleted";
                        $stmt = $con->prepare($sql);
                        $stmt->bindParam(':loan_id', $loan_id, PDO::PARAM_INT);
                        $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
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
                            $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
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
                            $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
                            $stmt->execute();
                            
                            if ($stmt->rowCount() > 0) {
                                return 1; // Successfully inserted
                            } else {
                                return -1; // Insertion failed
                            }
                        }

                        
                    }else{
                         //deleted from repayment
                        $deleted = 1;
                        $sql = "UPDATE fudscoops_loan_repayments SET is_deleted = :deleted WHERE loan_id = :loan_id AND member_id = :member_id";
                        $stmt = $con->prepare($sql);
                        $stmt->bindParam(':loan_id', $loan_id, PDO::PARAM_INT); 
                        $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
                        $stmt->bindParam(':deleted', $deleted, PDO::PARAM_INT);
                        $stmt->execute();
                        if ($stmt) {
                            return 1;
                        } else {
                            return -1;
                        }

                    }
            }else{
                return -1;
            }
            

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
            if($loanDetails['guarantor_status']==1){
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
                                <button type='submit' class='btn btn-success' $button>Submit Undertaking</button>
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
                        <p><strong>Loan Repayment Period:</strong> " . htmlspecialchars($loanDetails['loan_tenor']) . "  month(s) .</p>
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
public function getChairmanLoanDecision($loan_id) {
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
            if($loanDetails['loan_status'] == 'Approve'){
               $button ='disabled';
               $amount_recommended =$loanDetails['amount_recommended'];
            }else{
                $button='';
            }
            if($loanDetails['is_action_disabled']==1){
                $unlock ="
                
                <button type='button' class='btn btn-danger'id='unlock' loan_id=".$loanDetails['loan_id']." member_id=".$loanDetails['member_id']." >Unlock</button>";
            }else{
               $unlock=''; 
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
                                    <p><strong>Chairman Remarks:</strong></p>
                                </div> 
                                <div>
                                    <label> Comment:</label>
                                     <textarea class='form-control' row='2' name ='comment' required placeholder='Comment here'>".$loanDetails['chairman_comment']."</textarea>
                                     <input type='hidden' name='laon_tenor' id='loan_tenor' class='form-control' value=".$loanDetails['loan_tenor'].">
                                     <input type='hidden' name='member_id' id='member_id' class='form-control' value=".$loanDetails['member_id'].">
                                     <input type='hidden' name='decision'  class='form-control' value='chairman'>
                                     <input type='hidden' name='type' id='type' class='form-control' value='decision'>
                                    <h4 class='amount_msg text-primary'></h4>
                                </div>
                                
                                <div class='msg'></div>
                                <br>
                                <button type='submit' class='btn btn-success' name='proceed' id='proceed' $button value='proceed' >Proceed</button>
                                <button type='submit' class='btn btn-danger' name='Decline' id='Decline' $button value='Decline'>Decline</button>
                                $unlock
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
    $db = new DB();
    $con = $db->getConnection();
    $date =date('Y-m-d');
    if($action =='Proceed'){
        $decision =1;
    }else{
        $decision =0;
    }
    $sql = "UPDATE fudscoops_loan SET secretary_decision =:decision,secretary_date=:date,secretary_comment=:comment WHERE loan_id=:loan_id AND member_id=:member_id";
    
    $stmt = $con->prepare($sql);
    $stmt->bindParam(':date', $date, PDO::PARAM_STR);
    $stmt->bindParam(':decision', $decision, PDO::PARAM_INT);
    $stmt->bindParam(':loan_id', $loan_id, PDO::PARAM_INT);
    $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
    $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
    $stmt->execute();
    if ($stmt) {
         return 1;
    } else {
        return 0; 
    }
}
public function chairmanDecision($loan_id,$member_id,$action,$comment) {
    $db = new DB();
    $con = $db->getConnection();
    $date =date('Y-m-d');
   
            if($action =='Proceed'){
                $decision =1;
            }else{
                $decision =0;
            }
        $sql = "UPDATE fudscoops_loan SET chairman_approval =:decision,chairman_approval_date=:date,chairman_comment=:comment WHERE loan_id=:loan_id AND member_id=:member_id";
        
        $stmt = $con->prepare($sql);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR);
        $stmt->bindParam(':decision', $decision, PDO::PARAM_INT);
        $stmt->bindParam(':loan_id', $loan_id, PDO::PARAM_INT);
        $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
        $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt) {
             return 1;
        } else {
            return 0; 
        }

}
public function chairmanUnlockLoandDecision($loan_id,$member_id) {
    $db = new DB();
    $con = $db->getConnection();
        $lock=0;
        $sql = "UPDATE fudscoops_loan SET is_action_disabled =:lock WHERE loan_id=:loan_id AND member_id=:member_id";
        $stmt = $con->prepare($sql);
        $stmt->bindParam(':loan_id', $loan_id, PDO::PARAM_INT);
        $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
        $stmt->bindParam(':lock', $lock, PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt) {
             return 1;
        } else {
            return 0; 
        }

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
    $db = new DB();
    $con = $db->getConnection();
    $status = 1;
    $date_paid = date('Y-m-d');

    // Check if repayment for this month already exists
    $checkSql = "SELECT loan_repayment_id FROM fudscoops_loan_repayments 
                 WHERE loan_id = :loan_id 
                   AND member_id = :member_id 
                   AND DATE_FORMAT(loan_repayment_date, '%Y-%m') = DATE_FORMAT(:repayment_date, '%Y-%m')";
    $checkStmt = $con->prepare($checkSql);
    $checkStmt->bindParam(':loan_id', $loan_id);
    $checkStmt->bindParam(':member_id', $member_id);
    $checkStmt->bindParam(':repayment_date', $repayment_date);
    $checkStmt->execute();

    if ($checkStmt->rowCount() > 0) {
        // date existing repayment
        $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
        $updateSql = "UPDATE fudscoops_loan_repayments 
                      SET amount_paid = :amount_paid,
                          date_paid = :date_paid,
                          status = :status,
                          loan_repayment_amount = :loan_repayment_amount
                      WHERE loan_repayment_id = :repayment_id";
        $updateStmt = $con->prepare($updateSql);
        $updateStmt->bindParam(':amount_paid', $amount_paid);
        $updateStmt->bindParam(':date_paid', $date_paid);
        $updateStmt->bindParam(':status', $status);
        $updateStmt->bindParam(':loan_repayment_amount', $loan_repayment_amount);
        $updateStmt->bindParam(':repayment_id', $existing['loan_repayment_id']);
        return $updateStmt->execute() ? 1 : 0;

    } else {
        // sert new repayment
        $insertSql = "INSERT INTO fudscoops_loan_repayments 
                      (loan_id, member_id, amount_paid, loan_repayment_date, date_paid, status, loan_repayment_amount)
                      VALUES (:loan_id, :member_id, :amount_paid, :repayment_date, :date_paid, :status, :loan_repayment_amount)";
        $insertStmt = $con->prepare($insertSql);
        $insertStmt->bindParam(':loan_id', $loan_id);
        $insertStmt->bindParam(':member_id', $member_id);
        $insertStmt->bindParam(':amount_paid', $amount_paid);
        $insertStmt->bindParam(':repayment_date', $repayment_date);
        $insertStmt->bindParam(':date_paid', $date_paid);
        $insertStmt->bindParam(':status', $status);
        $insertStmt->bindParam(':loan_repayment_amount', $loan_repayment_amount);
        return $insertStmt->execute() ? 1 : 0;
    }
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

public function getUnpaidMonths($loan_id, $member_id) {
    $db = new DB();
    $con = $db->getConnection();

    $status = 1;

    // SQL joins fudscoops_loan with repayments
    $sql = "
        SELECT 
            l.loan_tenor,
            COUNT(lr.loan_repayment_id) AS paid_months
        FROM 
            fudscoops_loan l
        LEFT JOIN 
            fudscoops_loan_repayments lr 
                ON l.loan_id = lr.loan_id 
                AND lr.member_id = :member_id 
                AND lr.status = :status
        WHERE 
            l.loan_id = :loan_id
        GROUP BY 
            l.loan_tenor
    ";

    $stmt = $con->prepare($sql);
    $stmt->bindParam(':loan_id', $loan_id, PDO::PARAM_INT);
    $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
    $stmt->bindParam(':status', $status, PDO::PARAM_INT);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        return 0; // if loan not found
    }

    $loanTenor = (int)$row['loan_tenor'];
    $paidMonths = (int)$row['paid_months'];
    $unpaidMonths = max($loanTenor - $paidMonths, 0);

    return $unpaidMonths;
}
// get repyament amount
public function getRepaymentAmount($loan_id, $member_id) {
    $db = new DB();
    $con = $db->getConnection();

    $status = 1;

    // SQL joins fudscoops_loan with repayments
    $sql = "
        SELECT 
           loan_repayment_amount
        FROM 
            fudscoops_loan_repayments
        
        WHERE 
            loan_id = :loan_id AND
            member_id = :member_id
        ORDER BY 
            loan_repayment_id ASC
    ";

    $stmt = $con->prepare($sql);
    $stmt->bindParam(':loan_id', $loan_id, PDO::PARAM_INT);
    $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        return 0; // if loan not found
    }
    return $row['loan_repayment_amount'];
}





//=======================Hussain methode end from here============================================================================




}