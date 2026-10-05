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
                               
                        <h1 class="text-center">FUD STAFF COOPERATIVE SOCIETY LIMITED</h1>
                        <h2 class="text-center">[An Interest Free Thrift and Loan Society]</h2>
                        <h3 class="text-center mb-4">COMPLETE WITHDRAWAL FORM</h3>
                
                        <form>
                            <div class="form-group row">
                                <div class="col-md-6">
                                    <label for="name">Name:</label>
                                    <input type="text" class="form-control" id="name" name="name">
                                </div>
                
                                <div class="col-md-6">
                                    <label for="dept">Dept/Unit:</label>
                                    <input type="text" class="form-control" id="dept" name="dept">
                                </div>
                            </div>
                
                            <div class="form-group row">
                                <div class="col-md-6">
                                    <label for="staff-no">Staff No:</label>
                                    <input type="text" class="form-control" id="staff-no" name="staff-no">
                                </div>
                
                                <div class="col-md-6">
                                    <label for="membership-reg-no">Membership Reg. No:</label>
                                    <input type="text" class="form-control" id="membership-reg-no" name="membership-reg-no">
                                </div>
                            </div>
                
                            <div class="form-group row">
                                <div class="col-md-6">
                                    <label for="gsm-no">GSM No:</label>
                                    <input type="tel" class="form-control" id="gsm-no" name="gsm-no">
                                </div>
                
                                <div class="col-md-6">
                                    <label for="sort-code">Sort Code:</label>
                                    <input type="text" class="form-control" id="sort-code" name="sort-code">
                                </div>
                            </div>
                
                            <div class="form-group row">
                                <div class="col-md-6">
                                    <label for="bank">Bank:</label>
                                    <input type="text" class="form-control" id="bank" name="bank">
                                </div>
                
                                <div class="col-md-6">
                                    <label for="acct-no">Acct. No.:</label>
                                    <input type="text" class="form-control" id="acct-no" name="acct-no">
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