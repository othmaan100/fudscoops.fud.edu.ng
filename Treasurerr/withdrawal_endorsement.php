
  
    <?php 
        require_once('../config/classes/View.php');
        require_once('../config/classes/User.php');
        require_once('../config/classes/MemberG1.php');
        // require_once('../config/classes/Putme.php');
        $view = new View();
        $user = new User();
        $members = new MemberG1();
        
        $ListWithdrawalEndorsments = $members->SectaryWithdrawalEndorsment();
        

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
                                        <h6 class="m-0 font-weight-bold text-primary text-nowrap">Manage Members Withdrawal Endorsement</h6>
                                        <input class="form-control ml-4" type="search" id="searchEndorsment" placeholder="Search ....">
                                     </div>
                                </div>
                    
                                    <div class="card-body">
                                        <div class="table-responsive">
                                           
                                                    <div class="msg">
                                                        
                                                    </div>
                                                        
                                            <table id="table" data-toggle="table" data-show-print="true" data-pagination="true" data-search="true" data-show-columns="true" data-show-pagination-switch="true" data-show-refresh="false" data-key-events="true" data-show-toggle="false" data-Show-Print ="true" data-resizable="true" data-cookie="true"
                                        data-cookie-id-table="saveId" data-show-export="true" data-click-to-select="true" data-toolbar="#toolbar">
                                                <thead>
                                                    <tr>
                                                        <th>S/N</th>
                                                        <th>Staff Number</th>
                                                        <th> Full Name</th>
                                                        <th>Cadre </th>
                                                        <th>Amount Requested</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tfoot>
                                                    <tr>
                                                         <th>S/N</th>
                                                        <th>Staff Number</th>
                                                        <th>Full Name</th>
                                                        <th>Cadre </th>
                                                        <th>Amount Requested</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </tfoot>
                                                <tbody id="searchEndorsment">
                                                   <?php
                                                       
                                                            $sn = 1; // Initialize the serial number
                                                            foreach ($ListWithdrawalEndorsments as $endorsements) {
                                                                ?>
                                                                <tr>
                                                                    <td><?php echo $sn; ?></td>
                                                                    <td><?php echo $endorsements['sp_no']; ?></td>
                                                                    <td><?php echo $endorsements['fname'].' '.$endorsements['lname'].' '.$endorsements['oname']; ?></td>
                                                                    <td><?php echo $endorsements['cadre']; ?></td>
                                                                    <td>₦<?php echo $endorsements['withrawals_amount']; ?></td>
                                                                   
                                                          
                                                            <td class = "d-flex">
                                                               <button  class="btn btn-sm btn-success sec_approve_withdrawal" data-withrawal="<?php echo $endorsements['withrawals_id']; ?>" onclick="return confirm('Are you sure you want to approve this?');">Approve</button> 
                                
                                                               

                                                                <form class = "ml-2" action="/products/{{$item->id}}/delete" method="POST" onsubmit="return confirm('Are you sure you want to cancle the endorsment.')">
                                                                   
                                                                    <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                        <?php
                                                                $sn++; // Increment the serial number
                                                            }
                                                            ?>
                                                            
                                                    
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                    
                  </div>                  
                </div>
                
              </div>
            </div>
          </div>
                  
        </div>
  

            <!-- data table JS
	============================================  -->
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
            $(document).ready(function() {
                $('#searchEndorsment').on('keyup', function() {
                    const value = $(this).val().toLowerCase();
                    
                     $('#searchEndorsment tr').each(function() {
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
       
         