<?php
        require_once('../config/classes/DB.php');
        require_once('../config/classes/User.php');
        require_once('../config/classes/Payslip.php');
        $db = new DB();
        $user = new User();
        $payslip = new Payslip();
        //$session  =   $db->cleanData($_POST['session']);
    if(isset($_FILES['uploadFile'])) {
        //$test_id = $_POST['test'];
        if(isset($_FILES['uploadFile']['name']) && $_FILES['uploadFile']['name'] != "") {
            $allowedExtensions = array("csv","xlsx");
            $ext = pathinfo($_FILES['uploadFile']['name'], PATHINFO_EXTENSION);
            if(in_array($ext, $allowedExtensions)) {
                $file_size = $_FILES['uploadFile']['size'] / 1024;
                if($file_size < 50000) {
                    $file = "../uploads/staff/".$_FILES['uploadFile']['name'];
                    $isUploaded = copy($_FILES['uploadFile']['tmp_name'], $file);
                    if($isUploaded) {
                        $handle = fopen($_FILES['uploadFile']['tmp_name'], 'r');
                        $i = 0;$inserted =0;
                        $r = '';
                                    $db = new DB();
                                    $con = $db->getConnection();    
                        while(($data = fgetcsv($handle,1000,',')) !== FALSE){
                            if($i >0){
                               // echo $data[0];
                                
                                 $employee_id = $user->staffId($data[0]); //check if exist
                                 //$access_level = $user->addLogin($data[1]);
                                 if ($employee_id ==0) { 
                                    //$stateId = $putme->getStateId($data[3]);
                                    //echo $courseId = $putme->getCourseId($data[5]);
                                    $query = "INSERT INTO employee
                                    (sp_no,title,fname,lname,oname,gender,cadre,nature_of_appo,do_first_appo,do_present_appo,rank,salary_structure,grade_level,
                                     step,qualification,dob,email,phone_no)
                                     VALUES (:sp,:title,:fname,:lname,:oname,:gender,:cadre,:nature,:dfa,:dpa,:rank,:salary,:level,:step,:qualy,:dob,:email,:phone)
                                    ";
                                    $stm = $con->prepare($query);
                                    $stm->bindParam(':sp',$data[0],PDO::PARAM_STR);
                                    $stm->bindParam(':title',$data[1],PDO::PARAM_STR);
                                    $stm->bindParam(':fname',$data[2],PDO::PARAM_STR);
                                    $stm->bindParam(':lname',$data[3],PDO::PARAM_STR);
                                    $stm->bindParam(':oname',$data[4],PDO::PARAM_STR);
                                    $stm->bindParam(':gender',$data[5],PDO::PARAM_STR);
                                    $stm->bindParam(':cadre',$data[6],PDO::PARAM_STR);
                                    $stm->bindParam(':nature',$data[7],PDO::PARAM_STR);
                                    $stm->bindParam(':dfa',$data[8],PDO::PARAM_INT);
                                    $stm->bindParam(':dpa',$data[9],PDO::PARAM_STR);
                                    $stm->bindParam(':rank',$data[10],PDO::PARAM_STR);
                                    $stm->bindParam(':salary',$data[11],PDO::PARAM_STR);
                                    $stm->bindParam(':level',$data[12],PDO::PARAM_INT);
                                    $stm->bindParam(':step',$data[13],PDO::PARAM_INT);
                                    $stm->bindParam(':qualy',$data[14],PDO::PARAM_STR);
                                    $stm->bindParam(':dob',$data[15],PDO::PARAM_STR);
                                    $stm->bindParam(':email',$data[16],PDO::PARAM_STR);
                                    $stm->bindParam(':phone',$data[17],PDO::PARAM_STR);
                                    $stm->execute();
                                    if($stm){
                                        $inserted++;
                                      $user->addLogin($data[0],$data[0]);
                                        
                                    }
                                 }else {               //if exist update
                                    // //if score already inserted
                                    // if(Result::isResultExist($session,$student,$course)>0){
                                    //     $queryU = "UPDATE score SET ca='$data[1]',
                                    //     exam='$data[2]' WHERE session_id='$session'AND student_id='$student'
                                    //     AND course_id='$course'";
                                    //     $stmU = $con->prepare($queryU);
                                        
                                    //     $stmU->execute();
                                    //     $inserted++;
                                    // }
                                    // else{
                                        
                                    // }
                                    
                                    // $r .= "<tr/>
                                    //             <td>".$i."</td>
                                    //             <td>".$data[0]."</td>
                                    //             <td>".Generic::getStudentFullname($data[0])."</td>
                                    //             <td>".$data[1]."</td>
                                    //             <td>".$data[2]."</td>
                                    //             <td>".($data[1]+$data[2])."</td>
                                    //             <td>".Result::getGrade(($data[1]+$data[2]))."</td>
                                    //            </tr>
                                    //    ";

                                 }
                                
                            }
                            $i++;

                        }
                        fclose($handle);
                        // $r .='<tfoot>
                        // <tr>
                        //     <th>SN</th>
                        //     <th>Registration No</th>
                        //     <th>Name</th>
                        //     <th>C.A Test</th>
                        //     <th>Exam</th>
                        //     <th>Total</th>
                        //     <th>Grade</th>
                        // </tr>
                        //                 </tfoot>';
                        if($inserted ==0)
                            echo $inserted."<h1> records uploaded</h1>";
                        else echo "<h3 class='text-success text-center'>".($inserted)." Record(s) Successfully Uploaded</h3>".$r;
                    } else {
                        echo '<img src="../images/status.gif" hidden="hidden" class="spin pull-center"/>
                                <h3 class="text-danger">Error!!! File not uploaded!</h3>';
                    }
                } else {
                    echo '<img src="../images/status.gif" hidden="hidden" class="spin pull-center"/>
                            <h3 class="text-danger">Error!!! Maximum file size should not cross 50 KB on size!</h3>';
                }
            } else {
                echo ' <img src="../images/status.gif" hidden="hidden" class="spin pull-center"/>
                        <h3 class="text-danger">Error!!! This type of file is not allowed, select an excel file and try again!</h3>';
            }
        } else {
            echo '<h3 class="text-danger">Error!!! Select an excel file first!</h3>';
        }
    }else echo "<h3 class='text-danger'>Error!!! Please select an excel file and try again!</h3>"

    ?>
