<?php 
        require_once('../config/classes/View.php');
        require_once('../config/classes/User.php');
        // require_once('../config/classes/Putme.php');
        $view = new View();
        $user = new User();
        // $putme = new Putme();

        $user->is_authenticated('../auth/');
        $user->isStaff('../auth/logout.php');
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
              <div class="d-flex justify-content-between flex-wrap">
                <div class="d-flex align-items-end card flex-wrap">
                  
                  
                  
                  
                   <div class="container mt-5">
                       <img src="../../images/logo-mini.png" style="display:block;margin-left:auto;margin-right:auto;margin-bottom:2px;margin-top:-30px; width:100px;" class="rounded mx-auto d-block" alt="logo">
                                <h2 class="text-center">FUD STAFF COOPERATIVE SOCIETY LIMITED</h2>
                                <h5 class="text-center">[An Interest Free Thrift and Loan Society]</h5>
                                <h5 class="text-center mb-4" style="text-decoration: underline;">CASH PURCHASE OF SHARE</h5>
                        
                               <form class="my-form form-vertical" id="form-share">
                                    <div class="form-group row">
                                        <div class="col-md-6">
                                            <label for="name">Name:</label>
                                            <input type="text" class="form-control" required id="name" name="name">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="dept">Dept/Unit:</label>
                                            <input type="text" class="form-control" required id="dept" name="dept">
                                        </div>
                                    </div>
                        
                                    
                        
                                    <div class="form-group row">
                                        <div class="col-md-6">
                                            <label for="staff-no">Staff No:</label>
                                            <input type="text" class="form-control" required id="staff-no" name="staff-no">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="gsm-no">GSM No:</label>
                                            <input type="tel" class="form-control" required id="gsm-no" name="gsm-no">
                                        </div>
                                    </div>
                        
                                    <div class="form-group">
                                        <h5 style="text-decoration: underline;">AUTHORIZATION:</h5>
                                        <p>
                                            I 
                                             
                                            hereby made a cash payment to purchase ......................................................... (&#8358;..............) purchase  ............................... 
                                            units of FUDScoops Ltd. Shares as follows:
                                        </p>
                                    </div>
                        
                        
                                    <div class="form-group row">
                                        <div class="col-md-6">
                                            <label for="bank_name">Bank of Payment</label>
                                            <input type="text" class="form-control" required id="bank_name" name="bank_name">
                                        </div>
                                         <div class="col-md-6">
                                            <label for="acct-no">Teller. No:</label>
                                            <input type="text" class="form-control" required id="tellerno" name="tellerno">
                                        </div>
                                    </div>
                        
                                    <div class="form-group row">
                                        <div class="col-md-6">
                                            <label for="salary-grade">Amount Paid in Figures &#8358;:</label>
                                            <input type="text" class="form-control" required id="amountpaid" name="amountpaid">
                                        </div>
                            
                                        <div class="col-md-6">
                                            <label for="home-address">Date:</label>
                                            <input type="date"class="form-control" required  value="dob" id="dob" name="dob" placeholder="">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="home-address">Amount in words:</label>
                                        <input type="text" class="form-control" required id="share_amount" name="share_amount">
                                    </div>
                                    
                                <div class="row">
                                    
                                    <div class="form-group offset-md-5" style="display: flex; justify-content: center;">
                                        <button type="submit" class="btn btn-primary" style="font-size: 20px; padding: 10px 20px; width: 300px;">Submit</button>
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
        <?php 
          echo $view->subFooter('../');
        ?>