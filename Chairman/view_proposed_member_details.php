    <?php 
        require_once('../config/classes/View.php');
        require_once('../config/classes/User.php');
        require_once('../config/classes/MemberG1.php');
        $view = new View();
        $user = new User();
         $members = new MemberG1();
         $user->is_authenticated('auth/');
        $user->isChairman('../auth/logout.php');

        echo $view->subHeader('../');
    ?>
    
    
     <!-- normalize data-table CSS
		============================================ -->
    
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
            <div class="col-md-12">
              <div class="d-flex1 justify-content-between flex-wrap1">
                <div class="d-flex1 align-items-end1 card flex-wrap1">
                  <div class="mr-md-10 card-body mr-xl-10">
                        <div class="card-body">
                                 <?php  if (isset($_GET['member_id'])) {
                                     $member_id = $_GET['member_id'];
                                    echo $members->getSecretaryMembershipDecision($member_id);
                                    
                                    } else {
                                     echo "<p>No Member Record selected.</p>";
                                    }
                                    ?>
                        
    
                        </div>
       
                    
                    
                  </div>                  
                </div>
                
              </div>
            </div>
          </div>
                  
        </div>
        <!-- content-wrapper ends -->
        <!-- DataTables CSS -->
                <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
                
                <!-- DataTables Buttons CSS -->
                <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css">
                
                <!-- jQuery (required for DataTables) -->
                <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                
                <!-- DataTables JS -->
                <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
                
                <!-- DataTables Buttons JS -->
                <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
                
                <!-- JSZip (required for Excel export) -->
                <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
                
                <!-- pdfMake (required for PDF export) -->
                <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
                
                <!-- vfs_fonts (required for PDF export) -->
                <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
                
                <!-- Buttons for export options (CSV, Excel, PDF, etc.) -->
                <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
                <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>

        <?php 
          echo $view->subFooter('../');
        ?>
        
  <script>
$(document).ready(function() {
    $('#example').DataTable({
        dom: 'Bfrtip',  // Defines the button layout
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ]
    });
});
</script>

   </body>

</html>
         