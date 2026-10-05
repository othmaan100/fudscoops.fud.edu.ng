<?php
if(!isset($_SESSION)) 
    { 
        session_start(); 
    } 
    
require_once('User.php');

class MemberG2{
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
  
    public function PurchaseShare($employeeid,$share_amount,$bank_id,$tellerno,$amount_paid,$share_amount_word){
        
            
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
                
              $sql ="UPDATE fudscoops_shares SET unit_price=:share_amount,bank_id=:bank_id,teller_no=:tellerno,
              amount_paid=:amount_paid,shares_amount_word=:share_amount_word WHERE member_id='$memberid'";
              $stmt = $con->prepare($sql);
              $stmt->bindParam(':share_amount', $share_amount,PDO::PARAM_STR);
              $stmt->bindParam(':bank_id', $bank_id,PDO::PARAM_STR);
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
             
            
                $sql = "INSERT INTO fudscoops_shares(member_id, unit_price, bank_id, teller_no,amount_paid,shares_amount_word)
                VALUES (:memberid, :share_amount, :bank_id, :tellerno, :amount_paid, :share_amount_word)";
                // Create a prepared statement
                $stmt = $con->prepare($sql);
                // Bind parameters
                $stmt->bindParam(':memberid', $memberid,PDO::PARAM_STR);
                $stmt->bindParam(':share_amount', $share_amount,PDO::PARAM_STR);
                $stmt->bindParam(':bank_id', $bank_id,PDO::PARAM_STR);
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
            $user = new User();
            $con = $db->getConnection();
            $employeeid = $user->getEmployeeId($staffno);
            $receiverInfo = $user->getShareReceiverInfo($employeeid);
           // $share_amount_word = $user->numberToWords($worth);

            $q = "SELECT fname,lname,oname,sp_no,n.dept,phone_no,e.employee_id,f.member_id FROM employee e
                  JOIN ippis_static_record s ON e.employee_id=s.employee_id JOIN ippis_nonstatic_record n
                  ON s.row_id=n.ippis_static_id JOIN fudscoops_member f ON e.employee_id=f.employee_id WHERE e.sp_no='".$staffno."' ";
            $stm = $con->prepare($q);
            $stm->execute();
            $total_rows_found = $stm->rowCount();
            if ($total_rows_found > 0) {
                $row = $stm->fetch(PDO::FETCH_ASSOC);

                    $r ="<br/><div class='alert alert-success' style='width: 170%;'>
                        <div class='form-group row'>
                           <div class='col-12 col-md-6'>
                                <b class='text-danger'>Name: <input type='text' disabled class='form-control' value='".$row['fname']." ".$row['lname']." ".$row['oname']."'/>
                                </b>
                           </div>
                           <div class='col-12 col-md-6'>
                                <b class='text-danger'>Staff No: <input type='text' disabled class='form-control' id='sp_no' name='sp_no' value='".$row['sp_no']."'/>
                                </b>
                           </div>    
                        
                        </div><br/>
                        <div class='form-group row'>
                           <div class='col-12 col-md-6'>
                                <b class='text-danger'>Department: <input type='text' disabled class='form-control' value='".$row['dept']."'/>
                                </b>
                            </div>
                           <div class='col-12 col-md-6'>
                                <b class='text-danger'>Phone No: <input type='text' disabled class='form-control' value='".$row['phone_no']."'/>
                                </b>
                           </div>    
                        
                        </div><br/>
                        
                        <b class='text-primary'>AMOUNT (WORTH ₦): <input type='number' class='form-control' id='worth' name='worth' required value='".$receiverInfo['worth']."'/>
                        </b><br/>
                        <div class='amount_msg'></div>
                        <b class='text-primary'>AMOUNT IN WORDS: <input type='text' class='form-control' id='amount_word' name='amount_word' required value='".$receiverInfo['amount']."'/>
                        </b><br/>
                           
                            <div class='row container-fluid'>
                                <button id='add_share_transfer' rel='".$row['member_id']."' class='pull-right btn btn-sm btn-danger'><i class='fa fa-submit'></i> Submit</button>
                            </div>
                            <div id='msg'></div>
                        </div>";
                } else {
                $r = "<div class='alert alert-danger'><h4 class=''>No data found</h4></div>";
            }//end if ($total_rows_found == 1)
            //$con=null;
            return $r;
    }
    
    
    public function getCurrentSavingsAmount($spNo)
    {
        $r = '';
        $db = new DB();
        $con = $db->getConnection();
        $user = new User();
        $employeeid = $user->getEmployeeId($spNo);
        $member_id =$user->getMemberId($employeeid);
        
        // Query for total savings
        $query_savings = "SELECT SUM(savings_amount) as total_savings 
                      FROM fudscoops_savings 
                      WHERE member_idmember = :member_id";
        $stm_savings = $con->prepare($query_savings);
        $stm_savings->bindParam(':member_id', $member_id, PDO::PARAM_INT);
        $stm_savings->execute();
        $result_savings = $stm_savings->fetch(PDO::FETCH_ASSOC);
        $total_savings = $result_savings['total_savings'] ?? 0;
    
    
        // Query for total withdrawals
        $query_withdrawals = "SELECT SUM(approved_withrawal_amount) as total_withdrawals 
                              FROM fudscoops_withrawals 
                              WHERE chairman_approval='1' AND member_id = :member_id";
        $stm_withdrawals = $con->prepare($query_withdrawals);
        $stm_withdrawals->bindParam(':member_id', $member_id, PDO::PARAM_INT);
        $stm_withdrawals->execute();
        $result_withdrawals = $stm_withdrawals->fetch(PDO::FETCH_ASSOC);
        $total_withdrawals = $result_withdrawals['total_withdrawals'] ?? 0;
        
        // Calculate the difference
        $difference = $total_savings - $total_withdrawals;
        
        if ($difference) {
            
            $r = $difference;
        } else {
            $r = 0;
        }
        
        $con = null;
        return $r;
    }

    
    public static function checkWorth($worth)
    {
        $db = new DB();
        $user = new User();
        $con = $db->getConnection();
        $shareAmount = $user->getShareAmount($_SESSION['username']);
    
        // Ensure worth is not greater than total share amount
        if ($worth > $shareAmount) {
            return -1; // worth exceeds available share amount
        }
    
        // Ensure remaining balance is not less than ₦20,000
        $remainingBalance = $shareAmount - $worth;
        if ($remainingBalance < 20000) {
            return -2; // remaining balance too low
        }
    
        return 1; // valid transaction
    }
    
    public function checkAmount($amount_paid)
    {
        $db = new DB();
        $user = new User();
        $con = $db->getConnection();
        $currentsavings = $this->getCurrentSavingsAmount($_SESSION['username']);

    
        // Ensure worth is not greater than total share amount
        if ($amount_paid > $currentsavings) {
            return -1; // worth exceeds available share amount
        }
    
        // Ensure remaining balance is not less than ₦20,000
        $remainingBalance = $currentsavings - $amount_paid;
        if ($remainingBalance < 2000) {
            return -2; // remaining balance too low
        }
    
        return 1; // valid transaction
    }


    
    public function transferPendingShare($employeeid, $worth, $share_amount_word) {
        $member = new MemberG2();
        $db = new DB();
        $user = new User();
        $con = $db->getConnection();
        
        // Fetch member ID and share ID
        $transfer_employeeid =$user->getEmployeeId($_SESSION['username']);
        $memberid = $user->getMemberId($transfer_employeeid);
        //$employeeid =$user->getEmployeeId($spNo);
        $shareId = $user->getShareId($memberid);
        $date = new DateTime();
        $shareDate = $date->format('Y-m-d H:i:s');
        
        // Check transfer_status in fudscoops_shares
        $sql = "SELECT transfer_status FROM fudscoops_shares WHERE shares_id = :shareid";
        $stmt = $con->prepare($sql);
        $stmt->bindParam(':shareid', $shareId, PDO::PARAM_INT);
        $stmt->execute();
        $shareRow = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($shareRow) {
            // If a transfer has been initiated (transfer_status = 1)
            if ($shareRow['transfer_status'] == 1) {
        
                // Check the latest transfer status in fudscoops_shares_transfer
                $sqlCheckTransfer = "SELECT share_transfer_status 
                                     FROM fudscoops_shares_transfer 
                                     WHERE shares_id = :shareid 
                                     ORDER BY transfer_id DESC LIMIT 1";
                $stmtCheckTransfer = $con->prepare($sqlCheckTransfer);
                $stmtCheckTransfer->bindParam(':shareid', $shareId, PDO::PARAM_INT);
                $stmtCheckTransfer->execute();
                $transferRow = $stmtCheckTransfer->fetch(PDO::FETCH_ASSOC);
        
                // If there is a transfer record and it's still pending (0), block new transfer
                if ($transferRow && $transferRow['share_transfer_status'] == 0) {
                    return "Error: You already have a pending share transfer awaiting approval.";
                }
        
                // If the last transfer is approved (1), allow new transfer (no return)
                // Optionally, you may reset transfer_status back to 0 here if you want
                // $sqlUpdate = "UPDATE fudscoops_shares SET transfer_status = 0 WHERE shares_id = :shareid";
                // $stmtUpdate = $con->prepare($sqlUpdate);
                // $stmtUpdate->bindParam(':shareid', $shareId, PDO::PARAM_INT);
                // $stmtUpdate->execute();
            }
        }

    
        // If transfer_status is 0, update it to 1
        $sql2 = "UPDATE fudscoops_shares SET transfer_status = 1 WHERE shares_id = :shareid";
        $stmt2 = $con->prepare($sql2);
        $stmt2->bindParam(':shareid', $shareId, PDO::PARAM_INT);
        $stmt2->execute();
            if (!$stmt2->execute()) {
            return "Error: Failed to update transfer status.";
            }
        
        // Check if the receiver has existing shares
        $sql3 = "SELECT * FROM fudscoops_shares_transfer WHERE received_employee_id = :employeeid AND share_transfer_status=0";
        $stmt3 = $con->prepare($sql3);
        $stmt3->bindParam(':employeeid', $employeeid, PDO::PARAM_INT);
        $stmt3->execute();
        
        if ($stmt3->rowCount() > 0) {
            // Receiver found: Update their amount_paid and shares_amount_word
            $sql4 = "UPDATE fudscoops_shares_transfer SET worth = :worth, amount_word = :share_amount_word, transfer_employee_id = :transfer_employee_id, received_employee_id = :received_employee_id, transfer_date = :date WHERE shares_id = :shareid";
            $stmt4 = $con->prepare($sql4);
            $stmt4->bindParam(':worth', $worth, PDO::PARAM_INT);
            $stmt4->bindParam(':share_amount_word', $share_amount_word, PDO::PARAM_STR);
            $stmt4->bindParam(':transfer_employee_id', $transfer_employeeid, PDO::PARAM_INT);
            $stmt4->bindParam(':received_employee_id', $employeeid, PDO::PARAM_INT);
            $stmt4->bindParam(':date', $shareDate, PDO::PARAM_STR);
            $stmt4->bindParam(':shareid', $shareId, PDO::PARAM_INT);
            $stmt4->execute();
            
            if ($stmt4->rowCount() > 0) {
                return 1; // Update successful
            } else {
                return -1; // Update failed
            }
        } else {
            // Receiver not found: Insert a new record
            $sql5 = "INSERT INTO fudscoops_shares_transfer (shares_id, worth, amount_word, transfer_employee_id, received_employee_id, transfer_date) VALUES (:shareid, :worth, :amount_word, :transfer_employee_id, :received_employee_id, :date)";
            $stmt5 = $con->prepare($sql5);
            $stmt5->bindParam(':shareid', $shareId, PDO::PARAM_INT);
            $stmt5->bindParam(':worth', $worth, PDO::PARAM_INT);
            $stmt5->bindParam(':amount_word', $share_amount_word, PDO::PARAM_STR);
            $stmt5->bindParam(':transfer_employee_id', $transfer_employeeid, PDO::PARAM_INT);
            $stmt5->bindParam(':received_employee_id', $employeeid, PDO::PARAM_INT);
            $stmt5->bindParam(':date', $shareDate, PDO::PARAM_STR);
            if ($stmt5->execute()) {
                return 1; // Insert successful
            } else {
                return -1; // Insert failed
            }
        }
    }
  
    
    public function TransferShare($memberid, $employeeid) {
        $db = new DB();
        $user = new User();
        $con = $db->getConnection();
    
        // Fetch receiver's member ID
        $receiverMemberId = $user->getShareReceiverMemberId($employeeid);
        if (!$receiverMemberId) {
            return -1; // Receiver does not exist
        }
    
        // Fetch 'amount_paid' from fudscoops_shares and 'worth' from fudscoops_shares_transfer (ensuring matching shares_id)
        $sql1 = "SELECT s.amount_paid, s.unit_price,t.worth 
                 FROM fudscoops_shares s
                 JOIN fudscoops_shares_transfer t ON s.shares_id = t.shares_id
                 WHERE s.member_id = :memberid";
        $stmt1 = $con->prepare($sql1);
        $stmt1->bindParam(':memberid', $memberid, PDO::PARAM_INT);
        $stmt1->execute();
        $shareData = $stmt1->fetch(PDO::FETCH_ASSOC);
    
        if (!$shareData) {
            return -2; // No matching shares found
        } else {
    
        $amount_paid = $shareData['amount_paid'];
        $unit_price = $shareData['unit_price'];
        $worth = $shareData['worth']; // Worth is from fudscoops_shares_transfer
    
        // Deduct from sender
        $total_amount_paid_left = $amount_paid - $worth;
        $total_unit_price_left = $unit_price - $worth;
        $total_share_amount_word_left = $user->numberToWords($total_amount_paid_left);
    
        $sql2 = "UPDATE fudscoops_shares 
                 SET amount_paid = :amount_paid, shares_amount_word = :share_amount_word, unit_price =:unit_price 
                 WHERE member_id = :memberid";
        $stmt2 = $con->prepare($sql2);
        $stmt2->bindParam(':amount_paid', $total_amount_paid_left, PDO::PARAM_INT);
        $stmt2->bindParam(':share_amount_word', $total_share_amount_word_left, PDO::PARAM_STR);
        $stmt2->bindParam(':unit_price', $total_unit_price_left, PDO::PARAM_INT);
        $stmt2->bindParam(':memberid', $memberid, PDO::PARAM_INT);
        $stmt2->execute();
        
        }
        // Check if receiver already has shares using the required SQL pattern
        $sql3 = "SELECT *,t.chairman_approval AS Approval,t.chairman_approval_date AS Date FROM fudscoops_shares_transfer t 
                 JOIN employee e ON t.received_employee_id = e.employee_id 
                 JOIN fudscoops_member m ON e.employee_id = m.employee_id 
                 JOIN fudscoops_shares s ON m.member_id = s.member_id 
                 WHERE s.member_id = :memberid";
        $stmt3 = $con->prepare($sql3);
        $stmt3->bindParam(':memberid', $receiverMemberId, PDO::PARAM_INT);
        $stmt3->execute();
        $receiverData = $stmt3->fetch(PDO::FETCH_ASSOC);
    
        $shareDate = (new DateTime())->format('Y-m-d H:i:s');
        $unit_price = $user->getShareUnitAmount();
        $share_amount_word_to_receiver = $user->numberToWords($worth);
        $chairman_approval = $receiverData['Approval'];
        $chairman_approval_date = $receiverData['Date'];
        
        if ($receiverData) {
            // Update receiver's share
            
            $total_amount_to_receiver = $receiverData['amount_paid'] + $worth;
            $total_unit_to_receiver = $receiverData['unit_price'] + $worth;
            $total_share_amount_word_to_receiver = $user->numberToWords($total_amount_to_receiver);
            $sql4 = "UPDATE fudscoops_shares 
                     SET amount_paid = :amount_paid, shares_amount_word = :share_amount_word, unit_price =:unit_price 
                     WHERE member_id = :receiverMemberId";
            $stmt4 = $con->prepare($sql4);
            $stmt4->bindParam(':amount_paid', $total_amount_to_receiver, PDO::PARAM_INT);
            $stmt4->bindParam(':share_amount_word', $total_share_amount_word_to_receiver, PDO::PARAM_STR);
            $stmt4->bindParam(':unit_price', $total_unit_to_receiver, PDO::PARAM_INT);
            $stmt4->bindParam(':receiverMemberId', $receiverMemberId, PDO::PARAM_INT);
            $stmt4->execute();
        } else {
            // Insert new record for receiver
            $sql5 = "INSERT INTO fudscoops_shares (member_id, unit_price, amount_paid, shares_amount_word, date, chairman_approval, chairman_approval_date, update_at) 
                     VALUES (:receiverMemberId, :share_unit_price, :amount_paid, :share_amount_word, :share_date, :chairman_approval, :chairman_approval_date, :update_at)";
            $stmt5 = $con->prepare($sql5);
            $stmt5->bindParam(':receiverMemberId', $receiverMemberId, PDO::PARAM_INT);
            $stmt5->bindParam(':share_unit_price', $worth, PDO::PARAM_INT);
            $stmt5->bindParam(':amount_paid', $worth, PDO::PARAM_INT);
            $stmt5->bindParam(':share_amount_word', $share_amount_word_to_receiver, PDO::PARAM_STR);
            $stmt5->bindParam(':share_date', $shareDate, PDO::PARAM_STR);
            $stmt5->bindParam(':chairaman_approval', $chairman_approval, PDO::PARAM_INT);
            $stmt5->bindParam(':chairaman_approval_date', $chairman_approval_date, PDO::PARAM_STR);
            $stmt5->bindParam(':update_at', $shareDate, PDO::PARAM_STR);
            $stmt5->execute();
        }
    
        return 1; // Transfer successful
    }

    
    public function getTotalShareTransfer($received_employee_id) {
        $db = new DB();
        $con = $db->getConnection();
    
        $sql = "SELECT COUNT(transfer_id) as total FROM fudscoops_shares_transfer WHERE received_employee_id=:id";
        
        // Prepare the statement
        $stmt = $con->prepare($sql);
        
        // Bind parameters
        $stmt->bindParam(':id', $received_employee_id, PDO::PARAM_INT);
        
        // Execute the statement
        $stmt->execute();
        
        // Fetch the result
        
        // Check if the result is valid
        if ($stmt->rowCount() > 0) {
             $row = $stmt->fetch(PDO::FETCH_ASSOC);
            // Return the total with a link
            return "<a href='transfer.php' class='btn btn-info mt-2 mt-xl-0'>Accept Shares: " . $row['total'] . "</a>";
        } else {
            return ''; 
        }
     }
      
    public function checkTransferShare($received_employee_id) {
            $db = new DB();
            $con = $db->getConnection();
        
            // Get all share details associated with transfer check
            $sql = "SELECT 
                        m.member_id, e.fname, e.lname,
                        t.shares_id, t.worth, t.amount_word,t.transfer_date,
                        t.transfer_employee_id,t.transfer_id,t.is_accepted
                    FROM 
                        fudscoops_shares_transfer t
                    JOIN
                        fudscoops_shares s ON t.shares_id=s.shares_id 
                    JOIN 
                        fudscoops_member m ON s.member_id = m.member_id
                    JOIN 
                        employee e ON e.employee_id = m.employee_id
                    WHERE 
                        t.received_employee_id = :received_employee_id";
        
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':received_employee_id', $received_employee_id, PDO::PARAM_INT);
            $stmt->execute();
        
            // Fetch all loan information for the guarantor
            $transferInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
            if ($transferInfo) {
                $sn = 0;
                echo "<table id='transferTable' class='display table table-striped table-bordered'>";
                echo "<thead>
                        <tr>
                            <th>S/N</th>
                            <th>Sender Name</th>
                            <th>Share Amount</th>
                            <th>Share in Words</th>
                            <th>Share Transfer Date</th>
                            <th>Action</th>
                        </tr>
                      </thead>";
                echo "<tbody>";
                // Display each loan applicant in a table row
                foreach ($transferInfo as $transfer) {
                    $sn++;
                    if ($transfer['is_accepted'] == 0) {
                    $action = "<button class='btn btn-primary accept-transfer' data-transfer_id='" . htmlspecialchars($transfer['transfer_id']) . "'>Accept Shares</button>";
                    } else {
                    $action = "<button class='btn btn-primary msg disabled' disabled>Shares Accepted</button>";
                    }
                    echo "<tr>";
                    echo "<td>" . $sn . "</td>";
                    echo "<td>" . htmlspecialchars($transfer['fname'] . ' ' . $transfer['lname']) . "</td>";
                    echo "<td>" . htmlspecialchars($transfer['worth']) . "</td>";
                    echo "<td>" . htmlspecialchars($transfer['amount_word']) . "</td>";
                    echo "<td>" . htmlspecialchars($transfer['transfer_date']) . "</td>";
                    echo "<td>.$action.</td>";
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
                        $('#transferTable').DataTable({
                            paging: true,
                            searching: true,
                            ordering: true,
                            responsive: true
                        });
                    });
                    
                    $(document).on('click', '.accept-transfer', function (e) {
                    e.preventDefault();
                        // Extract the transfer ID from the button's ID
                      const transferId = $(this).data('transfer_id');
                       //alert('ok');
                       
                       // Confirm the submission
                        var result = confirm('Are you sure you want to accept this share?');
                        if (!result) {
                            return; // If the user clicks Cancel, return without showing the loading message
                        }

                                $.ajax({
                                    url: '../ajax/accept_transfer_share.php',
                                    data:{transfer_id: transferId}, // Pass the transfer ID
                                    type: 'post',
                                    success: function (msg) {
                                        // alert(msg)
                                        if(msg == 1){
                                            alert('Transfer successfully accepted!');
                                            // Dynamically update the button
                                                const button = $(`.accept-transfer[data-transfer_id='${transferId}'']`);
                                                button.text('Accepted'); // Change button text
                                                button.prop('disabled', true); // Disable button
                                                button.removeClass('accept-transfer').addClass('msg disabled'); // Update classes

                                            
                                        }
                                        else{
                                            alert('Failed to accept transfer, contact system admin.')
                                        }
                                    }
                                });//end ajax
                    
                        });
                       
                    </script>";
    }
    
    public function acceptShare($transferId) {
        // Initialize the database connection
        $db = new DB();
        $con = $db->getConnection();
           
        // Update the transfer status to accepted
        $updateSql = "UPDATE fudscoops_shares_transfer SET is_accepted = 1 WHERE transfer_id = :transfer_id";
        $updateStmt = $con->prepare($updateSql);
        $updateStmt->bindParam(':transfer_id', $transferId, PDO::PARAM_INT);
        
        if ($updateStmt->execute()) {
            return 1;
        } else {
            return -1;
        }
    }
    
    public function conversionPendingShare($employeeid, $worth, $share_amount_word) {
        $member = new MemberG2();
        $db = new DB();
        $user = new User();
        $con = $db->getConnection();
        
        // Fetch member ID
        $memberid = $user->getMemberId($employeeid);
        
        
        $date = new DateTime();
        $shareDate = $date->format('Y-m-d H:i:s');
        
        // Check if the member has the existing shares conversion
        $sql = "SELECT * FROM fudscoops_shares_conversion WHERE member_id = :memberid AND conversion_status=0";
        $stmt = $con->prepare($sql);
        $stmt->bindParam(':memberid', $memberid, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            // Shares Coversion found: Update their amount_paid and shares_amount_word
            $sql2 = "UPDATE fudscoops_shares_conversion SET amount_paid = :worth, shares_amount_word = :share_amount_word, conversion_date = :date WHERE member_id = :memberid AND conversion_status=0";
            $stmt2 = $con->prepare($sql2);
            $stmt2->bindParam(':worth', $worth, PDO::PARAM_STR);
            $stmt2->bindParam(':share_amount_word', $share_amount_word, PDO::PARAM_STR);
            $stmt2->bindParam(':date', $shareDate, PDO::PARAM_STR);
            $stmt2->bindParam(':memberid', $memberid, PDO::PARAM_INT);
            $stmt2->execute();
            
            if ($stmt2->rowCount() > 0) {
                return 1; // Update successful
            } else {
                return -1; // Update failed
            }
        } else {
            // Receiver not found: Insert a new record
            $sql3 = "INSERT INTO fudscoops_shares_conversion (member_id, amount_paid, shares_amount_word, conversion_date) VALUES (:memberid, :worth, :amount_word, :date)";
            $stmt3 = $con->prepare($sql3);
            $stmt3->bindParam(':memberid', $memberid, PDO::PARAM_INT);
            $stmt3->bindParam(':worth', $worth, PDO::PARAM_STR);
            $stmt3->bindParam(':amount_word', $share_amount_word, PDO::PARAM_STR);
            $stmt3->bindParam(':date', $shareDate, PDO::PARAM_STR);
            if ($stmt3->execute()) {
                return 1; // Insert successful
            } else {
                return -1; // Insert failed
            }
        }
    }
  
    
    public function updateShareUnitAmount($share,$price)
    {
            
            $db = new DB();
            $con = $db->getConnection();

            //check already inserted
            $query = "SELECT * FROM fudscoops_share_unit WHERE  share=:share";
            $stmt = $con->prepare($query);
            $stmt->bindParam(':share', $share, PDO::PARAM_STR); // Use PARAM_INT if share is an integer
            $stmt->execute();
            if($stmt->rowCount()>0){
                $q = "UPDATE fudscoops_share_unit SET price=:price WHERE share=:share";
                $stm = $con->prepare($q);
                $stm->bindParam(':share',$share,PDO::PARAM_STR);
                $stm->bindParam(':price',$price,PDO::PARAM_INT);
                 if ($stm->execute()) {
                    return 'Share Unit Updated Successfully!'; // Update successful
                 } else {
                    return 'Share Unit Failed to Update!';// Update failed
                 }
                
            } else{
                $q = "INSERT INTO fudscoops_share_unit (share,price) VALUES(:share,:price)";

                $stm = $con->prepare($q);
                $stm->bindParam(':share',$share,PDO::PARAM_STR);
                $stm->bindParam(':price',$price,PDO::PARAM_INT);
                 if ($stm->execute()) {
                    return 'Share Unit Created Successfully!'; // Insert successful
                 } else {
                    return 'Share Unit Failed to Create!';// Insert failed
                }
            }
    }
    
    public function getShareTable() {
            $db = new DB();
            $con = $db->getConnection();
        
            // Get all loan details associated with the guarantor
            $sql = "SELECT 
                        m.member_id, e.fname, e.lname,e.oname,
                        s.shares_id, s.amount_paid, s.shares_amount_word, s.unit_price,s.teller_no, s.date
                    FROM 
                        fudscoops_shares s
                    JOIN 
                        fudscoops_member m ON s.member_id = m.member_id
                    JOIN 
                        employee e ON e.employee_id = m.employee_id
                        
                        WHERE s.share_status=0
                    ";
            $stmt = $con->prepare($sql);
            $stmt->execute();
        
            // Fetch all share information for the guarantor
            $shareInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
            if ($shareInfo) {
                $sn = 0;
                echo "<h3>FUDCOOPS Shares Details</h3>";
                echo "<table id='example' class='display table table-striped table-bordered' width='100%'>";
                echo "<thead>
                        <tr>
                            <th>S/N</th>
                            <th>Share Applicant Name</th>
                            <th>Share Amount</th>
                            <th>Share Unit Price</th>
                            <th>Share Date</th>
                            <th>Actions</th>
                        </tr>
                      </thead>";
                echo "<tbody>";
                // Display each loan applicant in a table row
                foreach ($shareInfo as $share) {
                    $sn++;
                    echo "<tr>";
                    echo "<td>" . $sn . "</td>";
                    echo "<td>" . htmlspecialchars($share['fname'] . ' ' . $share['lname'] . ' ' . $share['oname']) . "</td>";
                    echo "<td>" . htmlspecialchars($share['amount_paid']) . "</td>";
                    echo "<td>" . htmlspecialchars($share['unit_price']) . "</td>";
                    echo "<td>" . htmlspecialchars($share['date']) . "</td>";
                    echo "<td>
                            <a href='view_share_details.php?shares_id=" . htmlspecialchars($share['shares_id']) . "' class='btn btn-primary'>Action</a>
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
                        $('#transferTable').DataTable({
                            paging: true,
                            searching: true,
                            ordering: true,
                            responsive: true
                        });
                    });
                  </script>";
        
    }
    
    public function getShareTransferTable() {
            $db = new DB();
            $con = $db->getConnection();
        
            // Get all share details associated with the member
            // Get all share transfers with received and transferred employees in the same row
            $sql = "SELECT 
                e1.fname AS received_fname, e1.lname AS received_lname, e1.oname AS received_oname,
                e2.fname AS transfer_fname, e2.lname AS transfer_lname, e2.oname AS transfer_oname,
                s.shares_id, s.amount_paid, s.shares_amount_word, s.unit_price, 
                t.*, t.amount_word AS Amount, t.shares_id AS trans_sharesid
            FROM 
                fudscoops_shares s
            JOIN 
                fudscoops_shares_transfer t ON s.shares_id = t.shares_id
            LEFT JOIN 
                employee e1 ON e1.employee_id = t.received_employee_id
            LEFT JOIN 
                employee e2 ON e2.employee_id = t.transfer_employee_id
            WHERE 
                t.is_accepted = 1 AND t.share_transfer_status=0";
            $stmt = $con->prepare($sql);
            $stmt->execute();
        
            // Fetch all share information for the guarantor
            $shareInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Merge both share information results
            //$allShares = array_merge($shareInfo, $shareInfo2);
            
            if ($shareInfo) {
                $sn = 0;
                echo "<h3>FUDCOOPS Shares Transfer Details</h3>";
                echo "<table id='transferTable' class='display table table-striped table-bordered' width='100%'>";
                echo "<thead>
                        <tr>
                            <th>S/N</th>
                            <th>Received By</th>
                            <th>Transferred By</th>
                            <th>Amount Transferred</th>
                            <th>Current Share Amount</th>
                            <th>Share Transfer Date</th>
                            <th>Actions</th>
                        </tr>
                      </thead>";
                echo "<tbody>";
                // Display each loan applicant in a table row
                foreach ($shareInfo as $share) {
                    $sn++;
                    echo "<tr>";
                    echo "<td>" . $sn . "</td>";
                    echo "<td>" . htmlspecialchars(($share['received_fname'] ?? '') . ' ' . ($share['received_lname'] ?? '') . ' ' . ($share['received_oname'] ?? '')) . "</td>";
                    echo "<td>" . htmlspecialchars(($share['transfer_fname'] ?? '') . ' ' . ($share['transfer_lname'] ?? '') . ' ' . ($share['transfer_oname'] ?? '')) . "</td>";
                    echo "<td>" . htmlspecialchars($share['worth']) . "</td>";
                    echo "<td>" . htmlspecialchars($share['amount_paid']) . "</td>";
                    echo "<td>" . htmlspecialchars($share['transfer_date']) . "</td>";
                    echo "<td>
                            <a href='view_share_transfer_details.php?transfer_id=" . htmlspecialchars($share['transfer_id']) . "' class='btn btn-primary'>Action</a>
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
                        $('#transferTable').DataTable({
                            paging: true,
                            searching: true,
                            ordering: true,
                            responsive: true
                        });
                    });
                  </script>";
        
    }
    
    public function getShareViewTable($employeeid) {
            $db = new DB();
            $con = $db->getConnection();
        
            // Get all loan details associated with the guarantor
            $sql = "SELECT 
                        m.member_id, e.fname, e.lname,e.oname,
                        s.shares_id, s.amount_paid, s.shares_amount_word, s.unit_price,s.teller_no, s.date
                    FROM 
                        fudscoops_shares s
                    JOIN 
                        fudscoops_member m ON s.member_id = m.member_id
                    JOIN 
                        employee e ON e.employee_id = m.employee_id
                    WHERE e.employee_id =:empId AND s.share_status = 1 AND s.chairman_approval = 1
                    ";
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':empId', $employeeid, PDO::PARAM_INT);
            $stmt->execute();
        
            // Fetch all share information for the guarantor
            $shareInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
            if ($shareInfo) {
                $sn = 0;
                echo "<h3>FUDCOOPS Shares Details</h3>";
                echo "<table id='example' class='display table table-striped table-bordered' width='100%'>";
                echo "<thead>
                        <tr>
                            <th>S/N</th>
                            <th>Share Holder Name</th>
                            <th>Share Amount</th>
                            <th>Share Unit Price</th>
                            <th>Share Purchase Date</th>
                        </tr>
                      </thead>";
                echo "<tbody>";
                // Display each loan applicant in a table row
                foreach ($shareInfo as $share) {
                    $sn++;
                    echo "<tr>";
                    echo "<td>" . $sn . "</td>";
                    echo "<td>" . htmlspecialchars($share['fname'] . ' ' . $share['lname'] . ' ' . $share['oname']) . "</td>";
                    echo "<td>" . htmlspecialchars($share['amount_paid']) . "</td>";
                    echo "<td>" . htmlspecialchars($share['unit_price']) . "</td>";
                    echo "<td>" . htmlspecialchars($share['date']) . "</td>";
                    
                    echo "</tr>";
                }
        
                echo "</tbody>";
                echo "</table>";
            } else {
                echo "<p>No Shares found.</p>";
            }
            
            // Initialize DataTable
        echo "<script>
                    $(document).ready(function() {
                        $('#transferTable').DataTable({
                            paging: true,
                            searching: true,
                            ordering: true,
                            responsive: true
                        });
                    });
                  </script>";
        
    }
    
    public function getShareHolderViewTable() {
            $db = new DB();
            $con = $db->getConnection();
        
            // Get all loan details associated with the guarantor
            $sql = "SELECT 
                        m.member_id, e.fname, e.lname,e.oname,
                        s.shares_id, s.amount_paid, s.shares_amount_word, s.unit_price,s.teller_no, s.date
                    FROM 
                        fudscoops_shares s
                    JOIN 
                        fudscoops_member m ON s.member_id = m.member_id
                    JOIN 
                        employee e ON e.employee_id = m.employee_id
                    WHERE s.share_status = 1 AND s.chairman_approval = 1
                    ";
            $stmt = $con->prepare($sql);
            $stmt->execute();
        
            // Fetch all share information for the guarantor
            $shareInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
            if ($shareInfo) {
                $sn = 0;
                echo "<h3>FUDCOOPS Shares Details</h3>";
                echo "<table id='example' class='display table table-striped table-bordered' width='100%'>";
                echo "<thead>
                        <tr>
                            <th>S/N</th>
                            <th>Share Holder Name</th>
                            <th>Share Amount</th>
                            <th>Share Unit Price</th>
                            <th>Share Purchase Date</th>
                        </tr>
                      </thead>";
                echo "<tbody>";
                // Display each loan applicant in a table row
                foreach ($shareInfo as $share) {
                    $sn++;
                    echo "<tr>";
                    echo "<td>" . $sn . "</td>";
                    echo "<td>" . htmlspecialchars($share['fname'] . ' ' . $share['lname'] . ' ' . $share['oname']) . "</td>";
                    echo "<td>" . htmlspecialchars($share['amount_paid']) . "</td>";
                    echo "<td>" . htmlspecialchars($share['unit_price']) . "</td>";
                    echo "<td>" . htmlspecialchars($share['date']) . "</td>";
                    
                    echo "</tr>";
                }
        
                echo "</tbody>";
                echo "</table>";
            } else {
                echo "<p>No Shares found.</p>";
            }
            
            // Initialize DataTable
        echo "<script>
                    $(document).ready(function() {
                        $('#transferTable').DataTable({
                            paging: true,
                            searching: true,
                            ordering: true,
                            responsive: true
                        });
                    });
                  </script>";
        
    }
    
    public function getStaffViewTable() {
            $db = new DB();
            $con = $db->getConnection();
        
            // Get all loan details associated with the guarantor
            $sql = "SELECT 
                        employee_id, sp_no, title, CONCAT(fname, ' ', lname, ' ', oname)AS fullName, 
                        cadre, phone_no, email
                    FROM 
                        employee";
            $stmt = $con->prepare($sql);
            $stmt->execute();
        
            // Fetch all share information for the guarantor
            $staffInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
            if ($staffInfo) {
                $sn = 0;
                echo "<h3>All FUD Staff Details</h3>";
                echo "<table id='example' class='display table table-striped table-bordered' width='100%'>";
                echo "<thead>
                        <tr>
                            <th>S/N</th>
                            <th>Staff No.</th>
                            <th>Title</th>
                            <th>Staff Name</th>
                            <th>Cadre</th>
                            <th>Phone No.</th>
                            <th>Email</th>
                        </tr>
                      </thead>";
                echo "<tbody>";
                // Display each loan applicant in a table row
                foreach ($staffInfo as $staff) {
                    $sn++;
                    echo "<tr>";
                    echo "<td>" . $sn . "</td>";
                    echo "<td>" . htmlspecialchars($staff['sp_no']) . "</td>";
                    echo "<td>" . htmlspecialchars($staff['title']) . "</td>";
                    echo "<td>" . htmlspecialchars($staff['fullName']) . "</td>";
                    echo "<td>" . htmlspecialchars($staff['cadre']) . "</td>";
                    echo "<td>" . htmlspecialchars($staff['phone_no']) . "</td>";
                    echo "<td>" . htmlspecialchars($staff['email']) . "</td>";
                    
                    echo "</tr>";
                }
        
                echo "</tbody>";
                echo "</table>";
            } else {
                echo "<p>No Staff found.</p>";
            }
            
            // Initialize DataTable
        echo "<script>
                    $(document).ready(function() {
                        $('#transferTable').DataTable({
                            paging: true,
                            searching: true,
                            ordering: true,
                            responsive: true
                        });
                    });
                  </script>";
        
    }
    
    public function getChairmanShareTable() {
            $db = new DB();
            $con = $db->getConnection();
        
            // Get all loan details associated with the guarantor
            $sql = "SELECT 
                        m.member_id, e.fname, e.lname,e.oname,
                        s.shares_id, s.amount_paid, s.shares_amount_word, s.unit_price,s.teller_no, s.date
                    FROM 
                        fudscoops_shares s
                    JOIN 
                        fudscoops_member m ON s.member_id = m.member_id
                    JOIN 
                        employee e ON e.employee_id = m.employee_id
                    WHERE s.share_status=0";
            $stmt = $con->prepare($sql);
            $stmt->execute();
        
            // Fetch all share information for the guarantor
            $shareInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
            if ($shareInfo) {
                $sn = 0;
                echo "<h3>FUDCOOPS Shares Details</h3>";
                echo "<table id='example' class='display table table-striped table-bordered' width='100%'>";
                echo "<thead>
                        <tr>
                            <th>S/N</th>
                            <th>Share Applicant Name</th>
                            <th>Share Amount</th>
                            <th>Share Unit Price</th>
                            <th>Share Date</th>
                            <th>Actions</th>
                        </tr>
                      </thead>";
                echo "<tbody>";
                // Display each loan applicant in a table row
                foreach ($shareInfo as $share) {
                    $sn++;
                    echo "<tr>";
                    echo "<td>" . $sn . "</td>";
                    echo "<td>" . htmlspecialchars($share['fname'] . ' ' . $share['lname'] . ' ' . $share['oname']) . "</td>";
                    echo "<td>" . htmlspecialchars($share['amount_paid']) . "</td>";
                    echo "<td>" . htmlspecialchars($share['unit_price']) . "</td>";
                    echo "<td>" . htmlspecialchars($share['date']) . "</td>";
                    echo "<td>
                            <a href='view_share_details.php?shares_id=" . htmlspecialchars($share['shares_id']) . "' class='btn btn-primary'>Action</a>
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
                        $('#transferTable').DataTable({
                            paging: true,
                            searching: true,
                            ordering: true,
                            responsive: true
                        });
                    });
                  </script>";
        
    }
    
    public function getChairmanShareTransferTable() {
            $db = new DB();
            $con = $db->getConnection();
        
            // Get all share details associated with the member
            // Get all share transfers with received and transferred employees in the same row
            $sql = "SELECT 
                e1.fname AS received_fname, e1.lname AS received_lname, e1.oname AS received_oname,
                e2.fname AS transfer_fname, e2.lname AS transfer_lname, e2.oname AS transfer_oname,
                s.shares_id, s.amount_paid, s.shares_amount_word, s.unit_price, 
                t.*, t.amount_word AS Amount, t.shares_id AS trans_sharesid
            FROM 
                fudscoops_shares s
            JOIN 
                fudscoops_shares_transfer t ON s.shares_id = t.shares_id
            LEFT JOIN 
                employee e1 ON e1.employee_id = t.received_employee_id
            LEFT JOIN 
                employee e2 ON e2.employee_id = t.transfer_employee_id
            WHERE 
                t.is_accepted = 1 AND t.share_transfer_status=0";
            $stmt = $con->prepare($sql);
            $stmt->execute();
        
            // Fetch all share information for the guarantor
            $shareInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Merge both share information results
            //$allShares = array_merge($shareInfo, $shareInfo2);
            
            if ($shareInfo) {
                $sn = 0;
                echo "<h3>FUDCOOPS Shares Transfer Details</h3>";
                echo "<table id='transferTable' class='display table table-striped table-bordered' width='100%'>";
                echo "<thead>
                        <tr>
                            <th>S/N</th>
                            <th>Received By</th>
                            <th>Transferred By</th>
                            <th>Amount Transferred</th>
                            <th>Current Share Amount</th>
                            <th>Share Transfer Date</th>
                            <th>Actions</th>
                        </tr>
                      </thead>";
                echo "<tbody>";
                // Display each loan applicant in a table row
                foreach ($shareInfo as $share) {
                    $sn++;
                    echo "<tr>";
                    echo "<td>" . $sn . "</td>";
                    echo "<td>" . htmlspecialchars(($share['received_fname'] ?? '') . ' ' . ($share['received_lname'] ?? '') . ' ' . ($share['received_oname'] ?? '')) . "</td>";
                    echo "<td>" . htmlspecialchars(($share['transfer_fname'] ?? '') . ' ' . ($share['transfer_lname'] ?? '') . ' ' . ($share['transfer_oname'] ?? '')) . "</td>";
                    echo "<td>" . htmlspecialchars($share['worth']) . "</td>";
                    echo "<td>" . htmlspecialchars($share['amount_paid']) . "</td>";
                    echo "<td>" . htmlspecialchars($share['transfer_date']) . "</td>";
                    echo "<td>
                            <a href='view_share_transfer_details.php?transfer_id=" . htmlspecialchars($share['transfer_id']) . "' class='btn btn-primary'>Action</a>
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
                        $('#transferTable').DataTable({
                            paging: true,
                            searching: true,
                            ordering: true,
                            responsive: true
                        });
                    });
                  </script>";
        
    }
    
    public function getChairmanShareConversionTable() {
            $db = new DB();
            $con = $db->getConnection();
        
            // Get all share details associated with the member
            // Get all share transfers with received and transferred employees in the same row
            $sql = "SELECT 
                e.fname, e.lname, e.oname, e.sp_no, m.member_id, c.amount_paid, c.shares_amount_word, 
                c.conversion_id, c.conversion_date 
            FROM 
                fudscoops_member m
            JOIN 
                fudscoops_shares_conversion c ON m.member_id = c.member_id
            JOIN
                employee e ON e.employee_id = m.employee_id
            WHERE 
                c.conversion_status=0";
            $stmt = $con->prepare($sql);
            $stmt->execute();
        
            // Fetch all share information for the guarantor
            $shareInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Merge both share information results
            //$allShares = array_merge($shareInfo, $shareInfo2);
            
            if ($shareInfo) {
                $sn = 0;
                echo "<h3>FUDCOOPS Shares Conversion Details</h3>";
                echo "<table id='transferTable' class='display table table-striped table-bordered' width='100%'>";
                echo "<thead>
                        <tr>
                            <th>S/N</th>
                            <th>SP No</th>
                            <th>Name</th>
                            <th>Total Savings</th>
                            <th>Share Amount</th>
                            <th>Share Conversion Date</th>
                            <th>Actions</th>
                        </tr>
                      </thead>";
                echo "<tbody>";
                // Display each loan applicant in a table row
                foreach ($shareInfo as $share) {
                    $sn++;
                    $spNo = $share['sp_no'];
                    $currentsavings = $this->getCurrentSavingsAmount($spNo);

                    echo "<tr>";
                    echo "<td>" . $sn . "</td>";
                    echo "<td>" . $spNo . "</td>";
                    echo "<td>" . htmlspecialchars(($share['fname'] ?? '') . ' ' . ($share['lname'] ?? '') . ' ' . ($share['oname'] ?? '')) . "</td>";
                    echo "<td>" .$currentsavings. "</td>";
                    echo "<td>" . htmlspecialchars($share['amount_paid']) . "</td>";
                    echo "<td>" . htmlspecialchars($share['conversion_date']) . "</td>";
                    echo "<td>
                            <a href='view_share_conversion_details.php?conversion_id=" . htmlspecialchars($share['conversion_id']) . "' class='btn btn-primary'>Action</a>
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
                        $('#transferTable').DataTable({
                            paging: true,
                            searching: true,
                            ordering: true,
                            responsive: true
                        });
                    });
                  </script>";
        
    }


    
    public function getTreasurerShareTable() {
            $db = new DB();
            $con = $db->getConnection();
        
            // Get all loan details associated with the guarantor
            $sql = "SELECT 
                        m.member_id, e.fname, e.lname,e.oname,
                        s.shares_id, s.amount_paid, s.shares_amount_word, s.unit_price,s.teller_no, s.date
                    FROM 
                        fudscoops_shares s
                    JOIN 
                        fudscoops_member m ON s.member_id = m.member_id
                    JOIN 
                        employee e ON e.employee_id = m.employee_id
                    WHERE s.chairman_approval=1 AND s.share_status=0";
            $stmt = $con->prepare($sql);
            $stmt->execute();
        
            // Fetch all share information for the guarantor
            $shareInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
            if ($shareInfo) {
                $sn = 0;
                echo "<h3>FUDCOOPS Shares Details</h3>";
                echo "<table id='example' class='display table table-striped table-bordered' width='100%'>";
                echo "<thead>
                        <tr>
                            <th>S/N</th>
                            <th>Share Applicant Name</th>
                            <th>Share Amount</th>
                            <th>Share Unit Price</th>
                            <th>Share Date</th>
                            <th>Actions</th>
                        </tr>
                      </thead>";
                echo "<tbody>";
                // Display each loan applicant in a table row
                foreach ($shareInfo as $share) {
                    $sn++;
                    echo "<tr>";
                    echo "<td>" . $sn . "</td>";
                    echo "<td>" . htmlspecialchars($share['fname'] . ' ' . $share['lname'] . ' ' . $share['oname']) . "</td>";
                    echo "<td>" . htmlspecialchars($share['amount_paid']) . "</td>";
                    echo "<td>" . htmlspecialchars($share['unit_price']) . "</td>";
                    echo "<td>" . htmlspecialchars($share['date']) . "</td>";
                    echo "<td>
                            <a href='view_share_details.php?shares_id=" . htmlspecialchars($share['shares_id']) . "' class='btn btn-primary'>Action</a>
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
                        $('#transferTable').DataTable({
                            paging: true,
                            searching: true,
                            ordering: true,
                            responsive: true
                        });
                    });
                  </script>";
        
    }
    
    public function getTreasurerShareTransferTable() {
            $db = new DB();
            $con = $db->getConnection();
        
            // Get all share details associated with the member
            // Get all share transfers with received and transferred employees in the same row
            $sql = "SELECT 
                e1.fname AS received_fname, e1.lname AS received_lname, e1.oname AS received_oname,
                e2.fname AS transfer_fname, e2.lname AS transfer_lname, e2.oname AS transfer_oname,
                s.shares_id, s.amount_paid, s.shares_amount_word, s.unit_price, 
                t.*, t.amount_word AS Amount, t.shares_id AS trans_sharesid
            FROM 
                fudscoops_shares s
            JOIN 
                fudscoops_shares_transfer t ON s.shares_id = t.shares_id
            LEFT JOIN 
                employee e1 ON e1.employee_id = t.received_employee_id
            LEFT JOIN 
                employee e2 ON e2.employee_id = t.transfer_employee_id
            WHERE 
                t.is_accepted = 1 AND t.chairman_approval=1 AND t.share_transfer_status=0";
            $stmt = $con->prepare($sql);
            $stmt->execute();
        
            // Fetch all share information for the guarantor
            $shareInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Merge both share information results
            //$allShares = array_merge($shareInfo, $shareInfo2);
            
            if ($shareInfo) {
                $sn = 0;
                echo "<h3>FUDCOOPS Shares Transfer Details</h3>";
                echo "<table id='transferTable' class='display table table-striped table-bordered' width='100%'>";
                echo "<thead>
                        <tr>
                            <th>S/N</th>
                            <th>Received By</th>
                            <th>Transferred By</th>
                            <th>Amount Transferred</th>
                            <th>Current Share Amount</th>
                            <th>Share Transfer Date</th>
                            <th>Actions</th>
                        </tr>
                      </thead>";
                echo "<tbody>";
                // Display each loan applicant in a table row
                foreach ($shareInfo as $share) {
                    $sn++;
                    echo "<tr>";
                    echo "<td>" . $sn . "</td>";
                    echo "<td>" . htmlspecialchars(($share['received_fname'] ?? '') . ' ' . ($share['received_lname'] ?? '') . ' ' . ($share['received_oname'] ?? '')) . "</td>";
                    echo "<td>" . htmlspecialchars(($share['transfer_fname'] ?? '') . ' ' . ($share['transfer_lname'] ?? '') . ' ' . ($share['transfer_oname'] ?? '')) . "</td>";
                    echo "<td>" . htmlspecialchars($share['worth']) . "</td>";
                    echo "<td>" . htmlspecialchars($share['amount_paid']) . "</td>";
                    echo "<td>" . htmlspecialchars($share['transfer_date']) . "</td>";
                    echo "<td>
                            <a href='view_share_transfer_details.php?transfer_id=" . htmlspecialchars($share['transfer_id']) . "' class='btn btn-primary'>Action</a>
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
                        $('#transferTable').DataTable({
                            paging: true,
                            searching: true,
                            ordering: true,
                            responsive: true
                        });
                    });
                  </script>";
        
    }
    
    public function getTreasurerShareConversionTable() {
            $db = new DB();
            $con = $db->getConnection();
        
            // Get all share details associated with the member
            // Get all share transfers with received and transferred employees in the same row
            $sql = "SELECT e.fname, e.lname, e.oname,e.sp_no,
                m.member_id, c.amount_paid, c.shares_amount_word, c.conversion_id, 
                c.conversion_status, c.conversion_date
            FROM 
                fudscoops_member m
            JOIN 
                fudscoops_shares_conversion c ON m.member_id = c.member_id
            JOIN 
                employee e ON e.employee_id = m.employee_id
           
            WHERE 
                c.chairman_approval=1 AND c.conversion_status=0";
            $stmt = $con->prepare($sql);
            $stmt->execute();
        
            // Fetch all share information for the guarantor
            $shareInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Merge both share information results
            //$allShares = array_merge($shareInfo, $shareInfo2);
            
            if ($shareInfo) {
                $sn = 0;
                echo "<h3>FUDCOOPS Shares Conversion Details</h3>";
                echo "<table id='conversionTable' class='display table table-striped table-bordered' width='100%'>";
                echo "<thead>
                        <tr>
                            <th>S/N</th>
                            <th>SP No</th>
                            <th>Name</th>
                            <th>Total Savings</th>
                            <th>Share Amount</th>
                            <th>Share Conversion Date</th>
                            <th>Actions</th>
                        </tr>
                      </thead>";
                echo "<tbody>";
                // Display each loan applicant in a table row
                foreach ($shareInfo as $share) {
                    $sn++;
                    $spNo = $share['sp_no'];
                    $currentsavings = $this->getCurrentSavingsAmount($spNo);
                    echo "<tr>";
                    echo "<td>" . $sn . "</td>";
                    echo "<td>" . $spNo . "</td>";
                    echo "<td>" . htmlspecialchars(($share['fname'] ?? '') . ' ' . ($share['lname'] ?? '') . ' ' . ($share['oname'] ?? '')) . "</td>";
                    echo "<td>" .$currentsavings. "</td>";
                    echo "<td>" . htmlspecialchars($share['amount_paid']) . "</td>";
                    echo "<td>" . htmlspecialchars($share['conversion_date']) . "</td>";
                    echo "<td>
                            <a href='view_share_conversion_details.php?conversion_id=" . htmlspecialchars($share['conversion_id']) . "' class='btn btn-primary'>Action</a>
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
                        $('#transferTable').DataTable({
                            paging: true,
                            searching: true,
                            ordering: true,
                            responsive: true
                        });
                    });
                  </script>";
        
    }
    
    
    public function getSecretaryShareDecision($shares_id) {
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
                m.member_id, e.fname, e.lname, e.oname,
                sh.*
            FROM 
                fudscoops_shares sh
            JOIN 
                fudscoops_member m ON sh.member_id = m.member_id
            JOIN  
                employee e ON e.employee_id = m.employee_id
            WHERE 
                sh.shares_id = :share_id";
    
    $stmt = $con->prepare($sql);
    $stmt->bindParam(':share_id', $shares_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $shareDetails = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($shareDetails) {
        
        $applicant_info = $user->getStaffInformationByMemberId($shareDetails['member_id']);
        $date = $shareDetails['date'];
        
        // Prepare staff information
        
         $bankName = $user->getShareBankByMemberId($shareDetails['member_id']);
         $shareAmount = $shareDetails['amount_paid'];

            if($shareDetails['secretary_endorsement']=='1'){
               $button ='disabled';
               $share_amount =$shareDetails['amount_paid'];

            }else{
                $button='';
            }
        // Output the professional header and layout
        echo "<html>
                <head>
                    <title>Share Purchase</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        .container { max-width: 800px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
                        h3 { text-align: center; color: #007bff; }
                        .header { text-align: center; }
                        .header img { width: 150px; }
                        .staff-info { margin-top: 20px; }
                        .staff-info p { margin: 5px 0; }
                        .form-section { margin-top: 30px; }
                    </style>
                </head>
                <body>
                    
                    <div class=''>
                            <div class='header'>
                                <img src='../images/logo.png' alt='logo'>
                                  <h2>FUD STAFF COOPERATIVE PORTAL</h2>
                                  <h3>Share Purchase Form</h3>
                            </div>
                       
                        <hr>
                        <p><strong>Share Details:</strong></p>
                        <p><strong>Applicant Name:</strong> " . htmlspecialchars($shareDetails['fname'] . ' ' . $shareDetails['lname'] . ' ' . $shareDetails['oname']) . "</p>
                        <p><strong>Shares Amount in Figures :</strong> " . htmlspecialchars($shareDetails['amount_paid']) . "</p>
                        <p><strong>Shares Amount in Words :</strong> " . htmlspecialchars($shareDetails['shares_amount_word']) . "</p>
                        <p><strong>Bank of Payment :</strong> $bankName</p>
                        <p><strong>Teller No :</strong> " . htmlspecialchars($shareDetails['teller_no']) . "</p>
                        <p><strong>Share Date:</strong> " . htmlspecialchars($shareDetails['date']) . "</p>
                        <p><strong>Share Status:</strong> " . htmlspecialchars($shareDetails['share_status']) . "</p>
                        <p><strong><a href='slip.php?legacy=".$applicant_info['sp_no']."&s=".$m1."'>Download Applicant $month_name1 Payslip</a> &nbsp;
                        <a href='slip.php?legacy=".$applicant_info['sp_no']."&s=".$m2."'>Download Applicant $month_name2 Payslip</a> &nbsp; 
                        <a href='slip.php?legacy=".$applicant_info['sp_no']."&s=".$m3."'>Download Applicant $month_name3 Payslip</a>
                        </strong> </p>
                        
                        <div class='form-section'>
                           <form class='share-decision-form' method='POST'>
                                
                                
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
                                     <textarea class='form-control' row='2' name ='comment' required placeholder='Comment here'>".$shareDetails['secretary_comment']."</textarea>
                                     <input type='hidden' name='shares_id' id='shares_id' value=".$shareDetails['shares_id'] . ">
                                     <input type='hidden' name='member_id' id='member_id' class='form-control' value=".$shareDetails['member_id'].">
                                     <input type='hidden' name='decision' id='decision' class='form-control' value='secretary'>
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
                          <input name='repayment' id='repayment' required type='number' value ='".$shareDetails['loan_tenor']."' class='form-control-sm  form-control-md  form-control form-control-sm 'placeholder='Write the Number in month'>
                          <input name='shares_id' id='shares_id' type='hidden' value ='".$shareDetails['shares_id']."'>
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
        echo "<p>Share details not found.</p>";
    }
}

    public function getSecretaryShareTransferDecision($transfer_id) 
    {
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
                e1.fname AS received_fname, e1.lname AS received_lname, e1.oname AS received_oname,
                e2.fname AS transfer_fname, e2.lname AS transfer_lname, e2.oname AS transfer_oname,
                s.shares_id, s.amount_paid, s.shares_amount_word, s.unit_price,s.member_id, 
                t.*, t.amount_word AS Amount, t.shares_id AS trans_sharesid, t.secretary_comment
                AS sec_comment, t.secretary_endorsement AS sec_endorse
            FROM 
                fudscoops_shares s
            JOIN 
                fudscoops_shares_transfer t ON s.shares_id = t.shares_id
            LEFT JOIN 
                employee e1 ON e1.employee_id = t.received_employee_id
            LEFT JOIN 
                employee e2 ON e2.employee_id = t.transfer_employee_id
            WHERE 
                t.is_accepted = 1";
        
        $stmt = $con->prepare($sql);
        $stmt->bindParam(':transfer_id', $transfer_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $shareDetails = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($shareDetails) {
            
            $applicant_info = $user->getStaffInformationByMemberId($shareDetails['member_id']);
            $date = $shareDetails['transfer_date'];
            
            // Prepare staff information
            
             //$bankName = $user->getShareBankByMemberId($shareDetails['member_id']);
             $shareAmount = $shareDetails['amount_paid'];
    
                if($shareDetails['sec_endorse']=='1'){
                   $button ='disabled';
                   $shareAmount =$shareDetails['amount_paid'];
    
                }else{
                    $button='';
                }
                
              $acceptance_status = ($shareDetails['is_accepted'] == 0) ? "Shares Not Accepted" : "Shares Accepted";
              $worth = $shareDetails['worth'];

            // Output the professional header and layout
            echo "<html>
                    <head>
                        <title>Share Transfer</title>
                        <style>
                            body { font-family: Arial, sans-serif; margin: 20px; }
                            .container { max-width: 800px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
                            h3 { text-align: center; color: #007bff; }
                            .header { text-align: center; }
                            .header img { width: 150px; }
                            .staff-info { margin-top: 20px; }
                            .staff-info p { margin: 5px 0; }
                            .form-section { margin-top: 30px; }
                        </style>
                    </head>
                    <body>
                        
                        <div class=''>
                                <div class='header'>
                                    <img src='../images/logo.png' alt='logo'>
                                      <h2>FUD STAFF COOPERATIVE PORTAL</h2>
                                      <h3>Share Transfer Form</h3>
                                </div>
                           
                            <hr>
                            <p><strong>Shares Details:</strong></p>
                            <p><strong>Shares Receiver Name:</strong> " . htmlspecialchars($shareDetails['received_fname'] . ' ' . $shareDetails['received_lname'] . ' ' . $shareDetails['received_oname']) . "</p>
                            <p><strong>Shares Amount Received (in Figures) :</strong> " . htmlspecialchars($shareDetails['worth']) . "</p>
                            <p><strong>Shares Amount Received (in Words) :</strong> " . htmlspecialchars($shareDetails['Amount']) . "</p>
                            <p><strong>Shares Sender Name:</strong> " . htmlspecialchars($shareDetails['transfer_fname'] . ' ' . $shareDetails['transfer_lname'] . ' ' . $shareDetails['transfer_oname']) . "</p>
                            <p><strong>Current Shares Amount :</strong> " . htmlspecialchars($shareDetails['amount_paid']) . "</p>
                            <p><strong>Share Transfer Date:</strong> " . htmlspecialchars($shareDetails['transfer_date']) . "</p>
                            <p><strong>Receiver Acceptance Status:</strong> $acceptance_status</p>
                            <p><strong><a href='slip.php?legacy=".$applicant_info['sp_no']."&s=".$m1."'>Download Applicant $month_name1 Payslip</a> &nbsp;
                            <a href='slip.php?legacy=".$applicant_info['sp_no']."&s=".$m2."'>Download Applicant $month_name2 Payslip</a> &nbsp; 
                            <a href='slip.php?legacy=".$applicant_info['sp_no']."&s=".$m3."'>Download Applicant $month_name3 Payslip</a>
                            </strong> </p>
                            
                            <div class='form-section'>
                               <form class='share-transfer-decision-form' method='POST'>
                                    
                                    
                                    <hr>
                                    <p><strong>OFFICIAL SECTION</strong></p>
                                    <div>
                                        <p><strong><i>Value of Member’s Share Capital:</i></strong> $shareAmount</p>
                                    </div>
                                    <div>
                                        <p><strong><i>Amount of Share Transfered:</i></strong> $worth</p>
                                    </div>
                                    <div>
                                        <p><strong>Secretary’s Remarks:</strong></p>
                                    </div> 
                                    <div>
                                        <label> Remark:</label>
                                         <textarea class='form-control' row='2' name ='comment' required placeholder='Comment here'>".$shareDetails['sec_comment']."</textarea>
                                         <input type='hidden' name='transfer_id' id='transfer_id' value=".$shareDetails['transfer_id'] . ">
                                         <input type='hidden' name='trans_sharesid' id='trans_sharesid' class='form-control' value=".$shareDetails['trans_sharesid'].">
                                         <input type='hidden' name='member_id' id='member_id' class='form-control' value=".$shareDetails['member_id'].">
                                         <input type='hidden' name='decision' id='decision' class='form-control' value='secretary'>
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
                              <input name='repayment' id='repayment' required type='number' value ='".$shareDetails['loan_tenor']."' class='form-control-sm  form-control-md  form-control form-control-sm 'placeholder='Write the Number in month'>
                              <input name='shares_id' id='shares_id' type='hidden' value ='".$shareDetails['shares_id']."'>
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
            echo "<p>Share details not found.</p>";
        }
    }



    public function getChairmanShareDecision($shares_id) 
    {
        
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
                    m.member_id, e.fname, e.lname, e.oname,
                    sh.*
                FROM 
                    fudscoops_shares sh
                JOIN 
                    fudscoops_member m ON sh.member_id = m.member_id
                JOIN  
                    employee e ON e.employee_id = m.employee_id
                WHERE 
                    sh.shares_id = :shares_id";
        $status=1;
        $stmt = $con->prepare($sql);
        $stmt->bindParam(':shares_id', $shares_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $shareDetails = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($shareDetails) {
            $applicant_info=$user->getStaffInformationByMemberId($shareDetails['member_id']);
            $date = $shareDetails['date'];
            
            // Prepare staff information
             $bankName = $user->getShareBankByMemberId($shareDetails['member_id']);
             $shareAmount = $shareDetails['amount_paid'];
                if($shareDetails['chairman_approval']=='1'){
                   $button ='disabled';
                   $share_amount =$shareDetails['amount_paid'];
                }else{
                    $button='';
                }
            // Output the professional header and layout
            echo "<html>
                    <head>
                        <title>Share Purchase</title>
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
                                      <h3>Share Purchase Form</h3>
                                </div>
                            <hr>
                            
                            <hr>
                            <p><strong>Share Details:</strong></p>
                            <p><strong>Applicant Name:</strong> " . htmlspecialchars($shareDetails['fname'] . ' ' . $shareDetails['lname'] . ' ' . $shareDetails['oname']) . "</p>
                            <p><strong>Shares Amount in Figures :</strong> " . htmlspecialchars($shareDetails['amount_paid']) . "</p>
                            <p><strong>Shares Amount in Words :</strong> " . htmlspecialchars($shareDetails['shares_amount_word']) . "</p>
                            <p><strong>Bank of Payment :</strong> $bankName</p>
                            <p><strong>Teller No :</strong> " . htmlspecialchars($shareDetails['teller_no']) . "</p>
                            <p><strong>Share Date:</strong> " . htmlspecialchars($shareDetails['date']) . "</p>
                            <p><strong>Share Status:</strong> " . htmlspecialchars($shareDetails['share_status']) . "</p>
                            <p><strong><a href='slip.php?legacy=".$applicant_info['sp_no']."&s=".$m1."'>Download Applicant $month_name1 Payslip</a> &nbsp;
                            <a href='slip.php?legacy=".$applicant_info['sp_no']."&s=".$m2."'>Download Applicant $month_name2 Payslip</a> &nbsp; 
                            <a href='slip.php?legacy=".$applicant_info['sp_no']."&s=".$m3."'>Download Applicant $month_name3 Payslip</a>
                            </strong> </p>
                            
                            <div class='form-section'>
                               <form class='share-decision-form' method='POST'>
                                    
                                    <hr>
                                    <p><strong>OFFICIAL SECTION</strong></p>
                                    <div>
                                        <p><strong><i>Value of Member’s Share Capital:</i></strong> $shareAmount</p>
                                    </div>
                                    
                                    <div>
                                        <p><strong>Chairman's Remarks:</strong></p>
                                    </div> 
                                    <div>
                                        <label> Comment:</label>
                                         <textarea class='form-control' rows='2' name ='comment' required placeholder='Comment here'>".$shareDetails['chairman_comment']."</textarea>
                                         <input type='hidden' name='shares_id' id='shares_id' class='form-control' value=".$shareDetails['shares_id'].">
                                         <input type='hidden' name='member_id' id='member_id' class='form-control' value=".$shareDetails['member_id'].">
                                         <input type='hidden' name='decision'  class='form-control' value='chairman'>
                                         <input type='hidden' name='type' id='type' class='form-control' value='decision'>
                                        <h4 class='amount_msg text-primary'></h4>
                                    </div>
                                    
                                    <div class='msg'></div>
                                    <br>
                                    <button type='submit' class='btn btn-success' name='proceed' id='proceed' $button value='proceed' >Approve</button>
                                    <button type='submit' class='btn btn-danger' name='Decline' id='Decline' value='Decline'>Decline</button>
                                    <button type='submit' class='btn btn-success' name='unlock' id='unlock' value='Unlock'>Unlock Share</button>

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
            echo "<p>Share details not found.</p>";
        }
    }
    
    
    public function getChairmanShareTransferDecision($transfer_id) 
    {
        
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
                e1.fname AS received_fname, e1.lname AS received_lname, e1.oname AS received_oname,
                e2.fname AS transfer_fname, e2.lname AS transfer_lname, e2.oname AS transfer_oname,
                s.shares_id, s.amount_paid, s.shares_amount_word, s.unit_price,s.member_id, 
                t.*, t.amount_word AS Amount, t.shares_id AS trans_sharesid,
                t.chairman_comment AS chair_comment, t.chairman_approval AS chair_approval
            FROM 
                fudscoops_shares s
            JOIN 
                fudscoops_shares_transfer t ON s.shares_id = t.shares_id
            LEFT JOIN 
                employee e1 ON e1.employee_id = t.received_employee_id
            LEFT JOIN 
                employee e2 ON e2.employee_id = t.transfer_employee_id
            WHERE 
                t.is_accepted = 1";
        

        $status=1;
        $stmt = $con->prepare($sql);
        $stmt->bindParam(':transfer_id', $transfer_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $shareDetails = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($shareDetails) {
            $applicant_info = $user->getStaffInformationByMemberId($shareDetails['member_id']);
            $date = $shareDetails['transfer_date'];
            
            // Prepare staff information
            
             //$bankName = $user->getShareBankByMemberId($shareDetails['member_id']);
             $shareAmount = $shareDetails['amount_paid'];
    
                if($shareDetails['chair_approval']=='1'){
                   $button ='disabled';
                   $shareAmount =$shareDetails['amount_paid'];
    
                }else{
                    $button='';
                }
                
              $acceptance_status = ($shareDetails['is_accepted'] == 0) ? "Shares Not Accepted" : "Shares Accepted";
              $worth = $shareDetails['worth'];
            // Output the professional header and layout
            echo "<html>
                    <head>
                        <title>Share Purchase</title>
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
                                      <h3>Share Transfer Form</h3>
                                </div>
                            <hr>
                            
                            <hr>
                             <p><strong>Shares Details:</strong></p>
                            <p><strong>Shares Receiver Name:</strong> " . htmlspecialchars($shareDetails['received_fname'] . ' ' . $shareDetails['received_lname'] . ' ' . $shareDetails['received_oname']) . "</p>
                            <p><strong>Shares Amount Received (in Figures) :</strong> " . htmlspecialchars($shareDetails['worth']) . "</p>
                            <p><strong>Shares Amount Received (in Words) :</strong> " . htmlspecialchars($shareDetails['Amount']) . "</p>
                            <p><strong>Shares Sender Name:</strong> " . htmlspecialchars($shareDetails['transfer_fname'] . ' ' . $shareDetails['transfer_lname'] . ' ' . $shareDetails['transfer_oname']) . "</p>
                            <p><strong>Current Shares Amount :</strong> " . htmlspecialchars($shareDetails['amount_paid']) . "</p>
                            <p><strong>Share Transfer Date:</strong> " . htmlspecialchars($shareDetails['transfer_date']) . "</p>
                            <p><strong>Receiver Acceptance Status:</strong> $acceptance_status</p>
                            <p><strong><a href='slip.php?legacy=".$applicant_info['sp_no']."&s=".$m1."'>Download Applicant $month_name1 Payslip</a> &nbsp;
                            <a href='slip.php?legacy=".$applicant_info['sp_no']."&s=".$m2."'>Download Applicant $month_name2 Payslip</a> &nbsp; 
                            <a href='slip.php?legacy=".$applicant_info['sp_no']."&s=".$m3."'>Download Applicant $month_name3 Payslip</a>
                            </strong> </p>
                            
                            <div class='form-section'>
                               <form class='share-transfer-decision-form' method='POST'>
                                    
                                    <hr>
                                    <p><strong>OFFICIAL SECTION</strong></p>
                                    <div>
                                        <p><strong><i>Value of Member’s Share Capital:</i></strong> $shareAmount</p>
                                    </div>
                                    <div>
                                        <p><strong><i>Amount of Share Transfered:</i></strong> $worth</p>
                                    </div>
        
                                    <div>
                                        <p><strong>Chairman's Remarks:</strong></p>
                                    </div> 
                                    <div>
                                        <label> Comment:</label>
                                         <textarea class='form-control' rows='2' name ='comment' required placeholder='Comment here'>".$shareDetails['chair_comment']."</textarea>
                                         <input type='hidden' name='trans_sharesid' id='trans_sharesid' class='form-control' value=".$shareDetails['trans_sharesid'].">
                                         <input type='hidden' name='transfer_id' id='transfer_id' class='form-control' value=".$shareDetails['transfer_id'].">
                                         <input type='hidden' name='member_id' id='member_id' class='form-control' value=".$shareDetails['member_id'].">
                                         <input type='hidden' name='decision'  class='form-control' value='chairman'>
                                         <input type='hidden' name='type' id='type' class='form-control' value='decision'>
                                        <h4 class='amount_msg text-primary'></h4>
                                    </div>
                                    
                                    <div class='msg'></div>
                                    <br>
                                    <button type='submit' class='btn btn-success' name='proceed' id='proceed' $button value='proceed' >Approve</button>
                                    <button type='submit' class='btn btn-danger' name='Decline' id='Decline' value='Decline'>Decline</button>
                                    <button type='submit' class='btn btn-success' name='unlock' id='unlock' value='Unlock'>Unlock Share</button>

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
            echo "<p>Share Transfer details not found.</p>";
        }
    }
    
    
    public function getChairmanShareConversionDecision($conversion_id) 
    {
        
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
                e.fname, e.lname, e.oname, e.sp_no, m.member_id, c.amount_paid, c.shares_amount_word, c.conversion_id, c.conversion_date,
                c.chairman_approval AS chair_approval, c.chairman_comment AS chair_comment, c.chairman_approval_date
            FROM 
                fudscoops_member m
            JOIN 
                fudscoops_shares_conversion c ON m.member_id = c.member_id
            JOIN 
                employee e ON e.employee_id = m.employee_id
                
                WHERE c.conversion_id =:conversion_id AND c.conversion_status=0";
        

        $status=1;
        $stmt = $con->prepare($sql);
        $stmt->bindParam(':conversion_id', $conversion_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $shareDetails = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($shareDetails) {
            $applicant_info = $user->getStaffInformationByMemberId($shareDetails['member_id']);
            $date = $shareDetails['conversion_date'];
            $spNo = $shareDetails['sp_no'];
            $currentsavings = $this->getCurrentSavingsAmount($spNo);
            
            // Prepare staff information
            
             //$bankName = $user->getShareBankByMemberId($shareDetails['member_id']);
             $shareAmount = $shareDetails['amount_paid'];
    
                if($shareDetails['chair_approval']=='1'){
                   $button ='disabled';
                   $shareAmount =$shareDetails['amount_paid'];
    
                }else{
                    $button='';
                }
                
             // $acceptance_status = ($shareDetails['is_accepted'] == 0) ? "Shares Not Accepted" : "Shares Accepted";
             // $worth = $shareDetails['worth'];
            // Output the professional header and layout
            echo "<html>
                    <head>
                        <title>Share Conversion</title>
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
                                      <h3>Share Conversion Form</h3>
                                </div>
                            <hr>
                            
                            <hr>
                             <p><strong>Shares Details:</strong></p>
                            <p><strong>Name:</strong> " . htmlspecialchars($shareDetails['fname'] . ' ' . $shareDetails['lname'] . ' ' . $shareDetails['oname']) . "</p>
                            <p><strong>Total Savings :</strong> " . $currentsavings . "</p>
                            <p><strong>Shares Amount(in Figures) :</strong> " . $shareAmount . "</p>
                            <p><strong>Shares Amount (in Words) :</strong> " . htmlspecialchars($shareDetails['shares_amount_word']) . "</p>
                            <p><strong>Share Conversion Date:</strong> " . htmlspecialchars($shareDetails['conversion_date']) . "</p>
                            <p><strong><a href='slip.php?legacy=".$applicant_info['sp_no']."&s=".$m1."'>Download Applicant $month_name1 Payslip</a> &nbsp;
                            <a href='slip.php?legacy=".$applicant_info['sp_no']."&s=".$m2."'>Download Applicant $month_name2 Payslip</a> &nbsp; 
                            <a href='slip.php?legacy=".$applicant_info['sp_no']."&s=".$m3."'>Download Applicant $month_name3 Payslip</a>
                            </strong> </p>
                            
                            <div class='form-section'>
                               <form class='share-conversion-decision-form' method='POST'>
                                    
                                    <hr>
                                    <p><strong>OFFICIAL SECTION</strong></p>
                                    
                                    <div>
                                        <p><strong><i>Amount of Share to be Converted:</i></strong>$shareAmount</p>
                                    </div>
        
                                    <div>
                                        <p><strong>Chairman's Remarks:</strong></p>
                                    </div> 
                                    <div>
                                        <label> Comment:</label>
                                         <textarea class='form-control' rows='2' name ='comment' required placeholder='Comment here'>".$shareDetails['chair_comment']."</textarea>
                                         <input type='hidden' name='conversion_id' id='conversion_id' class='form-control' value=".$shareDetails['conversion_id'].">
                                         <input type='hidden' name='member_id' id='member_id' class='form-control' value=".$shareDetails['member_id'].">
                                         <input type='hidden' name='decision'  class='form-control' value='chairman'>
                                         <input type='hidden' name='type' id='type' class='form-control' value='decision'>
                                        <h4 class='amount_msg text-primary'></h4>
                                    </div>
                                    
                                    <div class='msg'></div>
                                    <br>
                                    <button type='submit' class='btn btn-success' name='proceed' id='proceed' $button value='proceed' >Approve</button>
                                    <button type='submit' class='btn btn-danger' name='Decline' id='Decline' value='Decline'>Decline</button>
                                    <button type='submit' class='btn btn-success' name='unlock' id='unlock' value='Unlock'>Unlock Share</button>

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
            echo "<p>Share Conversion details not found.</p>";
        }
    }
    
    
    
    public function getTreasurerShareDecision($shares_id) 
    {
        
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
                    m.member_id, e.fname, e.lname, e.oname,
                    sh.*
                FROM 
                    fudscoops_shares sh
                JOIN 
                    fudscoops_member m ON sh.member_id = m.member_id
                JOIN  
                    employee e ON e.employee_id = m.employee_id
                WHERE 
                    sh.shares_id = :share_id AND sh.chairman_approval=1";
        
        $stmt = $con->prepare($sql);
        $stmt->bindParam(':share_id', $shares_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $shareDetails = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($shareDetails) {
            
            $applicant_info = $user->getStaffInformationByMemberId($shareDetails['member_id']);
            $date = $shareDetails['date'];
            
            // Prepare staff information
            
             $bankName = $user->getShareBankByMemberId($shareDetails['member_id']);
             $shareAmount = $shareDetails['amount_paid'];
    
                if($shareDetails['share_status']=='1'){
                   $button ='disabled';
                   $share_amount =$shareDetails['amount_paid'];
    
                }else{
                    $button='';
                }
            // Output the professional header and layout
            echo "<html>
                    <head>
                        <title>Share Purchase</title>
                        <style>
                            body { font-family: Arial, sans-serif; margin: 20px; }
                            .container { max-width: 800px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
                            h3 { text-align: center; color: #007bff; }
                            .header { text-align: center; }
                            .header img { width: 150px; }
                            .staff-info { margin-top: 20px; }
                            .staff-info p { margin: 5px 0; }
                            .form-section { margin-top: 30px; }
                        </style>
                    </head>
                    <body>
                        
                        <div class=''>
                                <div class='header'>
                                    <img src='../images/logo.png' alt='logo'>
                                      <h2>FUD STAFF COOPERATIVE PORTAL</h2>
                                      <h3>Share Purchase Form</h3>
                                </div>
                           
                            <hr>
                            <p><strong>Share Details:</strong></p>
                            <p><strong>Applicant Name:</strong> " . htmlspecialchars($shareDetails['fname'] . ' ' . $shareDetails['lname'] . ' ' . $shareDetails['oname']) . "</p>
                            <p><strong>Shares Amount in Figures :</strong> " . htmlspecialchars($shareDetails['amount_paid']) . "</p>
                            <p><strong>Shares Amount in Words :</strong> " . htmlspecialchars($shareDetails['shares_amount_word']) . "</p>
                            <p><strong>Bank of Payment :</strong> $bankName</p>
                            <p><strong>Teller No :</strong> " . htmlspecialchars($shareDetails['teller_no']) . "</p>
                            <p><strong>Share Date:</strong> " . htmlspecialchars($shareDetails['date']) . "</p>
                            <p><strong>Share Status:</strong> " . htmlspecialchars($shareDetails['share_status']) . "</p>
                            <p><strong><a href='slip.php?legacy=".$applicant_info['sp_no']."&s=".$m1."'>Download Applicant $month_name1 Payslip</a> &nbsp;
                            <a href='slip.php?legacy=".$applicant_info['sp_no']."&s=".$m2."'>Download Applicant $month_name2 Payslip</a> &nbsp; 
                            <a href='slip.php?legacy=".$applicant_info['sp_no']."&s=".$m3."'>Download Applicant $month_name3 Payslip</a>
                            </strong> </p>
                            
                            <div class='form-section'>
                               <form class='share-decision-form' method='POST'>
                                    
                                    
                                    <hr>
                                    <p><strong>OFFICIAL SECTION</strong></p>
                                    <div>
                                        <p><strong><i>Value of Member’s Share Capital:</i></strong></p>
                                            <input type='text' name='share_amount' id='share_amount' disabled class='form-control' value=".htmlspecialchars($shareAmount, ENT_QUOTES, "UTF-8").">

                                    </div>
                                    
                                    <div>
                                        <p><strong>Chairman's Remarks:</strong></p>
                                        <textarea class='form-control' rows='2' name='comment' disabled>".$shareDetails['chairman_comment']."</textarea>
                                    </div><br/>
                                    <div>
                                         <input type='hidden' name='shares_id' id='shares_id' value=".$shareDetails['shares_id'] . ">
                                         <input type='hidden' name='member_id' id='member_id' class='form-control' value=".$shareDetails['member_id'].">
                                         <input type='hidden' name='decision' id='decision' class='form-control' value='treasurer'>
                                        <h4 class='amount_msg text-primary'></h4>
                                    </div>
                                    
                                    <div class='msg'></div>
                                    <br>
                                    <button type='submit' class='btn btn-success' name='proceed' id='proceed' $button value='proceed' >Authorize</button>
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
                              <input name='repayment' id='repayment' required type='number' value ='".$shareDetails['loan_tenor']."' class='form-control-sm  form-control-md  form-control form-control-sm 'placeholder='Write the Number in month'>
                              <input name='shares_id' id='shares_id' type='hidden' value ='".$shareDetails['shares_id']."'>
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
            echo "<p>Share details not found.</p>";
        }
    }

     public function getTreasurerShareTransferDecision($transfer_id) 
     {
        
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
                e1.fname AS received_fname, e1.lname AS received_lname, e1.oname AS received_oname,
                e2.fname AS transfer_fname, e2.lname AS transfer_lname, e2.oname AS transfer_oname,
                s.shares_id, s.amount_paid, s.shares_amount_word, s.unit_price,s.member_id, 
                t.*, t.amount_word AS Amount, t.shares_id AS trans_sharesid,
                t.chairman_comment AS chair_comment, t.chairman_approval AS chair_approval
            FROM 
                fudscoops_shares s
            JOIN 
                fudscoops_shares_transfer t ON s.shares_id = t.shares_id
            LEFT JOIN 
                employee e1 ON e1.employee_id = t.received_employee_id
            LEFT JOIN 
                employee e2 ON e2.employee_id = t.transfer_employee_id
            WHERE 
                t.is_accepted = 1 AND t.chairman_approval=1";
        

        $status=1;
        $stmt = $con->prepare($sql);
        $stmt->bindParam(':transfer_id', $transfer_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $shareDetails = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($shareDetails) {
            $applicant_info = $user->getStaffInformationByMemberId($shareDetails['member_id']);
            $date = $shareDetails['transfer_date'];
            
            // Prepare staff information
            
             //$bankName = $user->getShareBankByMemberId($shareDetails['member_id']);
             $shareAmount = $shareDetails['amount_paid'];
    
                if($shareDetails['share_transfer_status']=='1'){
                   $button ='disabled';
                   $shareAmount =$shareDetails['amount_paid'];
    
                }else{
                    $button='';
                }
                
              $acceptance_status = ($shareDetails['is_accepted'] == 0) ? "Shares Not Accepted" : "Shares Accepted";
              $worth = $shareDetails['worth'];
            // Output the professional header and layout
            echo "<html>
                    <head>
                        <title>Share Purchase</title>
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
                                      <h3>Share Transfer Form</h3>
                                </div>
                            <hr>
                            
                            <hr>
                             <p><strong>Shares Details:</strong></p>
                            <p><strong>Shares Receiver Name:</strong> " . htmlspecialchars($shareDetails['received_fname'] . ' ' . $shareDetails['received_lname'] . ' ' . $shareDetails['received_oname']) . "</p>
                            <p><strong>Shares Amount Received (in Figures) :</strong> " . htmlspecialchars($shareDetails['worth']) . "</p>
                            <p><strong>Shares Amount Received (in Words) :</strong> " . htmlspecialchars($shareDetails['Amount']) . "</p>
                            <p><strong>Shares Sender Name:</strong> " . htmlspecialchars($shareDetails['transfer_fname'] . ' ' . $shareDetails['transfer_lname'] . ' ' . $shareDetails['transfer_oname']) . "</p>
                            <p><strong>Current Shares Amount :</strong> " . htmlspecialchars($shareDetails['amount_paid']) . "</p>
                            <p><strong>Share Transfer Date:</strong> " . htmlspecialchars($shareDetails['transfer_date']) . "</p>
                            <p><strong>Receiver Acceptance Status:</strong> $acceptance_status</p>
                            <p><strong><a href='slip.php?legacy=".$applicant_info['sp_no']."&s=".$m1."'>Download Applicant $month_name1 Payslip</a> &nbsp;
                            <a href='slip.php?legacy=".$applicant_info['sp_no']."&s=".$m2."'>Download Applicant $month_name2 Payslip</a> &nbsp; 
                            <a href='slip.php?legacy=".$applicant_info['sp_no']."&s=".$m3."'>Download Applicant $month_name3 Payslip</a>
                            </strong> </p>
                            
                            <div class='form-section'>
                               <form class='share-transfer-decision-form' method='POST'>
                                    
                                    <hr>
                                    <p><strong>OFFICIAL SECTION</strong></p>
                                    <div>
                                        <p><strong><i>Value of Member’s Share Capital:</i></strong> $shareAmount</p>
                                    </div>
                                    <div>
                                        <p><strong><i>Amount of Share Transfered:</i></strong> $worth</p>
                                    </div>
                                    
                                    <div>
                                        <p><strong>Chairman's Remarks:</strong></p>
                                        <textarea class='form-control' rows='2' name='comment' disabled>".$shareDetails['chair_comment']."</textarea>
                                    </div><br/>
                                    
                                    <div>
                                         <input type='hidden' name='trans_sharesid' id='trans_sharesid' class='form-control' value=".$shareDetails['trans_sharesid'].">
                                         <input type='hidden' name='transfer_id' id='transfer_id' class='form-control' value=".$shareDetails['transfer_id'].">
                                         <input type='hidden' name='member_id' id='member_id' class='form-control' value=".$shareDetails['member_id'].">
                                         <input type='hidden' name='decision'  class='form-control' value='treasurer'>
                                         <h4 class='amount_msg text-primary'></h4>
                                    </div>
                                    
                                    <div class='msg'></div>
                                    <br>
                                    <button type='submit' class='btn btn-success' name='proceed' id='proceed' $button value='proceed' >Approve</button>
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
            echo "<p>Share Transfer details not found.</p>";
        }
    }
    
    public function getTreasurerShareConversionDecision($conversion_id) 
     {
        
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
                e.fname, e.lname, e.oname, e.sp_no,
                c.conversion_id, c.amount_paid, c.shares_amount_word, m.member_id, 
                c.conversion_status, c.conversion_date, c.update_at,
                c.chairman_comment AS chair_comment, c.chairman_approval AS chair_approval
            FROM 
                fudscoops_member m
            JOIN 
                fudscoops_shares_conversion c ON m.member_id = c.member_id
            JOIN 
                employee e ON e.employee_id = m.employee_id
            
            WHERE 
                c.conversion_status = 0 AND c.chairman_approval=1 AND c.conversion_id =:conversion_id";
        

        $status=1;
        $stmt = $con->prepare($sql);
        $stmt->bindParam(':conversion_id', $conversion_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $shareDetails = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($shareDetails) {
            $applicant_info = $user->getStaffInformationByMemberId($shareDetails['member_id']);
            $date = $shareDetails['conversion_date'];
            $spNo = $shareDetails['sp_no'];
            $currentsavings = $this->getCurrentSavingsAmount($spNo);
            
            // Prepare staff information
            
             //$bankName = $user->getShareBankByMemberId($shareDetails['member_id']);
             $shareAmount = $shareDetails['amount_paid'];
    
                if($shareDetails['conversion_status']=='1'){
                   $button ='disabled';
                   $shareAmount =$shareDetails['amount_paid'];
    
                }else{
                    $button='';
                }
                
            //  $acceptance_status = ($shareDetails['is_accepted'] == 0) ? "Shares Not Accepted" : "Shares Accepted";
            //  $worth = $shareDetails['worth'];
            // Output the professional header and layout
            echo "<html>
                    <head>
                        <title>Share Conversion</title>
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
                                      <h3>Share Conversion Form</h3>
                                </div>
                            <hr>
                            
                            <hr>
                             <p><strong>Shares Details:</strong></p>
                            <p><strong>Name:</strong> " . htmlspecialchars($shareDetails['fname'] . ' ' . $shareDetails['lname'] . ' ' . $shareDetails['oname']) . "</p>
                            <p><strong>Total Savings :</strong> " . $currentsavings . "</p>
                            <p><strong>Shares Amount(in Figures) :</strong> " . $shareAmount . "</p>
                            <p><strong>Shares Amount (in Words) :</strong> " . htmlspecialchars($shareDetails['shares_amount_word']) . "</p>
                            <p><strong>Share Conversion Date:</strong> " . htmlspecialchars($shareDetails['conversion_date']) . "</p>
                            <p><strong><a href='slip.php?legacy=".$applicant_info['sp_no']."&s=".$m1."'>Download Applicant $month_name1 Payslip</a> &nbsp;
                            <a href='slip.php?legacy=".$applicant_info['sp_no']."&s=".$m2."'>Download Applicant $month_name2 Payslip</a> &nbsp; 
                            <a href='slip.php?legacy=".$applicant_info['sp_no']."&s=".$m3."'>Download Applicant $month_name3 Payslip</a>
                            </strong> </p>
                            
                            <div class='form-section'>
                               <form class='share-conversion-decision-form' method='POST'>
                                    
                                    <hr>
                                    <p><strong>OFFICIAL SECTION</strong></p>
                                    
                                    <div>
                                        <p><strong><i>Amount of Shares to be converted:</i></strong> $shareAmount</p>
                                    </div>
                                    
                                    <div>
                                        <p><strong>Chairman's Remarks:</strong></p>
                                        <textarea class='form-control' rows='2' name='comment' disabled>".$shareDetails['chair_comment']."</textarea>
                                    </div><br/>
                                    
                                    <div>
                                         <input type='hidden' name='conversion_id' id='conversion_id' class='form-control' value=".$shareDetails['conversion_id'].">
                                         <input type='hidden' name='member_id' id='member_id' class='form-control' value=".$shareDetails['member_id'].">
                                         <input type='hidden' name='decision'  class='form-control' value='treasurer'>
                                         <h4 class='amount_msg text-primary'></h4>
                                    </div>
                                    
                                    <div class='msg'></div>
                                    <br>
                                    <button type='submit' class='btn btn-success' name='proceed' id='proceed' $button value='proceed' >Approve</button>
                                    <button type='submit' class='btn btn-danger' name='Decline' id='Decline' value='decline'>Decline</button>
                                    
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
            echo "<p>Share Transfer details not found.</p>";
        }
    }
    
    
    public function secretaryEndorsement($shares_id,$member_id,$action,$comment) 
    {
        $db = new DB();
        $con = $db->getConnection();
        $date =date('Y-m-d');
        if($action =='Proceed'){
            $decision =1;
        }else{
            $decision =0;
        }
        $sql = "UPDATE fudscoops_shares SET secretary_comment=:comment,secretary_endorsement =:endorse,secretary_endorse_date=:date WHERE shares_id=:shares_id AND member_id=:member_id";
        
        $stmt = $con->prepare($sql);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR);
        $stmt->bindParam(':endorse', $decision, PDO::PARAM_INT);
        $stmt->bindParam(':shares_id', $shares_id, PDO::PARAM_INT);
        $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
        $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt) {
             return 1;
        } else {
            return 0; 
        }
   }
   
   public function secretaryTransferEndorsement($shares_id,$transfer_id,$action,$comment) 
    {
        $db = new DB();
        $con = $db->getConnection();
        $date =date('Y-m-d');
        if($action =='Proceed'){
            $decision =1;
        }else{
            $decision =0;
        }
        $sql = "UPDATE fudscoops_shares_transfer SET secretary_comment=:comment,secretary_endorsement =:endorse,secretary_endorse_date=:date WHERE shares_id=:shares_id AND transfer_id=:transfer_id";
        
        $stmt = $con->prepare($sql);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR);
        $stmt->bindParam(':endorse', $decision, PDO::PARAM_INT);
        $stmt->bindParam(':shares_id', $shares_id, PDO::PARAM_INT);
        $stmt->bindParam(':transfer_id', $transfer_id, PDO::PARAM_INT);
        $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt) {
             return 1;
        } else {
            return 0; 
        }
   }
   public function chairmanApproval($shares_id,$member_id,$action,$comment) 
   {
        $db = new DB();
        $con = $db->getConnection();
        $date =date('Y-m-d');
       
                if($action =='Proceed'){
                    $decision =1;
                }else{
                    $decision =0;
                }
            $sql = "UPDATE fudscoops_shares SET chairman_comment=:comment,chairman_approval =:decision,chairman_approval_date=:date WHERE shares_id=:shares_id AND member_id=:member_id";
            
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':date', $date, PDO::PARAM_STR);
            $stmt->bindParam(':decision', $decision, PDO::PARAM_INT);
            $stmt->bindParam(':shares_id', $shares_id, PDO::PARAM_INT);
            $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
            $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
            $stmt->execute();
            if ($stmt) {
                 return 1;
            } else {
                return 0; 
            }

    }
    
    public function chairmanTransferApproval($shares_id,$transfer_id,$action,$comment) 
    {
        $db = new DB();
        $con = $db->getConnection();
        $date =date('Y-m-d');
       
                if($action =='Proceed'){
                    $decision =1;
                }else{
                    $decision =0;
                }
            $sql = "UPDATE fudscoops_shares_transfer SET chairman_comment=:comment, chairman_approval =:decision, chairman_approval_date=:date WHERE shares_id=:shares_id AND transfer_id=:transfer_id";
            
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':date', $date, PDO::PARAM_STR);
            $stmt->bindParam(':decision', $decision, PDO::PARAM_INT);
            $stmt->bindParam(':shares_id', $shares_id, PDO::PARAM_INT);
            $stmt->bindParam(':transfer_id', $transfer_id, PDO::PARAM_INT);
            $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
            $stmt->execute();
            if ($stmt) {
                 return 1;
            } else {
                return 0; 
            }

    }
    
    public function chairmanConversionApproval($conversion_id,$action,$comment) 
    {
        $db = new DB();
        $con = $db->getConnection();
        $date =date('Y-m-d');
       
                if($action =='Proceed'){
                    $decision =1;
                }else{
                    $decision =0;
                }
            $sql = "UPDATE fudscoops_shares_conversion SET chairman_comment=:comment, chairman_approval =:decision, chairman_approval_date=:date WHERE conversion_id=:conversion_id";
            
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':date', $date, PDO::PARAM_STR);
            $stmt->bindParam(':decision', $decision, PDO::PARAM_INT);
            $stmt->bindParam(':conversion_id', $conversion_id, PDO::PARAM_INT);
            $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
            $stmt->execute();
            if ($stmt) {
                 return 1;
            } else {
                return 0; 
            }

    }
    
    
    public function processShare($shares_id,$member_id,$action){
        $db = new DB();
        $con = $db->getConnection();
        $date=date('Y-m-d');
        $status =1;
       
            // Update loan status in the database
            $sql = "UPDATE fudscoops_shares SET share_status = :status , update_at=:date  WHERE shares_id = :shares_id";
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':status', $status, PDO::PARAM_INT);
            $stmt->bindParam(':shares_id', $shares_id, PDO::PARAM_INT);
            $stmt->bindParam(':date', $date, PDO::PARAM_STR);
            $stmt->execute();
            if($stmt){
                //insert only if the loan is approved
                    
                  return 1;
            } else {
                return 0; 
            }
    }
    
    public function processTransferShare($shares_id, $transfer_id, $member_id, $action) {
        $db = new DB();
        $con = $db->getConnection();
        $date = date('Y-m-d');
        $status = 1;
        $transfer_status = 0;
    
        // Update transfer status
        $sql = "UPDATE fudscoops_shares_transfer 
                SET share_transfer_status = :status, update_at = :date  
                WHERE shares_id = :shares_id AND transfer_id = :transfer_id";
        $stmt = $con->prepare($sql);
        $stmt->bindParam(':status', $status, PDO::PARAM_INT);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR);
        $stmt->bindParam(':shares_id', $shares_id, PDO::PARAM_INT);
        $stmt->bindParam(':transfer_id', $transfer_id, PDO::PARAM_INT);
        $stmt->execute();
    
        if ($stmt->rowCount() > 0) { // Ensure the first update was successful
            // Update shares table
        $sql1 = "UPDATE fudscoops_shares 
                 SET transfer_status = :status  
                 WHERE shares_id = :shares_id AND member_id = :member_id";
        $stmt1 = $con->prepare($sql1);
        $stmt1->bindParam(':status', $transfer_status, PDO::PARAM_INT);
        $stmt1->bindParam(':shares_id', $shares_id, PDO::PARAM_INT);
        $stmt1->bindParam(':member_id', $member_id, PDO::PARAM_INT);
        $stmt1->execute();
    
            if ($stmt1->rowCount() > 0) {
                // Get employee ID from the transfer table
                $sql2 = "SELECT received_employee_id FROM fudscoops_shares_transfer 
                         WHERE transfer_id = :transfer_id";
                $stmt2 = $con->prepare($sql2);
                $stmt2->bindParam(':transfer_id', $transfer_id, PDO::PARAM_INT);
                $stmt2->execute();
                $row = $stmt2->fetch(PDO::FETCH_ASSOC);
    
                if ($row) {
                    $employeeid = $row['received_employee_id'];
                    $this->TransferShare($member_id, $employeeid);
                }
    
                return 1; // Success
            }
        }
        return 0; // Failure
    }

    public function processConversionShare($conversion_id, $member_id, $action) 
    {
        $db = new DB();
        $con = $db->getConnection();
        $date = date('Y-m-d H:i:s');
        $status = 1; // approved
    
        // Only proceed if action is "approve"
        if (strcasecmp($action, 'approve') === 0) 
        {

            // Update conversion status to approved
            $sql = "UPDATE fudscoops_shares_conversion 
                    SET conversion_status = :status, update_at = :date  
                    WHERE member_id = :member_id AND conversion_id = :conversion_id AND conversion_status = 0";
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':status', $status, PDO::PARAM_INT);
            $stmt->bindParam(':date', $date, PDO::PARAM_STR);
            $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
            $stmt->bindParam(':conversion_id', $conversion_id, PDO::PARAM_INT);
            $stmt->execute();
    
            // Check if update actually affected a record
            if ($stmt->rowCount() > 0) {
                // Proceed with share conversion
                $this->convertShare($member_id, $conversion_id);
                return 1; // Success
            } else {
                return -1; // No record updated (maybe already approved)
            }
        } 
        else if (strcasecmp($action, 'decline') === 0)
        {
            // Handle rejection if needed
            $status = 0; // rejected
            $sql = "UPDATE fudscoops_shares_conversion 
                    SET conversion_status = :status, update_at = :date  
                    WHERE member_id = :member_id AND conversion_id = :conversion_id";
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':status', $status, PDO::PARAM_INT);
            $stmt->bindParam(':date', $date, PDO::PARAM_STR);
            $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
            $stmt->bindParam(':conversion_id', $conversion_id, PDO::PARAM_INT);
            $stmt->execute();
            return 2; // Rejected
        }
    
        return 0; // Invalid action
    }

    
    public function convertShare($memberid, $conversion_id) 
    {
        $db = new DB();
        $user = new User();
        $con = $db->getConnection();
        $chairman_approval = 1;
        $type_id = 2;
        $status = 1;
        $shareDate = (new DateTime())->format('Y-m-d H:i:s');
    
        // Step 1: Fetch conversion details (worth, etc.)
        $sql = "SELECT amount_paid AS worth, conversion_status 
                FROM fudscoops_shares_conversion 
                WHERE conversion_id = :conversionid";
        $stmt = $con->prepare($sql);
        $stmt->bindParam(':conversionid', $conversion_id, PDO::PARAM_INT);
        $stmt->execute();
        $conversion = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$conversion) {
            return 0; // no pending conversion found
        }
    
        $worth = $conversion['worth'];
        $share_amount_word_to_receiver = $user->numberToWords($worth);
    
        // Step 2: Check if the member already has a share record
        $sqlCheck = "SELECT amount_paid FROM fudscoops_shares WHERE member_id = :memberId AND share_status =1";
        $stmtCheck = $con->prepare($sqlCheck);
        $stmtCheck->bindParam(':memberId', $memberid, PDO::PARAM_INT);
        $stmtCheck->execute();
        $shareData = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    
        if ($shareData) {
            // Member already has shares → update
            $total_amount = $shareData['amount_paid'] + $worth;
            $total_share_amount_word = $user->numberToWords($total_amount);
    
            $sql1 = "UPDATE fudscoops_shares 
                     SET amount_paid = :amount_paid, shares_amount_word = :share_amount_word, unit_price = :unit_price 
                     WHERE member_id = :memberId AND share_status =1";
            $stmt1 = $con->prepare($sql1);
            $stmt1->bindParam(':amount_paid', $total_amount, PDO::PARAM_INT);
            $stmt1->bindParam(':share_amount_word', $total_share_amount_word, PDO::PARAM_STR);
            $stmt1->bindParam(':unit_price', $total_amount, PDO::PARAM_INT);
            $stmt1->bindParam(':memberId', $memberid, PDO::PARAM_INT);
            $stmt1->execute();
    
        } else {
            // Member does not have a share → insert new record
            $sql2 = "INSERT INTO fudscoops_shares 
                     (member_id, unit_price, amount_paid, shares_amount_word, date, share_status, chairman_approval, chairman_approval_date, update_at)
                     VALUES (:memberId, :unit, :amount_paid, :share_amount_word, :share_date, :status, :chairman_approval, :chairman_approval_date, :update_at)";
            $stmt2 = $con->prepare($sql2);
            $stmt2->bindParam(':memberId', $memberid, PDO::PARAM_INT);
            $stmt2->bindParam(':unit', $worth, PDO::PARAM_INT);
            $stmt2->bindParam(':amount_paid', $worth, PDO::PARAM_INT);
            $stmt2->bindParam(':share_amount_word', $share_amount_word_to_receiver, PDO::PARAM_STR);
            $stmt2->bindParam(':share_date', $shareDate, PDO::PARAM_STR);
            $stmt2->bindParam(':status', $status, PDO::PARAM_INT);
            $stmt2->bindParam(':chairman_approval', $chairman_approval, PDO::PARAM_INT);
            $stmt2->bindParam(':chairman_approval_date', $shareDate, PDO::PARAM_STR);
            $stmt2->bindParam(':update_at', $shareDate, PDO::PARAM_STR);
            $stmt2->execute();
        }
    
        // Step 3: Record withdrawal transaction
        $sql3 = "INSERT INTO fudscoops_withrawals 
                 (member_id, proposed_withrawal_amount, approved_withrawal_amount, withdrawal_type_id, proposed_date, approved_date, chairman_approval) 
                 VALUES (:memberId, :proposed_amount, :approved_amount, :type_id, :proposed_date, :date, :chairman_approval)";
        $stmt3 = $con->prepare($sql3);
        $stmt3->bindParam(':memberId', $memberid, PDO::PARAM_INT);
        $stmt3->bindParam(':proposed_amount', $worth, PDO::PARAM_INT);
        $stmt3->bindParam(':approved_amount', $worth, PDO::PARAM_INT);
        $stmt3->bindParam(':type_id', $type_id, PDO::PARAM_INT);
        $stmt3->bindParam(':proposed_date', $shareDate, PDO::PARAM_STR);
        $stmt3->bindParam(':date', $shareDate, PDO::PARAM_STR);
        $stmt3->bindParam(':chairman_approval', $chairman_approval, PDO::PARAM_INT);
        $stmt3->execute();
    
        return 1; // Success
    }


        
    public function notifyMemberShareApproval($memberId) 
    
    {
        
        $db = new DB();
        $con = $db->getConnection();
    
        // Get the date two months ago
        $twoMonthsAgo = date('Y-m-d', strtotime('-2 months'));
    
        // Query to check the member's share status and approval date
        $q = "SELECT share_status, update_at FROM fudscoops_shares 
              WHERE member_id = :member_id";
        $stm = $con->prepare($q);
        $stm->bindParam(':member_id', $memberId, PDO::PARAM_INT);
        $stm->execute();
        
        // Fetch all rows
        $shares = $stm->fetchAll(PDO::FETCH_ASSOC);
    
        if ($shares) {
            foreach ($shares as $share) {
                // Check if approval happened within the last 2 months
                if ($share['share_status'] == 1 && $share['update_at'] >= $twoMonthsAgo) {
                    return "<button class='btn btn-success'>Your Share Purchase Application has been Approved!</button>";
                }
            }
            return "<button class='btn btn-danger'>Your Share Purchase Application is still Pending.</button>";
        }
    
        return ''; // No message for members who don't exist in the table
    }
    
    public function notifyMemberShareTransferApproval($memberId) 
    
    {
        
        $db = new DB();
        $con = $db->getConnection();
    
        // Get the date two months ago
        $twoMonthsAgo = date('Y-m-d', strtotime('-2 months'));
    
        // Query to check the member's share status and approval date
        $q = "SELECT share_transfer_status, update_at FROM fudscoops_shares_transfer t 
              JOIN employee e ON t.received_employee_id = e.employee_id 
              JOIN fudscoops_member m ON e.employee_id = m.employee_id  
              WHERE m.member_id = :member_id";
        $stm = $con->prepare($q);
        $stm->bindParam(':member_id', $memberId, PDO::PARAM_INT);
        $stm->execute();
        
        // Fetch all rows
        $shares = $stm->fetchAll(PDO::FETCH_ASSOC);
    
        if ($shares) {
            foreach ($shares as $share) {
                // Check if approval happened within the last 2 months
                if ($share['share_transfer_status'] == 1 && $share['update_at'] >= $twoMonthsAgo) {
                    return "<button class='btn btn-success'>The Share Transfered to you has been Approved!</button>";
                }
            }
            return "<button class='btn btn-danger'>The Share Transfer Application is still Pending.</button>";
        }
    
        return ''; // No message for members who don't exist in the table
    }

        
    public function saveLoanApplication($amountApplied, $loanRepayment, $loanRepaymentSpecify, $memberId, $loanTypeId) {
        $db = new DB();
        $con = $db->getConnection();
    
            // Get the current date
            $loanDate = date('Y-m-d'); 
    
            $loanStatus = 'pending';
    
         $loanTenor = !empty($loanRepaymentSpecify) ? $loanRepaymentSpecify : $loanRepayment;
    
        $sqlCheck = "SELECT * FROM fudscoops_loan WHERE member_id = :member_id";
        $stmtCheck = $con->prepare($sqlCheck);
        $stmtCheck->bindParam(':member_id', $memberId, PDO::PARAM_INT);
        $stmtCheck->execute();
    
        if ($stmtCheck->rowCount() > 0) {
            $sqlUpdate = "UPDATE fudscoops_loan 
              SET loan_amount = :loan_amount,loan_date = :loan_date, loan_status = :loan_status, 
              loan_tenor = :loan_tenor  WHERE member_id = :member_id";
            $stmtUpdate = $con->prepare($sqlUpdate);
            $stmtUpdate->bindParam(':loan_amount', $amountApplied, PDO::PARAM_STR);
            $stmtUpdate->bindParam(':loan_date', $loanDate, PDO::PARAM_STR);
            $stmtUpdate->bindParam(':loan_status', $loanStatus, PDO::PARAM_STR);
            $stmtUpdate->bindParam(':loan_tenor', $loanTenor, PDO::PARAM_STR);
            //$stmtUpdate->bindParam(':loan_type_id', $loanTypeId, PDO::PARAM_INT);
            $stmtUpdate->bindParam(':member_id', $memberId, PDO::PARAM_INT);
    
            if ($stmtUpdate->execute()) {
                return 1; 
            } else {
                return -1;
            }
        } else {
            $sqlInsert = "INSERT INTO fudscoops_loan (loan_amount, loan_date, loan_status, loan_tenor, member_id) 
                          VALUES (:loan_amount, :loan_date, :loan_status, :loan_tenor, :member_id)";
            $stmtInsert = $con->prepare($sqlInsert);
            $stmtInsert->bindParam(':loan_amount', $amountApplied, PDO::PARAM_STR);
            $stmtInsert->bindParam(':loan_date', $loanDate, PDO::PARAM_STR);
            $stmtInsert->bindParam(':loan_status', $loanStatus, PDO::PARAM_STR);
            $stmtInsert->bindParam(':loan_tenor', $loanTenor, PDO::PARAM_STR);
            //$stmtInsert->bindParam(':loan_type_id', $loanTypeId, PDO::PARAM_INT);
            $stmtInsert->bindParam(':member_id', $memberId, PDO::PARAM_INT);
    
            if ($stmtInsert->execute()) {
                return 1; 
            } else {
                return -1;
            }
        }
    }

    public function RequestCommodity($member_id, $commodity_id, $quantity_applied, $unit_price, $total_amount)
    {
        $db = new DB(); // create new DB instance
        $con = $db->getConnection(); // get MySQLi connection
    
        // Check if the requested quantity is available
        $sql = "SELECT quantity_available FROM commodities WHERE id = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("i", $commodity_id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows === 0) {
            return 0; // Commodity not found
        }
    
        $row = $result->fetch_assoc();
        $available_qty = $row['quantity_available'];
    
        if ($quantity_applied > $available_qty) {
            return 0; // Requested quantity exceeds available stock
        }
    
        // Insert the commodity request
        $sql2 = "INSERT INTO commodity_requests(member_id, commodity_id, quantity_requested, total_price, status, requested_at) VALUES (?, ?, ?, ?, 'pending', NOW())";
        $stmt2 = $con->prepare($sql2);
        $stmt2->bind_param("iiid", $member_id, $commodity_id, $quantity_applied, $total_amount);
    
        if ($stmt2->execute()) {
            return 1;
        } else {
            return 0;
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

    public function resetPasswordByUsername($username)
{
    $db  = new DB();
    $con = $db->getConnection();

    try {
        // Step 1: Check if username exists
        $sql = "SELECT username FROM user WHERE username = :username LIMIT 1";
        $stmt = $con->prepare($sql);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return -1; // Username not found
        }

        // Step 2: Generate MD5 hash of username
        $newPassword = md5($username);

        // Step 3: Update password
        $updateSql = "UPDATE user SET password = :newpassword WHERE username = :username";
        $updateStmt = $con->prepare($updateSql);
        $updateStmt->bindParam(':newpassword', $newPassword, PDO::PARAM_STR);
        $updateStmt->bindParam(':username', $username, PDO::PARAM_STR);

        if ($updateStmt->execute() && $updateStmt->rowCount() > 0) {
            return 1; // Success
        } else {
            return -1; // Update failed
        }

    } catch (Exception $e) {
        return -1; // Error occurred
    }
}





}