<?php
// Check if session is not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once('DB.php');
require_once('Payslip.php');
class User{
    private $db;

/**
    public function login($user, $password)
    {
        $password = md5($password);
        // $type = substr($user,0,1);
        $db = new DB();
        $con = $db->getConnection();
        
            $sql = "SELECT * FROM user WHERE username=:username AND password=:password";
            $stm = $con->prepare($sql);
            //$password = md5($password);
            $stm->bindParam(':password', $password, PDO::PARAM_STR);
            $stm->bindParam(':username', $user, PDO::PARAM_STR);
            $stm->execute();
            $count = $stm->rowCount();
            $stmt = $stm ->fetchObject();
            if ($count>0) {
                $_SESSION['user_id'] = $stmt ->user_id;
                $_SESSION['username'] = $stmt ->username;
                $_SESSION['access_level'] = $stmt ->access_level;
                
                return $stmt ->access_level;
            }else
                return -1;
        
    } **/
    
    //Fetch Only the ID number of employee
    public function getEmployeeId($spNo)
    {
            
            $r = '';
             $db = new DB();
             $con = $db->getConnection();
        
            $q = "SELECT employee_id FROM employee
                    WHERE sp_no=:sp";
            $stm = $con->prepare($q);
            $stm->bindParam(':sp', $spNo, PDO::PARAM_STR);
            $stm->execute();
            $total_rows_found = $stm->rowCount();
            if ($total_rows_found > 0) {
                    $row = $stm->fetch(PDO::FETCH_ASSOC);
                        $r =  $row['employee_id'];                        
                    
            }else {
                $r = 0;
            }//end if ($total_rows_found == 1)
            $con=null;
            return $r;
    }
    
    public function getIPPIS_no($emp_id)
    {
            
            $r = '';
             $db = new DB();
             $con = $db->getConnection();
        
            $q = "SELECT ippis_no FROM ippis_static_record WHERE employee_id =:emp_id";
            $stm = $con->prepare($q);
            $stm->bindParam(':emp_id', $emp_id, PDO::PARAM_STR);
            $stm->execute();
            $total_rows_found = $stm->rowCount();
            if ($total_rows_found > 0) {
                    $row = $stm->fetch(PDO::FETCH_ASSOC);
                        $r =  $row['ippis_no'];                        
                    
            }else {
                $r = 0;
            }//end if ($total_rows_found == 1)
            $con=null;
            return $r;
    }
    
     public function getMemberId($employeeid)
    {
            $r = '';
             $db = new DB();
             $con = $db->getConnection();
        
            $q = "SELECT member_id FROM fudscoops_member
                    WHERE employee_id=:emp";
            $stm = $con->prepare($q);
            $stm->bindParam(':emp', $employeeid, PDO::PARAM_STR);
            $stm->execute();
            $total_rows_found = $stm->rowCount();
            if ($total_rows_found > 0) {
                    $row = $stm->fetch(PDO::FETCH_ASSOC);
                        $r =  $row['member_id'];                        
            }else {
                $r = 0;
            }//end if ($total_rows_found == 1)
            $con=null;
            return $r;
    }
    
     public function getShareReceiverMemberId($employeeid)
    {
            $r = '';
             $db = new DB();
             $con = $db->getConnection();
        
            $q = "SELECT member_id FROM fudscoops_shares_transfer t JOIN employee e
                ON t.received_employee_id=e.employee_id JOIN fudscoops_member m
                ON e.employee_id=m.employee_id WHERE e.employee_id=:emp";
            $stm = $con->prepare($q);
            $stm->bindParam(':emp', $employeeid, PDO::PARAM_STR);
            $stm->execute();
            $total_rows_found = $stm->rowCount();
            if ($total_rows_found > 0) {
                    $row = $stm->fetch(PDO::FETCH_ASSOC);
                        $r =  $row['member_id'];                        
            }else {
                $r = 0;
            }//end if ($total_rows_found == 1)
            $con=null;
            return $r;
    }
    
    public function getinActiveStatus($employeeid)
    {
        $result = 0;
        $db = new DB();
        $con = $db->getConnection();
    
        $query = "SELECT member_id FROM fudscoops_member
                    WHERE employee_id=:emp AND is_active=0 AND chairman_approval=0";
        $stm = $con->prepare($query);
        $stm->bindParam(':emp', $employeeid, PDO::PARAM_STR);
        $stm->execute();
        $total_rows_found = $stm->rowCount();
        
        if ($total_rows_found > 0) {
            $result = 1;
        }
    
        $con = null;
        return $result;
    }

    // public static function has_membership_application($employeeId, $url) {
    //     $db = new DB();
    //     $statusChecker = new self();
        
       
        
    //     if ((isset($_SESSION['access_level']) && $_SESSION['access_level'] == 0) && $statusChecker->getinActiveStatus($employeeId) == 1) {
    //         header('location:' . $url);
    //         exit();
    //     }
        
    //     return false;
    // }
    
    // public static function has_no_membership_application($employeeId, $url) {
    //     $db = new DB();
    //     $statusChecker = new self();
        
    //     if ((isset($_SESSION['access_level']) && $_SESSION['access_level'] == 0) && $statusChecker->getinActiveStatus($employeeId) == 0) {
    //         header('location:' . $url);
    //         exit();
    //     }
        
    //     return false;
    // }
    
    
    public static function check_membership_application($employeeId, $url, $checkActive)
    {
        $db = new DB();
        $statusChecker = new self();
        
        $inactiveStatus = $statusChecker->getinActiveStatus($employeeId);
    
        if (isset($_SESSION['access_level']) && $_SESSION['access_level'] == 0 && 
            (($checkActive && $inactiveStatus == 1) || (!$checkActive && $inactiveStatus == 0))) 
        {
            header('location:' . $url);
            exit();
        }
        
        return false;
    }

    
    
    public function getShareId($memberid)
    {
            
            $r = '';
             $db = new DB();
             $con = $db->getConnection();
        
            $q = "SELECT shares_id FROM fudscoops_shares
                    WHERE member_id=:mem";
            $stm = $con->prepare($q);
            $stm->bindParam(':mem', $memberid, PDO::PARAM_STR);
            $stm->execute();
            $total_rows_found = $stm->rowCount();
            if ($total_rows_found > 0) {
                    $row = $stm->fetch(PDO::FETCH_ASSOC);
                        $r =  $row['shares_id'];                        
                    
            }else {
                $r = 0;
            }//end if ($total_rows_found == 1)
            $con=null;
            return $r;
    }



//   public function login($username, $password) {
//     $hashedPassword = md5($password); // Consider using a stronger hashing algorithm, like password_hash
    
//     $db = new DB();
//     $luser = new User();
//     $con = $db->getConnection();
    
//     $sql = "SELECT * FROM user WHERE username = :username AND password = :password";
//     $stmt = $con->prepare($sql);
//     $stmt->bindParam(':username', $username, PDO::PARAM_STR);
//     $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
    
//     $stmt->execute();
//     $user = $stmt->fetch(PDO::FETCH_OBJ);
    
//     if ($user) {
        
//         $employee_id = $luser->getEmployeeId($username);
//       // $sql2 = "SELECT * FROM employee WHERE sp_no = :username";
//         $sql2 = "SELECT * FROM fudscoops_member WHERE employee_id = :username AND secretary_approval = '1'";
//         $stmt2 = $con->prepare($sql2);
//         $stmt2->bindParam(':username', $employee_id, PDO::PARAM_STR);
//         $stmt2->execute();
//         $memberCount = $stmt2->rowCount();

//         if ($memberCount > 0) {
//             $_SESSION['user_id'] = $user->user_id;
//             $_SESSION['username'] = $user->username;
//             $_SESSION['access_level'] = $user->access_level;

//             return $user->access_level;
//         } else {
//             $_SESSION['access_level'] =0;
//             return  $_SESSION['access_level']; // User is not a member of fudscoops_member
//         }
//     }

//     return -1; // Invalid login
// }

public function login($username, $password) {
    $hashedPassword = md5($password); // Consider using a stronger hashing algorithm, like password_hash
    
    $db = new DB();
    $luser = new User();
    $con = $db->getConnection();
    
    $sql = "SELECT * FROM user WHERE username = :username AND password = :password";
    $stmt = $con->prepare($sql);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
    
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_OBJ);
    
    if ($user) {
        // Store user info in session
        $_SESSION['user_id'] = $user->user_id;
        $_SESSION['username'] = $user->username;
        $_SESSION['access_level'] = $user->access_level;
        
        // Check if access code is 0 or 1
        if ($user->access_level == 0 || $user->access_level == 1) {
            $employee_id = $luser->getEmployeeId($username);
            $sql2 = "SELECT * FROM fudscoops_member WHERE employee_id = :username AND chairman_approval = '1'  AND treasurer_approval= '1'";
            $stmt2 = $con->prepare($sql2);
            $stmt2->bindParam(':username', $employee_id, PDO::PARAM_STR);
            $stmt2->execute();
            $memberCount = $stmt2->rowCount();

            if ($memberCount > 0) {
                return $user->access_level;
            } else {
                $_SESSION['access_level'] = 0;
                return $_SESSION['access_level']; // User is not a member of fudscoops_member
            }
        } else {
            // Return the access level directly if it's not 0 or 1
            return $user->access_level;
        }
    }

    return -1; // Invalid login
}
    
    public function resetPassword($username) 
    {
        $db = new DB();
        $con = $db->getConnection();
    
        $username = strtoupper(trim($username));
        $password = md5($username);
    
        /* Step 1: Check if username exists */
        $check = "SELECT username FROM user WHERE username = :username LIMIT 1";
        $stm = $con->prepare($check);
        $stm->bindParam(':username', $username, PDO::PARAM_STR);
        $stm->execute();
    
        if ($stm->rowCount() == 0) {
            return 0; // Username does not exist
        }
    
        /* Step 2: Reset password */
        $sql = "UPDATE user SET password = :password WHERE username = :username";
        $stm2 = $con->prepare($sql);
        $stm2->bindParam(':password', $password, PDO::PARAM_STR);
        $stm2->bindParam(':username', $username, PDO::PARAM_STR);
    
        if (!$stm2->execute()) {
            return -1; // SQL error
        }
    
        return 1; // Password successfully reset
    }

    public function staffId($sp)
    {
            $db = new DB();
            $r = '';
            $con = $db->getConnection();
            $q = "SELECT employee_id FROM employee WHERE sp_no LIKE '%/".$sp."'";
            $stm = $con->prepare($q);
            // $stm->bindParam(':sp', $sp, PDO::PARAM_STR);
            $stm->execute();
            $total_rows_found = $stm->rowCount();
            
            if ($total_rows_found > 0) {
                    $row = $stm->fetch(PDO::FETCH_ASSOC);
                        $r =  $row['employee_id'];                        
                    
            }else {
                $r = 0;
            }//end if ($total_rows_found == 1)
            $con=null;
            return $r;
    } 
    public function isStaffExist($sp)
    {
            
            $r = '';
            $con = $this->getConnection();
            $q = "SELECT employee_id FROM employee e                 
                  WHERE e.sp_no=:sp";
            $stm = $con->prepare($q);
            $stm->bindParam(':sp', $sp, PDO::PARAM_STR);
            $stm->execute();
            $total_rows_found = $stm->rowCount();
            if ($total_rows_found > 0) {
                    $row = $stm->fetch(PDO::FETCH_ASSOC);
                        $r =  $row['employee_id'];                        
                    
            }else {
                $r = 0;
            }//end if ($total_rows_found == 1)
            $con=null;
            return $r;
    } 
    public function getStaffInfoArray($sp)
    {
            $db = new DB();
            $user = new User();

            $r = '';
            $con = $db->getConnection();
            $array = array();
            $q = "SELECT * FROM employee e 
                    WHERE e.sp_no='$sp'";
            $stm = $con->prepare($q);
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
    
    public function getStaffInformation($staffno){
        
            $db = new DB();
            $user = new User();

            $r = '';
            $con = $db->getConnection();
           
            

            $array = array();
            
            
        $sql = "SELECT DISTINCT(employee.sp_no), employee.employee_id, ippis_nonstatic_record.dept, employee.sp_no, employee.fname,employee.oname, 
                employee.lname, employee.phone_no, employee.cadre, employee.title, ippis_nonstatic_record.bank_name, ippis_nonstatic_record.acctno, 
                ippis_nonstatic_record.grade, employee.permanent_address, employee.nature_of_appo 
                FROM employee INNER JOIN ippis_static_record ON employee.employee_id=ippis_static_record.employee_id 
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
    
    public function getStaffInformationByEmployeeId($employee_id){
        
            $db = new DB();
            $user = new User();

            $r = '';
            $con = $db->getConnection();
           
            

            $array = array();
            
            
        $sql = "SELECT DISTINCT(employee.sp_no), ippis_nonstatic_record.dept, employee.sp_no, employee.fname,employee.oname, employee.lname, employee.phone_no, 
                ippis_nonstatic_record.bank_name, ippis_nonstatic_record.acctno, ippis_nonstatic_record.grade, employee.permanent_address, employee.nature_of_appo 
                FROM employee INNER JOIN ippis_static_record ON employee.employee_id=ippis_static_record.employee_id 
                INNER JOIN ippis_nonstatic_record ON ippis_static_record.row_id=ippis_nonstatic_record.ippis_static_id 
                WHERE employee.employee_id='$employee_id' LIMIT 1";
                
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
    
    public function getStaffInformationByMemberId($member_id){
        
            $db = new DB();
            $user = new User();

            $r = '';
            $con = $db->getConnection();
           
            

            $array = array();
            
            
        $sql = "SELECT DISTINCT(employee.sp_no), ippis_nonstatic_record.dept, employee.sp_no, employee.fname,employee.oname, employee.lname, employee.phone_no, 
                ippis_nonstatic_record.bank_name, ippis_nonstatic_record.acctno, ippis_nonstatic_record.grade, employee.permanent_address, employee.nature_of_appo 
                FROM employee INNER JOIN ippis_static_record ON employee.employee_id=ippis_static_record.employee_id 
                INNER JOIN ippis_nonstatic_record ON ippis_static_record.row_id=ippis_nonstatic_record.ippis_static_id 
                INNER JOIN fudscoops_member m ON m.employee_id= employee.employee_id
                WHERE m.member_id='$member_id' LIMIT 1";
                
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
    
     public function getMemberShareInformation($memberid){
        
            $db = new DB();
            $user = new User();

            $r = '';
            $con = $db->getConnection();
            
            $array = array();
            
            $sql = "SELECT DISTINCT(sh.member_id), sh.unit_price, b.name AS bank, sh.bank_id, sh.teller_no, 
                    sh.amount_paid,sh.share_status,sh.shares_amount_word,sh.date,m.*,e.* FROM fudscoops_shares sh INNER JOIN banks b 
                    ON sh.bank_id=b.id INNER JOIN fudscoops_member m ON sh.member_id=m.member_id INNER JOIN employee e ON
                    m.employee_id=e.employee_id WHERE sh.member_id='$memberid'";
                
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
    
    public function updateAdminBio($username,$firstname,$othernames,$lastname,$gender,$dob,$state,$lga,$phone,$email,$address)
    
    {

        $user = new User();

        $r = '';
        $con = $this->getConnection();
        $status = 1;
        $array = array();
        $q = "UPDATE candidate_biodata SET
                    gender=:gender,dob =:dob,
                    state=:state,lga=:lga,phone=:phone,othernames=:oname,
                    email=:email,firstname=:fname,lastname=:lname,
                    address=:address,status=:status
                    WHERE email='$username'";
        $stm = $con->prepare($q);
        $stm->bindParam(':gender', $gender, PDO::PARAM_STR);
        $stm->bindParam(':dob', $dob, PDO::PARAM_STR);
        $stm->bindParam(':oname',$othernames,PDO::PARAM_STR);
        $stm->bindParam(':fname',$firstname,PDO::PARAM_STR);
        $stm->bindParam(':lname',$lastname,PDO::PARAM_STR);
        $stm->bindParam(':state', $state, PDO::PARAM_INT);
        $stm->bindParam(':lga', $lga, PDO::PARAM_INT);
        $stm->bindParam(':phone', $phone, PDO::PARAM_STR);
        $stm->bindParam(':email', $email, PDO::PARAM_STR);
        $stm->bindParam(':status', $status, PDO::PARAM_INT);
        $stm->bindParam(':address', $address, PDO::PARAM_STR);
        $stm->execute();
        $q2 = "UPDATE login 
               SET username = :newemail
               WHERE username = :oldusername";
        $stm2 = $con->prepare($q2);
        $stm2->bindParam(':newemail', $email,PDO::PARAM_STR);
        $stm2->bindParam(':oldusername', $username,PDO::PARAM_STR);
        $stm2->execute();
        if ($stm && $stm2)
        {
            return 1;
        }
        else
        {
            return -1;
        } //end if  
    }

    
     public function getShareBankName($memberid)
     {
            //$db = new DB();
            $db = new DB();
            $r = '';
            $con = $db->getConnection();
            $q = "SELECT name FROM fudscoops_shares sh JOIN banks b ON sh.bank_id = b.id
                    WHERE sh.member_id='$memberid'";
            $stm = $con->prepare($q);
            $stm->execute();
            $total_rows_found = $stm->rowCount();
            if ($total_rows_found > 0) {
                    $row = $stm->fetch(PDO::FETCH_ASSOC);
                    $bankname = $row['name'];
                   
                    $r =  $bankname;
                } else {
                $r = "";
            }//end if ($total_rows_found == 1)
            //$con=null;
            return $r;
    }
    
    
    public function getShareBankByMemberId($memberId)
    {
            
            $r = '';
             $db = new DB();
             $con = $db->getConnection();
        
            $q = "SELECT name AS Name FROM banks b INNER JOIN fudscoops_shares s 
                  ON b.id=s.bank_id INNER JOIN fudscoops_member m ON s.member_id=m.member_id 
                  INNER JOIN employee e ON m.employee_id=e.employee_id WHERE m.member_id='$memberId'";
            $stm = $con->prepare($q);
            $stm->execute();
            $total_rows_found = $stm->rowCount();
            if ($total_rows_found > 0) {
                    $row = $stm->fetch(PDO::FETCH_ASSOC);
                        $r =  $row['Name'];                        
                    
            }else {
                $r = 0;
            }//end if ($total_rows_found == 1)
            $con=null;
            return $r;
    }
    
    
    
     public function getShareUnitInfoArray()
    {
            
            $user = new User();
            $db = new DB();

            $r = '';
            $con = $db->getConnection();

            $array = array();
            $q = "SELECT * FROM fudscoops_share_unit";
            $stm = $con->prepare($q);
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
    
     public function getShareUnitAmount()
    {
            
            $user = new User();
            $db = new DB();

            $r = '';
            $con = $db->getConnection();

            $array = array();
            $q = "SELECT price FROM fudscoops_share_unit";
            $stm = $con->prepare($q);
            $stm->execute();
            $total_rows_found = $stm->rowCount();
            if ($total_rows_found >0) {
                    $row = $stm->fetch(PDO::FETCH_ASSOC);
                    $r =  $row['price'];
            } else {
                return 0;
            }//end if 
            $con=null;
            return $r;
    }
    
     public function getShareAmount($spNo)
    {
            
            $r = '';
             $db = new DB();
             $con = $db->getConnection();
        
            $q = "SELECT amount_paid AS Amount FROM fudscoops_shares s INNER JOIN 
                   fudscoops_member m ON s.member_id=m.member_id INNER JOIN employee e ON 
                   m.employee_id=e.employee_id WHERE e.sp_no='$spNo'";
            $stm = $con->prepare($q);
            $stm->execute();
            $total_rows_found = $stm->rowCount();
            if ($total_rows_found > 0) {
                    $row = $stm->fetch(PDO::FETCH_ASSOC);
                        $r =  $row['Amount'];                        
                    
            }else {
                $r = 0;
            }//end if ($total_rows_found == 1)
            $con=null;
            return $r;
    }
     public function getShareAmountByMemberId($memberID)
    {
            
            $r = '';
            $db = new DB();
            $con = $db->getConnection();
        
            $q = "SELECT amount_paid AS Amount FROM fudscoops_shares s INNER JOIN 
                   fudscoops_member m ON s.member_id=m.member_id INNER JOIN employee e ON 
                   m.employee_id=e.employee_id WHERE m.member_id='$memberID'";
            $stm = $con->prepare($q);
            $stm->execute();
            $total_rows_found = $stm->rowCount();
            if ($total_rows_found > 0) {
                    $row = $stm->fetch(PDO::FETCH_ASSOC);
                        $r =  $row['Amount'];                        
                    
            }else {
                $r = 0;
            }//end if ($total_rows_found == 1)
            $con=null;
            return $r;
    }
    
      public function getShareUnitPrice($spNo)
    {
            
            $r = '';
             $db = new DB();
             $con = $db->getConnection();
        
            $q = "SELECT unit_price AS Unitprice FROM fudscoops_shares s INNER JOIN 
                   fudscoops_member m ON s.member_id=m.member_id INNER JOIN employee e ON 
                   m.employee_id=e.employee_id WHERE e.sp_no='$spNo'";
            $stm = $con->prepare($q);
            $stm->execute();
            $total_rows_found = $stm->rowCount();
            if ($total_rows_found > 0) {
                    $row = $stm->fetch(PDO::FETCH_ASSOC);
                        $r =  $row['Unitprice'];                        
                    
            }else {
                $r = 0;
            }//end if ($total_rows_found == 1)
            $con=null;
            return $r;
    }
    
    public function getShareReceiverInfo($employeeid){
        
        $r = '';
        $db = new DB();
        $con = $db->getConnection();
        
        $array = array();
            
            $sql = "SELECT st.received_employee_id, st.worth, st.shares_id, st.amount_word AS amount, st.transfer_employee_id,e.* 
                    FROM fudscoops_shares_transfer st INNER JOIN employee e 
                    ON st.received_employee_id=e.employee_id WHERE st.received_employee_id='$employeeid'";
                
            $stm = $con->prepare($sql);
            $stm->bindParam(':employeeid', $employeeid, PDO::PARAM_INT);
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
    
   
    public function numberToWords($worth) {
        $ones = array(
            0 => "", 1 => "One", 2 => "Two", 3 => "Three", 4 => "Four", 5 => "Five", 6 => "Six", 
            7 => "Seven", 8 => "Eight", 9 => "Nine", 10 => "Ten", 11 => "Eleven", 12 => "Twelve", 
            13 => "Thirteen", 14 => "Fourteen", 15 => "Fifteen", 16 => "Sixteen", 17 => "Seventeen", 
            18 => "Eighteen", 19 => "Nineteen"
        );
    
        $tens = array(
            2 => "Twenty", 3 => "Thirty", 4 => "Forty", 5 => "Fifty", 
            6 => "Sixty", 7 => "Seventy", 8 => "Eighty", 9 => "Ninety"
        );
    
        $scales = array(
            1 => "Thousand", 2 => "Million", 3 => "Billion", 4 => "Trillion"
        );
    
        if ($worth == 0) {
            return "Zero Naira Only";
        }
    
        $words = [];
    
        // Process hundreds separately
        $processChunk = function ($num) use ($ones, $tens) {
            $chunkWords = "";
            if ($num >= 100) {
                $chunkWords .= $ones[intval($num / 100)] . " Hundred";
                $num %= 100;
                if ($num > 0) {
                    $chunkWords .= " and ";
                }
            }
            if ($num >= 20) {
                $chunkWords .= $tens[intval($num / 10)];
                if ($num % 10 > 0) {
                    $chunkWords .= "-" . $ones[$num % 10];
                }
            } elseif ($num > 0) {
                $chunkWords .= $ones[$num];
            }
            return $chunkWords;
        };
    
        $scaleIndex = 0;
        while ($worth > 0) {
            $chunk = $worth % 1000;
            if ($chunk > 0) {
                $chunkWords = $processChunk($chunk);
                if ($scaleIndex > 0) {
                    $chunkWords .= " " . $scales[$scaleIndex];
                }
                array_unshift($words, $chunkWords); // Add words in correct order
            }
            $worth = intval($worth / 1000);
            $scaleIndex++;
        }
    
        return implode(" ", $words) . " Naira Only";
    }

    
    public function convertLegacy($legacy)
    {
        $legacy = trim($legacy);
        $prefix = substr($legacy,0,2);
        $r = substr($legacy,2,1);
        $sn = substr($legacy,3);
        if ($legacy == '') {
            return -1;
        }else
            return $prefix.'/'.$r.'/'.$sn;
    }
    public function getFullname($sp)
    {
            $db = new DB();
            $r = '';
            $con = $db->getConnection();
            $q = "SELECT fname,lname,oname,title FROM employee 
            
                    WHERE sp_no=:sp";
            $stm = $con->prepare($q);
            $stm->bindParam(':sp', $sp, PDO::PARAM_STR);
            $stm->execute();
            $total_rows_found = $stm->rowCount();
            if ($total_rows_found > 0) {
                    $row = $stm->fetch(PDO::FETCH_ASSOC);
                        $r =  $row['title']." ".strtoupper($row['lname'])." ".$row['fname']." ".$row['oname'];                        
                    
            }else {
                $r = "<h4 class='text-danger'>No data found</h4>";
            }//end if ($total_rows_found == 1)
            $con=null;
            return $r;
    } 
    public static function is_authenticated($url) {
        if(isset($_SESSION) && isset($_SESSION['username'])){
            return true;
        }
        else{
            header('location:'.$url);
        }
    }
    
    //  public static function has_no_membership_application($url) {
    //     if (isset($_SESSION['access_level']) && $_SESSION['access_level'] == 0) {
    //         header('Location: ' . $url);
    //         exit(); // Ensure no further code is executed after redirection
    //     }
    //     return true;
    // }

    
    
    
    public static function isStaff($url) {
        if (isset($_SESSION['access_level']) && $_SESSION['access_level'] == 1) {
            return true;
        }else {
            header('location:'.$url);
        }
    }
    
   
    public static function isBur($url) {
        if (isset($_SESSION['access_level']) && $_SESSION['access_level'] == 3) {
            return true;
        }else {
            header('location:'.$url);
        }
    }
    public static function isSecretary($url) {
        if (isset($_SESSION['access_level']) && $_SESSION['access_level'] == 2) {
            return true;
        }else {
            header('location:'.$url);
        }
    }
     public static function isChairman($url) {
        if (isset($_SESSION['access_level']) && $_SESSION['access_level'] == 4) {
            return true;
        }else {
            header('location:'.$url);
        }
    }
     public static function isNonM($url) {
        if (isset($_SESSION['access_level']) && $_SESSION['access_level'] == 0) {
            return true;
        }else {
            header('location:'.$url);
        }
    }
    
       
    

    
    public function updateUpload($spNo,$url,$type,$sitting='')
    {
            
            $user = new User();

            $r = '';
            $db = new DB();
            $con = $db->getConnection();
        
            
            $profileId = $this->getEmployeeId($spNo);

            //check already inserted
            $query = "SELECT * FROM upload where  employee_id='$profileId'
                AND type='$type'
             ";
            $stmt = $con->prepare($query);
            $stmt->execute();
            if($stmt->rowCount()>0){
                $q = "UPDATE  upload SET
                file=:url,status=1
                WHERE employee_id=:id AND type='$type' ";

                $stm = $con->prepare($q);
                $stm->bindParam(':url',$url,PDO::PARAM_STR);
                $stm->bindParam(':id',$profileId,PDO::PARAM_INT);
                // $status =1;
                // $stm->bindParam(':status',$status,PDO::PARAM_INT);
                $stm->execute();
                if ($stm) {
                    
                        return 1;
                } else {
                    return -1;
                }//end if
            }
            else{
                $q = "INSERT INTO upload (file,type,status,employee_id)
                VALUES (
                    :url,:type,:status,:id)";

                $stm = $con->prepare($q);
                $status = 1;
                $stm = $con->prepare($q);
                $stm->bindParam(':url',$url,PDO::PARAM_STR);
                $stm->bindParam(':type',$type,PDO::PARAM_STR);
                $stm->bindParam(':id',$profileId,PDO::PARAM_INT);
                $status =1;
                $stm->bindParam(':status',$status,PDO::PARAM_INT);
                $stm->execute();
                if ($stm) {                    
                        return 1;
                } else {
                    return -1;
                }//end if
            }
            
            
             
    }

    public function updateProfile($sp,$gender,$dob,$fname,$lname,$oname,$phone,$email,$office,$department,$rank,$cemail,$specialization,$type,$publication,$estatus,$qualification,$cadre)
    {
            
        $user = new User();

        $r = '';
        $db = new DB();
        $con = $db->getConnection();
    
        
        $profileId = $this->getEmployeeId($sp);

        
        $q = "UPDATE  employee SET
        gender=:gender,status=:status,dob=:dob,fname=:fname,lname=:lname,oname=:oname,phone_no=:phone
        ,email=:email,office_no=:office,dept=:dept,rank=:rank,corporate_mail=:cemail,specialization=:specialization,
        type=:type,publication=:publication,email_status=:estatus,qualification=:qualification,cadre=:cadre
        WHERE employee_id=:id ";
        $status =1;
        $stm = $con->prepare($q);
        $stm->bindParam(':gender',$gender,PDO::PARAM_STR);
        $stm->bindParam(':status',$status,PDO::PARAM_STR);
        $stm->bindParam(':dob',$dob,PDO::PARAM_STR);
        $stm->bindParam(':fname',$fname,PDO::PARAM_STR);
        $stm->bindParam(':lname',$lname,PDO::PARAM_STR);
        $stm->bindParam(':oname',$oname,PDO::PARAM_STR);
        $stm->bindParam(':phone',$phone,PDO::PARAM_STR);
        $stm->bindParam(':email',$email,PDO::PARAM_STR);
        $stm->bindParam(':office',$office,PDO::PARAM_STR);
        $stm->bindParam(':dept',$department,PDO::PARAM_STR);
        $stm->bindParam(':rank',$rank,PDO::PARAM_STR);
        $stm->bindParam(':cemail',$cemail,PDO::PARAM_STR);
        $stm->bindParam(':specialization',$specialization,PDO::PARAM_STR);
        $stm->bindParam(':type',$type,PDO::PARAM_INT);
        $stm->bindParam(':publication',$publication,PDO::PARAM_STR);
        $stm->bindParam(':estatus',$estatus,PDO::PARAM_STR);
        $stm->bindParam(':qualification',$qualification,PDO::PARAM_STR);
        $stm->bindParam(':cadre',$cadre,PDO::PARAM_STR);
        $stm->bindParam(':id',$profileId,PDO::PARAM_INT);
        
        // $stm->bindParam(':status',$status,PDO::PARAM_INT);
        $stm->execute();
        if ($stm) {
            
                return 1;
        } else {
            return -1;
        }//end if
    
             
    }
    
    public function updateAdminProfile($username,$spno,$firstname,$othernames,$lastname,$dept,$phone,$cadre,$title)
    
    {

        $user = new User();

        $r = '';
        $db = new DB();
        $con = $db->getConnection();
        $status = 1;
        $array = array();
        $q = "UPDATE employee SET
                    phone_no=:phone,oname=:oname,
                    sp_no=:spno,fname=:fname,lname=:lname,
                    cadre=:cadre,dept=:dept,
                    title=:title WHERE sp_no=:spno_old";
        $stm = $con->prepare($q);
        $stm->bindParam(':oname',$othernames,PDO::PARAM_STR);
        $stm->bindParam(':fname',$firstname,PDO::PARAM_STR);
        $stm->bindParam(':lname',$lastname,PDO::PARAM_STR);
        $stm->bindParam(':phone', $phone, PDO::PARAM_STR);
        $stm->bindParam(':spno', $spno, PDO::PARAM_STR);
        $stm->bindParam(':spno_old',$username,PDO::PARAM_STR);
        $stm->bindParam(':dept', $dept, PDO::PARAM_STR);
        $stm->bindParam(':cadre', $cadre, PDO::PARAM_STR);
        $stm->bindParam(':title', $title, PDO::PARAM_STR);
        $stm->execute();
        $q2 = "UPDATE user 
               SET username = :newspno
               WHERE username = :oldusername";
        $stm2 = $con->prepare($q2);
        $stm2->bindParam(':newspno', $spno,PDO::PARAM_STR);
        $stm2->bindParam(':oldusername', $username,PDO::PARAM_STR);
        $stm2->execute();
        if ($stm && $stm2)
        {
            return 1;
        }
        else
        {
            return -1;
        } //end if  
    }

    

    public function addLogin($sp,$password)
    {
            $db = new DB();
            //$user = new User();
            $r = '';
            $con = $db->getConnection();
            $employeeId = $this->staffId($sp);
        // echo $status;
            $array = array();
                $q = "INSERT INTO user (username,password,access_level)
                VALUES (:username,:password,:access_level)
                ";            
            $stm = $con->prepare($q);
            $password = md5($sp);
            $access_level = 1;
            $stm->bindParam(':username',$sp,PDO::PARAM_STR);
            $stm->bindParam(':password',$password,PDO::PARAM_STR);
            $stm->bindParam(':access_level',$access_level,PDO::PARAM_INT);
            $stm->execute();
            if ($stm) {                
                    return 1;
            } else {
                return -1;
            }//end if 
    }
    public function changePassword($user,$current,$newPass,$confirmPass)
    {
            $db = new DB();
            $con = $db->getConnection();
                    if ($current != '' && $newPass != '' && $confirmPass != '') {
                $sql = 'SELECT password FROM user WHERE user_id="' . $user . '"';
                $prepared_query = $con->prepare($sql);
                $prepared_query->execute();
                $stmt = $prepared_query ->fetchObject();

                $count = $prepared_query->rowCount();
                $curr = $stmt->password;
                if ($curr != md5($current)) {
                    return '<div class="alert alert-danger" style="font-weight: bolder">&times; Incorrect current password</div>'; //'invalid current password';
                }elseif($newPass != $confirmPass){
                    return '<div class="alert alert-danger" style="font-weight: bolder">&times; Password mismatch</div>'; //'password mismatch';
                }else {
                    $sql2 = 'UPDATE user SET password="' .md5($confirmPass).'" WHERE user_id="'.$user.'"';
                    $prepared_query2 = $con->prepare($sql2);
                    $prepared_query2->execute();
                    if($prepared_query2){
                        $con =null;//close connection
                        return '<div class="alert alert-success" style="font-weight: bolder">Password successfully changed<br/> <a href="index.html">Click here to login again</a></div>'; //'password successfully changed';
                    }else return '<div class="alert alert-danger" style="font-weight: bolder">&times; Error! Contact system administrator.</div>';//error
            }
        }else return '<div class="alert alert-danger" style="font-weight: bolder">&times; All fields required</div>'; //all fields required;
    }
    
    function uploadPassport($spno)
    {
        $payslip = new Payslip();
        // $spno = 
        if(isset($_FILES['file'])){
            $_SESSION['err_mssg1'] = $_SESSION['err_mssg2'] = $_SESSION['err_mssg3'] = $_SESSION['err_mssg4'] = "";
            $target_dir = "../uploads/passports/";
            // $target_file = $target_dir .$jambNo.'_'. time() . "_" . basename($_FILES["file"]["name"]);
            $uploadOk = 1;
            $filename = "";
            $ext = "";
            $imageFileType = pathinfo(basename($_FILES["file"]["name"]), PATHINFO_EXTENSION);
            
            $newFileName = round(microtime(true)) . '.' .$imageFileType;
            $target_file = $target_dir.$newFileName;

            function getExtension($str)
            {
                $i = strrpos($str, ".");
                if (!$i) {
                    return "";
                }
                $l = strlen($str) - $i;
                $ext = substr($str, $i + 1, $l);
                return $ext;
            }

            // Check if image file is a actual image or fake image
            if (isset($_POST["file"])) {
                $check = getimagesize($_FILES["file"]["tmp_name"]);
                if ($check !== false) {
                    $_SESSION['err_mssg0'] = "File is an image - " . $check["mime"] . ".";
                    $filename = stripslashes($target_file);
                    $ext = getExtension($filename);
                    $ext = strtolower($ext);

                    $uploadOk = 1;
                } else {
                    $_SESSION['err_mssg1'] = "File is not an image.";
                    $uploadOk = 0;
                    return "File is not an image"; //

                }
            } // Check if file already exists
            else if (file_exists($target_file)) {
                $_SESSION['err_mssg2'] = "Sorry, file already exists.";
                $uploadOk = 0;
                $target_file = "";
                return "Sorry, file already exists"; //

            } // Check file size 2MB
            else if ($_FILES["file"]["size"] > 2000 * 1024) {
                $_SESSION['err_mssg3'] = "Sorry, your file is too large.";
                $target_file = "";
                $uploadOk = 0;
                return "Sorry, your file is too large."; //
            } // Allow certain file formats
            elseif ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "JPG" && $imageFileType != "JPEG" && $imageFileType != "PNG") {
                $_SESSION['err_mssg4'] = "Only docs, docx, ppt, pdf, zip file extensions are allowed";
                $target_file = "";
                $uploadOk = 0;
                
                return "Sorry, only JPG, JPEG, PNG files are allowed"; // .
            } // Check if $uploadOk is set to 0 by an error
            elseif ($uploadOk == 0) {
                $err_mssg3 = "Sorry, your file was not uploaded.";
                return "Sorry, your file was not uploaded. Contact system administrator";
                // if everything is ok, try to upload file
            } else {
                if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
                    $url = $target_file;
                    $_SESSION['img'] = $target_file;
                    $this->updateUpload($spno,$url,'Passport');
                    return "Passport successfully uploaded.";
                    
                    // return 6;

                } else {
                    $_SESSION['err_mssg5'] = "Sorry, there was an error uploading your file.";
                    return "Sorry, there was an error uploading your file. Contact system administrator";
                }
            }
            //echo  $_SESSION['err_mssg1']. $_SESSION['err_mssg2']. $_SESSION['err_mssg3']. $_SESSION['err_mssg4'];

        }
        else{
            return "Passport not selected.";
        }
    }
    public function getUploadsInfoArray($sp_no)
    {
            
           
            $r = '';
            
            $db = new DB();
            $con = $db->getConnection();
            $profileId = $this->getEmployeeId($sp_no);$array = array();
            $q = "SELECT * FROM upload 
            WHERE employee_id='$profileId' AND type='Passport'";
            $stm = $con->prepare($q);
            $stm->execute();
            $total_rows_found = $stm->rowCount();
            if ($total_rows_found >0) {
                $row = $stm->fetch(PDO::FETCH_ASSOC);
                $array  =   $row;
            }//end if 
            $con=null;
            return $array;
    }
    
    public function AddMember($sp_no){
        // Get the form data
        $data = [
            'name' => $_POST['name'],
            'dept' => $_POST['dept'],
            'staff_no' => $_POST['staff_no'],
            'gsm_no' => $_POST['gsm_no'],
            'bank_name' => $_POST['bank_name'],
            'acct_no' => $_POST['acct_no'],
            'salary_grade' => $_POST['salary_grade'],
            'home_address' => $_POST['home_address'],
            'appointment_type' => $_POST['appointment_type'],
            'monthly_savings' => $_POST['monthly_savings'],
            'reg_fees' => $_POST['reg_fees'],
            'next_of_kin_name' => $_POST['next_of_kin_name'],
            'next_of_kin_gsm' => $_POST['next_of_kin_gsm'],
            'next_of_kin_address' => $_POST['next_of_kin_address'],
            'undertaking' => isset($_POST['undertaking']) ? 1 : 0
        ];
    }
    
    
    
    public function getMembershipDecision($employee_id) {
        $db = new DB();
        $con = $db->getConnection();
    
        // Check if the employee_id exists in the fudscoops_member table
        $sqlCheckMember = "SELECT employee_id FROM fudscoops_member WHERE employee_id = :employee_id";
        $stmtCheckMember = $con->prepare($sqlCheckMember);
        $stmtCheckMember->bindParam(':employee_id', $employee_id);
        $stmtCheckMember->execute();
    
        if ($stmtCheckMember->rowCount() == 0) {
            // Employee ID not found in fudscoops_member table
            return "To Apply for membership registration, <a href='register.php'>Click Here</a>.";
        }
    
        // Fetch the latest decision and comment from fudscoops_membership_decision table
        $sqlFetchDecision = "
            SELECT decision, comment 
            FROM fudscoops_membership_decision 
            WHERE employee_id = :employee_id 
            ORDER BY date DESC 
            LIMIT 1";
        $stmtFetchDecision = $con->prepare($sqlFetchDecision);
        $stmtFetchDecision->bindParam(':employee_id', $employee_id);
        $stmtFetchDecision->execute();
    
        if ($stmtFetchDecision->rowCount() > 0) {
            // Fetch the latest decision and comment
            $row = $stmtFetchDecision->fetch(PDO::FETCH_ASSOC);
            $decision = $row['decision'];
            $comment = $row['comment'];
    
            return "Latest Decision: $decision<br>Comment: $comment";
        } else {
            // No decision found for the employee_id
            return "No membership decision found for this employee.";
        }
    }
        
    
    
    
    
    

}


