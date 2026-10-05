<?php 
        require_once('../config/classes/View.php');
        require_once('../config/classes/User.php');
        require_once('../config/classes/MemberG3.php');
        $view = new View();
        $user = new User();
        $member = new MemberG3();
        // $putme = new Putme();
        $user->is_authenticated('../auth/logout.php');
        $user->isStaff('../auth/logout.php');
        $staff_info =  $user->getStaffInformation($_SESSION['username']);
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
                               
                         <?php  if (isset($_GET['loan_id'])) {
                                     $loan_id = $_GET['loan_id'];
                                    echo $member->getUndertaking($loan_id);
                                    
                                    } else {
                                     echo "<p>No loan selected.</p>";
                                    }
                                    ?>
                        
                  </div>
               
              
              </div>
            </div>
          </div>   
        </div>
        <!-- content-wrapper ends -->
        <?php 
          echo $view->subFooter('../');
        ?>