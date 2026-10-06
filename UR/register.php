<?php 
        require_once('../config/classes/View.php');
        require_once('../config/classes/User.php');
        require_once('../config/classes/SavingsG1.php');
        $minMonthlySavings = SavingsG1::getMinMonthlySavings();

        // require_once('../config/classes/Putme.php');
        $view = new View();
        $user = new User();
       
        
       $staff_info =  $user->getStaffInformation($_SESSION['username']);
       $employeeid = $user->getEmployeeId($_SESSION['username']);
       
       
    
       $user->is_authenticated('../auth/');
       $user->check_membership_application($employeeid, '/UR/update_savings.php', true); // Check for active membership
     
        //$payment = $putme->getPaymentInfoArray($_SESSION['username']);
        
        echo $view->subHeader('../');
    ?>
    <!-- partial:partials/_navbar.html -->
    <?php  echo $view->subPartialNav('../')?>
    <!-- partial -->
    <div class="container-fluid page-body-wrapper">
      <!-- partial:partials/_sidebar.html -->
      <?php  echo $view->nonMemberfSideNav()?>
      
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
                                <h3 class="text-center mb-4">MEMBERSHIP REGISTRATION FORM</h3>
                        
                        <?php
                        // var_dump($staff_info);
                        ?>
                       
                                <form class="my-form form-vertical" action="" id="registerMember">
                                    <div class="form-group row">
                                        <div class="col-md-6">
                                            <label for="name">Name:</label>
                                            <input type="text" class="form-control" id="name" name="name" value="<?php echo $staff_info['fname']. ' ' .$staff_info['oname'].' '. $staff_info['lname'] ?>" readonly>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="dept">Dept/Unit:</label>
                                            <input type="text" class="form-control" id="dept" name="dept" value="<?php echo $staff_info['dept'] ?>" readonly>
                                            
                                        </div>
                                    </div>
                        
                                    
                        
                                    <div class="form-group row">
                                        <div class="col-md-6">
                                            <label for="staff-no">Staff No:</label>
                                            <input type="text" class="form-control" id="staff_no" name="staff_no" value="<?php echo $staff_info['sp_no'] ?>" readonly>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="gsm-no">GSM No:</label>
                                            <input type="tel" class="form-control" id="gsm-no" name="gsm-no" value="<?php echo '+234' .$staff_info['phone_no'] ?>" readonly>
                                        </div>
                                    </div>
                        

                        
                                    <div class="form-group row">
                                        <div class="col-md-6">
                                            <label for="bank_name">Bank Name</label>
                                            <input type="text" class="form-control" id="bank_name" name="bank_name"  value="<?php echo $staff_info['bank_name'] ?>" readonly>
                                        </div>
                                         <div class="col-md-6">
                                            <label for="acct-no">Acct. No:</label>
                                            <input type="text" class="form-control" id="acct-no" name="acct-no" value="<?php echo str_pad($staff_info['acctno'], 10, '0',STR_PAD_LEFT);  ?>" maxlength="10" readonly>  
                                        </div>
                                    </div>
                        
                                    <div class="form-group">
                                        <label for="salary-grade">Present Salary Grade:</label>
                                        <input type="text" class="form-control" id="salary-grade" name="salary-grade" value="<?php echo $staff_info['grade'] ?>" readonly >
                                    </div>
                                    <!--
                                    <div class="form-group">
                                        <label for="home-address">Permanent Home Address:</label>
                                        <textarea class="form-control" id="home-address" name="home-address" rows="3"></textarea>
                                    </div>
                                    !-->
 
                                    <?php
                                    $default_appointment_type = isset($staff_info['nature_of_appo']) ? $staff_info['nature_of_appo'] : '';
                                    ?>                       
                                    <div class="form-group row">
                                        <label>Type of Appointment:</label><br>
                                        <div class="col-md-3">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="appointment-type" id="permanent" value="permanent" 
                                                    <?php if ($default_appointment_type === 'PERMANENT') echo 'checked'; ?> disabled>
                                                <label class="form-check-label" for="permanent">Permanent</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="appointment-type" id="contract" value="contract" 
                                                    <?php if ($default_appointment_type === 'CONTRACT') echo 'checked'; ?> disabled>
                                                <label class="form-check-label" for="contract">Contract</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="appointment-type" id="sabbatical" value="sabbatical" 
                                                    <?php if ($default_appointment_type === 'SABBATICAL') echo 'checked'; ?> disabled>
                                                <label class="form-check-label" for="sabbatical">Sabbatical Leave</label>
                                            </div>
                                        </div>
                                    </div>                        
                        
                                    
                                    <div class="form-group row">
                                        <div class="col-md-6">
                                            <label for="monthly_savings">Proposed Monthly Savings:</label>
                                            <input type="number" class="form-control" id="monthly_savings" name="monthly_savings"
                                                   required min="<?php echo $minMonthlySavings; ?>" step="1"
                                                   placeholder="Minimum ₦<?php echo number_format($minMonthlySavings); ?>">
                                            <small class="form-text text-muted">Minimum ₦<?php echo number_format($minMonthlySavings); ?> per month.</small>
                                        </div>
                            
                                        <div class="col-md-6">    
                                            <label for="reg-fees">Reg. Fees:</label>
                                            <input type="text" class="form-control" id="reg_fees" name="reg_fees" value="₦2500.00" readonly>
                                        </div>
                                    </div>
                                     
                                    <div class="form-group row">
                                        <div class="col-md-6">
                                            <label for="next-of-kin-name">Next of Kin’s Name:</label>
                                            <input type="text" class="form-control" id="next_of_kin_name" name="next_of_kin_name" required>
                                        </div>
                        
                                        <div class="col-md-6">
                                            <label for="next-of-kin-gsm">Next of Kin’s GSM No:</label>
                                            <input type="tel" class="form-control" id="next_of_kin_gsm" name="next_of_kin_gsm" maxlength="13" required>
                                        </div>
                                    </div>
                        
                                    <div class="form-group">
                                        <label for="next-of-kin-address">Next of Kin’s Address:</label>
                                        <textarea class="form-control" id="next_of_kin_address" name="next_of_kin_address" rows="3" required></textarea>
                                    </div>
                        
                                    <div class="form-group">
                                        <h4>UNDERTAKING:</h4>
                                        <p>
                                            I 
                                             <input class="form-check-input" required type="checkbox" name="undertaking" id="undertaking">
                                            hereby agree to join FUD Staff Cooperative Society Limited and abide by its Bye-Laws and resolutions made thereafter. Furthermore, I authorize the FUD Finance Officer to deduct the proposed monthly savings and the registration fees and pay the same to the society’s account.
                                        </p>
                                    </div>
                        
                                    
                        <div class="" style="justify-content:center; display:flex; margin:10px;">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                          <div class="msg"></div>           
                                </form>
                    </div>
                  
                  
                  
                  
                  
                  
                                   
                </div>
               
              
              </div>
            </div>
          </div>   
        </div>
        <!-- content-wrapper ends -->
        <?php 
          echo $view->subFooter('../');
        ?>