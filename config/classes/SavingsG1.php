<?php
//session_start();
require_once('DB.php');
require_once('MemberG1.php');
require_once('User.php');

class SavingsG1{
    private $db;
    
    public function addSavings() {
        
    }
    
     public function secretaryApprovedCompleteWithdrawal($spNo, $amount) {
            
            $db = new DB();
            $user = new User();
            $con = $db->getConnection();
            $employeeid = $user->getEmployeeId($spNo);
            $status = 1;
            //echo($employeeid);
                    $sql = "UPDATE fudscoops_complete_withrawal
                            SET secretary_approval = :status, approved_amount = :amount
                            WHERE employee_id = :employeeid";
            
                    $stmt = $con->prepare($sql);
            
                    $stmt->bindParam(':status', $status);
                    $stmt->bindParam(':employeeid', $employeeid);
                    $stmt->bindParam(':amount', $amount);
            
                    $result = $stmt->execute();
            
                    if ($result) {
                        return 1;
                } else {
                    return -1;
                }
        
    }
    
           public function secretaryApprovedUpdateSavings($spNo) {
            $db = new DB();
            $user = new User();
            $con = $db->getConnection();
        
            $employeeid = $user->getEmployeeId($spNo);
            if (!$employeeid) return -1;
        
            $status = 1;
            $sql = "UPDATE fudscoops_update_savings 
                    SET is_secretary_approved = :status, approved_at = NOW() 
                    WHERE employee_id = :employeeid AND is_secretary_approved = 0";
        
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':status', $status, PDO::PARAM_INT);
            $stmt->bindParam(':employeeid', $employeeid, PDO::PARAM_STR);
        
            return $stmt->execute() ? 1 : -1;
        }
        // New method for rejection
        public function rejectUpdateSavings($spNo) {
            $db = new DB();
            $user = new User();
            $con = $db->getConnection();
            
            $employeeid = $user->getEmployeeId($spNo);
            if (!$employeeid) return -1;
        
            $sql = "UPDATE fudscoops_update_savings 
                    SET is_secretary_approved = -1, approved_at = NOW() 
                    WHERE employee_id = :employeeid AND is_secretary_approved = 0";
            
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':employeeid', $employeeid, PDO::PARAM_STR);
            
            return $stmt->execute() ? 1 : -1;
        }
        
        public function getChairmanApprovedSavingsUpdates() {
            $db = new DB();
            $user = new User();
            $con = $db->getConnection();
            $sql = "
                SELECT 
                    u.*, e.sp_no, e.fname, e.lname, e.dept
                FROM fudscoops_update_savings u
                INNER JOIN employee e ON u.employee_id = e.employee_id
                WHERE u.is_secretary_approved = 1 AND u.is_treasurer_approved = 0
                ORDER BY e.fname
            ";
            $stmt = $con->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    
    
    public function secretaryRejectUpdateSavings($spNo) {
            
            $db = new DB();
            $user = new User();
            $con = $db->getConnection();
            $employeeid = $user->getEmployeeId($spNo);
            $status = -1;
            //echo($employeeid);
                    $sql = "UPDATE fudscoops_update_savings
                            SET is_secretary_approved = :status
                            WHERE employee_id = :employeeid";
            
                    $stmt = $con->prepare($sql);
            
                    $stmt->bindParam(':status', $status);
                    $stmt->bindParam(':employeeid', $employeeid);
            
                    $result = $stmt->execute();
            
                    if ($result) {
                        return 1;
                } else {
                    return -1;
                }
        
    }
    
    
     public function secretaryRejectCompleteWithdrawal($spNo) {
            
            $db = new DB();
            $user = new User();
            $con = $db->getConnection();
            $employeeid = $user->getEmployeeId($spNo);
            $status = 2;
            //echo($employeeid);
                    $sql = "UPDATE fudscoops_complete_withrawal
                            SET secretary_approval = :status
                            WHERE employee_id = :employeeid";
            
                    $stmt = $con->prepare($sql);
            
                    $stmt->bindParam(':status', $status);
                    $stmt->bindParam(':employeeid', $employeeid);
            
                    $result = $stmt->execute();
            
                    if ($result) {
                        return 1;
                } else {
                    return -1;
                }
        
    }
     public function completeWithdrawal($spNo, $bank_id, $acct_no,$acct_name) {
         
 
        $db = new DB();
        $user = new User();
        $con = $db->getConnection();
        
        $employeeid = $user->getEmployeeId($spNo);
        
        $status = 1;
        $sql = "SELECT approved_amount 
                FROM fudscoops_complete_withrawal
                WHERE employee_id = :employeeid AND chairman_approval = 0";
                
                
        
        $stmt = $con->prepare($sql);
        
        $stmt->bindParam(':employeeid', $employeeid);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            return "You have a pending Complete withdraw request";
        } else {
            // If no pending approval, insert a new record
            $insert_sql = "INSERT INTO fudscoops_complete_withrawal (employee_id, bank_id, acount_number, account_name)
                           VALUES (:employeeid, :bankid, :acountnumber, :account_name)";
            
            $insert_stmt = $con->prepare($insert_sql);
            $insert_stmt->bindParam(':employeeid', $employeeid);
            $insert_stmt->bindParam(':bankid', $bank_id);
            $insert_stmt->bindParam(':acountnumber', $acct_no);
            $insert_stmt->bindParam(':account_name', $acct_name);
            
            $insert_result = $insert_stmt->execute();
            
            if ($insert_result) {
                return 1;
            } else {
                return "Faild contact system admin";
        }
    }
    }
    
    
    
    public function adduser($role, $user_name) {
         
 
        $db = new DB();
        $user = new User();
        $con = $db->getConnection();
        
        $employeeid = $user->getEmployeeId($spNo);
        
        $status = 1;
        $sql = "SELECT username 
                FROM user
                WHERE username = :user_name";
                
                
        
        $stmt = $con->prepare($sql);
        
        $stmt->bindParam(':user_name', $user_name);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            return "Username exist";
        } else {
            $password =md5($user_name);
            // If no pending approval, insert a new record
            $insert_sql = "INSERT INTO user (username, access_level, password)
                           VALUES (:user_name, :role, :password)";
            
            $insert_stmt = $con->prepare($insert_sql);
            $insert_stmt->bindParam(':user_name', $user_name);
            $insert_stmt->bindParam(':password', $password);
            $insert_stmt->bindParam(':role', $role);
            // $insert_stmt->bindParam(':acountnumber', $acct_no);
            
            $insert_result = $insert_stmt->execute();
            
            if ($insert_result) {
                return 1;
            } else {
                return "Faild contact system admin";
        }
    }
    }
    
    
    
    
    //used in updating saving of member 
   public function updateSavingsAmount($spNo, $update_amount) {
    $db = new DB();
    $user = new User();
    $con = $db->getConnection();
    $employeeid = $user->getEmployeeId($spNo);

    // Check if there is a pending approval
    $sql = "SELECT savings_update_amount
            FROM fudscoops_update_savings
            WHERE employee_id = :employeeid 
            AND is_secretary_approved = 0
            AND is_treasurer_approved=0";
    
    $stmt = $con->prepare($sql);
    $stmt->bindParam(':employeeid', $employeeid);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        // If a record is found, update the savings_update_amount with the new amount
        $update_sql = "UPDATE fudscoops_update_savings
                       SET savings_update_amount = :update_amount
                       WHERE employee_id = :employeeid AND is_secretary_approved = 0";
        
        $update_stmt = $con->prepare($update_sql);
        $update_stmt->bindParam(':update_amount', $update_amount);
        $update_stmt->bindParam(':employeeid', $employeeid);
        
        $update_result = $update_stmt->execute();
        
        if ($update_result) {
            return 1;
        } else {
            return -1;
        }
    } else {
        // If no pending approval, insert a new record
        $insert_sql = "INSERT INTO fudscoops_update_savings (employee_id, savings_update_amount, is_secretary_approved)
                       VALUES (:employeeid, :update_amount, 0)";
        
        $insert_stmt = $con->prepare($insert_sql);
        $insert_stmt->bindParam(':employeeid', $employeeid);
        $insert_stmt->bindParam(':update_amount', $update_amount);
        
        $insert_result = $insert_stmt->execute();
        
        if ($insert_result) {
            return 1;
        } else {
            return -1;
        }
    }
}

    //endosrement of loan by sec-gen
            
    public function secretaryLoanEndorsement($spNo) {
            try {
                $db = new DB();
                $user = new User();
                $member = new MemberG1();
                $con = $db->getConnection();
                
                $employeeid = $user->getEmployeeId($spNo);
                $memberid = $user->getMemberId($employeeid);
                $new_status = 1;
                
                $sql = "UPDATE fudscoops_withrawals
                        SET status = :new_status
                        WHERE member_id = :member_id AND status = 0"; // Corrected query
                
                $stmt = $con->prepare($sql);
                
                // Binding parameters with correct names
                $stmt->bindParam(':new_status', $new_status);
                $stmt->bindParam(':member_id', $memberid);
                
                $result = $stmt->execute();
                
                if ($result) {
                    return 1;
                } else {
                    return -1;
                }
            } catch (Exception $e) {
                // Handle any exceptions here
                return -1;
            }
        }
        
        public function fetchMembersSavings($start_date, $end_date) {
            try {
                $db = new DB();
                $con = $db->getConnection();
                
                // Query to fetch savings data with member and employee info
                $query = "SELECT 
                            s.savings_id, 
                            s.savings_amount, 
                            s.savings_date, 
                            s.month, 
                            s.year,
                            m.member_id,
                            m.proposed_monthly_savings,
                            e.employee_id,
                            e.sp_no,
                            e.fname,
                            e.lname,
                            e.oname,
                            e.dept,
                            ip.ippis_no
                          FROM 
                            fudscoops_savings s
                          JOIN 
                            fudscoops_member m ON s.member_idmember = m.member_id
                          JOIN 
                            employee e ON m.employee_id = e.employee_id
                            JOIN 
                            ippis_static_record ip ON m.employee_id = ip.employee_id
                          WHERE 
                            s.savings_date BETWEEN :start_date AND :end_date
                           
                            AND m.is_deleted = 0
                          ORDER BY 
                            s.savings_date DESC, e.lname, e.fname";
        
                $stmt = $con->prepare($query);
                $stmt->bindParam(':start_date', $start_date);
                $stmt->bindParam(':end_date', $end_date);
                $stmt->execute();
                $savings = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Calculate totals
                $total_savings = array_sum(array_column($savings, 'savings_amount'));
                
                // Generate HTML output
                if (empty($savings)) {
                    return '<div class="alert alert-warning">No savings records found for the selected period</div>';
                } else {
                    $html = '
                    <div class="table-responsive">
                        <table id="savingsDataTable" class="table table-striped table-hover display nowrap" style="width:100%">
                            <thead class="thead-dark">
                                <tr>
                                    <th>S/N</th>
                                    <th>Staff Number</th>
                                    <th>IPPIS Number</th>
                                    <th>Member Name</th>
                                   
                                    <th>Savings Date</th>
                                    <th>Month/Year</th>
                                    
                                    <th>Proposed Monthly</th>
                                    <th>Total Amount (₦)</th>
                                </tr>
                            </thead>
                            <tbody>';
                
                    $count = 1;
                    foreach ($savings as $saving) {
                        $html .= '<tr>
                            <td>'.$count++.'</td>
                            <td>'.htmlspecialchars($saving['sp_no']).'</td>
                            <td>'.htmlspecialchars($saving['ippis_no']).'</td>
                            <td>'.htmlspecialchars($saving['lname']).' '.htmlspecialchars($saving['fname']).' '.htmlspecialchars($saving['oname']).'</td>
                           
                            <td>'.date('d M, Y', strtotime($saving['savings_date'])).'</td>
                            <td>'.date('F Y', mktime(0, 0, 0, $saving['month'], 1, $saving['year'])).'</td>
                             <td class="text-right">'.number_format($saving['proposed_monthly_savings'], 2).'</td>
                            <td class="text-right">'.number_format($saving['savings_amount'], 2).'</td>
                           
                        </tr>';
                    }
                
                    $html .= '</tbody>
                            <tfoot>
                                <tr class="table-info">
                                    <th colspan="6" class="text-right font-weight-bold">Total Savings:</th>
                                    <th class="text-right font-weight-bold">₦'.number_format($total_savings, 2).'</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <script>
                    $(document).ready(function() {
                        $("#savingsDataTable").DataTable({
                            "responsive": true,
                            "lengthChange": true,
                            "autoWidth": true,
                            "searching": true,
                            "ordering": true,
                            "info": true,
                            "paging": true,
                            "pageLength": 25,
                            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                            "order": [[ 5, "desc" ]], // Order by savings date descending
                            "columnDefs": [
                                { "orderable": false, "targets": 0 }, // Disable ordering on S/N column
                                { "type": "date", "targets": 5 }, // Specify date type for savings date
                                { "type": "num-fmt", "targets": [6, 7] } // Number formatting for amount columns
                            ],
                            "language": {
                                "lengthMenu": "Show _MENU_ entries per page",
                                "zeroRecords": "No matching records found",
                                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                                "infoEmpty": "No entries available",
                                "infoFiltered": "(filtered from _MAX_ total entries)",
                                "search": "Search:",
                                "paginate": {
                                    "first": "First",
                                    "last": "Last",
                                    "next": "Next",
                                    "previous": "Previous"
                                }
                            },
                            "dom": "Bfrtip",
                            "buttons": [
                                "copy", "csv", "excel", "pdf", "print"
                            ]
                        });
                    });
                    </script>';
                
                    return $html;
                }
                
            } catch (PDOException $e) {
                error_log("Error in fetchMembersSavings: " . $e->getMessage());
                return '<div class="alert alert-danger">Error fetching savings data. Please try again later.</div>';
            }
        }
        
        // In SavingsG1.php
       public function updateTreasurerApprovedAmount($employeeId, $amount) {
            $db = new DB(); 
            $con = $db->getConnection();
        
            try {
                $con->beginTransaction();
        
                // Update only the latest secretary-approved record
                $sql1 = "
                    UPDATE fudscoops_update_savings 
                    SET treasurer_approved_amount = :amount,
                        is_treasurer_approved = 1,
                        approved_at = NOW()
                    WHERE id = (
                        SELECT id 
                        FROM fudscoops_update_savings 
                        WHERE employee_id = :employee_id 
                          AND is_secretary_approved = 1
                        ORDER BY id DESC 
                        LIMIT 1
                    )
                ";
                $stmt1 = $con->prepare($sql1);
                $stmt1->bindParam(':amount', $amount, PDO::PARAM_INT);
                $stmt1->bindParam(':employee_id', $employeeId, PDO::PARAM_STR);
                $stmt1->execute();
        
                if ($stmt1->rowCount() > 0) {
                    // Update proposed monthly savings in fudscoops_member
                    $sql2 = "
                        UPDATE fudscoops_member 
                        SET proposed_monthly_savings = :amount
                        WHERE employee_id = :employee_id
                    ";
                    $stmt2 = $con->prepare($sql2);
                    $stmt2->bindParam(':amount', $amount, PDO::PARAM_INT);
                    $stmt2->bindParam(':employee_id', $employeeId, PDO::PARAM_STR);
                    $stmt2->execute();
        
                    $con->commit();
                    return $stmt2->rowCount() > 0;
                } else {
                    $con->rollBack();
                    return false;
                }
            } catch (Exception $e) {
                $con->rollBack();
                error_log("Error in updateTreasurerApprovedAmount: " . $e->getMessage());
                return false;
            }
}


}
