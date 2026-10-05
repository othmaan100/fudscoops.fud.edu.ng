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
              <div class="d-flex justify-content-between flex-wrap">
                <div class="d-flex align-items-end card flex-wrap">
                  
                  
                  
                  
                   <div class="container mt-5">
                       <img src="../../images/logo-mini.png" style="display:block;margin-left:auto;margin-right:auto;margin-bottom:2px;margin-top:-30px; width:100px;" class="rounded mx-auto d-block" alt="logo">
                                <h2 class="text-center">FUD STAFF COOPERATIVE SOCIETY LIMITED</h2>
                                <h5 class="text-center">[An Interest Free Thrift and Loan Society]</h5>
                                <h5 class="text-center mb-4" style="text-decoration: underline;">COMMODITY LOAN APPLICATION FORM</h5>
                        
                            <form class="my-form form-vertical" id="commodity_request" method="POST" action="print.php" onsubmit="handleSubmit(event)">
                                  <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                      <thead class="thead-dark">
                                        <tr>
                                          <th>S/No</th>
                                          <th>Commodity Items</th>
                                          <th>Q/Applied</th>
                                          <th>U/Price</th>
                                          <th>Q/Approved</th>
                                          <th>T/Amount</th>
                                        </tr>
                                      </thead>
                                      <tbody>
                                        <tr>
                                          <td>1</td>
                                          <td>
                                            <div class="form-group mb-0">
                                               <?php echo $view->loadCommodityItem()?>
                                            </div>
                                          </td>
                                          <td>
                                            <div class="form-group mb-0">
                                              <input type="number" name="quantity_applied[0]" id="qty_0" class="form-control" oninput="updateAmount(0)" max="">
                                            </div>
                                          </td>
                                          <td>
                                            <div class="form-group mb-0">
                                              <input type="number" step="0.01" name="unit_price[0]" id="price_0" class="form-control" readonly>
                                            </div>
                                          </td>
                                          <td>
                                            <div class="form-group mb-0">
                                              <input type="number" name="quantity_approved[0]" class="form-control" readonly>
                                            </div>
                                          </td>
                                          <td>
                                            <div class="form-group mb-0">
                                              <input type="text" name="total_amount[0]" id="total_0" class="form-control" readonly>
                                            </div>
                                          </td>
                                        </tr>
                                      </tbody>
                                      <tfoot>
                                        <tr>
                                          <td colspan="5" class="text-right font-weight-bold">TOTAL</td>
                                          <td>
                                            <div class="form-group mb-0">
                                              <input type="text" id="grand_total" class="form-control" readonly>
                                            </div>
                                          </td>
                                        </tr>
                                      </tfoot>
                                    </table>
                                  </div>
                                
                                  <div class="form-group mt-3">
                                    <button type="submit" class="btn btn-primary">Submit Application</button>
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
        
        <?php 
          echo $view->subFooter('../');
        ?>