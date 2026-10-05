<?php 
        require_once('../config/classes/View.php');
        require_once('../config/classes/User.php');
        // require_once('../config/classes/Putme.php');
        $view = new View();
        $user = new User();
        // $putme = new Putme();

        $user->is_authenticated('../auth/');
        $user->isStaff('../auth/logout.php');
        
        $staff_info =  $user->getStaffInformation($_SESSION['username']);
        $employeeid = $user->getEmployeeId($_SESSION['username']);
        $memberid = $user->getMemberId($employeeid);
        $share_info = $user->getMemberShareInformation($memberid);
        $share_unit = $user->getShareUnitInfoArray();
        $shareAmount = $user->getShareAmount($_SESSION['username']);
        //echo $shareAmount;
        //die();
        $date = date('Y-m-d', strtotime($share_info['date']));
        
    
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
                                <h1 class="text-center">FUD STAFF COOPERATIVE SOCIETY LIMITED</h1>
                                <h4 class="text-center">[An Interest Free Thrift and Loan Society]</h4>
                                <h5 class="text-center mb-4" style="text-decoration: underline;">REQUEST FOR TRANSFER/SALE OF SHARES</h5>
                                <h5 class="text-center mb-4" style="border: 3px solid black; padding: 10px; width: fit-content; margin: 0 auto;">TO BE COMPLETED BY THE SELLING PARTY</h5>
                        
                               <form class="my-form form-vertical">
                                
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
                                
                                <div class="form-group row">
                                    
                                        <div class="col-md-6">
                                            <label for="salary-grade">Share Amount (₦):</label>
                                            <input type="number" class="form-control" required id="amount_paid" name="amount_paid" value="<?php echo $share_info['amount_paid']?>" readonly>
                                        </div>
                                
                                        <div class="col-md-6">
                                            <label for="exampleInputUsername1">Date: </label>
                                            <input type="date" class="form-control form-control-sm" required  value="<?php echo $date ?>" id="date" name="date" placeholder="">
                                        </div>
                                </div>
                                
                                <p>
                                            <div class="form-group row" style="display: flex; align-items: center; width: 100%;">
                                                <div class="col-sm-12" style="flex: 2; margin-right: -40px;">
                                                    I <b><?php echo $staff_info['fname']. ' ' .$staff_info['lname'].' '. $staff_info['oname'] ?></b>
                                                     
                                                        Hereby agreed to sell my share to:
                                                        
                                                </div>
                                                 
                                            </div>
                                </p>
                                    
                            <div class="form-group row">
                            
                                <div class="col-md-10">
                                    <label for="buyer_sp_no">Buyer's staff number:</label>
                                    <div style="display: flex; align-items: center;">
                                        <input type="text" class="form-control" id="buyer_sp_no" name="buyer_sp_no" required placeholder="Please enter buyer's staff number" style="flex: 1; margin-right: 10px;">
                                        <button type="button" class="btn btn-success" id="verify_buyer" rel="<?php echo $_SESSION['username']?>">Verify Buyer</button>                                    
                                    </div>
                                </div>
                            
                                <div class="buyer"></div>
                                
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