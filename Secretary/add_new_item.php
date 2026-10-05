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
                <div class="d-flex align-items-end card flex-wrap" style='width: 100%;'>
                  <div class="mr-md-10 card-body mr-xl-10" style="width: 50%; margin: 0 auto;">
                        <h2>Secretary-General ACCOUNT</h2>
                                 <div class="card-header py-3">
                                     <div class="d-flex justify-content-center align-items-center">
                                        <h6 class="m-0 font-weight-bold text-primary text-nowrap">Add new Item</h6>
                                       
                                     </div>
                                </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            
                                              <div class="msg"></div> 
                                            
                                   <form id="item-form" >
                                        <div class="form-group">
                                            <label for="name">Item Name<span style="color: red;"> *</span>:</label>
                                            <input type="text" class="form-control" name="name" required>
                                        </div>
                                        
                                        <!--<div class="form-group">-->
                                        <!--    <label for="commodity_name_id">Commodity Supply Name<span style="color: red;"> *</span>:</label>-->
                                        <!--    <?php // echo $view->loadCommodityName()?>-->
                                        <!--</div>-->
                        
                                        <div class="form-group">
                                            <label for="type_id">Type<span style="color: red;"> *:</span></label>
                                            <select class="form-control" name="type_id" id="type_id" required>
                                                <option value="">Select Types</option>
                                                <!-- Dynamically populate -->
                                                 <?php echo $commodity->getItemTypesDropdown(); ?>
                                                
                                               
                                            </select>
                                        </div>
                        
                                        <div class="form-group">
                                            <label for="description">Description (Optional):</label>
                                            <textarea class="form-control" name="description" rows="3" placeholder='Description is optional'></textarea>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="unit">Quantity<span style="color: red;"> *</span>:</label>
                                            <input type="number" class="form-control" name="quantity" required>
                                        </div>
                        
                                        <div class="form-group">
                                            <label for="unit">Price (Per Unit)<span style="color: red;"> *</span>:</label>
                                            <input type="number" class="form-control" name="unit" required>
                                        </div>
                                        
                                        <div class="form-group">
                                        
                                            <label for="max_allowed_quantity">Maximum Quantity Allowed for Senior Staff<span style="color: red;"> *</span>:</label>
                                            <select name="max_senior_quantity" id="max_senior_quantity" class="form-control" required>
                                                <option value="">Select Max Quantity</option>
                                                <option value="1/2">1/2</option>
                                                <option value="1">1</option>
                                                <option value="2">2</option>
                                                <option value="3">3</option>
                                                <option value="4">4</option>
                                                <option value="5">5</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                        
                                            <label for="max_allowed_quantity">Maximum Quantity Allowed for Junior Staff<span style="color: red;"> *</span>:</label>
                                            <select name="max_junior_quantity" id="max_junior_quantity" class="form-control" required>
                                                <option value="">Select Max Quantity</option>
                                                <option value="1/2">1/2</option>
                                                <option value="1">1</option>
                                                <option value="2">2</option>
                                                <option value="3">3</option>
                                                <option value="4">4</option>
                                                <option value="5">5</option>
                                            </select>
                                        </div>
                        
                                       <div class="form-group mb-3">
                                            <label for="status">Status<span style="color: red;"> *:</span></label>
                                            <select class="form-control" name="status" id="status" required>
                                                <option value="">Select Status</option>
                                                <option value="1">Active</option>
                                                <option value="0">Inactive</option>
                                            </select>
                                       </div>

                        
                                        <button type="submit" class="btn btn-primary" style="display: block; margin: 0 auto;">Save Commodity Item</button>
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
         