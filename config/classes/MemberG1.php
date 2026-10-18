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
                fw.proposed_date,
                fw.bank_id,
                fw.acount_number,
                fw.account_name,
                fw.treasurer_comment,
                fw.treasurer_decided_by,
                fw.treasurer_decided_at,
                b.name AS bank_name,
                fw.member_id,
                fm.employee_id,
                e.sp_no,
                e.title,
                e.fname,
                e.lname,
                e.oname,
                e.dept,
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
            LEFT JOIN
                banks b ON b.id = fw.bank_id
            WHERE
                fw.status = '1'
                AND fw.chairman_approval = '0'
                AND fw.approved_withrawal_amount !=0
                AND COALESCE(fw.withrawals_is_deleted, 0) = 0
            ORDER BY fw.treasurer_decided_at, fw.withrawals_id;
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

// Membership application with the applicant's staff/payroll details, for the treasurer's review page.
// Returns null if the employee has no membership record.
public function getApplicantDetails($employeeId) {
    $con = (new DB())->getConnection();
    $stmt = $con->prepare("
        SELECT m.*, e.sp_no, e.title, e.fname, e.oname, e.lname, e.phone_no, e.email, e.cadre, e.dept,
               e.permanent_address, e.nature_of_appo, e.`rank`, e.grade_level, e.step
        FROM fudscoops_member m
        INNER JOIN employee e ON m.employee_id = e.employee_id
        WHERE m.employee_id = :employee_id");
    $stmt->bindParam(':employee_id', $employeeId, PDO::PARAM_INT);
    $stmt->execute();
    $applicant = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$applicant) {
        return null;
    }

    // Bank, account and grade come from the IPPIS payroll record
    $user = new User();
    $payroll = $user->getStaffInformation($applicant['sp_no']);
    $applicant['bank_name'] = is_array($payroll) ? $payroll['bank_name'] : '';
    $applicant['acctno'] = is_array($payroll) ? $payroll['acctno'] : '';
    $applicant['grade'] = is_array($payroll) ? $payroll['grade'] : '';
    if (empty($applicant['dept']) && is_array($payroll)) {
        $applicant['dept'] = $payroll['dept'];
    }
    return $applicant;
}

// Treasurer fixes the monthly savings for an applicant (step 1 of approval; the chairman authorizes next).
// Returns 1 on success, 0 if the applicant is not pending (already fully approved / not found),
// -2 if the amount is blank or below the minimum, -1 on database error.
public function treasurerApproveMembership($employeeId, $amount) {
    require_once('SavingsG1.php');
    $amount = SavingsG1::parseAmount($amount);
    if ($amount === null || $amount < SavingsG1::getMinMonthlySavings()) {
        return -2;
    }

    try {
        $con = (new DB())->getConnection();
        $sql = "UPDATE fudscoops_member
                SET proposed_monthly_savings = :amount, treasurer_approval = 1
                WHERE employee_id = :employee_id
                  AND NOT (chairman_approval = 1 AND treasurer_approval = 1)";
        $stmt = $con->prepare($sql);
        $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
        $stmt->bindParam(':employee_id', $employeeId, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return 1;
        }
        // rowCount is 0 when nothing changed, so check the applicant is still pending
        $check = $con->prepare("SELECT 1 FROM fudscoops_member WHERE employee_id = :employee_id
                                  AND treasurer_approval = 1 AND chairman_approval = 0");
        $check->bindParam(':employee_id', $employeeId, PDO::PARAM_INT);
        $check->execute();
        return $check->fetchColumn() ? 1 : 0;
    } catch (Exception $e) {
        error_log("Error in treasurerApproveMembership: " . $e->getMessage());
        return -1;
    }
}

// Chairman's decision on a membership application (step 2 of approval).
// Approval only succeeds once the treasurer has fixed the amount: returns -2 otherwise.
public function secretaryApproveRegisteration($spNo, $action, $comment) {
    $db = new DB();
    $con = $db->getConnection();
    $user = new User();
    $employeeid = $user->getEmployeeId($spNo);

    if ($action !== 'Proceed' && $action !== 'Decline') {
        return -1; // no decision button identified
    }

    // Begin a transaction to ensure both operations succeed or fail together
    $con->beginTransaction();

    try {
        // Determine the decision based on the action
        $decision = ($action === 'Proceed') ? 'Registration Approved' : 'Registration Rejected';

        if ($action === 'Proceed') {
            // The chairman authorizes the amount fixed by the treasurer, so the treasurer must have acted first
            $stmtCheck = $con->prepare("SELECT 1 FROM fudscoops_member
                                         WHERE employee_id = :employee_id AND treasurer_approval = 1 AND chairman_approval = 0");
            $stmtCheck->bindParam(':employee_id', $employeeid);
            $stmtCheck->execute();
            if (!$stmtCheck->fetchColumn()) {
                $con->rollBack();
                return -2;
            }
        }

        // Insert into the fudscoops_membership_decision table
        $sqlInsertDecision = "INSERT INTO fudscoops_membership_decision (employee_id, decision, comment, date) 
                              VALUES (:employee_id, :decision, :comment, :date)";
        $stmtInsertDecision = $con->prepare($sqlInsertDecision);
        $stmtInsertDecision->bindParam(':employee_id', $employeeid);
        $stmtInsertDecision->bindParam(':decision', $decision);
        $stmtInsertDecision->bindParam(':comment', $comment);
        $stmtInsertDecision->bindValue(':date', date('Y-m-d H:i:s'));
        $insertResult = $stmtInsertDecision->execute();

        if (!$insertResult) {
            throw new Exception("Failed to insert into fudscoops_membership_decision table.");
        }

        // If the action is 'Proceed' (Approved), update both tables
        if ($action === 'Proceed') {
            // Update fudscoops_member table
            $sqlUpdateMember = "UPDATE fudscoops_member
                                SET is_active = '1', chairman_approval = '1'
                                WHERE fudscoops_member.employee_id = :employee_id AND treasurer_approval = 1";
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



    
    // Treasurer approves an amount: the request goes to the Chairman (status 1). It can be changed until the Chairman decides.
    public function treasurerApproveWithdrawal($withrawals_id, $approved_amount, $comment = null, $by = null) {
        $db = new DB();
        $con = $db->getConnection();
        $user = new User();
        $state = $con->prepare("SELECT status, chairman_approval FROM fudscoops_withrawals WHERE withrawals_id = :id");
        $state->execute([':id' => $withrawals_id]);
        $state = $state->fetch(PDO::FETCH_ASSOC);
        if (!$state) {
            return 'Withdrawal request not found.';
        }
        if ((int) $state['chairman_approval'] !== 0 || (int) $state['status'] === -1) {
            return 'This request has already been decided.';
        }

        // Kobo allowed; the minimum savings balance must stay in the account (LoanControls)
        require_once('LoanControls.php');
        $approved_amount = preg_replace('/[^0-9.]/', '', (string) $approved_amount);
        if ($approved_amount === '' || !is_numeric($approved_amount) || (float) $approved_amount <= 0) {
            return 'Enter the approved amount.';
        }
        $approved_amount = (string) round((float) $approved_amount, 2);
        $who = $con->prepare("SELECT member_id FROM fudscoops_withrawals WHERE withrawals_id = :id");
        $who->execute([':id' => $withrawals_id]);
        if ($limit = LoanControls::withdrawalLimitError($who->fetchColumn(), $approved_amount)) {
            return $limit;
        }

        // status 1 puts it in the Chairman's list (it stayed 0 before, so approved requests never reached the Chairman)
        $sql = "UPDATE fudscoops_withrawals
                SET approved_withrawal_amount = :approved_amount, status = 1,
                    treasurer_comment = COALESCE(:comment, treasurer_comment),
                    treasurer_decided_by = COALESCE(:by, treasurer_decided_by), treasurer_decided_at = NOW()
                WHERE withrawals_id = :withrawals_id";

        $stmt = $con->prepare($sql);
        $stmt->bindParam(':withrawals_id', $withrawals_id, PDO::PARAM_INT);
        $stmt->bindParam(':approved_amount', $approved_amount, PDO::PARAM_STR);
        $comment = $comment === null ? null : trim((string) $comment);
        $stmt->bindValue(':comment', $comment === '' ? null : $comment);
        $stmt->bindValue(':by', $by);

        $secretaryApproveWithdrawal = $stmt->execute();

        if ($secretaryApproveWithdrawal) {
            return 1;
        } else {
            return -1;
        }
}

    // Treasurer rejects a savings withdrawal request (status -1); the reason is shown to the member. Returns 1 or a message.
    public function treasurerRejectWithdrawal($withrawals_id, $reason, $by) {
        $reason = trim((string) $reason);
        if ($reason === '') {
            return 'Give the reason for rejecting; the member will see it.';
        }
        $con = (new DB())->getConnection();
        $stmt = $con->prepare("UPDATE fudscoops_withrawals
                               SET status = -1, approved_withrawal_amount = 0, treasurer_comment = :c,
                                   treasurer_decided_by = :by, treasurer_decided_at = NOW()
                               WHERE withrawals_id = :id AND chairman_approval = 0 AND status IN (0, 1)");
        $stmt->execute([':c' => $reason, ':by' => $by, ':id' => $withrawals_id]);
        return $stmt->rowCount() ? 1 : 'This request has already been decided.';
}
  // Chairman approves a request the Treasurer approved (status 1): the amount leaves savings from now (approved_date). Returns 1 or a message.
  public function chairmanApproveWithdrawal($withrawals_id, $by = null) {
        $db = new DB();
        $con = $db->getConnection();

        // The minimum savings balance must still stay after this withdrawal (savings may have changed since)
        require_once('LoanControls.php');
        $w = $con->prepare("SELECT member_id, approved_withrawal_amount, status, chairman_approval FROM fudscoops_withrawals WHERE withrawals_id = :id");
        $w->execute([':id' => $withrawals_id]);
        $w = $w->fetch(PDO::FETCH_ASSOC);
        if (!$w) {
            return 'Withdrawal request not found.';
        }
        if ((int) $w['status'] !== 1 || (int) $w['chairman_approval'] !== 0 || (float) $w['approved_withrawal_amount'] <= 0) {
            return 'This request is not awaiting the Chairman.';
        }
        if ($limit = LoanControls::withdrawalLimitError($w['member_id'], $w['approved_withrawal_amount'])) {
            return 'Cannot approve. ' . $limit;
        }

        $stmt = $con->prepare("UPDATE fudscoops_withrawals
                               SET chairman_approval = 1, approved_date = NOW(), chairman_decided_by = :by, chairman_decided_at = NOW()
                               WHERE withrawals_id = :withrawals_id AND status = 1 AND chairman_approval = 0");
        $stmt->bindParam(':withrawals_id', $withrawals_id, PDO::PARAM_INT);
        $stmt->bindValue(':by', $by);
        if (!$stmt->execute()) {
            return -1;
        }
        return $stmt->rowCount() ? 1 : 'This request has already been decided.';
}

  // Chairman rejects a request awaiting them (chairman_approval -1); the reason is shown to the member. Returns 1 or a message.
  public function chairmanRejectWithdrawal($withrawals_id, $reason, $by = null) {
        $reason = trim((string) $reason);
        if ($reason === '') {
            return 'Give the reason for rejecting; the member will see it.';
        }
        $con = (new DB())->getConnection();
        $stmt = $con->prepare("UPDATE fudscoops_withrawals
                               SET chairman_approval = -1, chairman_comment = :c, chairman_decided_by = :by, chairman_decided_at = NOW()
                               WHERE withrawals_id = :id AND status = 1 AND chairman_approval = 0");
        $stmt->execute([':c' => $reason, ':by' => $by, ':id' => $withrawals_id]);
        return $stmt->rowCount() ? 1 : 'This request is not awaiting the Chairman.';
}


 // Treasurer sets the amount to pay a leaving member (it then goes to the Chairman; it can be changed until the Chairman decides).
 // $account: the 10-digit account number, required when the one on the request is missing (it was lost before 2026-10-17).
 // Returns 1, -1 or a message.
 public function treasurerApproveComWithdrawal($withrawals_id, $approved_amount, $comment = null, $by = null, $account = null) {
        $db = new DB();
        $con = $db->getConnection();

        // Amount paid out: savings less what is paid from savings for loans/commodity (a leaving member takes the rest)
        require_once('LoanControls.php');
        $approved_amount = preg_replace('/[^0-9.]/', '', (string) $approved_amount);
        if ($approved_amount === '' || !is_numeric($approved_amount) || (float) $approved_amount <= 0) {
            return 'Enter the amount to pay out.';
        }
        $approved_amount = (string) round((float) $approved_amount, 2);
        $req = $con->prepare("SELECT c.employee_id, c.settle_from_savings, c.chairman_approval, c.acount_number, m.member_id FROM fudscoops_complete_withrawal c
                              JOIN fudscoops_member m ON m.employee_id = c.employee_id WHERE c.comp_withdrwal_id = :id");
        $req->execute([':id' => $withrawals_id]);
        $req = $req->fetch(PDO::FETCH_ASSOC);
        if (!$req) {
            return 'Request not found.';
        }
        if ((int) $req['chairman_approval'] !== 0) {
            return 'This request has already been decided.';
        }
        $account = trim((string) $account);
        if ($account !== '' && !preg_match('/^\d{10}$/', $account)) {
            return 'Enter the 10-digit account number.';
        }
        if ($account === '' && !preg_match('/^\d{10}$/', (string) $req['acount_number'])) {
            return 'The account number on this request is missing. Enter the member\'s 10-digit account number.';
        }
        $ob = LoanControls::leavingObligations($req['member_id'], $req['employee_id'], LoanControls::savingsBalance($req['member_id']));
        if ($limit = LoanControls::withdrawalLimitError($req['member_id'], $approved_amount, $req['settle_from_savings'] ? $ob['total_owed'] : 0)) {
            return $limit;
        }

        $comment = trim((string) $comment);
        $stmt = $con->prepare("UPDATE fudscoops_complete_withrawal
                               SET approved_amount = :approved_amount,
                                   acount_number = COALESCE(:account, acount_number),
                                   treasurer_comment = COALESCE(:comment, treasurer_comment),
                                   treasurer_decided_by = COALESCE(:by, treasurer_decided_by), treasurer_decided_at = NOW()
                               WHERE comp_withdrwal_id = :withrawals_id AND chairman_approval = 0");
        $stmt->bindParam(':withrawals_id', $withrawals_id, PDO::PARAM_INT);
        $stmt->bindParam(':approved_amount', $approved_amount, PDO::PARAM_STR);
        $stmt->bindValue(':account', $account === '' ? null : $account);
        $stmt->bindValue(':comment', $comment === '' ? null : $comment);
        $stmt->bindValue(':by', $by);
        return $stmt->execute() ? 1 : -1;
}

 // Treasurer rejects a complete withdrawal request (chairman_approval -1); the reason is shown to the member,
 // who can apply again. Returns 1 or a message.
 public function treasurerRejectComWithdrawal($withrawals_id, $reason, $by) {
        $reason = trim((string) $reason);
        if ($reason === '') {
            return 'Give the reason for rejecting; the member will see it.';
        }
        $con = (new DB())->getConnection();
        $stmt = $con->prepare("UPDATE fudscoops_complete_withrawal
                               SET chairman_approval = -1, approved_amount = 0, treasurer_comment = :c,
                                   treasurer_decided_by = :by, treasurer_decided_at = NOW()
                               WHERE comp_withdrwal_id = :id AND chairman_approval = 0");
        $stmt->execute([':c' => $reason, ':by' => $by, ':id' => $withrawals_id]);
        return $stmt->rowCount() ? 1 : 'This request has already been decided.';
}

 // Every complete withdrawal request for the Treasurer's list (Treasurer/complete_withdrawals.php), newest first
 public function treasurerCompleteWithdrawals() {
        $con = (new DB())->getConnection();
        return $con->query("SELECT c.*, m.member_id, m.proposed_monthly_savings, e.sp_no, e.title, e.fname, e.lname, e.oname, e.dept, e.cadre,
                                   b.name AS bank_name
                            FROM fudscoops_complete_withrawal c
                            JOIN fudscoops_member m ON m.employee_id = c.employee_id
                            JOIN employee e ON e.employee_id = c.employee_id
                            LEFT JOIN banks b ON b.id = c.bank_id
                            WHERE COALESCE(c.complete_withrawal_is_deleted, 0) = 0
                            ORDER BY c.comp_withdrwal_id DESC")->fetchAll(PDO::FETCH_ASSOC);
}
  public function chairmanApproveComWithdrawal($withrawals_id) {
        $db = new DB();
        $con = $db->getConnection();
        $user = new User();

        // Check again what the member owes: a leaving member must owe nothing and guarantee no open loan.
        // If they chose to pay it from savings, post those payments now (LoanControls::settleFromSavings).
        require_once('LoanControls.php');
        $stmt = $con->prepare("SELECT c.*, e.sp_no, m.member_id FROM fudscoops_complete_withrawal c
                               JOIN employee e ON e.employee_id = c.employee_id
                               JOIN fudscoops_member m ON m.employee_id = c.employee_id
                               WHERE c.comp_withdrwal_id = :id AND c.chairman_approval = 0");
        $stmt->execute([':id' => $withrawals_id]);
        $request = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$request) {
            return 'This request is not awaiting approval.';
        }
        if ((float) $request['approved_amount'] <= 0) {
            return 'The Treasurer has not yet set the amount to pay.';
        }
        if (!preg_match('/^\d{10}$/', (string) $request['acount_number'])) {
            return 'The account number on this request is missing. The Treasurer must enter it first.';
        }
        $obligations = LoanControls::leavingObligations($request['member_id'], $request['employee_id'],
                                                        (float) $this->getCurrentSavingsAmount($request['sp_no']));
        if ($obligations['blockers']) {
            return 'Cannot approve: ' . implode(' ', $obligations['blockers']);
        }
        // The amount paid out cannot be more than the savings left after what is paid to loans/commodity
        if ($limit = LoanControls::withdrawalLimitError($request['member_id'], $request['approved_amount'],
                                                         $request['settle_from_savings'] ? $obligations['total_owed'] : 0)) {
            return 'Cannot approve. ' . $limit;
        }
        if ($obligations['total_owed'] > 0) {
            if (!$request['settle_from_savings']) {
                return 'Cannot approve: the member still owes ₦' . number_format($obligations['total_owed'], 2) . ' on loans or commodity.';
            }
            if (!$obligations['can_settle']) {
                return 'Cannot approve: the member\'s savings are less than the ₦' . number_format($obligations['total_owed'], 2) . ' they owe.';
            }
            $settled = LoanControls::settleFromSavings($obligations, isset($_SESSION['username']) ? $_SESSION['username'] : 'Chairman');
            if ($settled !== 1) {
                return 'Cannot approve: ' . $settled;
            }
            $con->prepare("UPDATE fudscoops_complete_withrawal SET amount_owed = :o, settled_at = NOW() WHERE comp_withdrwal_id = :id")
                ->execute([':o' => $obligations['total_owed'], ':id' => $withrawals_id]);
        }

        $sql = "UPDATE fudscoops_complete_withrawal SET chairman_approval = '1', date_approved = NOW(),
                       chairman_decided_by = :by, chairman_decided_at = NOW()
                WHERE fudscoops_complete_withrawal.comp_withdrwal_id = :withrawals_id";
    
        $stmt = $con->prepare($sql);
        $stmt->bindParam(':withrawals_id', $withrawals_id, PDO::PARAM_INT);
        $stmt->bindValue(':by', isset($_SESSION['username']) ? $_SESSION['username'] : null);
    
        $chairmanApproveWithdrawal = $stmt->execute();
    
        if ($chairmanApproveWithdrawal) {
            return 1;
        } else {
            return -1;
        }
}

  // Chairman rejects a complete withdrawal awaiting them (chairman_approval -1); the reason is shown to the member,
  // who can apply again. Returns 1 or a message.
  public function chairmanRejectComWithdrawal($withrawals_id, $reason, $by) {
        $reason = trim((string) $reason);
        if ($reason === '') {
            return 'Give the reason for rejecting; the member will see it.';
        }
        $con = (new DB())->getConnection();
        $stmt = $con->prepare("UPDATE fudscoops_complete_withrawal
                               SET chairman_approval = -1, chairman_comment = :c, chairman_decided_by = :by, chairman_decided_at = NOW()
                               WHERE comp_withdrwal_id = :id AND chairman_approval = 0 AND approved_amount > 0");
        $stmt->execute([':c' => $reason, ':by' => $by, ':id' => $withrawals_id]);
        return $stmt->rowCount() ? 1 : 'This request is not awaiting the Chairman.';
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

        // Kobo allowed; the minimum savings balance must stay in the account (LoanControls)
        require_once('LoanControls.php');
        $withrawals_amount = preg_replace('/[^0-9.]/', '', (string) $withrawals_amount);
        if ($withrawals_amount === '' || !is_numeric($withrawals_amount) || (float) $withrawals_amount <= 0) {
            return 'Enter the amount to withdraw.';
        }
        $withrawals_amount = round((float) $withrawals_amount, 2);
        if ($limit = LoanControls::withdrawalLimitError($member_id, $withrawals_amount)) {
            return $limit;
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
        
        // Approved loans only (a member can hold several loans; pending and declined ones are not borrowed money)
        $q = "SELECT SUM(COALESCE(NULLIF(amount_recommended, 0), loan_amount)) as totalloan FROM fudscoops_loan WHERE member_id = :member_id AND loan_status IN ('Disbursed', 'Approved', 'Repaid')";
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
            // plus approved complete withdrawals
            $stc = $con->prepare("SELECT (SELECT COALESCE(SUM(c.approved_amount + CASE WHEN c.settled_at IS NOT NULL THEN c.amount_owed ELSE 0 END), 0) FROM fudscoops_complete_withrawal c JOIN fudscoops_member cm ON cm.employee_id = c.employee_id WHERE cm.member_id = :member_id AND c.chairman_approval = 1 AND COALESCE(c.complete_withrawal_is_deleted, 0) = 0)");
            $stc->execute([':member_id' => $member_id]);
            $r = (float) $r + (float) $stc->fetchColumn();
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

    
            // Responses are always JSON: [1, spNo, monthly_savings] on success, [-1, message] on failure
            require_once('SavingsG1.php');
            $minimum = SavingsG1::getMinMonthlySavings();
            $monthly_savings = SavingsG1::parseAmount($monthly_savings);
            if ($monthly_savings === null || $monthly_savings < $minimum) {
                return json_encode([-1, 'Enter a proposed monthly savings of at least ₦' . number_format($minimum) . '.']);
            }

            $db = new DB();
            $user = new User();
            $con = $db->getConnection();
            $employeeid = $user->getEmployeeId($spNo);
            if (!$employeeid) {
                return json_encode([-1, 'Staff record not found. Contact system admin.']);
            }

            $sql ="SELECT chairman_approval, treasurer_approval FROM fudscoops_member WHERE employee_id=:employeeid";
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':employeeid', $employeeid, PDO::PARAM_INT);
            $stmt->execute();
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            if($existing){
              if ($existing['chairman_approval'] == 1 && $existing['treasurer_approval'] == 1) {
                  return json_encode([-1, 'Your membership has already been approved.']);
              }

              // Re-submitted application: the treasurer reviews the new amount again before the chairman
              $sql ="UPDATE fudscoops_member SET proposed_monthly_savings=:monthly_savings,next_of_kin_name=:next_of_kin_name,
              next_of_kin_gsm=:next_of_kin_gsm,next_of_kin_address=:next_of_kin_address, treasurer_approval=0
              WHERE employee_id=:employeeid";
              $stmt = $con->prepare($sql);
              $stmt->bindParam(':monthly_savings', $monthly_savings, PDO::PARAM_STR);
              $stmt->bindParam(':next_of_kin_name', $next_of_kin_name,PDO::PARAM_STR);
              $stmt->bindParam(':next_of_kin_gsm', $next_of_kin_gsm,PDO::PARAM_STR);
              $stmt->bindParam(':next_of_kin_address', $next_of_kin_address,PDO::PARAM_STR);
              $stmt->bindParam(':employeeid', $employeeid, PDO::PARAM_INT);
              if ($stmt->execute()) {
                    return json_encode([1, $spNo, $monthly_savings]);
                } else {
                    return json_encode([-1, 'Cannot register. Contact system admin.']);
                }

            }else{
             
            
                $sql = "INSERT INTO fudscoops_member(employee_Id, proposed_monthly_savings, next_of_kin_name, next_of_kin_gsm, next_of_kin_address)
                VALUES (:employeeid, :monthly_savings, :next_of_kin_name, :next_of_kin_gsm, :next_of_kin_address)";
                // Create a prepared statement
                $stmt = $con->prepare($sql);
                // Bind parameters
                $stmt->bindParam(':employeeid', $employeeid,PDO::PARAM_STR);
                $stmt->bindParam(':monthly_savings', $monthly_savings, PDO::PARAM_STR);
                $stmt->bindParam(':next_of_kin_name', $next_of_kin_name,PDO::PARAM_STR);
                $stmt->bindParam(':next_of_kin_gsm', $next_of_kin_gsm,PDO::PARAM_STR);
                $stmt->bindParam(':next_of_kin_address', $next_of_kin_address,PDO::PARAM_STR);
                // Execute the query
                    if ($stmt->execute()) {
                            return json_encode([1, $spNo, $monthly_savings]);  // Returning an array with the values
                    } else {
                        return json_encode([-1, 'Cannot register. Contact system admin.']);
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
        // Payslips for the last three months of this year
        $payslipLinks = '';
        for ($k = 1; $k <= 3; $k++) {
            $time = strtotime("first day of -$k month");
            if (date('Y', $time) == date('Y')) {
                $payslipLinks .= " <a class='ml-2' href='slip.php?legacy=" . urlencode($member_infor['sp_no']) . "&s=" . date('n', $time) . "'>" . date('F', $time) . "</a>";
            }
        }
        $department = htmlspecialchars($member_infor['dept']);
        $gsmNo = htmlspecialchars($member_infor['phone_no']);
        $bank = htmlspecialchars($member_infor['bank']);
        $acctNo = htmlspecialchars($member_infor['acct_no']);
        $address = htmlspecialchars($member_infor['permanent_address']);
        $appointmentType = htmlspecialchars($member_infor['nature_of_appo']);
        $proposed_monthly_savings = htmlspecialchars(number_format((float) $member_infor['proposed_monthly_savings'], 2));

        // The chairman authorizes the amount fixed by the treasurer
        $treasurerApproved = $member_infor['treasurer_approval'] == 1;
        $chairmanApproved = $member_infor['chairman_approval'] == 1;
        $savingsLabel = $treasurerApproved ? 'Monthly Savings (fixed by Treasurer)' : 'Proposed Monthly Saving (awaiting Treasurer)';
        if ($chairmanApproved && $treasurerApproved) {
            $decisionSection = "<div class='alert alert-success'>This membership has already been approved.</div>";
        } elseif (!$treasurerApproved) {
            $decisionSection = "<div class='alert alert-warning'>Awaiting the Treasurer to fix the monthly savings amount. The Chairman can authorize the application after that.</div>";
        } elseif (!isset($_SESSION['access_level']) || $_SESSION['access_level'] != 4) {
            $decisionSection = "<div class='alert alert-info'>Awaiting the Chairman's authorization.</div>";
        } else {
            $decisionSection = "
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
                            </form>";
        }
        
      
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
                        
                         <p><strong>$savingsLabel:</strong> &#8358; $proposed_monthly_savings</p>
                         
                         <hr>
                      
                        <p><strong>Payslips:</strong> $payslipLinks</p>
                        
                          <div class='form-section'>
                            $decisionSection
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
        $applicantName = htmlspecialchars($member_infor['fname'] . ' ' . $member_infor['lname']);
        $staffNo = htmlspecialchars($member_infor['sp_no']);
        $department = htmlspecialchars($member_infor['dept']);
        $gsmNo = htmlspecialchars($member_infor['phone_no']);
        $bank = htmlspecialchars(isset($member_infor['bank']) ? (string) $member_infor['bank'] : '');
        $acctNo = htmlspecialchars(isset($member_infor['acct_no']) ? (string) $member_infor['acct_no'] : '');
        $address = htmlspecialchars($member_infor['permanent_address']);
        $appointmentType = htmlspecialchars($member_infor['nature_of_appo']);
        $proposed_monthly_savings = htmlspecialchars($member_infor['proposed_monthly_savings']);

        $payslipLinks = '';
        for ($k = 1; $k <= 3; $k++) {
            $time = strtotime("first day of -$k month");
            if (date('Y', $time) == date('Y')) {
                $payslipLinks .= " <a class='ml-2' href='slip.php?legacy=" . urlencode($member_infor['sp_no']) . "&s=" . date('n', $time) . "'>" . date('F', $time) . "</a>";
            }
        }
        
      
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
                      
                        <p><strong>Payslips:</strong> $payslipLinks</p>
                        
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
        $applicantName = htmlspecialchars($member_infor['fname'] . ' ' . $member_infor['lname']);
        $staffNo = htmlspecialchars($member_infor['sp_no']);
        $department = htmlspecialchars($member_infor['dept']);
        $gsmNo = htmlspecialchars($member_infor['phone_no']);
        $bank = htmlspecialchars(isset($member_infor['bank']) ? (string) $member_infor['bank'] : '');
        $acctNo = htmlspecialchars(isset($member_infor['acct_no']) ? (string) $member_infor['acct_no'] : '');
        $address = htmlspecialchars($member_infor['permanent_address']);
        $appointmentType = htmlspecialchars($member_infor['nature_of_appo']);
        $proposed_monthly_savings = htmlspecialchars($member_infor['proposed_monthly_savings']);

        $payslipLinks = '';
        for ($k = 1; $k <= 3; $k++) {
            $time = strtotime("first day of -$k month");
            if (date('Y', $time) == date('Y')) {
                $payslipLinks .= " <a class='ml-2' href='slip.php?legacy=" . urlencode($member_infor['sp_no']) . "&s=" . date('n', $time) . "'>" . date('F', $time) . "</a>";
            }
        }
        
      
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
                      
                        <p><strong>Payslips:</strong> $payslipLinks</p>
                        
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
        $applicantName = htmlspecialchars($member_infor['fname'] . ' ' . $member_infor['lname']);
        $staffNo = htmlspecialchars($member_infor['sp_no']);
        $department = htmlspecialchars($member_infor['dept']);
        $gsmNo = htmlspecialchars($member_infor['phone_no']);
        $bank = htmlspecialchars(isset($member_infor['bank']) ? (string) $member_infor['bank'] : '');
        $acctNo = htmlspecialchars(isset($member_infor['acct_no']) ? (string) $member_infor['acct_no'] : '');
        $address = htmlspecialchars($member_infor['permanent_address']);
        $appointmentType = htmlspecialchars($member_infor['nature_of_appo']);
        $proposed_monthly_savings = htmlspecialchars($member_infor['proposed_monthly_savings']);

        // What the member owes or guarantees (LoanControls::leavingObligations), and whether they asked to pay it from savings
        require_once('LoanControls.php');
        $ob = LoanControls::leavingObligations($member_infor['member_id'], $member_infor['employee_id'], (float) $this->getCurrentSavingsAmount($member_infor['sp_no']));
        $req = $con->prepare("SELECT settle_from_savings FROM fudscoops_complete_withrawal WHERE comp_withdrwal_id = :id");
        $req->execute([':id' => $withrawals_id]);
        $settleChoice = (int) $req->fetchColumn();
        $obligationsHtml = '';
        $payslipLinks = '';
        for ($k = 1; $k <= 3; $k++) {
            $time = strtotime("first day of -$k month");
            if (date('Y', $time) == date('Y')) {
                $payslipLinks .= " <a class='ml-2' href='slip.php?legacy=" . urlencode($member_infor['sp_no']) . "&s=" . date('n', $time) . "'>" . date('F', $time) . "</a>";
            }
        }
        foreach ($ob['blockers'] as $b) {
            $obligationsHtml .= "<div class='alert alert-danger py-2 mb-2'>" . htmlspecialchars($b) . "</div>";
        }
        if ($ob['owed']) {
            $obligationsHtml .= "<p class='mb-1'><strong>Owed by the member:</strong></p><ul>";
            foreach ($ob['owed'] as $o) {
                $obligationsHtml .= "<li>" . htmlspecialchars($o['label']) . ": &#8358; " . number_format($o['balance'], 2) . "</li>";
            }
            $obligationsHtml .= "</ul><p><strong>" . ($settleChoice ? "The member chose to pay this from savings. It is recorded when the Chairman approves; pay out &#8358; " . number_format($ob['payout'], 2) . " (everything left; savings go to zero)." : "The member did not choose to pay this from savings; the Chairman cannot approve until it is repaid.") . "</strong></p>";
        } elseif (!$ob['blockers']) {
            $obligationsHtml .= "<p class='text-success'><strong>The member owes no loan or commodity and is not guaranteeing an open loan. Pay out &#8358; " . number_format($ob['payout'], 2) . " (all their savings).</strong></p>";
        }
        
      
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
                         $obligationsHtml
                         
                           <form class='com-withdrawal-decision-form'>
                         <label><strong>Approved Amount</strong></label> 
                          <input type='text' name='approved_amount' > 
                         <hr>
                      
                        <p><strong>Payslips:</strong> $payslipLinks</p>
                        
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
        // Approved members with their figures for the Treasurer's member list (Treasurer/member_list.php), in one query.
        // savings_balance uses the same sums as LoanControls::savingsBalance; loans_outstanding = loans being repaid
        // (total payable less repayments); commodity_outstanding = supplied commodity not yet repaid.
        public function treasurerMemberDirectory() {
            $con = (new DB())->getConnection();
            $sql = "
                SELECT m.member_id, m.employee_id, m.proposed_monthly_savings, m.reg_date, m.is_active,
                       e.sp_no, e.title, e.fname, e.lname, e.oname, e.cadre, e.dept, e.phone_no,
                       COALESCE(s.saved, 0) - COALESCE(w.withdrawn, 0) - COALESCE(c.withdrawn, 0) AS savings_balance,
                       COALESCE(s.last_saved, '') AS last_saved,
                       COALESCE(sh.shares, 0) AS shares,
                       COALESCE(l.open_loans, 0) AS open_loans,
                       COALESCE(l.outstanding, 0) AS loans_outstanding,
                       COALESCE(cm.outstanding, 0) AS commodity_outstanding
                FROM fudscoops_member m
                INNER JOIN employee e ON m.employee_id = e.employee_id
                LEFT JOIN (SELECT member_idmember AS member_id, SUM(savings_amount) AS saved,
                                  MAX(CONCAT(LPAD(year, 4, '0'), '-', LPAD(month, 2, '0'))) AS last_saved
                           FROM fudscoops_savings GROUP BY member_idmember) s ON s.member_id = m.member_id
                LEFT JOIN (SELECT member_id, SUM(approved_withrawal_amount) AS withdrawn
                           FROM fudscoops_withrawals WHERE chairman_approval = 1 GROUP BY member_id) w ON w.member_id = m.member_id
                LEFT JOIN (SELECT employee_id, SUM(approved_amount + CASE WHEN settled_at IS NOT NULL THEN amount_owed ELSE 0 END) AS withdrawn
                           FROM fudscoops_complete_withrawal WHERE chairman_approval = 1 AND COALESCE(complete_withrawal_is_deleted, 0) = 0
                           GROUP BY employee_id) c ON c.employee_id = m.employee_id
                LEFT JOIN (SELECT member_id, SUM(amount_paid) AS shares
                           FROM fudscoops_shares WHERE share_status = 1 AND shares_is_deleted = 0 GROUP BY member_id) sh ON sh.member_id = m.member_id
                LEFT JOIN (SELECT ln.member_id, COUNT(*) AS open_loans,
                                  SUM(GREATEST(0, COALESCE(ln.total_payable, NULLIF(ln.amount_recommended, 0), ln.loan_amount) - COALESCE(rp.paid, 0))) AS outstanding
                           FROM fudscoops_loan ln
                           LEFT JOIN (SELECT loan_id, SUM(amount_paid) AS paid FROM fudscoops_loan_repayments
                                      WHERE status = 1 AND COALESCE(is_deleted, 0) = 0 GROUP BY loan_id) rp ON rp.loan_id = ln.loan_id
                           WHERE ln.loan_status IN ('Disbursed', 'Approved')
                           GROUP BY ln.member_id) l ON l.member_id = m.member_id
                LEFT JOIN (SELECT a.member_id,
                                  SUM(GREATEST(0, COALESCE(a.total_payable, a.total_amount) - COALESCE(cr.paid, 0))) AS outstanding
                           FROM fudscoops_commodity_loan_applications a
                           LEFT JOIN (SELECT application_id, SUM(amount_paid) AS paid FROM fudscoops_commodity_repayments GROUP BY application_id) cr
                                  ON cr.application_id = a.application_id
                           WHERE a.status = 'supplied'
                           GROUP BY a.member_id) cm ON cm.member_id = m.member_id
                WHERE m.chairman_approval = 1 AND m.treasurer_approval = 1
                ORDER BY e.fname, e.lname
            ";
            return $con->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        }

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
        
        // Applicants awaiting the chairman's authorization: the treasurer has already fixed the amount
        public function getMembershipApplicants() {
             $db = new DB();
            $con = $db->getConnection();
            $sql = "
                SELECT m.*, e.sp_no, e.fname, e.lname, e.cadre, e.dept
                FROM fudscoops_member m
                INNER JOIN employee e ON m.employee_id = e.employee_id
                WHERE m.treasurer_approval = 1 AND m.chairman_approval = 0
                ORDER BY e.fname, e.lname
            ";
            $stmt = $con->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
         // Applicants the treasurer can still set the monthly savings for: everyone not yet fully approved.
         // treasurer_approval = 0 => awaiting the treasurer; treasurer_approval = 1 => awaiting the chairman
         // (the amount can still be changed until the chairman authorizes it).
        public function getTreasurerApplicants() {
             $db = new DB();
            $con = $db->getConnection();
            $sql = "
                SELECT m.*, e.sp_no, e.fname, e.lname, e.cadre, e.dept
                FROM fudscoops_member m
                INNER JOIN employee e ON m.employee_id = e.employee_id
                WHERE NOT (m.chairman_approval = 1 AND m.treasurer_approval = 1)
                ORDER BY m.treasurer_approval, e.fname, e.lname
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