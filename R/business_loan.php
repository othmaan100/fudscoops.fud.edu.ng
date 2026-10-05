<?php 
        require_once('../config/classes/View.php');
        require_once('../config/classes/User.php');
        require_once('../config/classes/MemberG1.php');
        $view = new View();
        $user = new User();
        $member = new MemberG1();
        // $putme = new Putme();
        $user->is_authenticated('../auth/');
        $user->isStaff('../auth/logout.php');
        //$payment = $putme->getPaymentInfoArray($_SESSION['username']);
        $staff_info =  $user->getStaffInformation($_SESSION['username']);
            $banks = $member->getBanks();
        echo $view->subHeader('../');
    ?>
    <!-- partial:partials/_navbar.html -->
    <?php  echo $view->subPartialNav('../')?>
    <!-- partial -->
    <div class="container-fluid page-body-wrapper">
      <!-- partial:partials/_sidebar.html -->
      <?php  echo $view->staffSideNav()?>
      
      <!-- partial -->
      <div class="main-panel">
        <div class="content-wrapper">
          
          <div class="row">
            <div class="col-md-12 grid-margin">
              <div class="d-flex justify-content-between flex-wrap">
                <div class="d-flex align-items-end card flex-wrap">
                  
                  
                  
                  
                   <div class="container mt-5">
                               
                        <h1 class="text-center">FUD STAFF COOPERATIVE SOCIETY LIMITED</h1>
                        <h2 class="text-center">[An Interest Free Thrift and Loan Society]</h2>
                        <h3 class="text-center mb-4"> BUSINESS LOAN APPLICATION</h3>
                
                        <form id="business_loan">
                            <div class="form-group row">
                                <div class="col-md-6">
                                    <label for="name">Name:</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo $staff_info['fname']. ' ' .$staff_info['lname'].' '. $staff_info['oname'] ?>" readonly>
                                </div>
                
                                <div class="col-md-6">
                                    <label for="dept">Dept/Unit:</label>
                                    <input type="text" class="form-control" id="dept" name="dept" value="<?php echo $staff_info['dept'] ?>" readonly>
                                </div>
                            </div>
                
                            <div class="form-group row">
                                <div class="col-md-6">
                                    <label for="staff-no">Staff No:</label>
                                    <input type="text" class="form-control" id="staff-no" name="staff_no" value="<?php echo $staff_info['sp_no'] ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label for="gsm-no">GSM No:</label>
                                    <input type="tel" class="form-control" id="gsm-no" name="gsm-no" value="<?php echo $staff_info['phone_no'] ?>" readonly>
                                </div>
                                
                            </div>
                
                            <div class="form-group row">
                                
                                <div class="col-md-12">
                                    <label for="membership-reg-no">Membership Reg. No:</label>
                                    <input type="text" class="form-control" id="membership-reg-no" name="membership-reg-no" value="<?php echo $staff_info['sp_no'] ?>" readonly>
                                </div>
                                
                            </div>
                
                            <div class="form-group row">
                                <div class="col-md-12">
                                    <label for="acct-no">Material(s) needed:</label>
                                     <input type="text" class="form-control" id="meterials" name="meterials" placeholder="specify the meterials" required>
                                </div>
                            </div>
                            <div class="form-group row">
                               <div class="col-md-12">
                                    <label for="acct-no">Reason(s):</label>
                                   <textarea class="form-control" id="reason" name="reason" rows="4"></textarea required>
                                </div>
                            </div>
                         <div class="form-group row">
                            <div class="col-md-6">
                                <label for="amount-applied">Amount Applied:</label>
                                <input type="tel" class="form-control" id="amount_applied" name="amount-applied" required>
                            <h4 class="amount_msg text-primary"></h4v>
                            </div>
                        </div>
                        
                        <div class="form-group row mt-2">
                            <div class="col-md-12">
                                <p class="text-muted">
                                    Please note the following conditions:
                                </p>
                                <ul class="list-unstyled">
                                    <li>10% profit margin will be added.</li>
                                    <li>You must be an active member.</li>
                                    <li>The supply must be no more than three times your share capital.</li>
                                    <li>You must have a net salary sufficient to cover the deductible amount within the repayment period.</li>
                                </ul> 
                                <p class="mb-1">
                                    Repayment Period:
                                </p>
                                <div class="row">
                                    <div class="col-md-3 offset-1 mb-2">
                                        <div class="d-flex align-items-center">
                                            <label class="form-check-label ms-2" for="loan_repayment_3">3 months</label>
                                            <input type="radio" class="form-check-input" id="loan_repayment_3" name="loan_repayment" value="3" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <div class="d-flex align-items-center">
                                            <label class="form-check-label ms-2" for="loan_repayment_6">6 months</label>
                                            <input type="radio" class="form-check-input" id="loan_repayment_6" name="loan_repayment" value="6">
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <div class="d-flex align-items-center">
                                            <label class="form-check-label ms-2" for="loan_repayment_9">9 months</label>
                                            <input type="radio" class="form-check-input" id="loan_repayment_9" name="loan_repayment" value="9">
                                        </div>
                                    </div>
                                    <div class="col-md-2  mb-2">
                                        <div class="d-flex align-items-center">
                                            <label class="form-check-label ms-2" for="loan_repayment_12">12 months</label>
                                            <input type="radio" class="form-check-input" id="loan_repayment_12" name="loan_repayment" value="12" >
                                        </div>
                                    </div>
                                    <!--<div class="col-md-6 mb-2">-->
                                    <!--    <div class="d-flex align-items-center">-->
                                    <!--         <input type="tel" class="form-control form-control-sm" id="loan_repayment_specify" name="loan_repayment_specify" placeholder="or specify">-->
                                    <!--    </div>-->
                                    <!--</div>-->
                                    
                                </div>
                            </div>
                        </div>
                            <p class="mb-1">
                                   SUPPLIER’S INFORMATION
                             </p>
                             
                            <div class="form-group row">
                                <div class="col-md-6">
                                    <label for="Supplier’s Name">Supplier’s Name:</label>
                                    <input type="text" class="form-control" id="supplier_name" name="supplier_name" placeholder="The name should match the one used at the bank" required>
                                </div>
                
                                <div class="col-md-6">
                                    <label for="supplier_address">Address:</label>
                                    <input type="text" class="form-control" id="supplier_address" name="supplier_address" required>
                                </div>
                            </div>
                
                            <div class="form-group row">
                                <div class="col-md-6">
                                    <label for="gsm-no">GSM No:</label>
                                    <input type="number" class="form-control" id="supplier_gsm-no" name="supplier_gsm-no" required>
                                </div>
                                
                                 <div class="col-md-6">
                                    <label for="bank">Bank:</label>
                                     <select class="form-control" id="bank" name="bank" required>
                                        <option value="">Select a Bank</option>
                                        <?php
                                            foreach ($banks as $bank) {
                                            echo '<option value="' . $bank['id'] . '">' . $bank['name'] . '</option>';
                                            }
                                        ?>
                                    </select>
                                    <!--<input type="text" class="form-control" id="bank" name="bank" required>-->
                                </div>
                                
                            </div>
                
                            <div class="form-group row">
                            
                                <div class="col-md-6">
                                    <label for="acct-no">Acct. No.:</label>
                                    <input type="number" class="form-control" id="account_number" name="account_number" placeholder="Enter account number" 
                                        maxlength="10"
                                        pattern="\d{1,10}" 
                                        title="Please enter up to 10 digits only" 
                                        required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="sort-code">Sort Code:</label>
                                    <input type="number" class="form-control" id="supplier_sort-code" name="supplier_sort-code" >
                                </div>
                            </div>
                            
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
                             
                                <div class="msg"></div>
                
                            <button type="submit" class="btn btn-danger mb-3">Submit</button>
                        </form>
                  </div>
               
              
              </div>
            </div>
          </div>   
        </div>
        <!-- content-wrapper ends -->
        <?php 
          echo $view->subFooter('../');
        ?>