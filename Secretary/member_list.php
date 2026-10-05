    <?php 
        require_once('../config/classes/View.php');
        require_once('../config/classes/User.php');
        require_once('../config/classes/MemberG1.php');
        // require_once('../config/classes/Putme.php');
        $view = new View();
        $user = new User();
         $members = new MemberG1();
        
         $proposed_members = $members->getProposedMembers();

       // $user->is_authenticated('auth/');
       // $user->isICT('../auth/logout.php');
        //$payment = $putme->getPaymentInfoArray($_SESSION['username']);
        
        echo $view->subHeader('../');
    ?>
    
     <!-- normalize data-table CSS
		============================================ -->
    <link rel="stylesheet" href="../../css/data-table/bootstrap-table.css">
    <link rel="stylesheet" href="../../css/data-table/bootstrap-editable.css">
    
    
    
    
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
              <div class="d-flex1 justify-content-between flex-wrap1">
                <div class="d-flex1 align-items-end1 card flex-wrap1">
                  <div class="mr-md-10 card-body mr-xl-10">
                    <h2>Secretary-General ACCOUNT</h2>
                                 <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Manage Membership Registeration List</h6>
                                </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                           
                                              
                                                    <div class="msg">
                                                        
                                                    </div>
                                              
                                              
                                               <?php
                                                $members->SecretaryMembershipApplicationsTable();
                                                ?>
                                              
                                             
                                        </div>
                                    </div>
    
                    
                    
                    
                    
                  </div>                  
                </div>
                
              </div>
            </div>
          </div>
                  
        </div>
        
             <!-- data table JS
		============================================ -->
    <script src="../../js/data-table/bootstrap-table.js"></script>
    <script src="../../js/data-table/data-table-active.js"></script>
    <script src="../../js/data-table/bootstrap-table.js"></script>
    <script src="../../js/data-table/tableExport.js"></script>
    <script src="../../js/data-table/data-table-active.js"></script>
    <script src="../../js/data-table/bootstrap-table-editable.js"></script>
    <script src="../../js/data-table/bootstrap-editable.js"></script>
    <script src="../../js/data-table/bootstrap-table-resizable.js"></script>
    <script src="../../js/data-table/colResizable-1.5.source.js"></script>
    <script src="../../js/data-table/bootstrap-table-export.js"></script>
    
    
    
        <!-- content-wrapper ends -->
        <?php 
          echo $view->subFooter('../');
        ?>
        
        
<script src="">
      $(document).ready(function (e) {
        alert('ok')
      })
    </script>
    <script>
$(document).ready(function(){
  $('[data-toggle="tooltip"]').tooltip();   
});
</script>
   </body>

</html>