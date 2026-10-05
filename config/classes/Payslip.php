<?php
require_once('DB.php');
class Payslip{
    private $db;

    public function getPayslip($legacy, $month,$year)
    {
        // $type = substr($user,0,1);
        $db = new DB();
        $legacy = $this->convertLegacy($legacy);
        $con = $db->getConnection();
        $total_earnings = 0.0;
        $total_deductions = 0.0;
    
        // $legacy_slash = '/'.$legacy;
        // $month_id = $this->getMonthId($month);
        // echo $legacy;
            $r = '';
            $sql = "SELECT * FROM employee e
                INNER JOIN ippis_static_record s ON e.employee_id = s.employee_id
                INNER JOIN ippis_nonstatic_record n ON n.ippis_static_id = s.row_id
                INNER JOIN month m ON m.month_id = month
                WHERE legacy LIKE '%$legacy' AND sp_no LIKE '%/".$legacy."' AND month=:month AND year=:year limit 1";
            $stm = $con->prepare($sql);

            // $stm->bindParam(':legacy', $legacy, PDO::PARAM_STR);
            $stm->bindParam(':month', $month, PDO::PARAM_INT);
            $stm->bindParam(':year', $year, PDO::PARAM_STR);
            $stm->execute();
            $count = $stm->rowCount();
            $fetch = $stm ->fetchObject();
            if ($count>0) {
                $r .='
                <div id="container">
                    <div class="header">
                        <img src="../images/arm.png" id="img"/>
                        <p class="p1 p" style="font-family:\'Carlito Regular\';">FEDERAL GOVERNMENT OF NIGERIA</p>
                        <p class="p2 p">IPPIS TERTIARY INSTITUTIONS</p>
                        <p>EMPLOYEE PAYSLIP</p>
                        <p><b>'.$fetch->month_name.' '.$year.'</b></p>
                    </div>
                    <table border="0" cellspacing="20" style="font-weight:bolder" class="print-friendly">
                        <tr>
                            <td style="font-size:17px;">Employee Name:</td>
                            <td style="font-size:17px;"><b>'.$fetch->fullname.'</b></td>
                            <td style="font-size:17px;">Grade:</td>
                            <td style="font-size:17px;">'.$fetch->grade.'</td>
                        </tr>
                        <tr>
                            <td style="font-size:17px;">IPPIS Number:</td>
                            <td style="font-size:17px;">'.$fetch->ippis_no.'</td>
                            <td style="font-size:17px;">Step:</td>
                            <td style="font-size:17px;">'.$fetch->step.'</td>
                        </tr>
                        <tr>
                            <td style="font-size:17px;">Legacy ID:</td>
                            <td style="font-size:17px;">'.$fetch->legacy.'</td>
                            <td style="font-size:17px;">Gender:</td>
                            <td style="font-size:17px;">'.$this->getGender($fetch->gender).'</td>
                        </tr>
                        <tr>
                            <td style="font-size:17px;">MDA/School/Command:</td>
                            <td style="font-size:17px;">'.$fetch->school.'</td>
                            <td style="font-size:17px;">Tax State:</td>
                            <td style="font-size:17px;">'.$fetch->tax_state.'</td>
                        </tr>
                        <tr>
                            <td style="font-size:17px;">Department:</td>
                            <td style="font-size:17px;">'.$fetch->dept.'</td>
                            <td style="font-size:17px;">TIN:</td>
                            <td style="font-size:17px;">'.$fetch->tin.'</td>
                        </tr>
                        <tr>
                            <td style="font-size:17px;">Location:</td>
                            <td style="font-size:17px;"></td>
                            <td style="font-size:17px;">Date of Appointment:</td>
                            <td style="font-size:17px;">'.date_format(date_create($fetch->doa),"d-M-Y").'</td>
                        </tr>
                        <tr>
                            <td style="font-size:17px;">Job:</td>
                            <td style="font-size:17px;">'.$fetch->job.'</td>
                            <td style="font-size:17px;">Date of Birth:</td>
                            <td style="font-size:17px;">'.date_format(date_create($fetch->dob_static),"d-M-Y").'</td>
                        </tr>
                        
                        <tr>
                            <td colspan="4" style="font-size:20px;font-weight:bold">Bank Information Details</td>
                        </tr>
                        <tr>
                        <td colspan="4" class="hr">____________________________________________________________________________________________________________________________________________________________________________</td>
                        </tr>
                        <tr>
                            <td style="font-size:17px;">Bank Name:</td>
                            <td style="font-size:17px;">'.$fetch->bank_name.'</td>
                            <td style="font-size:17px;">PFA Name:</td>
                            <td style="font-size:17px;">'.$fetch->pfa.'</td>
                        </tr>
                        <tr>
                            <td style="font-size:17px;">Account Number:</td>
                            <td style="font-size:17px;">'.$fetch->acctno.'</td>
                            <td style="font-size:17px;">Pension PIN:</td>
                            <td style="font-size:17px;">'.$fetch->pfa_pin.'</td>
                        </tr>
                        <tr>
                        <td colspan="4" class="hr">____________________________________________________________________________________________________________________________________________________________________________</td>
                        </tr> 
                        <tr>
                            <td colspan="2"  style="font-size:20px;font-weight:bold"class="gr"><h4>Gross Earnings Information</h4></td>
                            <td colspan="2" style="font-size:20px;font-weight:bold" class="gr"><h4>Gross Deduction Information</h4></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="avoid" style="vertical-align: top;">
                                <table cellpadding="10" style="vertical-align: top;" >
                                    <tr>
                                        <td style="font-size:17px;"><b>Earnings</b></td>
                                        <td style="font-size:17px;"><b>Amount</b></td>
                                    </tr>';
                                    // get earnings
                                $e ="SELECT * FROM earning WHERE row_nonstatic_id=:non";
                                $stmE = $con->prepare($e);

                                $stmE->bindParam(':non', $fetch->row_nonstatic_id, PDO::PARAM_INT);
                            
                                $stmE->execute();
                                while ($row = $stmE->fetch(PDO::FETCH_ASSOC)) {
                                    $amount = str_replace(" ","", trim($row['amount']));
                                    $amount = str_replace(",","", trim($row['amount']));
                                    // $amount = (double)$row['amount'];
                                    $total_earnings += $amount;
                                    $r .='
                                        <tr>
                                            <td style="font-size:17px;">'.$row['caption'].'</td>
                                            <td style="font-size:17px;">&#8358; '.number_format($amount,2).'</td>
                                            </tr>
                                    ';
                                }

                                    $r .='
                                </table>          
                            </td>
                            <td colspan="2" class="avoid">
                                <table cellpadding="10">                    
                                    <tr>
                                        <td style="font-size:17px;"><b>Deductions</b></td>
                                        <td style="font-size:17px;"><b>Amount</b></td>
                                    </tr>';
                                    // get earnings
                                    $d ="SELECT * FROM deduction WHERE row_nonstatic_id=:non";
                                $stmD = $con->prepare($d);
    
                                $stmD->bindParam(':non', $fetch->row_nonstatic_id, PDO::PARAM_INT);
                                
                                $stmD->execute();
                                while ($rowD = $stmD->fetch(PDO::FETCH_ASSOC)) {                                   
                                    $amount = str_replace(" ","", trim($rowD['amount']));
                                    $amount = str_replace(",","", trim($rowD['amount']));
                                    $total_deductions += $amount;
                                    $r .='
                                        <tr>
                                            <td style="font-size:17px;">'.$rowD['caption'].'</td>
                                            <td style="font-size:17px;">&#8358; '.number_format($amount,2).'</td>
                                        </tr>
                                    ';
                                }
                                // income tax
                                $i ="SELECT * FROM ippis_income_tax WHERE row_nonstatic_id=:non";
                                    $stmI = $con->prepare($i);

                                    $stmI->bindParam(':non', $fetch->row_nonstatic_id, PDO::PARAM_INT);
                                    $stmI->execute();
                                    $fetchTax = $stmI->fetchObject();
                                    $income_tax = $fetchTax->amount;
                                    $total_deductions += $income_tax;
                                    $r .='
                                    <tr>
                                        <td colspan="2">______________________________________________________________________</td>
                                    </tr>
                                    <tr>
                                        <td style="text-decoration:underline;font-size:18px;font-weight:bold" colspan="2">Summary of Payments</td>
                                    </tr>
                                    <tr>
                                        <td style="font-size:17px;font-weight:bold"><b>Total Gross Earnings:</b></td>
                                        <td style="font-size:17px;">&#8358; '.number_format($total_earnings,2).'</td>
                                    </tr>
                                    <tr>
                                        <td style="font-size:17px;font-weight:bold"><b>Income Tax:</b></td>
                                        <td style="font-size:17px;">&#8358; '.number_format($income_tax,2).'</td>
                                    </tr>
                                    <tr>
                                        <td style="font-size:17px;font-weight:bold"><b>Total Gross Deductions:</b></td>
                                        <td style="font-size:17px;">&#8358; '.number_format($total_deductions,2).'</td>
                                    </tr>
                                    <tr>
                                        <td style="font-size:17px;font-weight:bold"><b>Total Net Earnings:</b></td>
                                        <td style="font-size:17px;">&#8358; '.number_format(($total_earnings-$total_deductions),2).'</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                ';
                
                $r .="
                    <div id='bottom'>Powered By IPPIS - <b>SoftSuite</b></div>
                </div>
                ";
                
            }else
                $r = -1;
            
            return $r;
        
    }
    
    public function staffId($sp)
    {
            $db = new DB();
            $r = '';
            $con = $db->getConnection();
            $q = "SELECT employee_id FROM employee WHERE sp_no=:sp";
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
    public function getMonthId($month)
    {
            $db = new DB();
            $r = '';
            $con = $db->getConnection();
            $q = "SELECT month_id FROM month WHERE month_name like '%$month%' ";
            $stm = $con->prepare($q);
            $stm->execute();
            $total_rows_found = $stm->rowCount();
            if ($total_rows_found > 0) {
                    $row = $stm->fetch(PDO::FETCH_ASSOC);
                        $r =  $row['month_id'];                        
            }else {
                $r = 0;
            }//end if ($total_rows_found == 1)
            $con=null;
            return $r;
    }
    public function convertLegacy($legacy)
    {
        $legacy = trim($legacy);
        $legacy = str_replace("A","",$legacy);
        $legacy = str_replace("N","",$legacy);
        $legacy = str_replace("C","",$legacy);
        $legacy = str_replace("T","",$legacy);
        $legacy = str_replace("SP","",$legacy);
        $legacy = str_replace("SPP","",$legacy);
        $legacy = str_replace("JP","",$legacy);
        $legacy = str_replace("R","",$legacy);
        $legacy = str_replace("RR","",$legacy);
        $legacy = str_replace("/","",$legacy);
            // $prefix = substr($legacy,0,2);
            // $r = substr($legacy,2,1);
            // $sn = substr($legacy,3);
        
            
        if ($legacy == '') {
            return -1;
        }else
            return $legacy;
    }
    public function isStaticExist($ippis_no)
    {
            $db = new DB();
            $r =0;
            $con = $db->getConnection();
            $q = "SELECT * FROM ippis_static_record
                    WHERE ippis_no =:ippis";
            $stm = $con->prepare($q);
            $stm->bindParam(':ippis', $ippis_no, PDO::PARAM_STR);
            $stm->execute();
            $total_rows_found = $stm->rowCount();
            if ($total_rows_found > 0) {
                    $row = $stm->fetch(PDO::FETCH_ASSOC);
                        $r =  $row['row_id'];
                    
            }else {
                $r = 0;
            }//end if ($total_rows_found == 1)
            $con=null;
            return $r;
    }
    public function isNonStaticExist($static,$month,$year)
    {
            $db = new DB();
            $r =0;
            $month_id = $this->getMonthId($month);
            $con = $db->getConnection();
            $q = "SELECT row_nonstatic_id FROM ippis_nonstatic_record
                    WHERE ippis_static_id =:static AND month=:month AND year=:year  ";
            $stm = $con->prepare($q);
            $stm->bindParam(':static', $static, PDO::PARAM_INT);
            $stm->bindParam(':month', $month_id, PDO::PARAM_INT);
            $stm->bindParam(':year', $year, PDO::PARAM_INT);
            $stm->execute();
            $total_rows_found = $stm->rowCount();
            if ($total_rows_found > 0) {
                    $row = $stm->fetch(PDO::FETCH_ASSOC);
                        $r =  $row['row_nonstatic_id'];
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
            $profileId = $user->staffId($sp);

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
    public function getMonthName($monthId)
    {
            $db = new DB();
            $r = '';
            $con = $db->getConnection();
            $q = "SELECT * FROM month WHERE month_id ='$monthId' ";
            $stm = $con->prepare($q);
            $stm->execute();
            $total_rows_found = $stm->rowCount();
            if ($total_rows_found > 0) {
                    $row = $stm->fetch(PDO::FETCH_ASSOC);
                        $r =  $row['month_name'];                        
            }else {
                $r = 0;
            }//end if ($total_rows_found == 1)
            $con=null;
            return $r;
    }
    public function updateBio($jambNo,$gender,$dob,$state,$lga,$phone,$email,$address)
    {
            
            $user = new User();

            $r = '';
            $con = $this->getConnection();
            //$profileId = $user->getProfileId($jambNo);
            $status = 1;
            $array = array();
            $q = "UPDATE  jamb_biodata SET
                    gender=:gender,dob =:dob,
                    state=:state,lga=:lga,phone=:phone,email=:email,address=:address,status=:status
                    WHERE jamb_no='$jambNo'";
            $stm = $con->prepare($q);
            $stm->bindParam(':gender',$gender,PDO::PARAM_STR);
            $stm->bindParam(':dob',$dob,PDO::PARAM_STR);
            // $stm->bindParam(':country',$country,PDO::PARAM_INT);
            $stm->bindParam(':state',$state,PDO::PARAM_INT);
            $stm->bindParam(':lga',$lga,PDO::PARAM_INT);
            $stm->bindParam(':phone',$phone,PDO::PARAM_STR);
            $stm->bindParam(':email',$email,PDO::PARAM_STR);
            $stm->bindParam(':status',$status,PDO::PARAM_INT);
            $stm->bindParam(':address',$address,PDO::PARAM_STR);
            $stm->execute();
            if ($stm) {
                    return 1;
            } else {
                return -1;
            }//end if 
    }
    
    public function generateReport($type,$isPrint = '')
    {
        
        

        $r = '';
        $con = $this->getConnection();

        
        if ($type=='All') {
            $query = "SELECT e.*,states.name as state,program
            FROM employee e
            INNER JOIN promotion p ON p.emp_id = e.emp_id           
            INNER JOIN states ON states.id = j.state
            INNER JOIN program ON j.course_applied = program_id
            WHERE j.profile_id NOT IN ( SELECT profile_id FROM payment WHERE status = 1)
            AND j.session_id='$session'";            
        }else{
            $query = "SELECT j.*,states.name as state,program
            FROM  jamb_biodata j    
            INNER JOIN states ON states.id = j.state
            INNER JOIN program ON j.course_applied = program_id
            WHERE j.profile_id NOT IN ( SELECT profile_id FROM payment WHERE status = 1)
            AND j.session_id='$session' AND j.mode_of_entry='$type'";            
        }    
        $stmt = $con->prepare($query);
        $stmt->execute(); 
        if ($stmt->rowCount()>0) {
            $sn = 0;
            $r .= '
                    <table class="table table-bordered" id="myTable" style="width:70%">
                        <thead>
                        <tr>
                            <th colspan="4">REPORT TYPE: '.$type.' UNREGISTERED CANDIDATES</th>
                            <th colspan="4" class="text-right">SESSION: '.$this->getSessionName($session).'  </th>
                        </tr>
                            <tr>
                                <th colspan="4" class="text-left">
                                ';
                                if(empty($isPrint)){
                                    $r .='
                                <a target="_blank" href="print.php?type=unregistered_report&ses='.$session.'&report_type='.$type.'"  class="btn btn-outline-primary btn-icon-text">
                                <i class="mdi mdi-printer btn-icon-prepend"></i>
                                PRINT
                              </a>';
                                }
                                $r .='
                                </th>
                                <th colspan="4" class="text-right"></th>
                            </tr>
                            
                            <tr>
                                <th>S/N</th>
                                <th>Candidate Name</th>
                                <th>JAMB NO.</th>
                                <th>JAMB SCORE</th>
                                <th>ENTRY MODE</th>
                                <th>STATE</th>
                                <th>PROGRAM APPLIED</th>
                            </tr>
                        </thead>
                        <tbody>
            ';
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $sn++;
                $r .= '
                    <tr>
                        <td>'.$sn.'</td>
                        <td>'.$row['firstname'].' '.$row['lastname'].' ' .$row['othernames'].'</td>
                        <td>'.$row['jamb_no'].'</td>
                        <td>'.$row['jamb_score'].'</td>
                        <td>'.$row['mode_of_entry'].'</td>
                        <td>'.$row['state'].'</td>
                        <td>'.$row['program'].'</td>                        
                    </tr>
                ';
            }
            $r .= '
                    </body>
                    </table>
                ';
        }else{
            $r .= '
                <div class="alert alert-danger col-12">No data found</div>
            ';
        }
          return $r;
    } 
   public function cleanLegacy($legacy)
   {
        $legacy = trim($legacy);
        // $isRA = strpos($legacy,"RA");
        $legacy = str_replace("A","",$legacy);
        $legacy = str_replace("N","",$legacy);
        $legacy = str_replace("C","",$legacy);
        $legacy = str_replace("/","",$legacy);
        $legacy = str_replace("T","",$legacy);
        // $legacy = str_replace("SP","",$legacy);
        // $legacy = str_replace("JP","",$legacy);
        // $legacy = str_replace("R","",$legacy);
            
        return $legacy;
    }

    public function getGender($gender){
        if($gender =='M')
            return "MALE";
        else
            return "FEMALE";
    }

}


