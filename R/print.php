<?php 
        require_once('../config/classes/MemberG2.php');
        require_once('../config/classes/MemberG1.php');
        require_once('../config/classes/User.php');
        $memberg2 = new MemberG2();
        $memberg1 = new MemberG1();
        $user = new User();
        

         $type = $_GET['type'];
         

         if ($type == 'acknowledgeshare') {
           $spNo = $_GET['spNo'] ?? '';
           $staff_info =  $user->getStaffInformation($spNo);
           $employeeid = $user->getEmployeeId($spNo);
           $memberid = $user->getMemberId($employeeid);
           $share_info = $user->getMemberShareInformation($memberid);
           $bank_name = $user->getShareBankName($memberid);
                //   var_dump($payment);
           //$session = $putme->getCurrentSession();



           echo '
           <!-- plugin css for this page -->
          <link rel="stylesheet" href="../../vendors/datatables.net-bs4/dataTables.bootstrap4.css">
          <!-- End plugin css for this page -->
          <!-- inject:css -->
          <title>.</title>
          <link rel="stylesheet" href="../../css/style.css">
          <link rel="stylesheet" href="../../css/putme.css">
          
           <center>
           <div class=" container col-md-8 ">
              <div class="">
                <div class="-body">
                <img src="../../images/logo-mini.png" style="display:block;margin-left:auto;margin-right:auto;margin-bottom:2px;margin-top:0px; width:100px;" class="rounded mx-auto d-block" alt="logo">
                    <h3 class="text-center" style="text-align:center">
                    FUD STAFF COOPERATIVE SOCIETY LIMITED<br/>
                    <h4>[An Interest Free Thrift and Loan Society]</h4><br/>
                    <span class="btn btn-outline-primary" >CASH PURCHASE SHARES FORM </span>
                    </h3><br/><br/>

                    <div class="row">
                      <table class="table table-bordered"  cellspacing="0" border="1">
                        <tr>
                          <th colspan="" style="border:0px" class="text-left"><h4>STAFF NUMBER: '. $staff_info['sp_no'].'</h4></th>
                        </tr>
                        <tr>
                          <tr>
                            <td colspan="2"></td>
                          </tr>
                        </tr>
                        <tr>
                          <th colspan="2" class=""><h4>MEMBER INFORMATION</h4></th>
                        </tr>
                        <tr>
                          <td>FULLNAME:</td>
                          <td>'. $staff_info['fname']. " ".$staff_info['lname']." ".$staff_info['oname'].'</td>
                        </tr>
                        <tr>
                          <td>DEPAERRMENT/UNIT:</td>
                          <td>'. $staff_info['dept'].'</td>
                        </tr>
                        <tr>
                          <td>PHONE NUMBER:</td>
                          <td>'. $staff_info['phone_no'].'</td>
                        </tr>
                        <tr>
                          <td>PRICE OF SHARE:</td>
                          <td>'. $share_info['unit_price'].'</td>
                        </tr>
                        
                        <tr>
                          <td>BANK OF PAYMENT:</td>
                          <td>'.$bank_name.'</td>
                        </tr>
                        <tr>
                          <td>TELLER NO:</td>
                          <td>'. $share_info['teller_no'].'</td>
                        </tr>
                        <tr>
                          <td>AMOUNT PAID IN FIGURES:</td>
                          <td>'. $share_info['amount_paid'].'</td>
                        </tr>
                        <tr>
                          <td>AMOUNT PAID IN WORDS:</td>
                          <td>'. $share_info['shares_amount_word'].'</td>
                        </tr>
                        <tr>
                        <td>DATE:</td>
                        <td>'. $share_info['date'].'</td>
                        </tr>
                        
                      </table>
                    </div><!-- end row -->
                    <br/>
                    
                </div>
                
              </div>
           </div>
           </center>
           ';
         }
         
         elseif ($type == 'acceptancepayment') {
           $jambNo = $_GET['jambNo'];
           $info = $putme->getCandidateAdmissionInfoArray($jambNo);
           $invoice = $_GET['invoice'];
           $payment = $payments->getPaymentInfoArray($jambNo,2);
           $amount = $payments->getAmountInfoArray($info['session_id'],2);


           echo '
           <!-- plugin css for this page -->
          <link rel="stylesheet" href="vendors/datatables.net-bs4/dataTables.bootstrap4.css">
          <!-- End plugin css for this page -->
          <!-- inject:css -->
          <title>.</title>
          <link rel="stylesheet" href="css/style.css">
          <link rel="stylesheet" href="css/putme.css">
          
           <center>
           <div class=" container col-md-8 ">
              <div class="">
                <div class="-body">
                <img src="images/logo.png" style="height:100px;display:block;margin-left:auto;margin-right:auto;width:12%;margin-bottom:50px;margin-top:30px;" class="col-" alt="logo">

                    <h3 class="text-center" style="text-align:center">
                    FEDERAL UNIVERSITY DUTSE<br/>'. $putme->getSessionName($info['session']).' ACCEPTANCE FEE <br/><br/>
                    <span class="btn btn-outline-primary" >PAYMENT SLIP </span>
                    </h3><br/>

                    <div class="row">
                      <table class="table table-bordered"  cellspacing="0" border="1">
                        <tr>
                          <th colspan="" style="border:0px" class="text-left"><h4>INVOICE NUMBER: '. $payment['invoice_no'].'</h4></th>
                          <th colspan="" style="border:0px"><h4>REMITA RETRIEVAL REFERENCE (RRR): '. $payment['rrr'].'</h4></th>
                        </tr>
                        <tr>
                          <tr>
                            <td colspan="2"></td>
                          </tr>
                        </tr>
                        <tr>
                          <th colspan="2" class=""><h4>CANDIDATE INFORMATION</h4></th>
                        </tr>
                        <tr>
                          <td>FULLNAME:</td>
                          <td>'. $info['sname']. " ".$info['fname']." ".$info['oname'].'</td>
                        </tr>
                        <tr>
                          <td>JAMB NUMBER:</td>
                          <td>'. $info['jambno'].'</td>
                        </tr>
                        <tr>
                          <td>SESSION:</td>
                          <td>'. $putme->getSessionName($info['session']).'</td>
                        </tr>
                        <tr>
                          <td>PHONE NUMBER:</td>
                          <td>'. $info['gsm'].'</td>
                        </tr>
                        <tr>
                          <td>EMAIL:</td>
                          <td>'. $info['email'].'</td>
                        </tr>
                        <tr>
                          <td>MODE OF ENTRY:</td>
                          <td>'. $info['mentry'].'</td>
                        </tr>
                        <tr>
                          <td>FACULTY:</td>
                          <td>'. $info['faculty'].'</td>
                        </tr>
                        <tr>
                          <td>DEPARTMENT:</td>
                          <td>'. $info['department'].'</td>
                        </tr>
                        <tr>
                        <td>PROGRAM OFFERED:</td>
                        <td>'. $info['program'].'</td>
                        </tr>
                        <tr>
                          <th colspan="2" class=""><h4>TRANSACTION DETAILS</h4></th>
                        </tr>
                        <tr>
                          <td>AMOUNT:</td>
                          <td>&#8358;'. number_format($amount['amount'],2).'</td>
                        </tr>
                        <tr>
                          <td>DESCRIPTION:</td>
                          <td>'.'2022/2023 '.$info['mentry'].' Admission Acceptance Fee</td>
                        </tr>
                        <tr>                                                
                        <tr>
                          <td>PAYMENT STATUS:</td>
                          <td>';
                              if ($payment['status'] ==1) {
                                echo "<b>PAID</b>";
                              }else{
                                echo "<b>NOT PAID</b>";
                              } 
                            echo '</td>
                        </tr>
                        
                        <tr>
                          <td>DATE PAID:</td>
                          <td>'. $payment['date_paid']  .'</td>
                        </tr>
                        ';
                        if ($payment['status'] ==0) {
                          echo '
                            <tr><th colspan="2" class="text-center"><h3>INVOICE NOT PAID</h3></th></tr>
                          ';
                        }else{
                          echo '
                          <tr><th colspan="2" class="text-center"><h3>INVOICE IS PAID</h3></th></tr>
                          ';
                        }
                        echo '
                        </tr>
                      </table>
                    </div><!-- end row -->
                    <br/>
                    
                </div>
                
              </div>
           </div>
           </center>
           ';
         }

         elseif ($type == 'acknowledge') {
             
          $jambNo = $_GET['jambNo'];
          $info = $putme->getCandidateInfoArray($jambNo);
          $invoice = $_GET['invoice'];
          $sittin1 = $putme->getOlevelInfoArray($jambNo,1);
          $sittin2 = $putme->getOlevelInfoArray($jambNo,2);
          $alevel = $putme->getAlevelInfoArray($jambNo);
          $passportUpload = $putme->getUploadsInfoArray($jambNo,'Passport');


          echo '

         <link rel="stylesheet" href="css/style.css">
         <link rel="stylesheet" href="css/putme.css">
         <style>
            .no-pad{
                width: 1px !important;
            }
         </style>
         <title>.</title>
          <center>
          <div class=" container col-md-8 ">
             <div class="">
               <div class="-body">
               <img src="images/logo.png" style="height:100px;display:block;margin-left:auto;margin-right:auto;width:12%;margin-bottom:5px;margin-top:10px;" class="col-" alt="logo">

                   <h3 class="text-center" style="text-align:center;margin-top:0px;">
                   FEDERAL UNIVERISTY DUTSE<br/>'.$info['session'].' POST UTME SCREENING EXERCISE<br/>
                   <span class="btn btn-outline-primary" ><b>AKNOWLEDGEMENT SLIP </b></span>
                   </h3>

                   <div class="row">
                     <table class="table table-bordered"  cellspacing="0" border="1">                       
                       <tr>
                         
                       </tr>
                       <tr>
                         <th colspan="5" class=""><h4>CANDIDATE INFORMATION</h4></th>
                       </tr>
                       <tr>
                         <td class="no-pad"><b>FULLNAME:</b></td>
                         <td>'. $info['lastname']. " ".$info['firstname']." ".$info['othernames'].'</td>
                         <td class="no-pad"><b>FACULTY:</b></td>
                         <td colspan="">'. $info['faculty'].'</td>
                         <td colspan="" rowspan="5">
                            <div id="img2" class="text-right">
                                <img src="'.$passportUpload['file'].'" style="height:180px;width:180px;border-radius:2px;" name="img-pass" id="" class="img-responsive " alt=""/>
                            </div>
                         </td>
                         </tr>
                       <tr>
                         <td class="no-pad"><b>JAMB NO.:</b></td>
                         <td>'. $info['jamb_no'].'</td>
                         <td class="no-pad"><b>DEPARTMENT:</b></td>
                         <td colspan="">'. $info['department'].'</td>
                       </tr>
                       <tr>
                          <td class="no-pad"><b>ENTRY MODE:</b></td>
                          <td>'. $info['mode_of_entry'].'</td>
                          <td class="no-pad"><b>PROGRAM APPLIED:</b></td>
                            <td colspan="">'. $info['program'].'</td>
                        </tr>
                       <tr>
                         <td class="no-pad"><b>SESSION:</b></td>
                         <td>'. $info['session'].'</td>
                         <td class="no-pad"><b>PHONE NUMBER:</b></td>
                         <td>'. $info['phone'].'</td>
                       </tr>
                       <tr>
                         <td class="no-pad"><b>EMAIL:</b></td>
                         <td>'. $info['email'].'</td>
                         <td class="no-pad"><b>STATE:</b></td>
                         <td>'. $info['state_name'].'</td>
                       </tr>';
                       if($info['mode_of_entry'] =='UTME'){
                           echo '
                                <tr>
                                 <th colspan="5" class=""><h4>JAMB DETAILS</h4></th>
                               </tr>
                               <tr>
                                   <td colspan="2"><b>SUBJECT COMBINATION:</b></td>
                                   <td colspan="3">'. $info['sub1'].', '.$info['sub2'].', '.$info['sub3'].', '.$info['sub4'].'</td>
                                 </tr>
                                <tr>
                                   <td colspan="2"><b>JAMB SCORE:</b></td>
                                   <td colspan="3">'. $info['jamb_score'].'</td>
                                 </tr>
                           ';
                       }
                       echo '<tr><td colspan="5"></td></tr>
                       <tr>
                         <th colspan="5" class=""><h4>O\'LEVEL</h4></th>
                       </tr>
                       <tr>
                       <td><b>EXAM TYPE (1st Sitting):</b></td>
                       <td colspan="">'. $sittin1['exam_type'].'</td>
                       <td colspan="2"><b>EXAM TYPE (2nd Sitting):</b></td>
                       <td >'. $sittin2['exam_type'].'</td>
                       </tr>
                       <tr>
                       <td><b>EXAM YEAR:</b></td>
                       <td colspan="">'. $sittin1['exam_year'].'</td>
                       <td colspan="2"><b>EXAM YEAR:</b></td>
                       <td colspan="">'. $sittin2['exam_year'].'</td>
                       </tr>
                       <tr>
                       <td><b>CENTER NAME:</b></td>
                       <td colspan="">'. $sittin1['center_name'].'</td>
                       <td colspan="2"><b>CENTER NAME:</b></td>
                       <td colspan="">'. $sittin2['center_name'].'</td>
                       </tr>
                       <tr>
                        <td><b>CENTER NUMBER:</b></td>
                        <td colspan="">'. $sittin1['center_no'].'</td>
                        <td colspan="2"><b>CENTER NUMBER:</b></td>
                        <td colspan="">'. $sittin2['center_no'].'</td>
                       </tr>
                       <tr>
                        <td><b>EXAM Number:</b></td>
                        <td colspan="">'. $sittin1['exam_no'].'</td>
                        <td colspan="2"><b>EXAM Number:</b></td>
                        <td colspan="">'. $sittin2['exam_no'].'</td>
                       </tr>
                       <tr><td colspan="5"></td>
                       </tr>
                        <td colspan="2"><b>SUBJECT</b></td>
                        <td colspan=""><b>GRADE</b></td>
                        <td colspan=""><b>SUBJECT</b></td>
                        <td colspan=""><b>GRADE</b></td>
                       </tr>
                       <tr>
                        <td colspan="2">'.$putme->getSubjectName($sittin1['sub1']).'</td>
                        <td colspan="">'.$sittin1['grade1'].'</td>
                        <td colspan="">'.$putme->getSubjectName($sittin2['sub1']).'</td>
                        <td colspan="">'.$sittin2['grade1'].'</td>
                       </tr>
                       <tr>
                       <td colspan="2">'.$putme->getSubjectName($sittin1['sub2']).'</td>
                       <td colspan="">'.$sittin1['grade2'].'</td>
                       <td colspan="">'.$putme->getSubjectName($sittin2['sub2']).'</td>
                       <td colspan="">'.$sittin2['grade2'].'</td>
                       </tr>
                       <tr>
                       <td colspan="2">'.$putme->getSubjectName($sittin1['sub3']).'</td>
                       <td colspan="">'.$sittin1['grade3'].'</td>
                       <td colspan="">'.$putme->getSubjectName($sittin2['sub3']).'</td>
                       <td colspan="">'.$sittin2['grade3'].'</td>
                       </tr>
                       <tr>
                       <td colspan="2">'.$putme->getSubjectName($sittin1['sub4']).'</td>
                       <td colspan="">'.$sittin1['grade4'].'</td>
                       <td colspan="">'.$putme->getSubjectName($sittin2['sub4']).'</td>
                       <td colspan="">'.$sittin2['grade4'].'</td>
                       </tr>
                       <tr>
                       <td colspan="2">'.$putme->getSubjectName($sittin1['sub5']).'</td>
                       <td colspan="">'.$sittin1['grade5'].'</td>
                       <td colspan="">'.$putme->getSubjectName($sittin2['sub5']).'</td>
                       <td colspan="">'.$sittin2['grade5'].'</td>
                       </tr>
                       <tr>
                       <td colspan="2">'.$putme->getSubjectName($sittin1['sub6']).'</td>
                       <td colspan="">'.$sittin1['grade6'].'</td>
                       <td colspan="">'.$putme->getSubjectName($sittin2['sub6']).'</td>
                       <td colspan="">'.$sittin2['grade6'].'</td>
                       </tr>
                       <tr>
                       <td colspan="2">'.$putme->getSubjectName($sittin1['sub7']).'</td>
                       <td colspan="">'.$sittin1['grade7'].'</td>
                       <td colspan="">'.$putme->getSubjectName($sittin2['sub7']).'</td>
                       <td colspan="">'.$sittin2['grade7'].'</td>
                       </tr>
                       <tr>
                       <td colspan="2">'.$putme->getSubjectName($sittin1['sub8']).'</td>
                       <td colspan="">'.$sittin1['grade8'].'</td>
                       <td colspan="">'.$putme->getSubjectName($sittin2['sub8']).'</td>
                       <td colspan="">'.$sittin2['grade8'].'</td>
                       </tr>
                       <tr>
                       <td colspan="2">'.$putme->getSubjectName($sittin1['sub9']).'</td>
                       <td colspan="">'.$sittin1['grade9'].'</td>
                       <td colspan="">'.$putme->getSubjectName($sittin2['sub9']).'</td>
                       <td colspan="">'.$sittin2['grade9'].'</td>
                       </tr>
                       ';
                      //  if (!empty($sittin2)) {
                      //    echo '
                      //    <tr><td colspan="5"></td></tr>
                      //    <td>EXAM TYPE:</td>
                      //    <td colspan="2">'. $sittin2['exam_type'].'</td>
                      //    </tr>
                      //    <tr>
                      //    <td>EXAM YEAR:</td>
                      //    <td colspan="2">'. $sittin2['exam_year'].'</td>
                      //    </tr>
                      //    <tr>
                      //    <td>CENTER NAME:</td>
                      //    <td colspan="2">'. $sittin2['center_name'].'</td>
                      //    </tr>
                      //    <tr>
                      //     <td>CENTER NUMBER:</td>
                      //     <td colspan="2">'. $sittin2['center_no'].'</td>
                      //    </tr>
                      //    <tr>
                      //     <td>EXAM Number:</td>
                      //     <td colspan="2">'. $sittin2['exam_no'].'</td>
                      //    </tr>
                      //    <tr><td colspan="5"></td></tr>
  
                      //    <td>SUBJECT</td>
                      //    <td colspan="2">GRADE</td>
                      //    </tr>
                      //    <tr>
                      //    <td colspan="">'.$putme->getSubjectName($sittin2['sub1']).'</td>
                      //    <td colspan="2">'.$sittin2['grade1'].'</td>
                      //    </tr>
                      //    <tr>
                      //    <td colspan="">'.$putme->getSubjectName($sittin2['sub2']).'</td>
                      //    <td colspan="2">'.$sittin2['grade2'].'</td>
                      //    </tr>
                      //    <tr>
                      //    <td colspan="">'.$putme->getSubjectName($sittin2['sub3']).'</td>
                      //    <td colspan="2">'.$sittin2['grade3'].'</td>
                      //    </tr>
                      //    <tr>
                      //    <td colspan="">'.$putme->getSubjectName($sittin2['sub4']).'</td>
                      //    <td colspan="2">'.$sittin2['grade4'].'</td>
                      //    </tr>
                      //    <tr>
                      //    <td colspan="">'.$putme->getSubjectName($sittin2['sub5']).'</td>
                      //    <td colspan="2">'.$sittin2['grade5'].'</td>
                      //    </tr>
                      //    <tr>
                      //    <td colspan="">'.$putme->getSubjectName($sittin2['sub6']).'</td>
                      //    <td colspan="2">'.$sittin2['grade6'].'</td>
                      //    </tr>
                      //    <tr>
                      //    <td colspan="">'.$putme->getSubjectName($sittin2['sub7']).'</td>
                      //    <td colspan="2">'.$sittin2['grade7'].'</td>
                      //    </tr>
                      //    <tr>
                      //    <td colspan="">'.$putme->getSubjectName($sittin2['sub8']).'</td>
                      //    <td colspan="2">'.$sittin2['grade8'].'</td>
                      //    </tr>
                      //    <tr>
                      //    <td colspan="">'.$putme->getSubjectName($sittin2['sub9']).'</td>
                      //    <td colspan="2">'.$sittin2['grade9'].'</td>
                      //    </tr>
                         
                      //    ';
                      //  }
                       if (!empty($alevel) && $info['mode_of_entry'] =='DE') {
                        echo '
                        <tr><td colspan="5"></td></tr>
                        <tr><td colspan="5"><h3>A\'Level</h3></td></tr>
                        <td colspan="2"><b>QUALIFICATION:</b></td>
                        <td colspan="3">'. $alevel['alevel'].'</td>
                        </tr>
                        <tr>
                        <td colspan="2"><b>YEAR:</b></td>
                        <td colspan="3">'. $alevel['year_from'].' - '.$alevel['year_to'].'</td>
                        </tr>
                        <tr>
                        <td colspan="2"><b>COURSE OF STUDY/IJMB SUBJECTS:</b></td>
                        <td colspan="3">'. $alevel['course'].'</td>
                        </tr>
                        <tr>
                         <td colspan="2"><b>INSITITUTION:</b></td>
                         <td colspan="3">'. $alevel['institution'].'</td>
                        </tr>
                        <tr>
                         <td colspan="2"><b>GRADE:</b></td>
                         <td colspan="3">'. $alevel['grade'].'</td>
                        </tr>
                        <tr>
                         <td colspan="2"><b>CGPA/IJMB POINT:</b></td>
                         <td colspan="3">'. $alevel['cgpa'].'</td>
                        </tr>
                        ';
                      }
                       echo '
                     </table>
                   </div><!-- end row -->
                   <br/>
                   <div class="undertaking text-left">
                      <h4 style="text-decoration: underline">Declaration</h4>
                      <p class="text-justify">
                        I, <b> '. $info['lastname']. " ".$info['firstname']." ".$info['othernames'].' </b> hereby declare that the statements made by me in the application form are true and complete
                        to the best of my knowledge. 
                        I also understand that in case, any of my statement is found <b>untrue</b> during the screening ,
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
    
     
        