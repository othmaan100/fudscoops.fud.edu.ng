<?php
if(!isset($_SESSION)) 
    { 
        session_start(); 
    } 
    
require_once('User.php');


class MemberG1{
    private $db;
    
    public function addMember() {
        
    }
    
    public function editMember() {
        
    }
    
     public function getProposedMembers() {
            $member = new MemberG1();
    
            $db = new DB();
            $user = new User();
            $con = $db->getConnection();
        $sql = "SELECT * FROM fudscoops_member INNER JOIN employee on fudscoops_member.employee_id=employee.employee_id where fudscoops_member.is_active='0'";
        $stmt = $con->prepare($sql);
        $stmt->execute();
    
        $ProposedMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        return $ProposedMembers;
           
        
    }
    
    public function getRequestCompleteWithdrawalMembers() {
            $member = new MemberG1();
    
            $db = new DB();
            $user = new User();
            $con = $db->getConnection();
        $sql = "SELECT * 
                FROM fudscoops_member 
                INNER JOIN  fudscoops_complete_withrawal  
                ON fudscoops_member.employee_id=fudscoops_complete_withrawal.employee_id
                INNER JOIN employee 
                        ON fudscoops_member.employee_id = employee.employee_id 
                WHERE fudscoops_complete_withrawal.chairman_approval='0' and approved_amount='0'";
        $stmt = $con->prepare($sql); 
        $stmt->execute();
    
        $completeWithdrawalMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        return $completeWithdrawalMembers;
           
        
    }
    
     public function getChairmanRequestCompleteWithdrawalMembers() {
            $member = new MemberG1();
    
            $db = new DB();
            $user = new User();
            $con = $db->getConnection();
        $sql = "SELECT * 
                FROM fudscoops_member 
                INNER JOIN  fudscoops_complete_withrawal  
                ON fudscoops_member.employee_id=fudscoops_complete_withrawal.employee_id
                INNER JOIN employee 
                        ON fudscoops_member.employee_id = employee.employee_id 
                WHERE fudscoops_complete_withrawal.chairman_approval='0' and approved_amount!='0'";
        $stmt = $con->prepare($sql); 
        $stmt->execute();
    
        $completeWithdrawalMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        return $completeWithdrawalMembers;
           
        
    }
    
    public function getRequesUpdateSavings() {
            $member = new MemberG1();
    
            $db = new DB();
            $user = new User();
            $con = $db->getConnection();
        $sql = "SELECT * 
                    FROM fudscoops_member 
                    INNER JOIN fudscoops_update_savings 
                        ON fudscoops_member.employee_id = fudscoops_update_savings.employee_id 
                    INNER JOIN employee 
                        ON fudscoops_member.employee_id = employee.employee_id 
                    WHERE fudscoops_update_savings.is_secretary_approved = '0'";

        $stmt = $con->prepare($sql);
        $stmt->execute();
    
        $completeWithdrawalMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        return $completeWithdrawalMembers;
           
        
    }
    
public function loanWithdrawalEndorsment() {
    $db = new DB();
    $con = $db->getConnection();

    $sql = "SELECT
                fudscoops_withrawals.withrawals_id,
                fudscoops_withrawals.withrawals_amount,
                fudscoops_withrawals.member_id,
                employee.sp_no,
                employee.fname,
                employee.lname,
                employee.oname,
                employee.cadre
            FROM
                fudscoops_withrawals
            INNER JOIN
                fudscoops_member ON fudscoops_withrawals.member_id = fudscoops_member.member_id
            INNER JOIN
                employee ON fudscoops_member.employee_id = employee.employee_id
            WHERE
                fudscoops_withrawals.status = '0'"; 

        $stmt = $con->prepare($sql);
        $stmt->execute();
        $completeWithdrawalMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    

    return $completeWithdrawalMembers;
}


public function getchairmanWithdrawalEndorsment() {
    $db = new DB();
    $con = $db->getConnection();

   
    $sql = "SELECT
                fw.withrawals_id,
                fw.proposed_withrawal_amount,
                fw.approved_withrawal_amount,
                fw.member_id,
                fm.employee_id,
                e.sp_no,
                e.fname,
                e.lname,
                e.oname,
                e.cadre,
                (
                    SELECT SUM(fs.savings_amount)
                    FROM fudscoops_savings fs
                    WHERE fs.member_idmember = fw.member_id
                ) AS total_savings
            FROM
                fudscoops_withrawals fw
            INNER JOIN
                fudscoops_member fm ON fw.member_id = fm.member_id
            INNER JOIN
                employee e ON fm.employee_id = e.employee_id
            WHERE
                fw.status = '1'
                AND fw.chairman_approval = '0'
                AND fw.approved_withrawal_amount !=0;
            ";

        $stmt = $con->prepare($sql);
        $stmt->execute();
        $completeWithdrawalMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    

    return $completeWithdrawalMembers;
}


public function SectaryWithdrawalEndorsment() {
    $db = new DB();
    $con = $db->getConnection();

   
    $sql = "SELECT
                fw.withrawals_id,
                fw.proposed_withrawal_amount,
                fw.member_id,
                fw.bank_id,
                fw.acount_number,
                fw.account_name,
                fm.employee_id,
                e.sp_no,
                e.fname,
                e.lname,
                e.oname,
                e.cadre,
                (
                    SELECT SUM(fs.savings_amount)
                    FROM fudscoops_savings fs
                    WHERE fs.member_idmember = fw.member_id
                ) AS total_savings
            FROM
                fudscoops_withrawals fw
            INNER JOIN
                fudscoops_member fm ON fw.member_id = fm.member_id
            INNER JOIN
                employee e ON fm.employee_id = e.employee_id
            WHERE
                fw.status = '0'
                AND fw.chairman_approval = '0'
                AND fw.approved_withrawal_amount =0;
            ";

        $stmt = $con->prepare($sql);
        $stmt->execute();
        $completeWithdrawalMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    

    return $completeWithdrawalMembers;
}




// public function getWithdrawalEndorsment() {
//     $db = new DB();
//     $con = $db->getConnection();

//     // SQL to fetch pending withdrawals
//     $sql = "SELECT
//                 fudscoops_withrawals.withrawals_id,
//                 fudscoops_withrawals.proposed_withrawal_amount,
//                 fudscoops_withrawals.member_id,
//                 employee.sp_no,
//                 employee.fname,
//                 employee.lname,
//                 employee.oname,
//                 employee.cadre
//             FROM
//                 fudscoops_withrawals
//             INNER JOIN
//                 fudscoops_member ON fudscoops_withrawals.member_id = fudscoops_member.member_id
//             INNER JOIN
//                 employee ON fudscoops_member.employee_id = employee.employee_id
//             WHERE
//                 fudscoops_withrawals.status = '0'
//             AND
//                 fudscoops_withrawals.secretary_approval = '0'";

//     $stmt = $con->prepare($sql);
//     $stmt->execute();
//     $withdrawalInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);

//     if ($withdrawalInfo) {
//         $sn = 0;
//         echo "<h3>Pending Withdrawal Requests</h3>";
//         echo "<table id='withdrawalTable' class='display table table-striped table-bordered' width='100%'>";
//         echo "<thead>
//                 <tr>
//                     <th>S/N</th>
//                     <th>SP No</th>
//                     <th>Full Name</th>
//                     <th>Cadre</th>
//                     <th>Proposed Amount</th>
//                     <th>Actions</th>
//                 </tr>
//               </thead>";
//         echo "<tbody>";

//         foreach ($withdrawalInfo as $request) {
//             $sn++;
//             $fullName = htmlspecialchars($request['fname'] . ' ' . $request['lname'] . ' ' . $request['oname']);
//             echo "<tr>";
//             echo "<td>" . $sn . "</td>";
//             echo "<td>" . htmlspecialchars($request['sp_no']) . "</td>";
//             echo "<td>" . $fullName . "</td>";
//             echo "<td>" . htmlspecialchars($request['cadre']) . "</td>";
//             echo "<td>" . htmlspecialchars($request['proposed_withrawal_amount']) . "</td>";
//             echo "<td>
//                     <a href='view_withdrawal_details.php?withrawals_id=" . htmlspecialchars($request['withrawals_id']) . "' class='btn btn-primary'>View Details</a>
//                   </td>";
//             echo "</tr>";
//         }

//         echo "</tbody>";
//         echo "</table>";
//     } else {
//         echo "<p>No withdrawal requests at the moment.</p>";
//     }

//     // DataTables Initialization
//     echo "<script>
//             $(document).ready(function() {
//                 $('#withdrawalTable').DataTable({
//                     paging: true,
//                     searching: true,
//                     ordering: true,
//                     responsive: true
//                 });
//             });
//           </script>";
// }

public function getWithdrawalEndorsment() {
    $db = new DB();
    $con = $db->getConnection();
    
    // SQL to fetch pending withdrawals
    $sql = "SELECT
                fudscoops_withrawals.withrawals_id,
                fudscoops_withrawals.proposed_withrawal_amount,
                fudscoops_withrawals.member_id,
                employee.sp_no,
                employee.fname,
                employee.lname,
                employee.oname,
                employee.cadre
            FROM
                fudscoops_withrawals
            INNER JOIN
                fudscoops_member ON fudscoops_withrawals.member_id = fudscoops_member.member_id
            INNER JOIN
                employee ON fudscoops_member.employee_id = employee.employee_id
            WHERE
                fudscoops_withrawals.status = '0'
            AND
                fudscoops_withrawals.secretary_approval = '0'
            ORDER BY fudscoops_withrawals.withrawals_id DESC";
    
    $stmt = $con->prepare($sql);
    $stmt->execute();
    $withdrawalInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($withdrawalInfo && count($withdrawalInfo) > 0) {
        $sn = 0;
        ?>
        <div class="table-responsive">
            <table id="withdrawalTable" class="table table-striped table-bordered table-hover" style="width:100%">
                <thead class="thead-dark">
                    <tr>
                        <th>S/N</th>
                        <th>SP No</th>
                        <th>Full Name</th>
                        <th>Cadre</th> 
                        <th>Proposed Amount (₦)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="searchEndorsment">
                    <?php foreach ($withdrawalInfo as $request): 
                        $sn++;
                        $fullName = htmlspecialchars(trim($request['fname'] . ' ' . $request['lname'] . ' ' . $request['oname']));
                        $amount = number_format($request['proposed_withrawal_amount'], 2);
                    ?>
                    <tr>
                        <td><?php echo $sn; ?></td>
                        <td><?php echo htmlspecialchars($request['sp_no']); ?></td>
                        <td><?php echo $fullName; ?></td>
                        <td><?php echo htmlspecialchars($request['cadre']); ?></td>
                        <td class="text-right"><?php echo $amount; ?></td>
                        <td class="text-center">
                            <a href="view_withdrawal_details.php?withrawals_id=<?php echo htmlspecialchars($request['withrawals_id']); ?>" 
                               class="btn btn-sm btn-primary" 
                               data-toggle="tooltip" 
                               title="View withdrawal details">
                                <i class="fa fa-eye"></i> View Details
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="thead-dark">
                    <tr>
                        <th>S/N</th>
                        <th>SP No</th>
                        <th>Full Name</th>
                        <th>Cadre</th>
                        <th>Proposed Amount (₦)</th>
                        <th>Actions</th>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <script>
            $(document).ready(function() {
                // Initialize DataTable
                var table = $('#withdrawalTable').DataTable({
                    "paging": true,
                    "searching": true,
                    "ordering": true,
                    "info": true,
                    "responsive": true,
                    "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                    "pageLength": 10,
                    "order": [[0, 'asc']],
                    "columnDefs": [
                        { "orderable": false, "targets": 5 }
                    ],
                    "language": {
                        "search": "Search Endorsements:",
                        "lengthMenu": "Show _MENU_ entries",
                        "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                        "emptyTable": "No withdrawal endorsements available"
                    }
                });
                
                // Initialize tooltips
                $('[data-toggle="tooltip"]').tooltip();
            });
        </script>
        <?php
    } else {
        ?>
        <div class="alert alert-info text-center">
            <i class="fa fa-info-circle fa-3x mb-3"></i>
            <h4>No Pending Withdrawal Requests</h4>
            <p>There are no withdrawal endorsements awaiting approval at this time.</p>
        </div>
        <?php
    }
}

// public function secretaryApproveRegisteration($spNo) {
//     $db = new DB();
//     $con = $db->getConnection();
//     $user = new User();
//     $employeeid = $user->getEmployeeId($spNo);

//         $sql = "UPDATE fudscoops_member SET is_active = '2' WHERE fudscoops_member.employee_id = :employee_id"; 
         
            
//         $stmt = $con->prepare($sql);
//         $stmt->bindParam(':employee_id', $employeeid);
//         $secretaryApproveRegisteration = $stmt->execute();
        
//         if($secretaryApproveRegisteration){
//             return 1;
//         }else{
//             retun -1;
//         }
    
// }

public function secretaryApproveRegisteration($spNo, $action, $comment) {
    $db = new DB();
    $con = $db->getConnection();
    $user = new User();
    $employeeid = $user->getEmployeeId($spNo);

    // Begin a transaction to ensure both operations succeed or fail together
    $con->beginTransaction();

    try {
        // Determine the decision based on the action
        $decision = ($action === 'Proceed') ? 'Registration Approved' : 'Registration Rejected';

        // Insert into the fudscoops_membership_decision table
        $sqlInsertDecision = "INSERT INTO fudscoops_membership_decision (employee_id, decision, comment, date) 
                              VALUES (:employee_id, :decision, :comment, :date)";
        $stmtInsertDecision = $con->prepare($sqlInsertDecision);
        $stmtInsertDecision->bindParam(':employee_id', $employeeid);
        $stmtInsertDecision->bindParam(':decision', $decision);
        $stmtInsertDecision->bindParam(':comment', $comment);
        $stmtInsertDecision->bindParam(':date', date('Y-m-d H:i:s'));
        $insertResult = $stmtInsertDecision->execute();

        if (!$insertResult) {
            throw new Exception("Failed to insert into fudscoops_membership_decision table.");
        }

        // If the action is 'Proceed' (Approved), update both tables
        if ($action === 'Proceed') {
            // Update fudscoops_member table
            $sqlUpdateMember = "UPDATE fudscoops_member 
                                SET is_active = '1', chairman_approval = '1' 
                                WHERE fudscoops_member.employee_id = :employee_id";
            $stmtUpdateMember = $con->prepare($sqlUpdateMember);
            $stmtUpdateMember->bindParam(':employee_id', $employeeid);
            $updateResultMember = $stmtUpdateMember->execute();

            if (!$updateResultMember) {
                throw new Exception("Failed to update fudscoops_member table.");
            }

            // Update user table (set access_level = 1 where username = $spNo)
            $sqlUpdateUser = "UPDATE user 
                              SET access_level = '1' 
                              WHERE username = :username";
            $stmtUpdateUser = $con->prepare($sqlUpdateUser);
            $stmtUpdateUser->bindParam(':username', $spNo);
            $updateResultUser = $stmtUpdateUser->execute();

            if (!$updateResultUser) {
                throw new Exception("Failed to update user table.");
            }
        }

        // Commit the transaction if all operations succeed
        $con->commit();
        return 1; // Success
    } catch (Exception $e) {
        // Rollback the transaction if any operation fails
        $con->rollBack();
        error_log("Error in secretaryApproveRegisteration: " . $e->getMessage());
        return -1; // Failure
    }
}



    
    public function treasurerApproveWithdrawal($withrawals_id, $approved_amount) {
        $db = new DB();
        $con = $db->getConnection();
        $user = new User();
    
        $sql = "UPDATE fudscoops_withrawals 
                SET approved_withrawal_amount = :approved_amount 
                WHERE withrawals_id = :withrawals_id";
    
        $stmt = $con->prepare($sql);
        $stmt->bindParam(':withrawals_id', $withrawals_id, PDO::PARAM_INT);
        $stmt->bindParam(':approved_amount', $approved_amount, PDO::PARAM_INT);
    
        $secretaryApproveWithdrawal = $stmt->execute();
    
        if ($secretaryApproveWithdrawal) {
            return 1;
        } else {
            return -1;
        }
}
  public function chairmanApproveWithdrawal($withrawals_id) {
        $db = new DB();
        $con = $db->getConnection();
        $user = new User();
        
        //return $withrawals_id;
    
        $sql = "UPDATE fudscoops_withrawals SET chairman_approval = '1' WHERE fudscoops_withrawals.withrawals_id = :withrawals_id";
    
        $stmt = $con->prepare($sql);
        $stmt->bindParam(':withrawals_id', $withrawals_id, PDO::PARAM_INT);
    
        $chairmanApproveWithdrawal = $stmt->execute();
    
        if ($chairmanApproveWithdrawal) {
            return 1;
        } else {
            return -1;
        }
}


 public function treasurerApproveComWithdrawal($withrawals_id, $approved_amount) {
        $db = new DB();
        $con = $db->getConnection();
        $user = new User();
    
        $sql = "UPDATE fudscoops_complete_withrawal 
                SET approved_amount = :approved_amount 
                WHERE comp_withdrwal_id  = :withrawals_id";
    
        $stmt = $con->prepare($sql);
        $stmt->bindParam(':withrawals_id', $withrawals_id, PDO::PARAM_INT);
        $stmt->bindParam(':approved_amount', $approved_amount, PDO::PARAM_INT);
    
        $secretaryApproveWithdrawal = $stmt->execute();
    
        if ($secretaryApproveWithdrawal) {
            return 1;
        } else {
            return -1;
        }
}
  public function chairmanApproveComWithdrawal($withrawals_id) {
        $db = new DB();
        $con = $db->getConnection();
        $user = new User();
    
        $sql = "UPDATE fudscoops_complete_withrawal SET chairman_approval = '1' WHERE fudscoops_complete_withrawal.comp_withdrwal_id = :withrawals_id";
    
        $stmt = $con->prepare($sql);
        $stmt->bindParam(':withrawals_id', $withrawals_id, PDO::PARAM_INT);
    
        $chairmanApproveWithdrawal = $stmt->execute();
    
        if ($chairmanApproveWithdrawal) {
            return 1;
        } else {
            return -1;
        }
}
    
    //used in withdrawal  saving of member 
    public function saveWithdrawal($member_id, $bank_id, $account_name, $acount_number, $withrawals_amount) 
    {
        $db = new DB();
        $con = $db->getConnection();
    
        // Check if member already has a pending withdrawal
        $sql = "SELECT status 
                FROM fudscoops_withrawals 
                WHERE member_id = :memberid 
                ORDER BY withrawals_id DESC 
                LIMIT 1";
        
        $stmt = $con->prepare($sql);
        $stmt->bindParam(':memberid', $member_id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
        // Pending withdrawal exists → return -2
        if ($row && $row['status'] == 0) {
            return -2;
        }
    
        // Insert new withdrawal
        $sql = "INSERT INTO fudscoops_withrawals 
                (member_id, bank_id, account_name, acount_number, proposed_withrawal_amount, proposed_date)
                VALUES (:member_id, :bank_id, :account_name, :acct_no, :withdrawal_amount, NOW())";
    
        $stmt = $con->prepare($sql);
        $stmt->bindParam(':member_id', $member_id);
        $stmt->bindParam(':bank_id', $bank_id);
        $stmt->bindParam(':account_name', $account_name);
        $stmt->bindParam(':acct_no', $acount_number);
        $stmt->bindParam(':withdrawal_amount', $withrawals_amount);
    
        if ($stmt->execute()) {
            return 1; // success
        } else {
            return -1; // failure
        }
    }

       
            
    //provid us the list of bank names and bank id from the database
    public function getBanks() {
        $banks = [];
    
        $db = new DB();
        $this->conn = $db->getConnection();
        $sql = "SELECT id, name FROM banks ORDER BY name";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
    
        $banks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        return $banks; 
    }
    
    //Query the database to get the member_id for the given employee_id
    public function getMemberId($employee_id)
    {
        $r = '';
        $db = new DB();
        $con = $db->getConnection();
        
        $q = "SELECT member_id FROM fudscoops_member WHERE employee_id = :employee_id";
        $stm = $con->prepare($q);
        $stm->bindParam(':employee_id', $employee_id, PDO::PARAM_STR);
        $stm->execute();
        
        $total_rows_found = $stm->rowCount();
        if ($total_rows_found > 0) {
            $row = $stm->fetch(PDO::FETCH_ASSOC);
            $r = $row['member_id'];
        } else {
            $r = 0;
        }
        
        $con = null;
        return $r;
    }
    
        public function getProposedMonthlySavings($spNo)
    {
        $r = '';
        $db = new DB();
        $con = $db->getConnection();
        $user = new User();
        $employeeid = $user->getEmployeeId($spNo);
        $member = new MemberG1();
        $member_id =$member->getMemberId($employeeid);
        
        $q = "SELECT proposed_monthly_savings FROM fudscoops_member WHERE member_id = :member_id";
        $stm = $con->prepare($q);
        $stm->bindParam(':member_id', $member_id, PDO::PARAM_STR);
        $stm->execute();
        
        $total_rows_found = $stm->rowCount();
        
        if ($total_rows_found > 0) {
            $row = $stm->fetch(PDO::FETCH_ASSOC);
            $r = $row['proposed_monthly_savings'];
        } else {
            $r = 0;
        }
        
        $con = null;
        return $r;
    }
           public function getCumulativeMonthlySavings($spNo)
    {
        $r = '';
        $db = new DB();
        $con = $db->getConnection();
         $user = new User();
        $employeeid = $user->getEmployeeId($spNo);
         $member = new MemberG1();
        $member_id =$member->getMemberId($employeeid);
        
        $q = "SELECT SUM(savings_amount)  as totalsavings FROM fudscoops_savings WHERE member_idmember = :member_id";
        $stm = $con->prepare($q);
        $stm->bindParam(':member_id', $member_id, PDO::PARAM_STR);
        $stm->execute();
        
        $total_rows_found = $stm->rowCount();
        
        if ($total_rows_found > 0) {
            $row = $stm->fetch(PDO::FETCH_ASSOC);
            $r = $row['totalsavings'];
        } else {
            $r = 0;
        }
        
        $con = null;
        return $r;
    }
    
    
     public function getCumulativeShares($spNo)
    {
        $r = '';
        $db = new DB();
        $con = $db->getConnection();
         $user = new User();
        $employeeid = $user->getEmployeeId($spNo);
         $member = new MemberG1();
        $member_id =$member->getMemberId($employeeid);
        
        $q = "SELECT SUM(amount_paid)  as totalshares FROM fudscoops_shares WHERE member_id = :member_id AND share_status=1";
        $stm = $con->prepare($q);
        $stm->bindParam(':member_id', $member_id, PDO::PARAM_STR);
        $stm->execute();
        
        $total_rows_found = $stm->rowCount();
        
        if ($total_rows_found > 0) {
            $row = $stm->fetch(PDO::FETCH_ASSOC);
            $r = $row['totalshares'];
        } else {
            $r = 0;
        }
        
        $con = null;
        return $r;
    }
    
     public function getCumulativeLoan($spNo)
    {
        $r = '';
        $db = new DB();
        $con = $db->getConnection();
        $user = new User();
        $employeeid = $user->getEmployeeId($spNo);
        $member = new MemberG1();
        $member_id =$member->getMemberId($employeeid);
        
        $q = "SELECT SUM(loan_amount)  as totalloan FROM fudscoops_loan WHERE member_id = :member_id";
        $stm = $con->prepare($q);
        $stm->bindParam(':member_id', $member_id, PDO::PARAM_STR);
        $stm->execute();
        
        $total_rows_found = $stm->rowCount();
        
        if ($total_rows_found > 0) {
            $row = $stm->fetch(PDO::FETCH_ASSOC);
            $r = $row['totalloan'];
        } else {
            $r = 0;
        }
        
        $con = null;
        return $r;
    }
    
    public function getCumulativeSavingsWithdrawals($spNo)
    {
        $r = '';
        $db = new DB();
        $con = $db->getConnection();
        $user = new User();
        $employeeid = $user->getEmployeeId($spNo);
        $member = new MemberG1();
        $member_id =$member->getMemberId($employeeid);
        
        $q = "SELECT SUM(approved_withrawal_amount)  as totalwithawals FROM fudscoops_withrawals WHERE chairman_approval='1' AND member_id= :member_id";
        $stm = $con->prepare($q);
        $stm->bindParam(':member_id', $member_id, PDO::PARAM_STR);
        $stm->execute();
        
        $total_rows_found = $stm->rowCount();
        
        if ($total_rows_found > 0) {
            $row = $stm->fetch(PDO::FETCH_ASSOC);
            $r = $row['totalwithawals'];
        } else {
            $r = 0;
        }
        
        $con = null;
        return $r;
    }
    
     public function getLastSavingsWithdrawals($spNo)
    {
        $r = '';
        $db = new DB();
        $con = $db->getConnection();
         $user = new User();
        $employeeid = $user->getEmployeeId($spNo);
         $member = new MemberG1();
        $member_id =$member->getMemberId($employeeid);
        
        $q = "SELECT approved_withrawal_amount  
                FROM  fudscoops_withrawals 
                WHERE chairman_approval='1' AND member_id= :member_id
                ORDER BY approved_date DESC 
                LIMIT 1;";
        $stm = $con->prepare($q);
        $stm->bindParam(':member_id', $member_id, PDO::PARAM_STR);
        $stm->execute();
        
        $total_rows_found = $stm->rowCount();
        
        if ($total_rows_found > 0) {
            $row = $stm->fetch(PDO::FETCH_ASSOC);
            $r = $row['approved_withrawal_amount'];
        } else {
            $r = 0;
        }
        
        $con = null;
        return $r;
    }
    
    public function getCurrentSavingsAmount($spNo)
    {
        $r = '';
        $db = new DB();
        $con = $db->getConnection();
         $user = new User();
        $employeeid = $user->getEmployeeId($spNo);
         $member = new MemberG1();
        $member_id =$member->getMemberId($employeeid);
        
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
    
    public static function checkWithdrawalAmount($withdrawal_amount)
    {
        $db = new DB();
        $user = new User();
        $member = new MemberG1();
        $con = $db->getConnection();
        
        
        $savingsAmount = $member->getCurrentSavingsAmount($_SESSION['username']);
        
    
        // Ensure worth is not greater than total share amount
        if ($withdrawal_amount > $savingsAmount) {
            return -1; // worth exceeds available share amount
        }
    
        // Ensure remaining balance is not less than ₦20,000
        $remainingBalance = $savingsAmount - $withdrawal_amount;
        if ($remainingBalance < 2000) {
            return -2; // remaining balance too low
        }
    
        return 1; // valid transaction
    }
    


    public function getStaffInformation($staffno){
        
            $db = new DB();
            $user = new MemberG1();

            $r = '';
            $con = $db->getConnection();
            

            $array = array();
            
            
        $sql = "SELECT DISTINCT(employee.sp_no), employee.employee_id, ippis_nonstatic_record.dept, employee.sp_no, employee.fname,employee.oname, employee.lname, employee.phone_no, 
                ippis_nonstatic_record.bank_name, ippis_nonstatic_record.acctno, ippis_nonstatic_record.grade, employee.permanent_address, employee.nature_of_appo 
                from employee INNER JOIN ippis_static_record ON employee.employee_id=ippis_static_record.employee_id 
                INNER JOIN ippis_nonstatic_record ON ippis_static_record.row_id=ippis_nonstatic_record.ippis_static_id 
                WHERE employee.sp_no='$staffno'";
                
            $stm = $con->prepare($sql);
            $stm->execute();
            $total_rows_found = $stm->rowCount();
            if ($total_rows_found >0) {
                    $row = $stm->fetch(PDO::FETCH_ASSOC);
                    $array  =   $row;
            } else {
                return 0;
            }//end if 
            $con=null;
            return $array;
          
    }
    
    
    public function registerMember($spNo,$monthly_savings,$next_of_kin_name,$next_of_kin_gsm,$next_of_kin_address){
        
            
            $member = new MemberG1();
            //return $next_of_kin_name. " ". $next_of_kin_gsm. " ". $next_of_kin_address." ". $employeeid." ". $monthly_savings;

    
            $db = new DB();
            $user = new User();
            $con = $db->getConnection();
            $employeeid = $user->getEmployeeId($spNo);
            
            $sql ="SELECT * FROM fudscoops_member WHERE employee_id='$employeeid'";
            $stmt = $con->prepare($sql);
            $stmt->execute();
            $count = $stmt->rowCount();
            if($count>0){
                
              $sql ="UPDATE fudscoops_member SET proposed_monthly_savings=:monthly_savings,next_of_kin_name=:next_of_kin_name,
              next_of_kin_gsm=:next_of_kin_gsm,next_of_kin_address=:next_of_kin_address WHERE employee_id='$employeeid'";
              $stmt = $con->prepare($sql);
              $stmt->bindParam(':monthly_savings', $monthly_savings,PDO::PARAM_STR);
              $stmt->bindParam(':next_of_kin_name', $next_of_kin_name,PDO::PARAM_STR);
              $stmt->bindParam(':next_of_kin_gsm', $next_of_kin_gsm,PDO::PARAM_STR);
              $stmt->bindParam(':next_of_kin_address', $next_of_kin_address,PDO::PARAM_STR);
              $stmt->execute();
              if ($stmt) {
                        return 1;
                } else {
                    return -1;
                }
              
            }else{
             
            
                $sql = "INSERT INTO fudscoops_member(employee_Id, proposed_monthly_savings, next_of_kin_name, next_of_kin_gsm, next_of_kin_address)
                VALUES (:employeeid, :monthly_savings, :next_of_kin_name, :next_of_kin_gsm, :next_of_kin_address)";
                // Create a prepared statement
                $stmt = $con->prepare($sql);
                // Bind parameters
                $stmt->bindParam(':employeeid', $employeeid,PDO::PARAM_STR);
                $stmt->bindParam(':monthly_savings', $monthly_savings,PDO::PARAM_STR);
                $stmt->bindParam(':next_of_kin_name', $next_of_kin_name,PDO::PARAM_STR);
                $stmt->bindParam(':next_of_kin_gsm', $next_of_kin_gsm,PDO::PARAM_STR);
                $stmt->bindParam(':next_of_kin_address', $next_of_kin_address,PDO::PARAM_STR);
                // Execute the query
                $stmt->execute();
                
               // $stmt=1;
    
                    if ($stmt) {
                        
                            //return 1;
                            return json_encode([1, $spNo, $monthly_savings]);  // Returning an array with the values
                    } else {
                        return -1;
                    }//end if
            }
    }
    
    public  function getMyDownload() {
        $r_v = '';
        $db = new DB();
        $con = getConnection();
        $q = "SELECT course_code,level,course_title,l.course_id,l.staff_id FROM course c
                INNER JOIN lecturer_course_allocation l ON c.course_id = l.course_id
                    WHERE l.staff_id = '".$staff_id."' AND l.session_id='".$session_id."'";
        
        $stm = $con->prepare($q);
        $stm->execute();
        $total_rows_found = $stm->rowCount();
        if ($total_rows_found >= 1) {
            while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
                    $result=$row['course_title'].'('.($row['course_code']).')'.' '.$row['level'];
                    $r_v .= "
                    
                    <a href='download_template.php?sid=".$row['staff_id']."&cid=".$row['course_id']."&session=".$session_id."&code=".$row['course_code']."' &level=".$row['level']."'>".$result.",</a>
                                      
                ";
            }
        } else {
            $r_v = "";
        }//end if ($total_rows_found == 1)
        $con = null;
        return $r_v;
    }
    
    public function getMembershipApplicationsCount() {
    $db = new DB();
    $con = $db->getConnection();

    $sql = "SELECT * FROM fudscoops_member INNER JOIN employee on fudscoops_member.employee_id=employee.employee_id where fudscoops_member.is_active='0' or fudscoops_member.chairman_approval='0'";
    $stmt = $con->prepare($sql);
    $stmt->execute();

    // Get the count of rows returned by the query
    $count = $stmt->rowCount();

    return $count;
}

    
    
    public function getMembershipApplicationsTable() {
    $db = new DB();
    $con = $db->getConnection();

    
    
    
    $sql = "SELECT * FROM fudscoops_member INNER JOIN employee on fudscoops_member.employee_id=employee.employee_id where fudscoops_member.is_active='0' or fudscoops_member.chairman_approval='0'";
    $stmt = $con->prepare($sql);
    $stmt->execute();
        

    // Fetch all loan information for the guarantor
    $proposed_members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($proposed_members) {
        $sn = 0;
        //echo var_dump($proposed_members);
        echo "<h3>List of Membership Applicants</h3>";
       echo '<table id="table" 
             data-toggle="table" 
             data-show-print="true" 
             data-pagination="true" 
             data-search="true" 
             data-show-columns="true" 
             data-show-pagination-switch="true" 
             data-show-refresh="false" 
             data-key-events="true" 
             data-show-toggle="false" 
             data-show-print="true" 
             data-resizable="true" 
             data-cookie="true" 
             data-cookie-id-table="saveId" 
             data-show-export="true" 
             data-click-to-select="true" 
             data-toolbar="#toolbar">';
     echo "<thead>
                <tr>
                   <th>S/N</th>
                    <th>Staff Number</th>
                    <th> Name</th>
                    <th>Cadre</th>
                    <th>Department </th>
                    <th>Proposed Monthly Savings</th>
                    <th>Action</th>
                </tr>
              </thead>";
        echo "<tbody>";
       
        foreach ($proposed_members as $members) {
            $sn++;
         
            echo "<tr>";
            echo "<td>" . $sn . "</td>";
            echo "<td>" . htmlspecialchars($members['sp_no']) . "</td>";
            echo "<td>" . htmlspecialchars($members['fname'] . ' ' . $members['lname']) . "</td>"; 
            echo "<td>" . htmlspecialchars($members['cadre']) . "</td>";
            echo "<td>" . htmlspecialchars($members['dept']) . "</td>";
            echo "<td>" . htmlspecialchars($members['proposed_monthly_savings']) . "</td>";
          
            echo "<td>
                    <a href='view_proposed_member_details.php?member_id=" . htmlspecialchars($members['employee_id']) . "' class='btn btn-primary'>View Details</a>
                  </td>";
            echo "</tr>";
        }

        echo "
                 <tfoot>
                    <tr>
                         <th>S/N</th>
                        <th>Staff Number</th>
                        <th> Name</th>
                         <th>Cadre</th>
                        <th>Department </th>
                        <th>Proposed Monthly Savings</th>
                        <th>Action</th>
                    </tr>
                 </tfoot>
        
        </tbody>";
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

 public function SecretaryMembershipApplicationsTable() {
    $db = new DB();
    $con = $db->getConnection();

    
    
    
    $sql = "SELECT * FROM fudscoops_member INNER JOIN employee on fudscoops_member.employee_id=employee.employee_id where fudscoops_member.is_active='0' or fudscoops_member.chairman_approval='0'";
    $stmt = $con->prepare($sql);
    $stmt->execute();
        

    // Fetch all loan information for the guarantor
    $proposed_members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($proposed_members) {
        $sn = 0;
        //echo var_dump($proposed_members);
        echo "<h3>List of Membership Applicants</h3>";
       echo '<table id="table" 
             data-toggle="table" 
             data-show-print="true" 
             data-pagination="true" 
             data-search="true" 
             data-show-columns="true" 
             data-show-pagination-switch="true" 
             data-show-refresh="false" 
             data-key-events="true" 
             data-show-toggle="false" 
             data-show-print="true" 
             data-resizable="true" 
             data-cookie="true" 
             data-cookie-id-table="saveId" 
             data-show-export="true" 
             data-click-to-select="true" 
             data-toolbar="#toolbar">';
     echo "<thead>
                <tr>
                   <th>S/N</th>
                    <th>Staff Number</th>
                    <th> Name</th>
                    <th>Cadre</th>
                    <th>Department </th>
                    <th>Proposed Monthly Savings</th>
                    
                </tr>
              </thead>";
        echo "<tbody>";
       
        foreach ($proposed_members as $members) {
            $sn++;
         
            echo "<tr>";
            echo "<td>" . $sn . "</td>";
            echo "<td>" . htmlspecialchars($members['sp_no']) . "</td>";
            echo "<td>" . htmlspecialchars($members['fname'] . ' ' . $members['lname']) . "</td>"; 
            echo "<td>" . htmlspecialchars($members['cadre']) . "</td>";
            echo "<td>" . htmlspecialchars($members['dept']) . "</td>";
            echo "<td>" . htmlspecialchars($members['proposed_monthly_savings']) . "</td>";
          
            
            echo "</tr>";
        }

        echo "
                 <tfoot>
                    <tr>
                         <th>S/N</th>
                        <th>Staff Number</th>
                        <th> Name</th>
                         <th>Cadre</th>
                        <th>Department </th>
                        <th>Proposed Monthly Savings</th>
                        <th>Action</th>
                    </tr>
                 </tfoot>
        
        </tbody>";
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

public function getSecretaryMembershipDecision($member_id) {
    $comment='';
    $target1 = date('n')-2;
    $target2 = date('n')-2;
    $target3 = date('n')-1;
    if($target1==0 || $target1 == -1){
        $target1 = date('n', strtotime('-3 months'));  
    }
    if($target2==0 || $target1 == -1){
        $target2 = date('n', strtotime('-2 months'));  
    }
    if($target3==0 || $target1 == -1){
        $target3 = date('n', strtotime('-1 months'));  
    }
    $payslip = new Payslip();
    $month_name1 = $payslip->getMonthName($target1);
    $month_name2 = $payslip->getMonthName($target2);
    $month_name3 = $payslip->getMonthName($target3);
    $db = new DB();
    $con = $db->getConnection();
    $user = new User();
    // Fetch loan details based on loan_id
    $sql = "SELECT * FROM fudscoops_member INNER JOIN employee on fudscoops_member.employee_id=employee.employee_id where fudscoops_member.employee_id=:member_id";
   
    $stmt = $con->prepare($sql);
    $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
    $stmt->execute();
    

    
    $member_infor = $stmt->fetch(PDO::FETCH_ASSOC);
    
   
   
    if ($member_infor) {
        
        // Prepare staff information
       $sp_no = htmlspecialchars($member_infor['sp_no']);
       $employee_id = htmlspecialchars($member_infor['employee_id']);
        $member_id = htmlspecialchars($member_infor['member_id']);
        $applicantName = htmlspecialchars($member_infor['fname'] . ' ' . $loanDetails['lname']);
        $staffNo = htmlspecialchars($member_infor['sp_no']);
        $department = htmlspecialchars($member_infor['dept']);
        $gsmNo = htmlspecialchars($member_infor['phone_no']);
        $bank = htmlspecialchars($member_infor['bank']);
        $acctNo = htmlspecialchars($member_infor['acct_no']);
        $address = htmlspecialchars($member_infor['permanent_address']);
        $appointmentType = htmlspecialchars($member_infor['nature_of_appo']);
        $proposed_monthly_savings = htmlspecialchars($member_infor['proposed_monthly_savings']);
        
      
        // Output the professional header and layout
        echo "<html>
                <head>
                    <title>Membership Application Form</title>
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
                                  <h3>Membership Application Form</h3>
                    </div>
                        <hr>
                        <div class='staff-info'>
                            <h4><strong>Applicants Information:</strong></h4>
                            <p><strong>Name:</strong> $applicantName</p>
                            <p><strong>Staff No:</strong> $staffNo</p>
                            <p><strong>Department/Unit:</strong> $department</p>
                            <p><strong>GSM No:</strong> $gsmNo</p>
                            <p><strong>Bank:</strong> $bank</p>
                            <p><strong>Acct. No:</strong> $acctNo</p>
                            <p><strong>Permanent Home Address:</strong> $address</p>
                            <p><strong>Type of Appointment:</strong> $appointmentType</p>
                        </div>
                        <hr>
                        
                         <p><strong>Proposed Monthly Saving:</strong> &#8358; $proposed_monthly_savings</p>
                         
                         <hr>
                      
                        <p><strong><a href='slip.php?legacy=".$member_infor['sp_no']."&s=".$m1."'>Download Applicant $month_name1 Payslip</a> &nbsp;
                        <a href='slip.php?legacy=".$member_infor['sp_no']."&s=".$m2."'>Download Applicant $month_name2 Payslip</a> &nbsp; 
                        <a href='slip.php?legacy=".$member_infor['sp_no']."&s=".$m3."'>Download Applicant $month_name3 Payslip</a>
                        </strong> </p>
                        
                          <div class='form-section'>
                            <form class='membership-decision-form'>
                                <hr>
                                <p class='undertaking-text'> 
                                    <strong>Comment:</strong><br>
                                    <label>
                                        <input type='hidden' name='member_id' value='" . $employee_id . "'>
                                        <input type='hidden' name='sp_no' value='" . $sp_no . "'>
                                    </label>
                                </p>
                                <input type='hidden' name='type' id='type' class='form-control' value='decision'>
                                <textarea name='comment' class='form-control' id='comment'></textarea>
                                
                                <button type='submit' class='btn btn-sm btn-success' id='proceed' >Approve</button>
                                <button type='submit' class='btn btn-sm btn-danger' id='Decline' >Reject</button>
                                
                                <div class='msg'></div>
                                <br>
                            </form>  
                        </div>
                        
                    </div>
                    
                                        
        <div id='updateRepayment' data-backdrop='static' data-keyboard='false' class='modal modal-edu-general Customwidth-popup-WarningModal fade' role='dialog'>

     
        
        
        </div>

                </body>
            </html>";
    } else {
        echo "<p>Applicants details not found.</p>";
    }
}

public function getTreasurerWithdrawalDecision($withrawals_id) {
    $comment='';
    $target1 = date('n')-2;
    $target2 = date('n')-2;
    $target3 = date('n')-1;
    if($target1==0 || $target1 == -1){
        $target1 = date('n', strtotime('-3 months'));  
    }
    if($target2==0 || $target1 == -1){
        $target2 = date('n', strtotime('-2 months'));  
    }
    if($target3==0 || $target1 == -1){
        $target3 = date('n', strtotime('-1 months'));  
    }
    $payslip = new Payslip();
    $month_name1 = $payslip->getMonthName($target1);
    $month_name2 = $payslip->getMonthName($target2);
    $month_name3 = $payslip->getMonthName($target3);
    $db = new DB();
    $con = $db->getConnection();
    $user = new User();
    // Fetch loan details based on loan_id
    $sql = "SELECT * FROM fudscoops_member INNER JOIN employee on fudscoops_member.employee_id=employee.employee_id where fudscoops_member.employee_id=:member_id";
   
    $stmt = $con->prepare($sql);
    $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
    $stmt->execute();
    

    
    $member_infor = $stmt->fetch(PDO::FETCH_ASSOC);
    
   
   
    if ($member_infor) {
        
        // Prepare staff information
       $sp_no = htmlspecialchars($member_infor['sp_no']);
       $employee_id = htmlspecialchars($member_infor['employee_id']);
        $member_id = htmlspecialchars($member_infor['member_id']);
        $applicantName = htmlspecialchars($member_infor['fname'] . ' ' . $loanDetails['lname']);
        $staffNo = htmlspecialchars($member_infor['sp_no']);
        $department = htmlspecialchars($member_infor['dept']);
        $gsmNo = htmlspecialchars($member_infor['phone_no']);
        $bank = htmlspecialchars($member_infor['bank']);
        $acctNo = htmlspecialchars($member_infor['acct_no']);
        $address = htmlspecialchars($member_infor['permanent_address']);
        $appointmentType = htmlspecialchars($member_infor['nature_of_appo']);
        $proposed_monthly_savings = htmlspecialchars($member_infor['proposed_monthly_savings']);
        
      
        // Output the professional header and layout
        echo "<html>
                <head>
                    <title>Membership Application Form</title>
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
                                  <h3>Membership Application Form</h3>
                    </div>
                        <hr>
                        <div class='staff-info'>
                            <h4><strong>Applicants Information:</strong></h4>
                            <p><strong>Name:</strong> $applicantName</p>
                            <p><strong>Staff No:</strong> $staffNo</p>
                            <p><strong>Department/Unit:</strong> $department</p>
                            <p><strong>GSM No:</strong> $gsmNo</p>
                            <p><strong>Bank:</strong> $bank</p>
                            <p><strong>Acct. No:</strong> $acctNo</p>
                            <p><strong>Permanent Home Address:</strong> $address</p>
                            <p><strong>Type of Appointment:</strong> $appointmentType</p>
                        </div>
                        <hr>
                        
                         <p><strong>Proposed Monthly Saving:</strong> &#8358; $proposed_monthly_savings</p>
                         
                         <hr>
                      
                        <p><strong><a href='slip.php?legacy=".$member_infor['sp_no']."&s=".$m1."'>Download Applicant $month_name1 Payslip</a> &nbsp;
                        <a href='slip.php?legacy=".$member_infor['sp_no']."&s=".$m2."'>Download Applicant $month_name2 Payslip</a> &nbsp; 
                        <a href='slip.php?legacy=".$member_infor['sp_no']."&s=".$m3."'>Download Applicant $month_name3 Payslip</a>
                        </strong> </p>
                        
                          <div class='form-section'>
                            <form class='membership-decision-form'>
                                <hr>
                                <p class='undertaking-text'> 
                                    <strong>Comment:</strong><br>
                                    <label>
                                        <input type='hidden' name='member_id' value='" . $employee_id . "'>
                                        <input type='hidden' name='sp_no' value='" . $sp_no . "'>
                                    </label>
                                </p>
                                <input type='hidden' name='type' id='type' class='form-control' value='decision'>
                                <textarea name='comment' class='form-control' id='comment'></textarea>
                                
                                <button type='submit' class='btn btn-sm btn-success' id='proceed' >Approve</button>
                                <button type='submit' class='btn btn-sm btn-danger' id='Decline' >Reject</button>
                                
                                <div class='msg'></div>
                                <br>
                            </form>  
                        </div>
                        
                    </div>
                    
                                        
        <div id='updateRepayment' data-backdrop='static' data-keyboard='false' class='modal modal-edu-general Customwidth-popup-WarningModal fade' role='dialog'>

     
        
        
        </div>

                </body>
            </html>";
    } else {
        echo "<p>Applicants details not found.</p>";
    }
}


public function getWithdrawal_details($withrawals_id) { 
     $db = new DB();
    $con = $db->getConnection();
    $user = new User();
    
    $member_idSql = "SELECT member_id,proposed_withrawal_amount FROM fudscoops_withrawals WHERE withrawals_id=:withrawals_id";
   
    $member = $con->prepare($member_idSql);
    $member->bindParam(':withrawals_id', $withrawals_id, PDO::PARAM_INT);
    $member->execute();
    $result = $member->fetch(PDO::FETCH_ASSOC);
    
    $member_id = (int) $result['member_id'];
     $proposed_withrawal_amount = (int) $result['proposed_withrawal_amount'];
    
    
       
    
    $comment='';
    $target1 = date('n')-2;
    $target2 = date('n')-2;
    $target3 = date('n')-1;
    if($target1==0 || $target1 == -1){
        $target1 = date('n', strtotime('-3 months'));  
    }
    if($target2==0 || $target1 == -1){
        $target2 = date('n', strtotime('-2 months'));  
    }
    if($target3==0 || $target1 == -1){
        $target3 = date('n', strtotime('-1 months'));  
    }
    $payslip = new Payslip();
    $month_name1 = $payslip->getMonthName($target1);
    $month_name2 = $payslip->getMonthName($target2);
    $month_name3 = $payslip->getMonthName($target3);
   
    // Fetch loan details based on loan_id
    $sql = "SELECT * FROM fudscoops_member INNER JOIN employee on fudscoops_member.employee_id=employee.employee_id where fudscoops_member.member_id=:member_id";
   
    $stmt = $con->prepare($sql);
    $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT); 
    $stmt->execute();
    
    
    $member_infor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    
   
    if ($member_infor) {
        
        // Prepare staff information
       $sp_no = htmlspecialchars($member_infor['sp_no']);
       $employee_id = htmlspecialchars($member_infor['employee_id']);
        $member_id = htmlspecialchars($member_infor['member_id']);
        $applicantName = htmlspecialchars($member_infor['fname'] . ' ' . $loanDetails['lname']);
        $staffNo = htmlspecialchars($member_infor['sp_no']);
        $department = htmlspecialchars($member_infor['dept']);
        $gsmNo = htmlspecialchars($member_infor['phone_no']);
        $bank = htmlspecialchars($member_infor['bank']);
        $acctNo = htmlspecialchars($member_infor['acct_no']);
        $address = htmlspecialchars($member_infor['permanent_address']);
        $appointmentType = htmlspecialchars($member_infor['nature_of_appo']);
        $proposed_monthly_savings = htmlspecialchars($member_infor['proposed_monthly_savings']);
        
      
        // Output the professional header and layout
        echo "<html>
                <head>
                    <title>Membership Savings Withdrawal Form</title>
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
                                  <h3>Membership Savings Withdrawal Form</h3>
                    </div>
                        <hr>
                        <div class='staff-info'>
                            <h4><strong>Applicants Information:</strong></h4>
                            <p><strong>Name:</strong> $applicantName</p>
                            <p><strong>Staff No:</strong> $staffNo</p>
                            <p><strong>Department/Unit:</strong> $department</p> 
                            <p><strong>GSM No:</strong> $gsmNo</p>
                            <p><strong>Bank:</strong> $bank</p>
                            <p><strong>Acct. No:</strong> $acctNo</p>
                            <p><strong>Permanent Home Address:</strong> $address</p>
                            <p><strong>Type of Appointment:</strong> $appointmentType</p>
                        </div>
                        <hr>
                        
                         <p><strong>Proposed Saving Withrawal Amount:</strong> &#8358; $proposed_withrawal_amount</p>
                         
                           <form class='sav-withdrawal-decision-form'>
                         <label><strong>Approved Amount</strong></label> 
                          <input type='text' name='approved_amount' > 
                         <hr>
                      
                        <p><strong><a href='slip.php?legacy=".$member_infor['sp_no']."&s=".$m1."'>Download Applicant $month_name1 Payslip</a> &nbsp;
                        <a href='slip.php?legacy=".$member_infor['sp_no']."&s=".$m2."'>Download Applicant $month_name2 Payslip</a> &nbsp; 
                        <a href='slip.php?legacy=".$member_infor['sp_no']."&s=".$m3."'>Download Applicant $month_name3 Payslip</a>
                        </strong> </p>
                        
                          <div class='form-section'>
                           
                               
                                <hr>
                                <p class='undertaking-text'> 
                                    <strong>Comment:</strong><br>
                                    <label>
                                        <input type='hidden' name='employee_id' value='" . $employee_id . "'>
                                        <input type='hidden' name='sp_no' value='" . $sp_no . "'>
                                        <input type='hidden' name='withrawals_id' value='" . $withrawals_id . "'>
                                    </label>
                                </p>
                                <input type='hidden' name='type' id='type' class='form-control' value='decision'>
                                <textarea name='comment' class='form-control' id='comment'></textarea>
                                
                                <button type='submit' class='btn btn-sm btn-success' id='proceed' >Approve</button>
                                <button type='submit' class='btn btn-sm btn-danger' id='Decline' >Reject</button>
                                
                                <div class='msg'></div>
                                <br>
                            </form>  
                        </div>
                        
                    </div>
                    
                                        
        <div id='updateRepayment' data-backdrop='static' data-keyboard='false' class='modal modal-edu-general Customwidth-popup-WarningModal fade' role='dialog'>

     
        
        
        </div>

                </body>
            </html>";
    } else {
        echo "<p>Applicants details not found.</p>";
    }
}


public function getComp_Withdrawal_details($withrawals_id,$sp_no) { 
     $db = new DB();
    $con = $db->getConnection();
    $user = new User();
     
    $currentsavings = number_format($this->getCurrentSavingsAmount($sp_no), 2);

   
     $member_idSql = "SELECT employee_id FROM fudscoops_complete_withrawal WHERE comp_withdrwal_id=:withrawals_id";
 
    $member = $con->prepare($member_idSql);
    $member->bindParam(':withrawals_id', $withrawals_id, PDO::PARAM_INT);
    $member->execute();
    $result = $member->fetch(PDO::FETCH_ASSOC);
    
  
    $employee_id = (int) $result['employee_id'];
    
       
    $comment='';
    $target1 = date('n')-2;
    $target2 = date('n')-2;
    $target3 = date('n')-1;
    if($target1==0 || $target1 == -1){
        $target1 = date('n', strtotime('-3 months'));  
    }
    if($target2==0 || $target1 == -1){
        $target2 = date('n', strtotime('-2 months'));  
    }
    if($target3==0 || $target1 == -1){
        $target3 = date('n', strtotime('-1 months'));  
    }
    $payslip = new Payslip();
    $month_name1 = $payslip->getMonthName($target1);
    $month_name2 = $payslip->getMonthName($target2);
    $month_name3 = $payslip->getMonthName($target3);
   
    // Fetch loan details based on loan_id
    $sql = "SELECT * FROM fudscoops_member INNER JOIN employee on fudscoops_member.employee_id=employee.employee_id where fudscoops_member.employee_id=:employee_id";
   
    $stmt = $con->prepare($sql);
    $stmt->bindParam(':employee_id', $employee_id, PDO::PARAM_INT); 
    $stmt->execute();
    
    
    $member_infor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    
   
    if ($member_infor) {
        
        // Prepare staff information
       $sp_no = htmlspecialchars($member_infor['sp_no']);
       $employee_id = htmlspecialchars($member_infor['employee_id']);
        $member_id = htmlspecialchars($member_infor['member_id']);
        $applicantName = htmlspecialchars($member_infor['fname'] . ' ' . $loanDetails['lname']);
        $staffNo = htmlspecialchars($member_infor['sp_no']);
        $department = htmlspecialchars($member_infor['dept']);
        $gsmNo = htmlspecialchars($member_infor['phone_no']);
        $bank = htmlspecialchars($member_infor['bank']);
        $acctNo = htmlspecialchars($member_infor['acct_no']);
        $address = htmlspecialchars($member_infor['permanent_address']);
        $appointmentType = htmlspecialchars($member_infor['nature_of_appo']);
        $proposed_monthly_savings = htmlspecialchars($member_infor['proposed_monthly_savings']);
        
      
        // Output the professional header and layout
        echo "<html>
                <head>
                    <title>Complete Withdrawal Form</title>
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
                                  <h3>Membership Complete Withdrawal Form</h3>
                    </div>
                        <hr>
                        <div class='staff-info'>
                            <h4><strong>Applicants Information:</strong></h4>
                            <p><strong>Name:</strong> $applicantName</p>
                            <p><strong>Staff No:</strong> $staffNo</p>
                            <p><strong>Department/Unit:</strong> $department</p> 
                            <p><strong>GSM No:</strong> $gsmNo</p>
                            <p><strong>Bank:</strong> $bank</p>
                            <p><strong>Acct. No:</strong> $acctNo</p>
                            <p><strong>Permanent Home Address:</strong> $address</p>
                            <p><strong>Type of Appointment:</strong> $appointmentType</p>
                        </div>
                        <hr>
                        
                         <p><strong>Cummulative Saving Amount:</strong> &#8358; $currentsavings</p>
                         
                           <form class='com-withdrawal-decision-form'>
                         <label><strong>Approved Amount</strong></label> 
                          <input type='text' name='approved_amount' > 
                         <hr>
                      
                        <p><strong><a href='slip.php?legacy=".$member_infor['sp_no']."&s=".$m1."'>Download Applicant $month_name1 Payslip</a> &nbsp;
                        <a href='slip.php?legacy=".$member_infor['sp_no']."&s=".$m2."'>Download Applicant $month_name2 Payslip</a> &nbsp; 
                        <a href='slip.php?legacy=".$member_infor['sp_no']."&s=".$m3."'>Download Applicant $month_name3 Payslip</a>
                        </strong> </p>
                        
                          <div class='form-section'>
                           
                               
                                <hr>
                                <p class='undertaking-text'> 
                                    <strong>Comment:</strong><br>
                                    <label>
                                        <input type='hidden' name='employee_id' value='" . $employee_id . "'>
                                        <input type='hidden' name='sp_no' value='" . $sp_no . "'>
                                        <input type='hidden' name='withrawals_id' value='" . $withrawals_id . "'>
                                    </label>
                                </p>
                                <input type='hidden' name='type' id='type' class='form-control' value='decision'>
                                <textarea name='comment' class='form-control' id='comment'></textarea>
                                
                                <button type='submit' class='btn btn-sm btn-success' id='proceed' >Approve</button>
                                <button type='submit' class='btn btn-sm btn-danger' id='Decline' >Reject</button>
                                
                                <div class='msg'></div>
                                <br>
                            </form>  
                        </div>
                        
                    </div>
                    
                                        
        <div id='updateRepayment' data-backdrop='static' data-keyboard='false' class='modal modal-edu-general Customwidth-popup-WarningModal fade' role='dialog'>

     
        
        
        </div>

                </body>
            </html>";
    } else {
        echo "<p>Applicants details not found.</p>";
    }
}

    public function addTargetSavingRequest($db, $amount, $saving_period, $withdraw_period, $guarantor_employee_id, $member_id) {
    $con = $db->getConnection();

    try {
        $query = "INSERT INTO fudscoops_Target_saving 
            (savings_amount, saving_period, withdraw_period, date_saved, target_saving_status, 
             guarantor_employee_id, guarantor_status, member_idmember) 
            VALUES (:amount, :saving_period, :withdraw_period, CURDATE(), 0, :guarantor_employee_id, 0, :member_id)";

        $stmt = $con->prepare($query);
        
        // Binding parameters
        $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
        $stmt->bindParam(':saving_period', $saving_period, PDO::PARAM_STR);
        $stmt->bindParam(':withdraw_period', $withdraw_period, PDO::PARAM_INT);
        $stmt->bindParam(':guarantor_employee_id', $guarantor_employee_id, PDO::PARAM_INT);
        $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return json_encode(["status" => 1, "message" => "Target saving request submitted successfully."]);
        } else {
            $errorInfo = $stmt->errorInfo();
            return json_encode(["status" => -1, "message" => "Database error: " . $errorInfo[2]]);
        }
    } catch (PDOException $e) {
        return json_encode(["status" => -1, "message" => "Exception error: " . $e->getMessage()]);
    }
}


public function getSavingRecords() {
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

    public function saveCommodity($commodity_name, $opening_date, $closing_date) {
        $db = new DB(); 
        $con = $db->getConnection();
    
        $sql = "INSERT INTO fudscoops_commodity_supply 
                    (commodity_name, opening_date, closing_date, created_at, is_deleted) 
                VALUES 
                    (:commodity_name, :opening_date, :closing_date, NOW(), 0)";
    
        $stmt = $con->prepare($sql);
    
        $stmt->bindParam(':commodity_name', $commodity_name);
        $stmt->bindParam(':opening_date', $opening_date);
        $stmt->bindParam(':closing_date', $closing_date);
    
        if ($stmt->execute()) {
            return 1;  // success
        } else {
            return -1; // failure
        }
    }
    
    // In MemberG1.php
    public function getChairmanApprovedWithdrawals() {
         $db = new DB(); 
        $con = $db->getConnection();
        $query = "
            SELECT 
                w.*, 
                e.sp_no,
                e.fname, e.lname, e.oname
            FROM fudscoops_withrawals w
            INNER JOIN fudscoops_member m ON w.member_id = m.member_id
            INNER JOIN employee e ON m.employee_id = e.employee_id
            WHERE w.chairman_approval = 1
            ORDER BY w.approved_date DESC, w.withrawals_id DESC
        ";
        $stmt = $con->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
     public function getTreasurerUploadedWithdrawals() {
         $db = new DB(); 
        $con = $db->getConnection();
        $query = "
            SELECT 
                w.*, 
                e.sp_no,
                e.fname, e.lname, e.oname
            FROM fudscoops_withrawals w
            INNER JOIN fudscoops_member m ON w.member_id = m.member_id
            INNER JOIN employee e ON m.employee_id = e.employee_id
            WHERE w.status = 1
            And w.chairman_approval= 0
            ORDER BY w.approved_date DESC, w.withrawals_id DESC
        ";
        $stmt = $con->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // In MemberG1.php
        public function getChairmanApprovedWithdrawalsByDate($date) {
        $db = new DB(); 
        $con = $db->getConnection();
            $query = "
                SELECT 
                    w.*, 
                    e.sp_no,
                    e.fname, 
                    e.lname, 
                    e.oname
                FROM fudscoops_withrawals w
                INNER JOIN fudscoops_member m ON w.member_id = m.member_id
                INNER JOIN employee e ON m.employee_id = e.employee_id
                WHERE w.chairman_approval = 1
                  AND DATE(w.approved_date) = :date
                ORDER BY w.withrawals_id DESC
            ";
            $stmt = $con->prepare($query);
            $stmt->bindParam(':date', $date, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
    public function getChairmanApprovedWithdrawalsLast7Days() {
             $db = new DB(); 
            $con = $db->getConnection();
            $query = "
                SELECT 
                    w.*, 
                    e.sp_no,
                    e.fname, 
                    e.lname, 
                    e.oname
                FROM fudscoops_withrawals w
                INNER JOIN fudscoops_member m ON w.member_id = m.member_id
                INNER JOIN employee e ON m.employee_id = e.employee_id
                WHERE w.chairman_approval = 1
                  AND w.approved_date >= CURDATE() - INTERVAL 7 DAY
                ORDER BY w.approved_date DESC, w.withrawals_id DESC
            ";
            $stmt = $con->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
         public function getTreasurerUploadedWithdrawalsLast7Days() {
             $db = new DB(); 
            $con = $db->getConnection();
            $query = "
                SELECT 
                    w.*, 
                    e.sp_no,
                    e.fname, 
                    e.lname, 
                    e.oname
                FROM fudscoops_withrawals w
                INNER JOIN fudscoops_member m ON w.member_id = m.member_id
                INNER JOIN employee e ON m.employee_id = e.employee_id
                WHERE w.status = 1
                 And w.chairman_approval= 0
                  AND w.approved_date >= CURDATE() - INTERVAL 7 DAY
                ORDER BY w.approved_date DESC, w.withrawals_id DESC
            ";
            $stmt = $con->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
     

        // Get APPROVED members (chairman_approval = 1)
        public function getChairmanApprovedMembers() {
             $db = new DB(); 
            $con = $db->getConnection();
            $sql = "
                SELECT m.*, e.sp_no, e.fname, e.lname, e.cadre, e.dept
                FROM fudscoops_member m
                INNER JOIN employee e ON m.employee_id = e.employee_id
                WHERE m.chairman_approval = 1
                ORDER BY e.fname, e.lname
            ";
            $stmt = $con->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
         // Get APPROVED members (chairman_approval = 1)
        public function getTreasurerApprovedMembers() {
             $db = new DB(); 
            $con = $db->getConnection();
            $sql = "
                SELECT m.*, e.sp_no, e.fname, e.lname, e.cadre, e.dept
                FROM fudscoops_member m
                INNER JOIN employee e ON m.employee_id = e.employee_id
                WHERE m.chairman_approval = 1 and m.treasurer_approval = 1
                ORDER BY e.fname, e.lname
            ";
            $stmt = $con->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // Get PENDING applicants (chairman_approval = 0)
        public function getMembershipApplicants() {
             $db = new DB(); 
            $con = $db->getConnection();
            $sql = "
                SELECT m.*, e.sp_no, e.fname, e.lname, e.cadre, e.dept
                FROM fudscoops_member m
                INNER JOIN employee e ON m.employee_id = e.employee_id
                WHERE m.chairman_approval = 0
                ORDER BY e.fname, e.lname
            ";
            $stmt = $con->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
         // Get PENDING applicants (chairman_approval = 0)
        public function getCharimanApprovedApplicants() {
             $db = new DB(); 
            $con = $db->getConnection();
            $sql = "
                SELECT m.*, e.sp_no, e.fname, e.lname, e.cadre, e.dept
                FROM fudscoops_member m
                INNER JOIN employee e ON m.employee_id = e.employee_id
                WHERE m.chairman_approval = 1 and treasurer_approval= 0
                ORDER BY e.fname, e.lname
            ";
            $stmt = $con->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // In MemberG1.php
        public function getRecentMemberWithdrawals($memberId, $limit = 5) {
             $db = new DB(); 
            $con = $db->getConnection();
            $sql = "
                SELECT 
                    approved_date,
                    approved_withrawal_amount,
                    bank_id,
                    chairman_approval
                FROM fudscoops_withrawals
                WHERE member_id = :member_id
                ORDER BY approved_date DESC, withrawals_id DESC
                LIMIT :limit
            ";
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':member_id', $memberId, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // In MemberG1.php
        public function getAllMemberWithdrawals($memberId) {
             $db = new DB(); 
            $con = $db->getConnection();
            $sql = "
                SELECT 
                    approved_date,
                    approved_withrawal_amount,
                    bank_id,
                    acount_number,
                    account_name,
                    chairman_approval
                FROM fudscoops_withrawals
                WHERE member_id = :member_id
                ORDER BY approved_date DESC, withrawals_id DESC
            ";
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':member_id', $memberId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    
    
}