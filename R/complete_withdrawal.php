<?php 
        require_once('../config/classes/View.php');
        require_once('../config/classes/User.php');
          require_once('../config/classes/MemberG1.php');
        // require_once('../config/classes/Putme.php');
        $view = new View();
        $user = new User();
        // $putme = new Putme();

        $user->is_authenticated('../auth/');
        $user->isStaff('../auth/logout.php');
        //$payment = $putme->getPaymentInfoArray($_SESSION['username']);
        $member = new MemberG1();
        $bankService = new MemberG1(); 
        $banks = $bankService->getBanks();
        $staff_info =  $user->getStaffInfoArray($_SESSION['username']);
        
        $currentsavings= $member-> getCurrentSavingsAmount($_SESSION['username']);
         
         
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
                        <h3 class="text-center mb-4">COMPLETE WITHDRAWAL FORM</h3>
                
                        <form id="completeWithdrawal">
                            
                            <input type="hidden" class="form-control" id="staff_no" name="staff_no" value="<?php echo $staff_info['sp_no']; ?>" readonly>
                            <div class="form-group d-flex align-items-center">
                                <h2 class="text-primary mb-0 mr-3">Total Savings:</h2>
                                <h3 class="text-success font-weight-bold mb-0">
                                    ₦<?php echo number_format($currentsavings, 2); ?>
                                </h3>
                            </div>

                            <div class="form-group row">
                              
                                 <div class="col-md-6">
                                    <label for="bank">Bank:</label>
                                    <select class="form-control" id="bank" name="bank_id" required>
                                        <option value="">Select a Bank</option>
                                        <?php
                                            foreach ($banks as $bank) {
                                            echo '<option value="' . $bank['id'] . '">' . $bank['name'] . '</option>';
                                            }
                                        ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="sort-code">Sort Code:</label>
                                    <input type="text" class="form-control" id="sort-code" name="sort-code">
                                </div>
                            </div>
                
                            <div class="form-group row">
                                
                
                                <div class="col-md-6">
                                    <label for="acct-no">Acct. No.:</label>
                                    <input type="text" class="form-control" id="acct-no" name="acct_no" required>
                                </div>
                            </div>
                               <div class="form-group row">
                            <div class="col-md-6">
                                <label for="acct_no">Account Name:</label>
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
                
                            <button type="submit" class="btn btn-primary">Submit</button>
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