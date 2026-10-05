    <?php 
        
        require_once('../classes/Putme.php');
        $putme = new Putme();

        

         $type = $_GET['type'];


         if ($type == 'payment_report') {
           $session = $_GET['ses'];
           $type2 = $_GET['report_type'];

           echo '
           <!-- plugin css for this page -->
          <link rel="stylesheet" href="vendors/datatables.net-bs4/dataTables.bootstrap4.css">
          <!-- End plugin css for this page -->
          <!-- inject:css -->
          <title>.</title>
          <link rel="stylesheet" href="../css/style.css">
          <link rel="stylesheet" href="../css/putme.css">
          
           <center>
           <div class=" container col-md-8 ">
              <div class="">
                <div class="-body">
                <img src="../images/logo.png" style="height:100px;display:block;margin-left:auto;margin-right:auto;width:12%;margin-bottom:50px;margin-top:30px;" class="col-" alt="logo">

                    <h3 class="text-center" style="text-align:center">
                    FEDERAL UNIVERISTY DUTSE<br/> POST UTME SCREENING EXERCISE<br/><br/>
                    <span class="btn btn-outline-primary" >PAYMENT REPORT </span>
                    </h3><br/>

                    <div class="row">
                      '.$putme->generatePaymentReport($session,$type2,1).'
                    </div><!-- end row -->
                    <br/>
                    
                </div>
                
              </div>
           </div>
           </center>
           ';
         }

         elseif ($type == 'acknowledge') {
          $invoice = $_GET['invoice'];
          $sittin1 = $putme->getOlevelInfoArray($jambNo,1);
          $sittin2 = $putme->getOlevelInfoArray($jambNo,2);
          $alevel = $putme->getAlevelInfoArray($jambNo);
          $passportUpload = $putme->getUploadsInfoArray($jambNo,'Passport');


          echo '
          <!-- plugin css for this page -->
         <link rel="stylesheet" href="vendors/datatables.net-bs4/dataTables.bootstrap4.css">
         <!-- End plugin css for this page -->
         <!-- inject:css -->
         <link rel="stylesheet" href="css/style.css">
         <link rel="stylesheet" href="css/putme.css">
         <title>.</title>
          <center>
          <div class=" container col-md-8 ">
             <div class="">
               <div class="-body">
               <img src="images/logo.png" style="height:100px;display:block;margin-left:auto;margin-right:auto;width:12%;margin-bottom:50px;margin-top:30px;" class="col-" alt="logo">

                   <h3 class="text-center" style="text-align:center">
                   FEDERAL UNIVERISTY DUTSE<br/>'.$info['session'].' POST UTME SCREENING EXERCISE<br/><br/>
                   <span class="btn btn-outline-primary" >AKNOWLEDGEMENT FORM </span>
                   </h3><br/>

                   <div class="row">
                     <table class="table table-bordered"  cellspacing="0" border="1">                       
                       <tr>
                         
                       </tr>
                       <tr>
                         <th colspan="3" class=""><h4>CANDIDATE INFORMATION</h4></th>
                       </tr>
                       <tr>
                         <td>Fullname:</td>
                         <td>'. $info['lastname']. " ".$info['firstname']." ".$info['othernames'].'</td>
                         <td colspan="2" rowspan="5">
                            <div id="img2" class="text-right">
                                <img src="uploads/'.$passportUpload['file'].'" style="height:250px;width:250px;border-radius:2px;" name="img-pass" id="" class="img-responsive img-thumbnail" alt=""/>
                            </div>
                         </td>
                         </tr>
                       <tr>
                         <td>JAMB NUMBER:</td>
                         <td>'. $info['jamb_no'].'</td>
                       </tr>
                       <tr>
                         <td>SESSION:</td>
                         <td>'. $info['session'].'</td>
                       </tr>
                       <tr>
                         <td>PHONE NUMBER:</td>
                         <td>'. $info['phone'].'</td>
                       </tr>
                       <tr>
                         <td>EMAIL:</td>
                         <td>'. $info['email'].'</td>
                       </tr>
                       <tr>
                         <td>FACULTY:</td>
                         <td colspan="2">'. $info['faculty'].'</td>
                       </tr>
                       <tr>
                         <td>DEPARTMENT:</td>
                         <td colspan="2">'. $info['department'].'</td>
                       </tr>
                       <tr>
                       <td>PROGRAM APPLIED:</td>
                       <td colspan="2">'. $info['program'].'</td>
                       </tr>
                       <tr><td colspan="3"></td></tr>
                       <tr>
                         <th colspan="3" class=""><h4>O\'LEVEL</h4></th>
                       </tr>
                       <tr>
                       <td>EXAM TYPE:</td>
                       <td colspan="2">'. $sittin1['exam_type'].'</td>
                       </tr>
                       <tr>
                       <td>EXAM YEAR:</td>
                       <td colspan="2">'. $sittin1['exam_year'].'</td>
                       </tr>
                       <tr>
                       <td>CENTER NAME:</td>
                       <td colspan="2">'. $sittin1['center_name'].'</td>
                       </tr>
                       <tr>
                        <td>CENTER NUMBER:</td>
                        <td colspan="2">'. $sittin1['center_no'].'</td>
                       </tr>
                       <tr>
                        <td>EXAM Number:</td>
                        <td colspan="2">'. $sittin1['exam_no'].'</td>
                       </tr>
                       <tr><td colspan="3"></td></tr>

                       <td>SUBJECT</td>
                       <td colspan="2">GRADE</td>
                       </tr>
                       <tr>
                       <td colspan="">'.$putme->getSubjectName($sittin1['sub1']).'</td>
                       <td colspan="2">'.$sittin1['grade1'].'</td>
                       </tr>
                       <tr>
                       <td colspan="">'.$putme->getSubjectName($sittin1['sub2']).'</td>
                       <td colspan="2">'.$sittin1['grade2'].'</td>
                       </tr>
                       <tr>
                       <td colspan="">'.$putme->getSubjectName($sittin1['sub3']).'</td>
                       <td colspan="2">'.$sittin1['grade3'].'</td>
                       </tr>
                       <tr>
                       <td colspan="">'.$putme->getSubjectName($sittin1['sub4']).'</td>
                       <td colspan="2">'.$sittin1['grade4'].'</td>
                       </tr>
                       <tr>
                       <td colspan="">'.$putme->getSubjectName($sittin1['sub5']).'</td>
                       <td colspan="2">'.$sittin1['grade5'].'</td>
                       </tr>
                       <tr>
                       <td colspan="">'.$putme->getSubjectName($sittin1['sub6']).'</td>
                       <td colspan="2">'.$sittin1['grade6'].'</td>
                       </tr>
                       <tr>
                       <td colspan="">'.$putme->getSubjectName($sittin1['sub7']).'</td>
                       <td colspan="2">'.$sittin1['grade7'].'</td>
                       </tr>
                       <tr>
                       <td colspan="">'.$putme->getSubjectName($sittin1['sub8']).'</td>
                       <td colspan="2">'.$sittin1['grade8'].'</td>
                       </tr>
                       <tr>
                       <td colspan="">'.$putme->getSubjectName($sittin1['sub9']).'</td>
                       <td colspan="2">'.$sittin1['grade9'].'</td>
                       </tr>
                       ';
                       if (!empty($sittin2)) {
                         echo '
                         <tr><td colspan="3"></td></tr>
                         <td>EXAM TYPE:</td>
                         <td colspan="2">'. $sittin2['exam_type'].'</td>
                         </tr>
                         <tr>
                         <td>EXAM YEAR:</td>
                         <td colspan="2">'. $sittin2['exam_year'].'</td>
                         </tr>
                         <tr>
                         <td>CENTER NAME:</td>
                         <td colspan="2">'. $sittin2['center_name'].'</td>
                         </tr>
                         <tr>
                          <td>CENTER NUMBER:</td>
                          <td colspan="2">'. $sittin2['center_no'].'</td>
                         </tr>
                         <tr>
                          <td>EXAM Number:</td>
                          <td colspan="2">'. $sittin2['exam_no'].'</td>
                         </tr>
                         <tr><td colspan="3"></td></tr>
  
                         <td>SUBJECT</td>
                         <td colspan="2">GRADE</td>
                         </tr>
                         <tr>
                         <td colspan="">'.$putme->getSubjectName($sittin2['sub1']).'</td>
                         <td colspan="2">'.$sittin2['grade1'].'</td>
                         </tr>
                         <tr>
                         <td colspan="">'.$putme->getSubjectName($sittin2['sub2']).'</td>
                         <td colspan="2">'.$sittin2['grade2'].'</td>
                         </tr>
                         <tr>
                         <td colspan="">'.$putme->getSubjectName($sittin2['sub3']).'</td>
                         <td colspan="2">'.$sittin2['grade3'].'</td>
                         </tr>
                         <tr>
                         <td colspan="">'.$putme->getSubjectName($sittin2['sub4']).'</td>
                         <td colspan="2">'.$sittin2['grade4'].'</td>
                         </tr>
                         <tr>
                         <td colspan="">'.$putme->getSubjectName($sittin2['sub5']).'</td>
                         <td colspan="2">'.$sittin2['grade5'].'</td>
                         </tr>
                         <tr>
                         <td colspan="">'.$putme->getSubjectName($sittin2['sub6']).'</td>
                         <td colspan="2">'.$sittin2['grade6'].'</td>
                         </tr>
                         <tr>
                         <td colspan="">'.$putme->getSubjectName($sittin2['sub7']).'</td>
                         <td colspan="2">'.$sittin2['grade7'].'</td>
                         </tr>
                         <tr>
                         <td colspan="">'.$putme->getSubjectName($sittin2['sub8']).'</td>
                         <td colspan="2">'.$sittin2['grade8'].'</td>
                         </tr>
                         <tr>
                         <td colspan="">'.$putme->getSubjectName($sittin2['sub9']).'</td>
                         <td colspan="2">'.$sittin2['grade9'].'</td>
                         </tr>
                         
                         ';
                       }
                       if (!empty($alevel)) {
                        echo '
                        <tr><td colspan="3"></td></tr>
                        <tr><td colspan="3"><h3>A\'Level</h3></td></tr>
                        <td>QUALIFICATION:</td>
                        <td colspan="2">'. $alevel['alevel'].'</td>
                        </tr>
                        <tr>
                        <td>YEAR:</td>
                        <td colspan="2">'. $alevel['year_from'].' - '.$alevel['year_to'].'</td>
                        </tr>
                        <tr>
                        <td>COURSE OF STUDY:</td>
                        <td colspan="2">'. $alevel['course'].'</td>
                        </tr>
                        <tr>
                         <td>INSITITUTION:</td>
                         <td colspan="2">'. $alevel['institution'].'</td>
                        </tr>
                        <tr>
                         <td>GRADE:</td>
                         <td colspan="2">'. $alevel['grade'].'</td>
                        </tr>
                        <tr>
                         <td>CGPA:</td>
                         <td colspan="2">'. $alevel['cgpa'].'</td>
                        </tr>
                        ';
                      }
                       echo '
                     </table>
                   </div><!-- end row -->
                   <br/>
                   <div class="undertaking text-left">
                      <h4>Declaration</h4>
                      <p class="text-justify">
                        I hereby declare that statements made by me in the application form are true and complete
                        to the best of my knowledge. 
                        I also understand that in case, any of my statement is found untrue during the screening 
                        I shall be disqualified.
                      </p>
                   </div>
               </div>
               
             </div>
          </div>
          </center>
          ';
        }
         
    ?>
    <script>
      window.print();
    </script>
    
     
        