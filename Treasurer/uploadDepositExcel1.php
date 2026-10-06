<?php
        require_once "../config/classes/DB.php";
        require_once "../config/classes/User.php";
        $db = new DB();
        $user = new User();
                

    if(isset($_FILES['uploadFile'])) {
    
        if(isset($_FILES['uploadFile']['name']) && $_FILES['uploadFile']['name'] != "") {
            $allowedExtensions = array("csv");
            $ext = pathinfo($_FILES['uploadFile']['name'], PATHINFO_EXTENSION);
            if(in_array($ext, $allowedExtensions)) {
                $file_size = $_FILES['uploadFile']['size'] / 1024;
                if($file_size < 500) {
                    $file = "../resources/uploads/".$_FILES['uploadFile']['name'];
                    $isUploaded = copy($_FILES['uploadFile']['tmp_name'], $file);
                    if($isUploaded) {
                        // echo 'ok';
                        $handle = fopen($_FILES['uploadFile']['tmp_name'], 'r');
                        $i = 0;$inserted =0;



                        $r ='<table id="example1" class="table table-bordered table-striped">
                                        <thead>
                                        <tr>
                                            <th>SN</th>
                                            <th>Staff ID</th>
                                            <th>Savings Amount</th>
                                            <th>Month</th>
                                            <th>Year</th>
                                        </tr>
                                        </thead>
                                    ';
                                   // $db = new DB();
                                  //  $con = DB::getConnection(); 
                                    $con = $db->getConnection(); 
                        while(($data = fgetcsv($handle,1000,',')) !== FALSE){
                            if($i >0){
                               // echo $data[0];
                                
                                        $month = $_POST['month'];
                                        $year = $_POST['year'];
                                        $stafNo = DB::cleanData($data[0]);
                                        $employeeid = $user->getEmployeeId($stafNo);
                                        $staffid = $user->getMemberId($employeeid);
                                        $q = "SELECT * FROM fudscoops_savings WHERE month = :month AND year = :year AND member_idmember = :staffid";
                                        $stm = $con->prepare($q);
                                        $stm->bindParam(':month', $month, PDO::PARAM_STR);
                                        $stm->bindParam(':year', $year, PDO::PARAM_STR);
                                        $stm->bindParam(':staffid', $staffid, PDO::PARAM_INT);
                                        $stm->execute();

                                          $count = $stm ->rowCount();
                                         if($count>0){
                                             $i++;
                                            $r .="<tr><td colspan='3'>".$data[0]." already exist.</td></tr>";
                                         }
                                         else{
                                            //  echo "ok";
                                                $query = "INSERT INTO fudscoops_savings (savings_amount, month, year,  member_idmember) 
                                                            VALUES (:savings_amount, :month, :year,  :member_idmember)";
                                                $stm = $con->prepare($query);
                                                // Bind parameters
                                                $stm->bindParam(':savings_amount', $data[1], PDO::PARAM_INT); //
                                                $stm->bindParam(':month', $month, PDO::PARAM_STR);
                                                $stm->bindParam(':year', $year, PDO::PARAM_STR);
                                                $stm->bindParam(':member_idmember', $staffid, PDO::PARAM_INT);
                                                
                                                $stm->execute();
                                                
                                                if($stm){
                                                    $inserted++;
                                                    $r .= "<tr/>
                                                                <td>".$i."</td>
                                                                <td>".$data[0]."</td>
                                                                <td>".$data[1]."</td>
                                                                <td>".$month."</td>
                                                                <td>".$year."</td>
                                                </tr>
                                                       ";
                                                    
                                                }
                                            }
                                        
                                        
                                }
                                    
                            $i++;
                        }

                        
                        fclose($handle);
                        $r .='<tfoot>
                        <tr>
                            <th>SN</th>
                            <th>Staff ID</th>
                            <th>Savings Amount</th>
                            <th>Month</th>
                            <th>Year</th>
                        </tr>
                    </tfoot>';
                        if($inserted ==0){
                            echo $inserted."<h1> records uploaded</h1>";
                            echo $r;
                            
                        }
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
