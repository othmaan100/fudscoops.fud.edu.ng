    <?php 
        require_once('../config/classes/View.php');
        require_once('../config/classes/User.php');
        require_once '../config/classes/MemberG3.php';
        require_once '../config/classes/MemberG1.php';
        require_once '../config/classes/MemberG2.php';

        $view = new View();
        $user = new User();
        $member = new MemberG3();
        $member1 = new MemberG1();
        $member2 = new MemberG2();
       
        $view = new View();
        $user = new User();
        $psavings =0;
      $applicantsCount= $member1->getMembershipApplicationsCount();

        $user->is_authenticated('auth/');
        $user->isSecretary('../auth/logout.php');

        echo $view->subHeader('../');
    ?>
    <!-- partial:partials/_navbar.html -->
    <?php  echo $view->subPartialNav('../')?>
    <!-- partial -->
    <div class="container-fluid page-body-wrapper">
      <!-- partial:partials/_sidebar.html -->
      <?php  echo $view->SecretarySideNav()?>
      
      <!-- partial -->
      <div class="main-panel">
        <div class="content-wrapper">
          
          <div class="row">
            <div class="col-md-12 grid-margin">
              <div class="d-flex justify-content-between flex-wrap">
                <div class="d-flex align-items-end card flex-wrap">
                  <div class="mr-md-3 card-body mr-xl-5">
                    <h2>Secretary-General ACCOUNT</h2>
                  </div>                  
                </div>
                <div class="d-flex align-items-end  flex-wrap">
                  <div class="mr-md-3 card-body mr-xl-5">
                        <?php echo $member->getTotalGuarantee($user->getEmployeeId($_SESSION['username']))?>
                  </div>                  
                </div>
                
                <div class="d-flex align-items-end  flex-wrap">
                  <div class="mr-md-3 card-body mr-xl-5">
                      <?php echo $member2->getTotalShareTransfer($user->getEmployeeId($_SESSION['username']))?>
                  </div>                  
                </div>
                
              </div>
            </div>
          </div>
          
          
          
                         <div class=" shadow mb-4">
                            <div class="row "> 
                            
                                           <div class="col-xl-3 col-md-6 mb-4">
                                    <div class="card border-left-primary shadow h-100 py-2">
                                        <div class="card-body">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col mr-2">
                                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                    Pending Membership Applications</div>
                                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $applicantsCount;?></div>
                                                    </div>
                                                <div class="col-auto">
                                                    <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-xl-3 col-md-6 mb-4">
                                    <div class="card border-left-primary shadow h-100 py-2">
                                        <div class="card-body">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col mr-2">
                                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                    Pending Share Purchases</div>
                                                        <div class="h5 mb-0 font-weight-bold text-gray-800">&#8358;<?php echo number_format($psavings);?></div>
                                                    </div>
                                                <div class="col-auto">
                                                    <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6 mb-4">
                                    <div class="card border-left-primary shadow h-100 py-2">
                                        <div class="card-body">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col mr-2">
                                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                    Approved Share Purchases</div>
                                                        <div class="h5 mb-0 font-weight-bold text-gray-800">&#8358;<?php echo number_format($psavings);?></div>
                                                    </div>
                                                <div class="col-auto">
                                                    <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-xl-3 col-md-6 mb-4">
                                    <div class="card border-left-primary shadow h-100 py-2">
                                        <div class="card-body">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col mr-2">
                                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                    Pending Share Conversions</div>
                                                        <div class="h5 mb-0 font-weight-bold text-gray-800">&#8358;<?php echo number_format($psavings);?></div>
                                                    </div>
                                                <div class="col-auto">
                                                    <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6 mb-4">
                                    <div class="card border-left-primary shadow h-100 py-2">
                                        <div class="card-body">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col mr-2">
                                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                    Active Loans</div>
                                                        <div class="h5 mb-0 font-weight-bold text-gray-800">&#8358;<?php echo number_format($psavings);?></div>
                                                    </div>
                                                <div class="col-auto">
                                                    <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                 <div class="col-xl-3 col-md-6 mb-4">
                                    <div class="card border-left-primary shadow h-100 py-2">
                                        <div class="card-body">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col mr-2">
                                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                    Total Sales Amount</div>
                                                        <div class="h5 mb-0 font-weight-bold text-gray-800">&#8358;<?php echo number_format($psavings);?></div>
                                                    </div>
                                                <div class="col-auto">
                                                    <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6 mb-4">
                                    <div class="card border-left-primary shadow h-100 py-2">
                                        <div class="card-body">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col mr-2">
                                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                    This Month Sales</div>
                                                        <div class="h5 mb-0 font-weight-bold text-gray-800">&#8358;<?php echo number_format($psavings);?></div>
                                                    </div>
                                                <div class="col-auto">
                                                    <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                 <div class="col-xl-3 col-md-6 mb-4">
                                    <div class="card border-left-primary shadow h-100 py-2">
                                        <div class="card-body">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col mr-2">
                                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                    Commodity Loans Transfers</div>
                                                        <div class="h5 mb-0 font-weight-bold text-gray-800">&#8358;<?php echo number_format($psavings);?></div>
                                                    </div>
                                                <div class="col-auto">
                                                    <i class="fas fa-calendar fa-2x text-gray-300"></i>
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