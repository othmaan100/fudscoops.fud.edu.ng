<?php 
        require_once('../config/classes/View.php');
        require_once('../config/classes/User.php');
        //require_once('../classes/Putme.php');
        $view = new View();
        $user = new User();
        //$putme = new Putme();

        //$user->is_authenticated('../auth/logout.php');
        //$user->isICT('../auth/logout.php');
        
        if (isset($_POST['add'])) {
          $putme->updateAmount($_POST['session'],$_POST['amount']);
        }
        echo $view->subHeader('../');
        ?>
        <!-- partial:partials/_navbar.html -->
        <?php  echo $view->subPartialNav('../')?>
        <!-- partial -->
        <div class="container-fluid page-body-wrapper">
          <!-- partial:partials/_sidebar.html -->
          <?php  echo $view->treasurerSideNav()?>
      <!-- partial -->
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

      <div class="main-panel">
          <div class="content-wrapper">
            <div class="row">
              <div class="col-md-12 grid-margin">
                <div class="d-flex justify-content-between flex-wrap">
                  <div class="d-flex align-items-end card stretch-card flex-wrap">
                    <div class="mr-md-5 card-body mr-xl-5">
                      <h2>UPLOAD LOAN DEDUCTION</h2>
                    </div>                  
                  </div>
                  
                </div>
              </div>
            </div>
            <div class="col-md-12 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">                  
                    <div class="row">
                      <div class="col-md-12 card">
                        <form class="form-vertical"  method ="POST"target="_blank" id=""  enctype="multipart/form-data"  >                                                  
                          <div class="row">
                            
                            <div class="col-md-2 col-sm-6 col-sx-12">
                              <div class="form-group">
                                <label for="exampleInputUsername1">LOAN TYPE</label>
                                <select class="form-control form-control-sm" name="loanId" id="loanId" required>
                                    <option value="">-- Select Loan --</option>
                                    <option value="1">Business Loan</option>
                                    <option value="2">Soft Loan</option>
                                </select>

                              </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-sx-12">
                              <div class="form-group">
                                <label for="exampleInputUsername1">FILE</label>
                                <input type="file" id="uploadFile" required name="uploadFile" class="form-control form-control-sm" />
                              </div>
                            </div>
                            <div class="col-md-1 col-sm-6 col-sx-12">
                              <div class="form-group"><br/>
                                <button type="submit" class="btn btn-lg btn-danger"  name="upload_loans_deduction" id="upload_loans_deduction" >
                                  UPLOAD
                                </button>
                              </div>
                            </div>
                            
                              <div class="col-lg-4 mt-4 offset-2">
                                <div class="panel panel-warning">
                                    <div class="panel-heading">Guide</div>
                                    <div class="panel-body">
                                        <ol>
                                            <li>
                                                <a href="Loan_repayment_template.csv" download class="text-primary">Download</a> Template.
                                            </li>
                                            <li>Always follow the above template to upload loan deductions.</li>
                                            <li>Ensure your file has a <b class="text-danger">.csv</b> extension.</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                            
                             <div id="msg" class="row text-center">
                              <h3 id="loading" hidden><img src="../../img/loading.gif" alt=""> <i>Uploading loan Deductions please wait...</i></h3>
                            </div>    
                            
                          </div>                                                    
                        </form>
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
$(document).ready(function() {
    $('#loanTable').DataTable({
        dom: 'Bfrtip',  // Defines the button layout
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ]
    });
});
</script>