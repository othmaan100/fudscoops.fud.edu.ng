<?php 
        require_once('../config/classes/View.php');
        require_once('../config/classes/User.php');
        require_once '../config/classes/MemberG3.php';
        require_once '../config/classes/MemberG1.php';
        require_once '../config/classes/MemberG2.php';
        require_once '../config/classes/Commodity.php';

        // require_once('../config/classes/Putme.php');
        $view = new View();
        $user = new User();
        $member = new MemberG3();
        $member1 = new MemberG1();
        $member2 = new MemberG2();
        $commodity = new Commodity();

        
         
         $employeeid = $user->getEmployeeId($_SESSION['username']);
         
         $memberId = $user->getMemberId($employeeid);

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
                
                 <div class="d-flex1 justify-content-between flex-wrap1">
                <div class="d-flex1  card flex-wrap1"> 
                  <div class="mr-md-10 card-body mr-xl-10">
                    
                                 <div class="card-header py-3">
                                     <div class="d-flex justify-content-center align-items-center">
                                        <h6 class="m-0 font-weight-bold text-primary text-nowrap">View Commodity Supply Name</h6>
                                       
                                     </div>
                                </div>
                                    <div class="card-body">
                                        <div class="responsive">
                                     <div class="msg"></div> 
                                            
                               <?php
                               echo $commodity->getMemberCommodityNameSupplyRequests($memberId);
                               
                               ?>
                                                     
                    
                                                                
                                        </div>
                                    </div>
                    
                  </div>                  
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
        