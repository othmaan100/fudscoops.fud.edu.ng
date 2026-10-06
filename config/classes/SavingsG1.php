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
        
        // Requests approved by the chairman and awaiting the treasurer's approved amount.
        // Only each member's latest request is returned; earlier_requests counts the older pending
        // ones, which are superseded when the latest is approved.
        public function getChairmanApprovedSavingsUpdates() {
            $db = new DB();
            $con = $db->getConnection();
            $sql = "
                SELECT
                    u.id, u.employee_id, u.savings_update_amount, u.update_proposed_date,
                    e.sp_no, e.fname, e.lname, e.oname, e.dept,
                    m.proposed_monthly_savings,
                    (SELECT COUNT(*) FROM fudscoops_update_savings o
                      WHERE o.employee_id = u.employee_id AND o.id < u.id
                        AND o.is_secretary_approved = 1 AND o.is_treasurer_approved = 0) AS earlier_requests
                FROM fudscoops_update_savings u
                INNER JOIN employee e ON u.employee_id = e.employee_id
                INNER JOIN fudscoops_member m ON u.employee_id = m.employee_id
                WHERE u.is_secretary_approved = 1 AND u.is_treasurer_approved = 0
                  AND NOT EXISTS (SELECT 1 FROM fudscoops_update_savings n
                                   WHERE n.employee_id = u.employee_id AND n.id > u.id
                                     AND n.is_secretary_approved = 1 AND n.is_treasurer_approved = 0)
                ORDER BY e.fname, e.lname
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
    // Returns 1 on success, -1 on error, -2 if the amount is blank or below the minimum monthly savings
   public function updateSavingsAmount($spNo, $update_amount) {
    $update_amount = self::parseAmount($update_amount);
    if ($update_amount === null || $update_amount < self::getMinMonthlySavings()) {
        return -2;
    }

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
        
    // Mandatory minimum monthly savings. The treasurer can change it on Treasurer/savings_settings.php;
    // this default applies until the fudscoops_settings table has a value.
    const DEFAULT_MIN_MONTHLY_SAVINGS = 2000;

    public static function getMinMonthlySavings() {
        require_once('Settings.php');
        $value = (int) Settings::get('min_monthly_savings', self::DEFAULT_MIN_MONTHLY_SAVINGS);
        return $value > 0 ? $value : self::DEFAULT_MIN_MONTHLY_SAVINGS;
    }

    // Normalise an amount typed by the treasurer or read from a spreadsheet, e.g. "₦5,000.00" => 5000.
    // Savings are stored in whole naira. Returns null when the value is not a usable amount
    public static function parseAmount($value) {
        $clean = preg_replace('/[^0-9.]/', '', (string) $value);
        if ($clean === '' || !is_numeric($clean)) {
            return null;
        }
        return (int) round((float) $clean);
    }

    // Treasurer approves one savings update request (by fudscoops_update_savings.id) with the
    // final amount, and the member's monthly savings is updated to that amount. The member's
    // earlier pending requests are marked superseded (is_treasurer_approved = -1).
    // Returns 1 on success, 0 if the request is not awaiting the treasurer (already processed,
    // not found, or a newer request exists), -2 if the amount is invalid, -1 on database error.
    public function approveSavingsUpdateRequest($updateId, $amount) {
        $amount = self::parseAmount($amount);
        if ($amount === null || $amount < self::getMinMonthlySavings()) {
            return -2;
        }

        $db = new DB();
        $con = $db->getConnection();

        try {
            $con->beginTransaction();

            // Lock the request row so it cannot be approved twice
            $stmt = $con->prepare("
                SELECT u.employee_id FROM fudscoops_update_savings u
                WHERE u.id = :id AND u.is_secretary_approved = 1 AND u.is_treasurer_approved = 0
                  AND NOT EXISTS (SELECT 1 FROM fudscoops_update_savings n
                                   WHERE n.employee_id = u.employee_id AND n.id > u.id
                                     AND n.is_secretary_approved = 1 AND n.is_treasurer_approved = 0)
                FOR UPDATE
            ");
            $stmt->bindParam(':id', $updateId, PDO::PARAM_INT);
            $stmt->execute();
            $employeeId = $stmt->fetchColumn();

            if (!$employeeId) {
                $con->rollBack();
                return 0;
            }

            $stmt1 = $con->prepare("
                UPDATE fudscoops_update_savings
                SET treasurer_approved_amount = :amount,
                    is_treasurer_approved = 1,
                    approved_at = NOW()
                WHERE id = :id
            ");
            $stmt1->bindParam(':amount', $amount, PDO::PARAM_INT);
            $stmt1->bindParam(':id', $updateId, PDO::PARAM_INT);
            $stmt1->execute();

            // Earlier pending requests by the same member are superseded by this one
            $stmtOld = $con->prepare("
                UPDATE fudscoops_update_savings
                SET is_treasurer_approved = -1
                WHERE employee_id = :employee_id AND id < :id
                  AND is_secretary_approved = 1 AND is_treasurer_approved = 0
            ");
            $stmtOld->bindParam(':employee_id', $employeeId, PDO::PARAM_INT);
            $stmtOld->bindParam(':id', $updateId, PDO::PARAM_INT);
            $stmtOld->execute();

            $stmt2 = $con->prepare("
                UPDATE fudscoops_member
                SET proposed_monthly_savings = :amount, savings_amount_update = CURDATE()
                WHERE employee_id = :employee_id
            ");
            $stmt2->bindParam(':amount', $amount, PDO::PARAM_INT);
            $stmt2->bindParam(':employee_id', $employeeId, PDO::PARAM_STR);
            $stmt2->execute();

            $con->commit();
            return 1;
        } catch (Exception $e) {
            if ($con->inTransaction()) {
                $con->rollBack();
            }
            error_log("Error in approveSavingsUpdateRequest: " . $e->getMessage());
            return -1;
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

    // Column order of the CSV the treasurer downloads and uploads back
    public static function treasurerSavingsCsvHeader() {
        return ['Request ID', 'Staff Number', 'Name', 'Department', 'Date Requested', 'Current Monthly Savings', 'Requested Amount', 'Approved Amount'];
    }

    // Process the approved list uploaded by the treasurer (CSV in the downloaded format).
    // Each row is matched by Request ID and checked against the Staff Number; rows with a blank
    // Approved Amount are left pending. Returns ['approved' => n, 'skipped' => n, 'errors' => [..]]
    public function processTreasurerSavingsUpload($filePath) {
        $summary = ['approved' => 0, 'skipped' => 0, 'errors' => []];

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            $summary['errors'][] = 'Could not read the uploaded file.';
            return $summary;
        }

        $pending = [];
        foreach ($this->getChairmanApprovedSavingsUpdates() as $request) {
            $pending[(int) $request['id']] = $request;
        }

        // Excel may drop leading zeros from staff numbers, so compare without them
        $normaliseSp = function ($sp) {
            return ltrim(strtoupper(trim($sp)), '0');
        };

        $line = 0;
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $line++;
            // Strip a UTF-8 BOM that Excel may add to the first cell
            $data[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $data[0]);

            if (count(array_filter($data, 'strlen')) == 0) {
                continue; // empty row
            }
            if (!ctype_digit(trim($data[0]))) {
                continue; // header or note row
            }

            $updateId = (int) trim($data[0]);
            $spNo = isset($data[1]) ? $data[1] : '';
            $approvedAmount = isset($data[7]) ? trim($data[7]) : '';

            if ($approvedAmount === '') {
                $summary['skipped']++;
                continue;
            }
            if (!isset($pending[$updateId])) {
                $summary['errors'][] = "Row $line: request $updateId ($spNo) is not awaiting approval (already processed, not found, or replaced by a newer request from the member). Download a fresh list.";
                continue;
            }
            if ($normaliseSp($pending[$updateId]['sp_no']) !== $normaliseSp($spNo)) {
                $summary['errors'][] = "Row $line: staff number $spNo does not match request $updateId (" . $pending[$updateId]['sp_no'] . ").";
                continue;
            }

            $result = $this->approveSavingsUpdateRequest($updateId, $approvedAmount);
            if ($result === 1) {
                $summary['approved']++;
                unset($pending[$updateId]);
            } elseif ($result === -2) {
                $summary['errors'][] = "Row $line: invalid approved amount \"$approvedAmount\" for $spNo (minimum ₦" . number_format(self::getMinMonthlySavings()) . ").";
            } else {
                $summary['errors'][] = "Row $line: could not approve request $updateId for $spNo.";
            }
        }
        fclose($handle);

        return $summary;
    }


}
