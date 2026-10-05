    <?php 
        require_once('../config/classes/View.php');
        require_once('../config/classes/User.php');
        require_once('../config/classes/MemberG1.php');
        // require_once('../config/classes/Putme.php');
        $view = new View();
        $user = new User();
         $members = new MemberG1();
        
         $proposed_members = $members->getRequesUpdateSavings();

       // $user->is_authenticated('auth/');
       // $user->isICT('../auth/logout.php');
        //$payment = $putme->getPaymentInfoArray($_SESSION['username']);
        
        echo $view->subHeader('../');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $comm_name = $_POST['comm_name'];
            $opening_date = $_POST['opening_date'];
            $closing_date = $_POST['closing_date'];
        
            $result = $members->saveCommodity($comm_name, $opening_date, $closing_date);
        
            if ($result == 1) {
                echo "<div class='alert alert-success'>Commodity saved successfully!</div>";
            } else {
                echo "<div class='alert alert-danger'>Failed to save commodity.</div>";
            }
        }
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
              <div class="d-flex justify-content-between flex-wrap">
                <div class="d-flex align-items-end card flex-wrap">
                  <div class="mr-md-10 card-body mr-xl-10">
                    <h2>Secretary-General ACCOUNT</h2>
                                 <div class="card-header py-3">
                                     <div class="d-flex justify-content-center align-items-center">
                                        <h6 class="m-0 font-weight-bold text-primary text-nowrap">Add New Commodity Name</h6>
                                       
                                     </div>
                                </div>
                                    <div class="card-body">
                                        <div class="responsive">
                                     <div class="msg"></div> 
                                            
                                <form id="commodity-name-form" method="POST" action="">
                                    <input type="hidden" name="type_id" value="">
                                
                                    <div class="form-group">
                                        <label for="type_name">Commodity Supply Name</label>
                                        <input type="text" class="form-control" name="comm_name" required>
                                    </div>
                                
                                    <div class="form-group">
                                        <label for="created_date">Opening Date</label>
                                        <input type="date" class="form-control" name="opening_date" required>
                                    </div>
                                
                                    <div class="form-group">
                                        <label for="updated_date">Closing Date</label>
                                        <input type="date" class="form-control" name="closing_date" required>
                                    </div>
                                
                                    <button type="submit" class="btn btn-primary" style="display: block; margin: 0 auto;">
                                        Save Commodity Name
                                    </button>
                                </form>

                                        </div>
                                    </div>
       
                    
                    
                  </div>                  
                </div>
                
              </div>
            </div>
          </div>
                  
        </div>
        <!-- content-wrapper ends -->
        
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
    
   
    <script>
        $(document).on("submit", "#commodity-name-form", function(e) {
            e.preventDefault(); // prevent normal form submit

            var formData = $(this).serialize(); // serialize form inputs (comm_name, opening_date, closing_date)

            $.ajax({
                url: "../ajax/save_commodity.php",   // make sure this file exists
                method: "POST",
                data: formData,
                success: function(response) {
                    console.log(response); // debug in browser console
                    if (response.trim() === "success") {
                        $('.msg').html(
                            '<div class="alert offset-md-2 col-6 msg alert-success text-primary">Commodity saved successfully!</div>'
                        );
                        $('#commodity-name-form')[0].reset(); // clear form
                    } else {
                        $('.msg').html(
                            '<div class="alert offset-md-2 col-6 msg alert-danger">Failed to save commodity: ' + response + '</div>'
                        );
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Error');
                    console.log('Status Code: ' + xhr.status);
                    console.log('Error: ' + error);
                    $('.msg').html(
                        '<div class="alert offset-md-2 col-6 msg alert-danger">AJAX Error: ' + error + '</div>'
                    );
                },
            });
        });
        </script>
        
        <!-- jQuery script to filter the table rows -->
       
        <?php 
          echo $view->subFooter('../');
        ?>
        
         
   </body>

</html>
         