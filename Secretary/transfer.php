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

        $user->is_authenticated('auth/');
        $user->isSecretary('../auth/logout.php');

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
      <?php  echo $view->SecretarySideNav()?>
   
     
      <!-- partial -->
      <div class="main-panel">
        <div class="content-wrapper">
          
          <div class="row">
            <div class="col-md-12 grid-margin">
                 <?php $employee_id =$user->getEmployeeId($_SESSION['username']);
                 echo $member2->checkTransferShare($employee_id)?>
            
            </div>
          </div>   
        </div>
        <!-- content-wrapper ends -->
        <?php 
          echo $view->subFooter('../');
        ?>
        <script>

</script>
