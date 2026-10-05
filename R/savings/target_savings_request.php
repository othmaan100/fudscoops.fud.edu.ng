<?php 
        require_once('../../config/classes/View.php');
        require_once('../../config/classes/User.php');
        require_once('../../config/classes/MemberG1.php');
        // require_once('../config/classes/Putme.php');
        $view = new View();
        $user = new User();
        // $putme = new Putme();

        $user->is_authenticated('../../auth/');
        $user->isStaff('../../auth/logout.php');
        //$payment = $putme->getPaymentInfoArray($_SESSION['username']);
        
        echo $view->subHeader('../../');
    ?>
    <!-- partial:partials/_navbar.html -->
    <?php  echo $view->subPartialNav('../../')?>
    <!-- partial -->
    <div class="container-fluid page-body-wrapper">
      <!-- partial:partials/_sidebar.html -->
      <?php  echo $view->staffSideNav()?>
      
      <!-- partial -->
    <?php  
    $user = new User();
    $bankService = new MemberG1(); 
    $member = new MemberG1();
    $name = new User();
    $staff_info =  $user->getStaffInfoArray($_SESSION['username']);
    $banks = $bankService->getBanks();
    $employ_id =  $user->getEmployeeId($staff_info['sp_no']);
    $member_id =  $member->getMemberId($employ_id );
    $fullname = $name->getFullname($staff_info['sp_no']);
    
    $staffGrade = explode("/", $staff_info['sp_no']);
    $grade = $staffGrade[0]; // This will be "SP"
    //echo $firstPart;

    //print($fullname);
   // print($employ_id);
   // print($member_id);
    ?>  
      
      <div class="main-panel">
        <div class="content-wrapper">
          
          <div class="row">
            <div class="col-md-12 grid-margin">
              <div class="d-flex justify-content-between flex-wrap">
                <div class="d-flex align-items-end card flex-wrap">

                   <div class="container mt-5">
                               
                        <h1 class="text-center">FUD STAFF COOPERATIVE SOCIETY LIMITED</h1>
                        <h2 class="text-center">[An Interest Free Thrift and Loan Society]</h2>
                        <h3 class="text-center mb-4">TARGET SAVINGS APPLICATION </h3>
                        <div class="msg"></div> 
                          <form id="targetsavingsrequest">
                            
                             
                              <div class="form-group row">
                                <label for="amount" class="col-sm-2 col-form-label">Amount to save:</label>
                                <div class="col-sm-10">
                                  <input type="number" class="form-control" id="amount" name="amount">
                                <input type="hidden" class="form-control" id="grade" name="grade" value="<?php echo $grade; ?>">
                                </div>
                              </div>
                             <div class="form-group row">
                                  <div class="col-sm-6">
                                    <label for="periodSave">Period to save (In Months):</label>
                                   <input type="number" class="form-control" id="periodSave" name="periodSave" >
                                  </div>
                                  <div class="col-sm-6">
                                    <label for="withdraw">Period to withdraw(In Months):</label>
                                    <input type="text" class="form-control" id="periodWithdraw" name="periodWithdraw" >
                                  </div>
                                </div>

                              <h3 class="mt-4">Referee:</h3>
                               <div class="form-group row">
                            
                                <div class="col-md-6">
                                    <label for="guarantor_sp_no">Guarantor's staff number:</label>
                                    <div style="display: flex; align-items: center;">
                                        <input type="text" class="form-control" id="guarantor_sp_no" name="guarantor_sp_no" required placeholder="Please enter guarantor's staff number" style="flex: 1; margin-right: 10px;">
                                        <button type="button" class="btn btn-success" id="verify_guarantor" rel="<?php echo $_SESSION['username']?>">Verify Guarantor</button>                                    
                                    </div>
                                </div>
                                 <div class="col-md-6 guarantor"></div>
                                
                            </div>
                             

                    
                    
                    
                    
                    
                        <input type="hidden" name="member_id" id="member_id" value="<?php echo $member_id; ?>">
                           <div class="form-group">
                                <label>Applicant’s Request:</label>
                                <div class="custom-control custom-checkbox mb-3">
                                    <input type="checkbox" class="custom-control-input" id="savingsUpdate" name="undertaking" required>
                                    <label class="custom-control-label" for="savingsUpdate">
                                        I hereby authorize the withdrawal of the stated amount from my savings and transfer it to the bank account specified above.
                                    </label>
                                </div>
                            </div>

                            <button type="submit" id = "savingWithdrwal" class="btn btn-primary">Submit</button>

                        </form>
                                <div class="mt-3">
                                    <p><strong>NB:</strong> Savings account should always be left with an amount not less than the mandatory N2,000 monthly savings.</p>
                                </div>
                    </div>
               
              </div>
            </div>
          </div>   
        </div>
        <!-- content-wrapper ends -->
        
        <?php 
          echo $view->subFooter('../../');
        ?>