    <?php 
        require_once('../config/classes/View.php');
        require_once('../config/classes/User.php');
        require_once('../config/classes/MemberG2.php');
        $view = new View();
        $user = new User();
        $members = new MemberG2();
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
            <div class="col-md-12 grid-margin">
              <div class="d-flex justify-content-between flex-wrap col-md-12">
                <div class="d-flex align-items-end card flex-wrap col-md-12">
                  
                  
                  
                  
                   <div class="container mt-5">
                               
                        <!--<h1 class="text-center">FUD STAFF COOPERATIVE SOCIETY LIMITED</h1>-->
                        <!--<h2 class="text-center">[An Interest Free Thrift and Loan Society]</h2>-->
                        <h3 class="text-center mb-4">Add New User</h3>
                
                        <form id="adduser">
                            
                            <div class="form-group row">
                              
                                 <div class="col-md-6">
                                    <label for="bank">Role:</label>
                                    <select class="form-control" id="role" name="role" required>
                                        <option value="">Select Role</option>
                                        <option value="5">Auditor</option>
                                        <option value="2">Secretary</option>
                                        <option value="3">Treasurer</option>
                                        
                                    </select>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="sort-code">User Name:</label>
                                    <input type="text" class="form-control" id="user_name" name="user_name" required>
                                    
                                </div>
                            </div>
                
                            
                
                            <button type="submit" class="btn btn-primary mb-4">Submit</button>
                        </form>
                  
                  

                    </div>
               
              
              </div>
            </div>
          </div>   
                  
        </div>
        
<?php 
          echo $view->subFooter('../');
        ?>
        
   </body>

</html>
         