    <?php 
        require_once('../config/classes/View.php');
        require_once('../config/classes/User.php');
        require_once('../config/classes/MemberG2.php');
        require_once '../config/classes/MemberG1.php';
        $view = new View();
        $user = new User();
        
         $id = $_GET['id'];
         $members = new MemberG2();
         $user->is_authenticated('auth/');
         $user->isSecretary('../auth/logout.php');
         $member1 = new MemberG1();
         $psavings= $member1->getProposedMonthlySavings($id);
        
         
        
         $cumsavings= $member1->getCumulativeMonthlySavings($id);
         
         $cumwithdrawal= $member1->getCumulativeSavingsWithdrawals($id);
         
         $currentsavings= $member1-> getCurrentSavingsAmount($id);
         
        

        echo $view->subHeader('../');
    ?>
    
    
     <!-- normalize data-table CSS
		============================================ -->
    
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
            <div class="col-md-12">
              <div class="d-flex justify-content-between flex-wrap">
                <div class="d-flex align-items-end card flex-wrap">
                  <div class="mr-md-10 card-body mr-xl-10">
                        <div class="card-body">
                            
                            
                            
                            
                         <div class=" P-3 mb-4">
                             
                          <div class="mr-md-3 card-body mr-xl-5">
                            <h2 class="text-center">Complete withdrawal Request</h2>
                          </div> 
                          
                          <div class="mr-md-3 card-body mr-xl-5">
                            <h3 class="text">Member summary</h3>
                          </div>  
                          
                          <div class="mr-md-3 card-body mr-xl-5">
                            <h4 class="text-primary"><?php echo $user->getFullname($id)?></h4>
                          </div>                  

                             
                            <div class="row P-3">  
                                <div class="col-xl-3 col-md-6 mb-4">
                                    <div class="card border-left-primary shadow h-100 py-2">
                                        <div class="card-body">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col mr-2">
                                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                    Current Monthly Savings</div>
                                                        <div class="h5 mb-0 font-weight-bold text-gray-800">&#8358;<?php echo number_format($psavings);?></div>
                                                    </div>
                                                <div class="col-auto">
                                                    <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-xl-3 col-md-6 mb-4">
                                    <div class="card border-left-primary shadow h-100 py-2">
                                        <div class="card-body">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col mr-2">
                                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                    Current Cummulative Savings</div>
                                                        <div class="h5 mb-0 font-weight-bold text-gray-800">&#8358;<?php echo number_format($currentsavings);?></div>
                                                    </div>
                                                <div class="col-auto">
                                                    <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                
                                <div class="col-xl-3 col-md-6 mb-4">
                                    <div class="card border-left-primary shadow h-100 py-2">
                                        <div class="card-body">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col mr-2">
                                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                    Last Saving Withdrawal Amount</div>
                                                        <div class="h5 mb-0 font-weight-bold text-gray-800">&#8358;<?php echo number_format(10000);?></div>
                                                    </div>
                                                <div class="col-auto">
                                                    <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-xl-3 col-md-6 mb-4">
                                    <div class="card border-left-primary shadow h-100 py-2">
                                        <div class="card-body">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col mr-2">
                                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                    Current Number(Units) of Shares</div>
                                                        <div class="h5 mb-0 font-weight-bold text-gray-800">&#8358;<?php echo number_format(10000);?></div>
                                                    </div>
                                                <div class="col-auto">
                                                    <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-xl-3 col-md-6 mb-4">
                                    <div class="card border-left-primary shadow h-100 py-2">
                                        <div class="card-body">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col mr-2">
                                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                    Your Current Loan Value (&#8358;)</div>
                                                        <div class="h5 mb-0 font-weight-bold text-gray-800">&#8358;<?php echo number_format(10000);?></div>
                                                    </div>
                                                <div class="col-auto">
                                                    <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-xl-3 col-md-6 mb-4">
                                    <div class="card border-left-primary shadow h-100 py-2">
                                        <div class="card-body">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col mr-2">
                                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                    Total Unit of shares (&#8358;)</div>
                                                        <div class="h5 mb-0 font-weight-bold text-gray-800">&#8358;<?php echo number_format(10000);?></div>
                                                    </div>
                                                <div class="col-auto">
                                                    <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-xl-3 col-md-6 mb-4">
                                    <div class="card border-left-primary shadow h-100 py-2">
                                        <div class="card-body">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col mr-2">
                                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                    Total Amount of Shares (&#8358;)</div>
                                                        <div class="h5 mb-0 font-weight-bold text-gray-800">&#8358;<?php echo number_format(10000);?></div>
                                                    </div>
                                                <div class="col-auto">
                                                    <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </div>    
                            
                            
                        <div class="d-flexd">
                            <div class="d-flexd mb-2">
                            <form class="mr-2" action="../ajax/approvd_complete_withdrawals.php" method="POST" onsubmit="return confirm('Are you sure you want to approved complete withdrawals request?.')">
                                <div class="custom-checkbox" style="display: none">
                                   
                                    <input type="checkbox" class="custom-checkbox" name="staff_no" value="<?php echo $id; ?>" id="<?php echo $id; ?>" checked> 
                                </div>
                                
                                <div class="mb-2" >
                                    <babel for="amount"> Amount</label>
                                    <input type="number" class="form-control" name="amount" id="amount" required> 
                                </div>
                                
                                <button id="<?php echo $id; ?>" class="btn btn-sm btn-success">Approve</button>
                            </form>
                            </div>
                            
                            <form action="../ajax/reject_complete_withdrawals.php" method="POST" onsubmit="return confirm('Are you sure you want to reject complete withdrawal request?')">
                                <div class="custom-checkbox" style="display: none">
                                    <input type="checkbox" class="custom-checkbox" name="staff_no" value="<?php echo $id; ?>" id="reject_<?php echo $id; ?>" checked> 
                                </div>
                                <button id="<?php echo $id; ?>" class="btn btn-sm btn-danger">Reject</button>
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
         