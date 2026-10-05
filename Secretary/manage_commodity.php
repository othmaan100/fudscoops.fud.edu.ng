    <?php 
        require_once('../config/classes/View.php');
        require_once('../config/classes/User.php');
        require_once('../config/classes/MemberG1.php');
        require_once('../config/classes/Commodity.php');
        $view = new View();
        $user = new User();
         $members = new MemberG1();
         
         $commodity = new Commodity();
        
         $proposed_members = $members->getRequesUpdateSavings();

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
                                        <h6 class="m-0 font-weight-bold text-primary text-nowrap">Manage Commodity Supply</h6>
                                       
                                     </div>
                                </div>
                                    <div class="card-body">
                              
                                          <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                                            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                                                <h1 class="h2">Commodity Supply List</h1>
                                                <div class="btn-toolbar mb-2 mb-md-0">
                                                  
                                                </div>
                                            </div>
                            
                                            <!-- Supply Selection -->
                                            <div class="card mb-4">
                                               
                                    <select id="supplyItemsSelect" class="form-select">
                                        <option value="">-- Select Supply --</option>
                                        <!-- Options will be loaded via AJAX -->
                                        <?php echo $commodity->getCommoditySupplyDropdown(); ?>
                                        </select>
                                               
                                            </div>
                            
                                            <!-- Batches Table -->
                                            <div class="card">
                                                <div class="card-header d-flex justify-content-between align-items-center">
                                                    <h5>Items</h5>
                                                    <div id="batchActions" style="display: none;">
                                                      
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                     
                                                    
                                                    <div class="table-responsive">
                                                        <table class="table table-hover" id="CommodityItemsTable">
                                                            <thead>
                                                                <tr>
                                                                    <th>S/N</th>
                                                                    <th>Item</th>
                                                                    <th>Quantity Allocated</th>
                                                                    <th>Quantity Distributed</th>
                                                                    <th>Max Quantity for Senior Staff</th>
                                                                    <th>Max Quantity for Junior Staff</th>
                                                                    <th>Status</th>
                                                                    <th>Actions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <!-- Batches will be loaded via AJAX -->
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </main>
                              
                              
  
                              
                            
                                    <!-- Batch Items Modal -->
                                    <div class="modal fade" id="batchItemsModal" tabindex="-1" aria-labelledby="batchItemsModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-xl">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="batchItemsModalLabel">Batch Items</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="table-responsive">
                                                        <table class="table table-hover" id="batchItemsTable">
                                                            <thead>
                                                                <tr>
                                                                    <th>Commodity</th>
                                                                    <th>Available Qty</th>
                                                                    <th>Unit</th>
                                                                    <th>Price</th>
                                                                    <th>Min/Max per Member</th>
                                                                    <th>Actions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <!-- Items will be loaded via AJAX -->
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
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
        
             <!-- data table JS
		============================================ -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
        // Automatically convert to uppercase
        $('input[name="comm_name"]').on('input', function () {
            this.value = this.value.toUpperCase();
        });
    </script>
        
        <!-- jQuery script to filter the table rows -->
       
        <?php 
          echo $view->subFooter('../');
        ?>
        
         
   </body>

</html>
         