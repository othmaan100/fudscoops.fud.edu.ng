<?php
        require_once "../classes/DB.php";
        require_once "../classes/Putme.php";
        require_once "../classes/View.php";
        $session  =   Generic::cleanData($_POST['staff']);
        $course  =   Generic::cleanData($_POST['course']);
        $session  =   Generic::cleanData($_POST['session']);
    if(isset($_FILES['uploadFile'])) {
        //$test_id = $_POST['test'];
        if(isset($_FILES['uploadFile']['name']) && $_FILES['uploadFile']['name'] != "") {
            $allowedExtensions = array("csv","xlsx");
            $ext = pathinfo($_FILES['uploadFile']['name'], PATHINFO_EXTENSION);
            if(in_array($ext, $allowedExtensions)) {
                $file_size = $_FILES['uploadFile']['size'] / 1024;
                if($file_size < 50) {
                    $file = "uploads/candidate/".$_FILES['uploadFile']['name'];
                    $isUploaded = copy($_FILES['uploadFile']['tmp_name'], $file);
                    if($isUploaded) {
                        $handle = fopen($_FILES['uploadFile']['tmp_name'], 'r');
                        $i = 0;$inserted =0;

                        $r ='<table id="example1" class="table table-bordered table-striped">
                                        <thead>
                                        <tr>
                                            <th>SN</th>
                                            <th>JAMB NO.</th>
                                            <th>NAME</th>
                                            <th>PROGRAM APPLIED</th>
                                            <th>JAMB SCORE </th>
                                            <th>STATE</th>
                                        </tr>
                                        </thead>
                                    ';
                                    $db = new DB();
                                    $con = $db->getConnection();    
                        while(($data = fgetcsv($handle,1000,',')) !== FALSE){
                            if($i >0){
                               // echo $data[0];
                                
                                 $profile_id = $user->getProfileId($data[0]); //check if exist
                                 if ($profile_id ==0) { 
                                    $query = "INSERT INTO jamb_biodata(ca,exam,session_id,student_id,course_id) VALUES ('$data[1]',
                                    '$data[2]','$session','$student',
                                    '$course')";
                                    $stm = $con->prepare($query);
                                    
                                    $stm->execute();
                                    if($stm){
                                        $inserted++;
                                        
                                    }
                                 }else {               //if exist update
                                    //if score already inserted
                                    if(Result::isResultExist($session,$student,$course)>0){
                                        $queryU = "UPDATE score SET ca='$data[1]',
                                        exam='$data[2]' WHERE session_id='$session'AND student_id='$student'
                                        AND course_id='$course'";
                                        $stmU = $con->prepare($queryU);
                                        
                                        $stmU->execute();
                                        $inserted++;
                                    }else{
                                        
                                    }
                                    
                                    $r .= "<tr/>
                                                <td>".$i."</td>
                                                <td>".$data[0]."</td>
                                                <td>".Generic::getStudentFullname($data[0])."</td>
                                                <td>".$data[1]."</td>
                                                <td>".$data[2]."</td>
                                                <td>".($data[1]+$data[2])."</td>
                                                <td>".Result::getGrade(($data[1]+$data[2]))."</td>
                                               </tr>
                                       ";

                                 }
                                
                            }
                            $i++;

                        }
                        fclose($handle);
                        $r .='<tfoot>
                        <tr>
                            <th>SN</th>
                            <th>Registration No</th>
                            <th>Name</th>
                            <th>C.A Test</th>
                            <th>Exam</th>
                            <th>Total</th>
                            <th>Grade</th>
                        </tr>
                                        </tfoot>';
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
