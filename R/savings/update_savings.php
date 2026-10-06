<?php 
        require_once('../../config/classes/View.php');
        require_once('../../config/classes/User.php');
        require_once('../../config/classes/MemberG1.php');
        require_once('../../config/classes/SavingsG1.php');
        $minMonthlySavings = SavingsG1::getMinMonthlySavings();
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
    $staff_info =  $user->getStaffInfoArray($_SESSION['username']);
    $banks = $bankService->getBanks();
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
                        <h3 class="text-center mb-4">INCREASE AND DECREASE OF SAVINGS</h3>
                        
                          <form id="update_savings_amount">
                             <input type="hidden" class="form-control" id="staff_no" name="staff_no" value="<?php echo $staff_info['sp_no']; ?>" readonly>
                            
                       
                        
                            <div class="form-group row">
                                <div class="col-md-6">
                                    <label for="savings_amount">New Savings Amount:</label>
                                    <input 
                                        required
                                        type="number" 
                                        class="form-control" 
                                        id="savings_amount" 
                                        name="savings_amount" 
                                        placeholder="Enter the new Savings Amount" 
                                        maxlength="10"
                                        pattern="\d*"
                                        min="<?php echo $minMonthlySavings; ?>"
                                        step="1"
                                    />
                                </div>
                            </div>
                            
                            
                            <div class="custom-control custom-checkbox mb-3">
                                <input type="checkbox" class="custom-control-input" id="savingsUpdate" name="undertaking" required>
                                <label class="custom-control-label" for="savingsUpdate">I hereby wish to request for savings Update from my savings with FUD Staff Cooperative Society Limited.</label>
                            </div>
    
                            <div id="$spNo" style="display: none;"></div>
                        
                            <button type="submit" id = "savingWithdrwal" class="btn btn-primary"><span id="spinnerUpdate" class="hidden-message spinner-border spinner-border-sm" style="display: none"></span> Submit</button>
                        </form>
  
                           
    
                                <div class="mt-3">
                                    <p><strong>NB:</strong> Savings account should always be left with an amount not less than the mandatory N<?php echo number_format($minMonthlySavings); ?> monthly savings.</p>
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