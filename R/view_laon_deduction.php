<?php 
        require_once('../config/classes/View.php');
        require_once('../config/classes/User.php');
        require_once '../config/classes/MemberG3.php';
        // require_once('../config/classes/Putme.php');
        $view = new View();
        $user = new User();
        $member = new MemberG3();
        // $putme = new Putme();

        $user->is_authenticated('../auth/');
        $user->isStaff('../auth/logout.php');
        //$payment = $putme->getPaymentInfoArray($_SESSION['username']);
        
        echo $view->subHeader('../');
    ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

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
                          <div class="mr-md-3 card-body mr-xl-5">
                            <h3 class="text-primary">WELCOME, <?php echo $user->getFullname($_SESSION['username'])." (".$_SESSION['username'].")"?></h3>
                          </div>                  
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-end flex-wrap">                  
                          <span></span>
                          <a href="payslip.php" class="btn btn-success mt-2 mt-xl-0">
                            <i class="mdi mdi-printer  icon-lg mr-3 "></i>
                            Print IPPIS Payslip</a>
                        </div>
                        
                        <br/>
                        <div class="d-flex justify-content-between align-items-end flex-wrap" >                  
                          <span></span>
                          <a href="profile.php" style="color:#fff" class="btn btn-warning mt-2 mt-xl-0">
                            <i class="mdi mdi-account  icon-lg mr-3 "></i>
                            My Profile</a>
                        </div>
                        
                        <div class="card p-3">
                            <?php 
                             $employee_id =$user->getEmployeeId($_SESSION['username']);
                             $member_id=$user->getMemberId($employee_id);
                         echo $member->getMyloanRepayment($member_id)?>
                        </div>
                         
                        
                        
              </div>
              
                
              
              
              
              
              
            </div>
          </div>   
        </div>
        <!-- content-wrapper ends -->
        <?php 
          echo $view->subFooter('../');
        ?>
        <script>

</script>
