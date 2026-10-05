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
                        <h3 class="text-center mb-4">SAVINGS WITHDRAWAL FORM</h3>
                        
                           <form id="confirmation-form">
                                <div class="form-group row">
                                    <div class="col-md-6">
                                        <label for="bank">Bank:</label>
                                        <select class="form-control" id="bank" name="bank">
                                            <option value="">Select a Bank</option>
                                        </select>
                                    </div>
                                </div>
                            
                                <div class="form-group row">
                                    <div class="col-md-6">
                                        <label for="acct-no">Acct No:</label>
                                        <input type="text" class="form-control" id="acct-no" name="acct-no">
                                    </div>
                                </div>
                            
                                <div class="form-group">
                                    <label>Applicant’s Request:</label>
                                    <p>
                                        I hereby wish to request for savings withdrawal of 
                                        (N<input type="text" class="form-control d-inline-block" name="withdrawal-amount-naira" style="width: auto; display: inline;">)
                                        from my savings with FUD Staff Cooperative Society Limited.
                                    </p>
                                </div>
                            
                                <button type="button" class="btn btn-primary" onclick="confirmSubmission()">Submit</button>
                            </form>
                            
                            <script>
                                function confirmSubmission() {
                                    if (confirm("Are you sure you want to submit the above information?")) {
                                        document.getElementById("confirmation-form").submit();
                                    } else {
                                        document.getElementById("confirmation-form").reset();
                                    }
                                }
                            </script>
    
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
          echo $view->subFooter('../');
        ?>