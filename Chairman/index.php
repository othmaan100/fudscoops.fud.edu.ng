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
       
        $user->is_authenticated('../auth/');
        $user->isChairman('../auth/logout.php');

        echo $view->subHeader('../');
    ?>
    <!-- partial:partials/_navbar.html -->
    <?php  echo $view->subPartialNav('../')?>
    <!-- partial -->
    <div class="container-fluid page-body-wrapper">
      <!-- partial:partials/_sidebar.html -->
      <?php  echo $view->subSideNav()?>
      
      <!-- partial -->
      <div class="main-panel">
        <div class="content-wrapper">
          
          <div class="row">
            <div class="col-md-12 grid-margin">
              <div class="d-flex justify-content-between flex-wrap">
                <div class="d-flex align-items-end card flex-wrap">
                  <div class="mr-md-3 card-body mr-xl-5">
                    <h2>COOPERATIVE CHAIRMAN ACCOUNT</h2>
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
        </div>
        <!-- content-wrapper ends -->
        <?php 
          echo $view->subFooter('../');
        ?>