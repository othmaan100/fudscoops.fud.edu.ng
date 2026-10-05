    <?php 
        require_once('../config/classes/View.php');
        require_once('../config/classes/User.php');
        require_once('../config/classes/MemberG1.php');
        // require_once('../config/classes/Putme.php');
         require_once('../config/classes/Commodity.php');
        $view = new View();
        $user = new User();
         $members = new MemberG1();
        
         $proposed_members = $members->getRequesUpdateSavings();
        $commodity = new Commodity();
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
              <div class="d-flex justify-content-between flex-wrap">
                <div class="d-flex align-items-end card flex-wrap">
                  <div class="mr-md-10 card-body mr-xl-10">
                    <h2>Secretary-General ACCOUNT</h2>
                                 <div class="card-header py-3">
                                     <div class="d-flex justify-content-center align-items-center">
                                        <h6 class="m-0 font-weight-bold text-primary text-nowrap">Add new Commodity</h6>
                                       
                                     </div>
                                </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            
                                              <div class="msg"></div> 
                                            
                                
                                             <form id="create-batch-form">
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label for="batch_name" class="form-label">Batch Name</label>
                                                    <input type="text" class="form-control" id="batch_name" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="batch_description" class="form-label">Description</label>
                                                    <input type="text" class="form-control" id="batch_description">
                                                </div>
                                            </div>
                
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label for="opening_date" class="form-label">Opening Date</label>
                                                    <input type="date" class="form-control" id="opening_date" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="closing_date" class="form-label">Closing Date</label>
                                                    <input type="date" class="form-control" id="closing_date" required>
                                                </div>
                                            </div>
                
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label for="max_members" class="form-label">Max Members (optional)</label>
                                                    <input type="number" class="form-control" id="max_members" min="1">
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="max_loan_amount" class="form-label">Max Loan Amount (optional)</label>
                                                    <input type="number" class="form-control" id="max_loan_amount" min="0" step="0.01">
                                                </div>
                                            </div>
                
                                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                                <button type="submit" class="btn btn-primary" id="create-batch-btn">
                                                    <i class="fas fa-save"></i> Create Batch
                                                </button>
                                            </div>
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
        
        <!-- jQuery script to filter the table rows -->
        
        <script>
            $(document).ready(function() {
                $('#searchCompleteWithdrawal').on('keyup', function() {
                    const value = $(this).val().toLowerCase();
                    
                     $('#searchCompleteWithdrawalBody tr').each(function() {
                         const sn = $(this).find("td").eq(0).text().toLowerCase();
                         const sp = $(this).find("td").eq(1).text().toLowerCase();
                         const name = $(this).find("td").eq(2).text().toLowerCase();
                         const dep = $(this).find("td").eq(3).text().toLowerCase();
                         const saving = $(this).find("td").eq(5).text().toLowerCase();
                         
                         if (sn.includes(value) || sp.includes(value) || name.includes(value) || saving.includes(value) || dep.includes(value)) {
                             $(this).show()
                         } else {
                             $(this).hide()
                         }
                     })
                   
                });
            });
        </script>
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
         