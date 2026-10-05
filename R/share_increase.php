<?php 
        require_once('../config/classes/View.php');
        require_once('../config/classes/User.php');
        require_once '../config/classes/MemberG1.php';
        // require_once('../config/classes/Putme.php');
        $view = new View();
        $user = new User();
        // $putme = new Putme();

        $user->is_authenticated('../auth/');
        $user->isStaff('../auth/logout.php');
        $member1 = new MemberG1();
        
        $staff_info =  $user->getStaffInformation($_SESSION['username']);
        $employeeid = $user->getEmployeeId($_SESSION['username']);
        $memberid = $user->getMemberId($employeeid);
        $share_info = $user->getMemberShareInformation($memberid);
        $share_unit = $user->getShareUnitInfoArray();
        $date = !empty($share_info['date']) ? date('Y-m-d', strtotime($share_info['date'])) : date('Y-m-d');
        $currentsavings= $member1-> getCurrentSavingsAmount($_SESSION['username']);
        
    
        //$payment = $putme->getPaymentInfoArray($_SESSION['username']);
        
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
              <div class="d-flex1 justify-content-between flex-wrap1">
                <div class="d-flex1 align-items-end card flex-wrap1">
                  
                  
                  
                  
                   <div class="container mt-5">
                       <img src="../../images/logo-mini.png" style="display:block;margin-left:auto;margin-right:auto;margin-bottom:2px;margin-top:-30px; width:100px;" class="rounded mx-auto d-block" alt="logo">
                                <h2 class="text-center">FUD STAFF COOPERATIVE SOCIETY LIMITED</h2>
                                <h5 class="text-center">[An Interest Free Thrift and Loan Society]</h5>
                                <h5 class="text-center mb-4" style="text-decoration: underline;">SHARE INCREASE</h5>
                        
                               <form class="my-form form-vertical" id="share_conversion" method="POST" action="print.php" onsubmit="handleSubmit(event)" target="_blank">
                                    <div class="form-group row">
                                        <div class="col-md-6">
                                            <label for="name">Name:</label>
                                            <input type="text" class="form-control" required id="name" name="name" value="<?php echo $staff_info['fname']. ' ' .$staff_info['lname'].' '. $staff_info['oname'] ?>" readonly>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="dept">Dept/Unit:</label>
                                            <input type="text" class="form-control" required id="dept" name="dept" value="<?php echo $staff_info['dept'] ?>" readonly>
                                        </div>
                                    </div>
                        
                                    
                        
                                    <div class="form-group row">
                                        <div class="col-md-6">
                                            <label for="staff-no">Staff No:</label>
                                            <input type="text" class="form-control" required id="staff_no" name="staff_no" value="<?php echo $staff_info['sp_no'] ?>" readonly>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="gsm-no">GSM No:</label>
                                            <input type="tel" class="form-control" required id="gsm-no" name="gsm-no" value="<?php echo '+234' .$staff_info['phone_no'] ?>" readonly>
                                        </div>
                                    </div>
                        
                                    
                                        <h5 style="text-decoration: underline;">AUTHORIZATION:</h5>
                                        
                                         <p>
                                            <div class="form-group row" style="display: flex; align-items: center; width: 100%;">
                                                <div class="col-sm-10" style="flex: 2; margin-right: -40px;">
                                                    I <b><?php echo $staff_info['fname']. ' ' .$staff_info['lname'].' '. $staff_info['oname'] ?></b>
                                                     
                                                        with Total 
                                                        <span style="display: inline-block; position: relative; border-bottom: 2px dotted black; padding-bottom: 5px; font-weight: bold;">
                                                            <span style="background-color: white; position: relative; z-index: 1; padding: 0 5px;">
                                                                FUDSCOOPS Savings of: &#8358;<?php echo number_format($currentsavings);?> 
                                                            </span>
                                                        </span>
                                                </div>
                                    
                                            </div>
                                        </p>
                                        
                                        <p>
                                            <div class="form-group row">
                                                
                                               
                                                
                                                <h5>
                                                      hereby agreed to convert part of my savings to purchase units of FUDScoops Ltd. Shares as follows:
                                                </h5>
                                            </div>
                                        </p>
                        
                                    <div class="form-group row">
                                        <div class="col-md-6">
                                            <label for="salary-grade">Amount Paid in Figures (₦):</label>
                                            <input type="number" class="form-control" required id="amount_paid2" name="amount_paid" value="<?php echo $share_info['amount_paid']?>">
                                        </div>
                            
                                        <div class="col-md-6">
                                            <label for="exampleInputUsername1">Date: </label>
                                            <input type="date" class="form-control form-control-sm" required  value="<?php echo $date ?>" id="date" name="date" placeholder="">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="home-address">Amount in words:</label>
                                        <input type="text" class="form-control" required id="share_amount_word" name="share_amount_word" value="<?php echo $share_info['shares_amount_word']?>">
                                    </div>
                                    
                                <div class="row">
                                    <?php
                                        // Determine if button should be disabled
                                        if ($share_info['share_status'] == 1) {
                                            $button = 'disabled';
                                        } else {
                                            $button = '';
                                        }
                                        ?>
                                        <div class="form-group offset-md-5" style="display: flex; justify-content: center;">
                                            <input type="hidden" name="username" id="username" value="<?php echo $_SESSION['username']; ?>">
                                            <button type="submit" class="btn btn-primary" <?php echo $button; ?> 
                                                style="font-size: 20px; padding: 10px 20px; width: 200px;">Submit</button>
                                        </div>

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
        
        </div>
        <!-- main panel ends -->
        </div>
        <!-- container fluids ends -->
        
        <!-- JavaScript to enforce matching values -->
        <script>
            document.getElementById('share_amount').addEventListener('input', function () {
                document.getElementById('amount_paid').value = this.value;
            });
        </script>
        
        <?php 
          echo $view->subFooter('../');
        ?>