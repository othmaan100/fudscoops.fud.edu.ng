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
            
            
    
    // ===================== Share ledger (from 2026-10-08) =====================
    // fudscoops_shares is a ledger (db/2026-10-08_loans_shares.sql): one row per purchase, transfer in,
    // transfer out (negative amount) or conversion from savings. share_status: 0 pending, 1 approved,
    // -1 declined. A member's holding is the sum of approved rows (getShareHolding). Purchases go through
    // Secretary (endorse) -> Chairman (approve) -> Treasurer (confirm); transfers through receiver (accept)
    // -> Secretary -> Chairman -> Treasurer; conversions through Chairman -> Treasurer. Declines are -1.
    // These methods return 1 on success, otherwise a message explaining why the request was refused.
    // Helpers (shareUnitPrice, getShareHolding, shareAmountError, ...) are at the end of the class.

    // A new purchase record every time; it counts once the Treasurer confirms it.

    public function PurchaseShare($employeeid,$share_amount,$bank_id,$tellerno,$amount_paid,$share_amount_word){
        $user = new User();
        $con = (new DB())->getConnection();
        $memberid = $user->getMemberId($employeeid);
        if (!$memberid || !self::isFullMember($memberid)) {
            return 'Only approved members can buy shares.';
        }

        $amount = self::money($share_amount);
        if ($error = self::shareAmountError($amount)) {
            return $error;
        }
        if (self::money($amount_paid) !== $amount) {
            return 'The amount paid must be the same as the share amount.';
        }
        $tellerno = trim((string) $tellerno);
        if ($tellerno === '' || !ctype_digit($tellerno) || (int) $tellerno <= 0) {
            return 'Enter the teller number of the payment.';
        }
        if (empty($bank_id) || (int) $bank_id <= 0) {
            return 'Select the bank of payment.';
        }

        $stmt = $con->prepare("SELECT 1 FROM fudscoops_shares WHERE member_id = :member_id AND entry_type = 'purchase'
                               AND share_status = 0 AND shares_is_deleted = 0");
        $stmt->bindParam(':member_id', $memberid, PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->fetchColumn()) {
            return 'You already have a share purchase awaiting approval.';
        }

        $stmt = $con->prepare("SELECT 1 FROM fudscoops_shares WHERE teller_no = :teller AND bank_id = :bank
                               AND entry_type = 'purchase' AND share_status <> -1 AND shares_is_deleted = 0");
        $stmt->bindValue(':teller', (int) $tellerno, PDO::PARAM_INT);
        $stmt->bindValue(':bank', (int) $bank_id, PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->fetchColumn()) {
            return 'This teller number has already been used for a share purchase.';
        }

        $words = $user->numberToWords($amount);
        $sql = "INSERT INTO fudscoops_shares (member_id, entry_type, unit_price, bank_id, teller_no, amount_paid, shares_amount_word, date,
                                              share_status, secretary_comment, chairman_comment, secretary_endorse_date, chairman_approval_date, update_at)
                VALUES (:memberid, 'purchase', :amount, :bank_id, :tellerno, :amount_paid, :words, NOW(), 0, '', '', NOW(), NOW(), NOW())";
        $stmt = $con->prepare($sql);
        $stmt->bindParam(':memberid', $memberid, PDO::PARAM_INT);
        $stmt->bindValue(':amount', (string) $amount, PDO::PARAM_STR);
        $stmt->bindValue(':bank_id', (int) $bank_id, PDO::PARAM_INT);
        $stmt->bindValue(':tellerno', (int) $tellerno, PDO::PARAM_INT);
        $stmt->bindValue(':amount_paid', $amount, PDO::PARAM_STR);
        $stmt->bindParam(':words', $words, PDO::PARAM_STR);
        return $stmt->execute() ? 1 : 'Could not save the share purchase. Contact system admin.';
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
        // Approved complete withdrawals (paid out, plus loans and commodity settled from savings)
        $stm_complete = $con->prepare("SELECT (SELECT COALESCE(SUM(c.approved_amount + CASE WHEN c.settled_at IS NOT NULL THEN c.amount_owed ELSE 0 END), 0) FROM fudscoops_complete_withrawal c JOIN fudscoops_member cm ON cm.employee_id = c.employee_id WHERE cm.member_id = :member_id AND c.chairman_approval = 1 AND COALESCE(c.complete_withrawal_is_deleted, 0) = 0)");
        $stm_complete->execute([':member_id' => $member_id]);
        $total_withdrawals += (float) $stm_complete->fetchColumn();
        
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
        $user = new User();
        $employeeId = $user->getEmployeeId($_SESSION['username']);
        $memberId = $user->getMemberId($employeeId);
        $available = self::getShareHolding($memberId) - self::pendingTransfersOut($employeeId);
        $worth = self::money($worth);

        if ($worth === null || $worth > $available) {
            return -1; // worth exceeds available share amount
        }
        if ($available - $worth < self::minBalanceAfterTransfer()) {
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
        $user = new User();
        $con = (new DB())->getConnection();

        $senderEmployeeId = $user->getEmployeeId($_SESSION['username']);
        $senderMemberId = $user->getMemberId($senderEmployeeId);
        if (!$senderMemberId || !self::isFullMember($senderMemberId)) {
            return 'Only approved members can transfer shares.';
        }
        $receiverMemberId = $employeeid ? $user->getMemberId($employeeid) : 0;
        if (!$receiverMemberId || !self::isFullMember($receiverMemberId)) {
            return 'The receiver must be an approved member of the cooperative.';
        }
        if ((int) $employeeid === (int) $senderEmployeeId) {
            return 'You cannot transfer shares to yourself.';
        }

        $worth = self::money($worth);
        if ($error = self::shareAmountError($worth)) {
            return $error;
        }
        if (self::pendingTransfersOut($senderEmployeeId) > 0) {
            return 'You already have a share transfer awaiting approval.';
        }
        $holding = self::getShareHolding($senderMemberId);
        $minBalance = self::minBalanceAfterTransfer();
        if ($worth > $holding) {
            return 'You can transfer at most ₦' . number_format($holding) . '.';
        }
        if ($holding - $worth < $minBalance) {
            return 'You must keep at least ₦' . number_format($minBalance) . ' of shares after the transfer.';
        }

        // Link to the sender's latest approved share entry (used by the approval lists)
        $stmt = $con->prepare("SELECT shares_id FROM fudscoops_shares WHERE member_id = :member_id AND share_status = 1
                               AND shares_is_deleted = 0 ORDER BY shares_id DESC LIMIT 1");
        $stmt->bindParam(':member_id', $senderMemberId, PDO::PARAM_INT);
        $stmt->execute();
        $shareId = (int) $stmt->fetchColumn();

        $words = $user->numberToWords($worth);
        $stmt = $con->prepare("INSERT INTO fudscoops_shares_transfer (shares_id, worth, amount_word, transfer_employee_id, received_employee_id, transfer_date)
                               VALUES (:shareid, :worth, :amount_word, :sender, :receiver, NOW())");
        $stmt->bindParam(':shareid', $shareId, PDO::PARAM_INT);
        $stmt->bindParam(':worth', $worth, PDO::PARAM_INT);
        $stmt->bindParam(':amount_word', $words, PDO::PARAM_STR);
        $stmt->bindParam(':sender', $senderEmployeeId, PDO::PARAM_INT);
        $stmt->bindParam(':receiver', $employeeid, PDO::PARAM_INT);
        return $stmt->execute() ? 1 : 'Could not save the share transfer. Contact system admin.';
    }
  
    
    public function TransferShare($transferId, $con = null) {
        $con = $con ?: (new DB())->getConnection();
        $user = new User();
        $ownTransaction = !$con->inTransaction();
        try {
            if ($ownTransaction) {
                $con->beginTransaction();
            }
            $stmt = $con->prepare("SELECT * FROM fudscoops_shares_transfer WHERE transfer_id = :id AND share_transfer_status = 0
                                   AND share_transfer_is_deleted = 0 FOR UPDATE");
            $stmt->bindParam(':id', $transferId, PDO::PARAM_INT);
            $stmt->execute();
            $transfer = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$transfer) {
                throw new RuntimeException('This share transfer is no longer awaiting approval.');
            }
            $senderMemberId = $user->getMemberId($transfer['transfer_employee_id']);
            $receiverMemberId = $user->getMemberId($transfer['received_employee_id']);
            $worth = (int) $transfer['worth'];
            if (!$senderMemberId || !$receiverMemberId) {
                throw new RuntimeException('The sender or receiver is not a member.');
            }
            if (self::getShareHolding($senderMemberId) < $worth) {
                throw new RuntimeException("The sender no longer holds enough shares for this transfer.");
            }

            $insert = $con->prepare("INSERT INTO fudscoops_shares (member_id, entry_type, reference_id, unit_price, bank_id, teller_no, amount_paid,
                                        shares_amount_word, date, share_status, secretary_comment, secretary_endorsement, chairman_comment,
                                        chairman_approval, secretary_endorse_date, chairman_approval_date, update_at)
                                     VALUES (:member_id, :type, :ref, :amount_text, 0, 0, :amount, :words, NOW(), 1, '', 1, '', 1, NOW(), NOW(), NOW())");
            $words = $user->numberToWords($worth);
            foreach ([[$senderMemberId, 'transfer_out', -$worth], [$receiverMemberId, 'transfer_in', $worth]] as $entry) {
                $insert->execute([':member_id' => $entry[0], ':type' => $entry[1], ':ref' => $transferId,
                                  ':amount_text' => (string) $entry[2], ':amount' => $entry[2], ':words' => $words]);
            }
            $update = $con->prepare("UPDATE fudscoops_shares_transfer SET share_transfer_status = 1, update_at = NOW() WHERE transfer_id = :id");
            $update->execute([':id' => $transferId]);

            if ($ownTransaction) {
                $con->commit();
            }
            return 1;
        } catch (Exception $e) {
            if ($ownTransaction && $con->inTransaction()) {
                $con->rollBack();
            }
            if (!($e instanceof RuntimeException)) {
                error_log('TransferShare: ' . $e->getMessage());
                return 'Could not complete the share transfer. Contact system admin.';
            }
            return $e->getMessage();
        }
    }

    
    public function getTotalShareTransfer($received_employee_id) {
        $db = new DB();
        $con = $db->getConnection();
    
        // Transfers offered to this member and still waiting for them to accept
        $sql = "SELECT COUNT(transfer_id) as total FROM fudscoops_shares_transfer
                WHERE received_employee_id=:id AND is_accepted = 0 AND share_transfer_status = 0 AND share_transfer_is_deleted = 0";
        
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
                        employee e ON e.employee_id = t.transfer_employee_id
                    JOIN
                        fudscoops_member m ON m.employee_id = e.employee_id
                    WHERE
                        t.received_employee_id = :received_employee_id AND t.share_transfer_is_deleted = 0";
        
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
                                                const button = $(`.accept-transfer[data-transfer_id='\${transferId}']`);
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
        $user = new User();
        $receiverEmployeeId = $user->getEmployeeId(isset($_SESSION['username']) ? $_SESSION['username'] : '');
        $con = (new DB())->getConnection();
        $stmt = $con->prepare("UPDATE fudscoops_shares_transfer SET is_accepted = 1
                               WHERE transfer_id = :transfer_id AND received_employee_id = :receiver
                                 AND share_transfer_status = 0 AND share_transfer_is_deleted = 0");
        $stmt->bindParam(':transfer_id', $transferId, PDO::PARAM_INT);
        $stmt->bindParam(':receiver', $receiverEmployeeId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0 ? 1 : -1;
    }
    
    public function conversionPendingShare($employeeid, $worth, $share_amount_word) {
        $user = new User();
        $con = (new DB())->getConnection();
        $memberid = $user->getMemberId($employeeid);
        if (!$memberid || !self::isFullMember($memberid)) {
            return 'Only approved members can convert savings to shares.';
        }
        $worth = self::money($worth);
        if ($error = self::shareAmountError($worth)) {
            return $error;
        }
        require_once('SavingsG1.php');
        $balance = self::savingsBalance($memberid);
        $keep = SavingsG1::getMinMonthlySavings();
        if ($worth > $balance - $keep) {
            return 'You can convert at most ₦' . number_format(max(0, $balance - $keep)) . ' (your savings balance is ₦' . number_format($balance)
                 . ' and at least ₦' . number_format($keep) . ' must remain).';
        }

        $words = $user->numberToWords($worth);
        // Change the amount of a conversion the Chairman has not decided yet, otherwise start a new one
        $stmt = $con->prepare("SELECT conversion_id, chairman_approval FROM fudscoops_shares_conversion
                               WHERE member_id = :memberid AND conversion_status = 0 AND share_conversion_is_deleted = 0");
        $stmt->bindParam(':memberid', $memberid, PDO::PARAM_INT);
        $stmt->execute();
        $pending = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($pending && $pending['chairman_approval'] != 0) {
            return 'You already have a conversion awaiting the Treasurer.';
        }
        if ($pending) {
            $stmt = $con->prepare("UPDATE fudscoops_shares_conversion SET amount_paid = :worth, shares_amount_word = :words, conversion_date = NOW()
                                   WHERE conversion_id = :id");
            $ok = $stmt->execute([':worth' => $worth, ':words' => $words, ':id' => $pending['conversion_id']]);
        } else {
            $stmt = $con->prepare("INSERT INTO fudscoops_shares_conversion (member_id, amount_paid, shares_amount_word, conversion_date)
                                   VALUES (:memberid, :worth, :words, NOW())");
            $ok = $stmt->execute([':memberid' => $memberid, ':worth' => $worth, ':words' => $words]);
        }
        return $ok ? 1 : 'Could not save the conversion. Contact system admin.';
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
                $stm->bindParam(':price', $price, PDO::PARAM_STR);
                 if ($stm->execute()) {
                    return 'Share Unit Updated Successfully!'; // Update successful
                 } else {
                    return 'Share Unit Failed to Update!';// Update failed
                 }
                
            } else{
                $q = "INSERT INTO fudscoops_share_unit (share,price) VALUES(:share,:price)";

                $stm = $con->prepare($q);
                $stm->bindParam(':share',$share,PDO::PARAM_STR);
                $stm->bindParam(':price', $price, PDO::PARAM_STR);
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
                t.share_transfer_is_deleted = 0 AND t.is_accepted = 1 AND t.share_transfer_status=0";
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
            $con = (new DB())->getConnection();
            $sql = "SELECT s.shares_id, s.entry_type, s.amount_paid, s.teller_no, s.date
                    FROM fudscoops_shares s
                    JOIN fudscoops_member m ON s.member_id = m.member_id
                    WHERE m.employee_id = :empId AND s.share_status = 1 AND s.shares_is_deleted = 0
                    ORDER BY s.date, s.shares_id";
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':empId', $employeeid, PDO::PARAM_INT);
            $stmt->execute();
            $shareInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $types = ['purchase' => 'Purchase', 'transfer_in' => 'Received (transfer)', 'transfer_out' => 'Given (transfer)', 'conversion' => 'Converted from savings'];
            $unitPrice = self::shareUnitPrice();

            if ($shareInfo) {
                $sn = 0;
                $total = 0;
                echo "<h3>FUDCOOPS Shares Details</h3>";
                echo "<table id='example' class='display table table-striped table-bordered' width='100%'>";
                echo "<thead>
                        <tr>
                            <th>S/N</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Amount (₦)</th>
                            <th>Teller No</th>
                            <th>Balance (₦)</th>
                        </tr>
                      </thead>";
                echo "<tbody>";
                foreach ($shareInfo as $share) {
                    $sn++;
                    $total += $share['amount_paid'];
                    echo "<tr>";
                    echo "<td>" . $sn . "</td>";
                    echo "<td>" . htmlspecialchars(date('d M Y', strtotime($share['date']))) . "</td>";
                    echo "<td>" . htmlspecialchars(isset($types[$share['entry_type']]) ? $types[$share['entry_type']] : $share['entry_type']) . "</td>";
                    echo "<td>" . number_format($share['amount_paid'], 2) . "</td>";
                    echo "<td>" . ($share['teller_no'] ? htmlspecialchars($share['teller_no']) : '') . "</td>";
                    echo "<td>" . number_format($total, 2) . "</td>";
                    echo "</tr>";
                }
                echo "</tbody>";
                echo "<tfoot><tr><th colspan='5'>Total shares" . ($unitPrice > 0 ? ' (' . number_format(floor($total / $unitPrice)) . ' units at ₦' . number_format($unitPrice) . ')' : '') . "</th><th>" . number_format($total, 2) . "</th></tr></tfoot>";
                echo "</table>";
            } else {
                echo "<p>No Shares found.</p>";
            }
    }
    
    public function getShareHolderViewTable() {
            $con = (new DB())->getConnection();
            $sql = "SELECT m.member_id, e.sp_no, e.fname, e.lname, e.oname, SUM(s.amount_paid) AS total, MAX(s.date) AS last_date
                    FROM fudscoops_shares s
                    JOIN fudscoops_member m ON s.member_id = m.member_id
                    JOIN employee e ON e.employee_id = m.employee_id
                    WHERE s.share_status = 1 AND s.shares_is_deleted = 0
                    GROUP BY m.member_id, e.sp_no, e.fname, e.lname, e.oname
                    HAVING total > 0
                    ORDER BY e.fname, e.lname";
            $shareInfo = $con->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            $unitPrice = self::shareUnitPrice();

            if ($shareInfo) {
                $sn = 0;
                echo "<h3>FUDCOOPS Shares Details</h3>";
                echo "<table id='example' class='display table table-striped table-bordered' width='100%'>";
                echo "<thead>
                        <tr>
                            <th>S/N</th>
                            <th>Staff No</th>
                            <th>Share Holder Name</th>
                            <th>Share Amount (₦)</th>
                            <th>Units</th>
                            <th>Last Movement</th>
                        </tr>
                      </thead>";
                echo "<tbody>";
                foreach ($shareInfo as $share) {
                    $sn++;
                    echo "<tr>";
                    echo "<td>" . $sn . "</td>";
                    echo "<td>" . htmlspecialchars($share['sp_no']) . "</td>";
                    echo "<td>" . htmlspecialchars($share['fname'] . ' ' . $share['lname'] . ' ' . $share['oname']) . "</td>";
                    echo "<td>" . number_format($share['total'], 2) . "</td>";
                    echo "<td>" . ($unitPrice > 0 ? number_format(floor($share['total'] / $unitPrice)) : '') . "</td>";
                    echo "<td>" . htmlspecialchars(date('d M Y', strtotime($share['last_date']))) . "</td>";
                    echo "</tr>";
                }
                echo "</tbody>";
                echo "</table>";
            } else {
                echo "<p>No Shares found.</p>";
            }
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
                t.share_transfer_is_deleted = 0 AND t.is_accepted = 1 AND t.share_transfer_status=0";
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
                c.share_conversion_is_deleted = 0 AND c.conversion_status=0";
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
                t.share_transfer_is_deleted = 0 AND t.is_accepted = 1 AND t.chairman_approval=1 AND t.share_transfer_status=0";
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
                c.share_conversion_is_deleted = 0 AND c.chairman_approval=1 AND c.conversion_status=0";
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
        $decision = self::decisionValue($action);
        if ($decision === null) {
            return 'Please click Proceed or Decline.';
        }
        $con = (new DB())->getConnection();
        $sql = "UPDATE fudscoops_shares
                SET secretary_comment = :comment, secretary_endorsement = :endorse, secretary_endorse_date = NOW(),
                    share_status = :status, update_at = NOW()
                WHERE shares_id = :shares_id AND member_id = :member_id AND entry_type = 'purchase'
                  AND share_status = 0 AND secretary_endorsement = 0";
        $stmt = $con->prepare($sql);
        $stmt->bindValue(':comment', (string) $comment, PDO::PARAM_STR);
        $stmt->bindValue(':endorse', $decision, PDO::PARAM_INT);
        $stmt->bindValue(':status', $decision === -1 ? -1 : 0, PDO::PARAM_INT);
        $stmt->bindParam(':shares_id', $shares_id, PDO::PARAM_INT);
        $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0 ? 1 : 'This share purchase is no longer awaiting the Secretary.';
   }
   
    public function secretaryTransferEndorsement($shares_id,$transfer_id,$action,$comment)
    {
        $decision = self::decisionValue($action);
        if ($decision === null) {
            return 'Please click Proceed or Decline.';
        }
        $con = (new DB())->getConnection();
        $sql = "UPDATE fudscoops_shares_transfer
                SET secretary_comment = :comment, secretary_endorsement = :endorse, secretary_endorse_date = NOW(),
                    share_transfer_status = :status, update_at = NOW()
                WHERE transfer_id = :transfer_id AND share_transfer_status = 0 AND secretary_endorsement = 0";
        $stmt = $con->prepare($sql);
        $stmt->bindValue(':comment', (string) $comment, PDO::PARAM_STR);
        $stmt->bindValue(':endorse', $decision, PDO::PARAM_INT);
        $stmt->bindValue(':status', $decision === -1 ? -1 : 0, PDO::PARAM_INT);
        $stmt->bindParam(':transfer_id', $transfer_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0 ? 1 : 'This share transfer is no longer awaiting the Secretary.';
   }
   public function chairmanApproval($shares_id,$member_id,$action,$comment)
   {
        $decision = self::decisionValue($action);
        if ($decision === null) {
            return 'Please click Proceed or Decline.';
        }
        $con = (new DB())->getConnection();
        $sql = "UPDATE fudscoops_shares
                SET chairman_comment = :comment, chairman_approval = :decision, chairman_approval_date = NOW(),
                    share_status = :status, update_at = NOW()
                WHERE shares_id = :shares_id AND member_id = :member_id AND entry_type = 'purchase'
                  AND share_status = 0 AND secretary_endorsement = 1 AND chairman_approval = 0";
        $stmt = $con->prepare($sql);
        $stmt->bindValue(':comment', (string) $comment, PDO::PARAM_STR);
        $stmt->bindValue(':decision', $decision, PDO::PARAM_INT);
        $stmt->bindValue(':status', $decision === -1 ? -1 : 0, PDO::PARAM_INT);
        $stmt->bindParam(':shares_id', $shares_id, PDO::PARAM_INT);
        $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return 1;
        }
        return $this->sharePurchaseStage($shares_id) === 'secretary'
            ? 'The Secretary has not endorsed this share purchase yet.'
            : 'This share purchase is no longer awaiting the Chairman.';
    }
    
    public function chairmanTransferApproval($shares_id,$transfer_id,$action,$comment)
    {
        $decision = self::decisionValue($action);
        if ($decision === null) {
            return 'Please click Proceed or Decline.';
        }
        $con = (new DB())->getConnection();
        $sql = "UPDATE fudscoops_shares_transfer
                SET chairman_comment = :comment, chairman_approval = :decision, chairman_approval_date = NOW(),
                    share_transfer_status = :status, update_at = NOW()
                WHERE transfer_id = :transfer_id AND share_transfer_status = 0 AND secretary_endorsement = 1 AND chairman_approval = 0";
        $stmt = $con->prepare($sql);
        $stmt->bindValue(':comment', (string) $comment, PDO::PARAM_STR);
        $stmt->bindValue(':decision', $decision, PDO::PARAM_INT);
        $stmt->bindValue(':status', $decision === -1 ? -1 : 0, PDO::PARAM_INT);
        $stmt->bindParam(':transfer_id', $transfer_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0 ? 1 : 'This share transfer is not awaiting the Chairman (the Secretary must endorse it first).';
    }
    
    public function chairmanConversionApproval($conversion_id,$action,$comment)
    {
        $decision = self::decisionValue($action);
        if ($decision === null) {
            return 'Please click Proceed or Decline.';
        }
        $con = (new DB())->getConnection();
        $sql = "UPDATE fudscoops_shares_conversion
                SET chairman_comment = :comment, chairman_approval = :decision, chairman_approval_date = NOW(),
                    conversion_status = :status, update_at = NOW()
                WHERE conversion_id = :conversion_id AND conversion_status = 0 AND chairman_approval = 0";
        $stmt = $con->prepare($sql);
        $stmt->bindValue(':comment', (string) $comment, PDO::PARAM_STR);
        $stmt->bindValue(':decision', $decision, PDO::PARAM_INT);
        $stmt->bindValue(':status', $decision === -1 ? -1 : 0, PDO::PARAM_INT);
        $stmt->bindParam(':conversion_id', $conversion_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0 ? 1 : 'This conversion is no longer awaiting the Chairman.';
    }
    
    
    public function processShare($shares_id,$member_id,$action){
        $decision = self::decisionValue($action);
        if ($decision === null) {
            return 'Please click Approve or Decline.';
        }
        $con = (new DB())->getConnection();
        $sql = "UPDATE fudscoops_shares SET share_status = :status, update_at = NOW()
                WHERE shares_id = :shares_id AND entry_type = 'purchase' AND share_status = 0 AND chairman_approval = 1";
        $stmt = $con->prepare($sql);
        $stmt->bindValue(':status', $decision, PDO::PARAM_INT);
        $stmt->bindParam(':shares_id', $shares_id, PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return 1;
        }
        return $this->sharePurchaseStage($shares_id) === 'chairman' || $this->sharePurchaseStage($shares_id) === 'secretary'
            ? 'The Chairman has not approved this share purchase yet.'
            : 'This share purchase has already been decided.';
    }
    
    public function processTransferShare($shares_id, $transfer_id, $member_id, $action) {
        $decision = self::decisionValue($action);
        if ($decision === null) {
            return 'Please click Approve or Decline.';
        }
        $con = (new DB())->getConnection();
        $stmt = $con->prepare("SELECT is_accepted, chairman_approval, share_transfer_status FROM fudscoops_shares_transfer WHERE transfer_id = :id");
        $stmt->bindParam(':id', $transfer_id, PDO::PARAM_INT);
        $stmt->execute();
        $transfer = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$transfer || $transfer['share_transfer_status'] != 0) {
            return 'This share transfer has already been decided.';
        }
        if ($transfer['chairman_approval'] != 1) {
            return 'The Chairman has not approved this share transfer yet.';
        }
        if ($decision === -1) {
            $stmt = $con->prepare("UPDATE fudscoops_shares_transfer SET share_transfer_status = -1, update_at = NOW() WHERE transfer_id = :id AND share_transfer_status = 0");
            $stmt->bindParam(':id', $transfer_id, PDO::PARAM_INT);
            $stmt->execute();
            return 1;
        }
        if ($transfer['is_accepted'] != 1) {
            return 'The receiver has not accepted this share transfer yet.';
        }
        return $this->TransferShare($transfer_id, $con);
    }

    public function processConversionShare($conversion_id, $member_id, $action)
    {
        $decision = self::decisionValue($action);
        if ($decision === null) {
            return 'Please click Approve or Decline.';
        }
        $con = (new DB())->getConnection();
        if ($decision === -1) {
            $stmt = $con->prepare("UPDATE fudscoops_shares_conversion SET conversion_status = -1, update_at = NOW()
                                   WHERE conversion_id = :id AND member_id = :member_id AND conversion_status = 0");
            $stmt->execute([':id' => $conversion_id, ':member_id' => $member_id]);
            return $stmt->rowCount() > 0 ? 1 : 'This conversion has already been decided.';
        }
        return $this->convertShare($member_id, $conversion_id);
    }

    
    public function convertShare($memberid, $conversion_id)
    {
        $user = new User();
        $con = (new DB())->getConnection();
        try {
            $con->beginTransaction();
            $stmt = $con->prepare("SELECT * FROM fudscoops_shares_conversion WHERE conversion_id = :id AND member_id = :member_id
                                   AND conversion_status = 0 AND share_conversion_is_deleted = 0 FOR UPDATE");
            $stmt->execute([':id' => $conversion_id, ':member_id' => $memberid]);
            $conversion = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$conversion) {
                throw new RuntimeException('This conversion has already been decided.');
            }
            if ($conversion['chairman_approval'] != 1) {
                throw new RuntimeException('The Chairman has not approved this conversion yet.');
            }
            $worth = (int) $conversion['amount_paid'];
            if ($worth <= 0 || self::savingsBalance($memberid) < $worth) {
                throw new RuntimeException("The member's savings balance is too low for this conversion.");
            }

            $words = $user->numberToWords($worth);
            $stmt = $con->prepare("INSERT INTO fudscoops_shares (member_id, entry_type, reference_id, unit_price, bank_id, teller_no, amount_paid,
                                       shares_amount_word, date, share_status, secretary_comment, secretary_endorsement, chairman_comment,
                                       chairman_approval, secretary_endorse_date, chairman_approval_date, update_at)
                                   VALUES (:member_id, 'conversion', :ref, :amount_text, 0, 0, :amount, :words, NOW(), 1, '', 1, '', 1, NOW(), NOW(), NOW())");
            $stmt->execute([':member_id' => $memberid, ':ref' => $conversion_id, ':amount_text' => (string) $worth, ':amount' => $worth, ':words' => $words]);

            $stmt = $con->prepare("INSERT INTO fudscoops_withrawals (member_id, proposed_withrawal_amount, approved_withrawal_amount, withdrawal_type_id,
                                       proposed_date, approved_date, chairman_approval)
                                   VALUES (:member_id, :amount, :amount2, 2, NOW(), NOW(), 1)");
            $stmt->execute([':member_id' => $memberid, ':amount' => $worth, ':amount2' => $worth]);

            $stmt = $con->prepare("UPDATE fudscoops_shares_conversion SET conversion_status = 1, update_at = NOW() WHERE conversion_id = :id");
            $stmt->execute([':id' => $conversion_id]);

            $con->commit();
            return 1;
        } catch (Exception $e) {
            if ($con->inTransaction()) {
                $con->rollBack();
            }
            if (!($e instanceof RuntimeException)) {
                error_log('convertShare: ' . $e->getMessage());
                return 'Could not complete the conversion. Contact system admin.';
            }
            return $e->getMessage();
        }
    }


        
    public function notifyMemberShareApproval($memberId)
    {
        $con = (new DB())->getConnection();
        $stmt = $con->prepare("SELECT share_status, update_at FROM fudscoops_shares
                               WHERE member_id = :member_id AND entry_type = 'purchase' AND shares_is_deleted = 0
                               ORDER BY shares_id DESC LIMIT 1");
        $stmt->bindParam(':member_id', $memberId, PDO::PARAM_INT);
        $stmt->execute();
        $latest = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$latest) {
            return '';
        }
        if ($latest['share_status'] == 0) {
            return "<button class='btn btn-danger'>Your Share Purchase Application is still Pending.</button>";
        }
        if ($latest['update_at'] >= date('Y-m-d', strtotime('-2 months'))) {
            return $latest['share_status'] == 1
                ? "<button class='btn btn-success'>Your Share Purchase Application has been Approved!</button>"
                : "<button class='btn btn-warning'>Your Share Purchase Application was Declined.</button>";
        }
        return '';
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

    // Secretary/password.php: same rules as User::resetPassword (members and applicants only).
    // Returns 1 reset, -1 not found or error, -2 officer account.
    public function resetPasswordByUsername($username)
    {
        $result = (new User())->resetPassword($username);
        return $result === 1 ? 1 : ($result === -2 ? -2 : -1);
    }






    public static function shareUnitPrice()
    {
        $con = (new DB())->getConnection();
        $price = $con->query("SELECT price FROM fudscoops_share_unit ORDER BY shares_unit_id DESC LIMIT 1")->fetchColumn();
        return $price ? (int) $price : 0;
    }

    private static function pendingTransfersOut($employeeId)
    {
        $con = (new DB())->getConnection();
        $stmt = $con->prepare("SELECT COALESCE(SUM(worth), 0) FROM fudscoops_shares_transfer
                               WHERE transfer_employee_id = :employee_id AND share_transfer_status = 0 AND share_transfer_is_deleted = 0");
        $stmt->bindParam(':employee_id', $employeeId, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    private static function isFullMember($memberId)
    {
        $con = (new DB())->getConnection();
        $stmt = $con->prepare("SELECT 1 FROM fudscoops_member WHERE member_id = :member_id
                               AND treasurer_approval = 1 AND chairman_approval = 1 AND is_deleted = 0");
        $stmt->bindParam(':member_id', $memberId, PDO::PARAM_INT);
        $stmt->execute();
        return (bool) $stmt->fetchColumn();
    }

    private static function money($value)
    {
        $clean = preg_replace('/[^0-9.]/', '', (string) $value);
        return ($clean === '' || !is_numeric($clean)) ? null : (int) round((float) $clean);
    }

    public static function shareAmountError($amount)
    {
        $price = self::shareUnitPrice();
        if ($amount === null || $amount <= 0) {
            return 'Enter a valid amount.';
        }
        if ($price > 0 && ($amount < $price || $amount % $price !== 0)) {
            return 'Shares are in whole units of ₦' . number_format($price) . ' (e.g. ₦' . number_format($price) . ', ₦' . number_format($price * 2) . ').';
        }
        return null;
    }

    private static function decisionValue($action)
    {
        $action = strtolower(trim((string) $action));
        if (in_array($action, ['proceed', 'approve', 'approved'], true)) {
            return 1;
        }
        if (in_array($action, ['decline', 'disapprove', 'deapproved', 'declined'], true)) {
            return -1;
        }
        return null;
    }

    private static function savingsBalance($memberId)
    {
        $con = (new DB())->getConnection();
        $stmt = $con->prepare("SELECT
                (SELECT COALESCE(SUM(savings_amount), 0) FROM fudscoops_savings WHERE member_idmember = :m1)
              - (SELECT COALESCE(SUM(approved_withrawal_amount), 0) FROM fudscoops_withrawals WHERE member_id = :m2 AND chairman_approval = 1)
              - (SELECT COALESCE(SUM(c.approved_amount + CASE WHEN c.settled_at IS NOT NULL THEN c.amount_owed ELSE 0 END), 0) FROM fudscoops_complete_withrawal c JOIN fudscoops_member cm ON cm.employee_id = c.employee_id WHERE cm.member_id = :m3 AND c.chairman_approval = 1 AND COALESCE(c.complete_withrawal_is_deleted, 0) = 0)");
        $stmt->execute([':m1' => $memberId, ':m2' => $memberId, ':m3' => $memberId]);
        return (float) $stmt->fetchColumn();
    }

    private static function minBalanceAfterTransfer()
    {
        require_once('Settings.php');
        return (int) Settings::get('share_min_balance_after_transfer', 20000);
    }

    private function sharePurchaseStage($shares_id)
    {
        $con = (new DB())->getConnection();
        $stmt = $con->prepare("SELECT share_status, secretary_endorsement, chairman_approval FROM fudscoops_shares WHERE shares_id = :id");
        $stmt->bindParam(':id', $shares_id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || $row['share_status'] != 0) {
            return 'decided';
        }
        if ($row['secretary_endorsement'] != 1) {
            return 'secretary';
        }
        return $row['chairman_approval'] != 1 ? 'chairman' : 'treasurer';
    }

    public static function getShareHolding($memberId)
    {
        $con = (new DB())->getConnection();
        $stmt = $con->prepare("SELECT COALESCE(SUM(amount_paid), 0) FROM fudscoops_shares
                               WHERE member_id = :member_id AND share_status = 1 AND shares_is_deleted = 0");
        $stmt->bindParam(':member_id', $memberId, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }
}