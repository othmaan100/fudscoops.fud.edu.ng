<?php 
        require_once('../../config/classes/View.php');
        require_once('../../config/classes/User.php');
        require_once('../../config/classes/MemberG1.php');
        // require_once('../config/classes/Putme.php');
        $view = new View();
        $user = new User();
         $member = new MemberG1();
        // $putme = new Putme();

        $user->is_authenticated('../../auth/');
        $user->isStaff('../../auth/logout.php');
        //$payment = $putme->getPaymentInfoArray($_SESSION['username']);
        $currentsavings= $member-> getCurrentSavingsAmount($_SESSION['username']);
         
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
    //print($fullname);
   // print($employ_id);
   // print($member_id);
    ?>  
      
      <div class="main-panel">
        <div class="content-wrapper">
          
          <div class="row">
            <div class="col-md-12 grid-margin">
              <div class="d-flex1 justify-content-between flex-wra1p">
                <div class="d-flex1 align-items-end1 card flex-wrap1">

                   <div class="container mt-5">
                               
                        <h1 class="text-center">FUD STAFF COOPERATIVE SOCIETY LIMITED</h1>
                        <h2 class="text-center">[An Interest Free Thrift and Loan Society]</h2>
                        <h3 class="text-center mb-4">SAVINGS WITHDRAWAL</h3>
                        
                          <form id="savingswithdrawal">
                             <input type="text" class="form-control" id="staff_no" name="staff_no" value="<?php echo $fullname  ?>" readonly>
                                <div class="form-group d-flex align-items-center">
                                    <h2 class="text-primary mb-0 mr-3">Total Savings:</h2>
                                    <h3 class="text-success font-weight-bold mb-0">
                                        ₦<?php echo number_format($currentsavings, 2); ?>
                                    </h3>
                                </div>

                            <div class="form-group row">
                                <div class="col-md-6">
                                    <label for="bank">Bank*:</label>
                                    <select class="form-control" id="bank" name="bank_id" required>
                                        <option value="">Select a Bank</option>
                                        <?php
                                            foreach ($banks as $bank) {
                                            echo '<option value="' . $bank['name'] . '">' . $bank['name'] . '</option>';
                                            }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        <div class="form-group row">
                            <div class="col-md-6">
                                <label for="acct_no">Account Name*:</label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="acct_name" 
                                    name="acct_name" 
                                    placeholder="Enter account Name" 
                                    
                                    title="Please enter up to 10 digits only" 
                                    required
                                />
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-6">
                                <label for="acct_no">Account Number*:</label>
                                <input 
                                    type="number" 
                                    class="form-control" 
                                    id="acct_no" 
                                    name="acct_no" 
                                    placeholder="Enter account number" 
                                    maxlength="10"
                                    pattern="\d{1,10}" 
                                    title="Please enter up to 10 digits only" 
                                    required
                                />
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <div class="col-md-6">
                                <label for="withdrawal_amount">Amount*:</label>
                                <input 
                                    type="number" 
                                    class="form-control" 
                                    id="withdrawal_amount" 
                                    name="withdrawal_amount" 
                                    placeholder="Enter Amount to Withdraw"
                                    style="width: 100%;" 
                                    min="1" 
                                    required
                                />
                            </div>
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
                        <div class="msg"></div> 
                    </div>
               
              </div>
            </div>
          </div>   
        </div>
        <!-- content-wrapper ends -->
        
        <?php 
          echo $view->subFooter('../../');
        ?>