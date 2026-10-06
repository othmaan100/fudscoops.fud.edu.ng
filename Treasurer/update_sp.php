<?php 
        require_once('../config/classes/View.php');
        require_once('../config/classes/User.php');
        // require_once('../config/classes/Putme.php');
        $view = new View();
        $user = new User();
        // $putme = new Putme();

        $user->is_authenticated('../auth/');
        $user->isBur('../auth/logout.php');
        
        $staff_info =  $user->getStaffInformation($_SESSION['username']);
        $employeeid = $user->getEmployeeId($_SESSION['username']);
        $memberid = $user->getMemberId($employeeid);
        $share_info = $user->getMemberShareInformation($memberid);
        $share_unit = $user->getShareUnitInfoArray();
        $date = !empty($share_info['date']) ? date('Y-m-d', strtotime($share_info['date'])) : date('Y-m-d');
    
        //$payment = $putme->getPaymentInfoArray($_SESSION['username']);
        
        echo $view->subHeader('../');
    ?>
    <!-- partial:partials/_navbar.html -->
    <?php  echo $view->subPartialNav('../')?>
    <!-- partial -->
    <div class="container-fluid page-body-wrapper">
      <!-- partial:partials/_sidebar.html -->
      <?php  echo $view->treasurerSideNav()?>
      
      <!-- partial -->
      <div class="main-panel">
        <div class="content-wrapper">
            
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                        
                     <form method="POST" class="form-inline" name = "form" enctype="multipart/form-data">
        					<div class="form-group mb-2 d-flex jusify-content-between">
                		 		<input type="text" name = "staffno" class="form-control mr-2 h-2" id="staffno" placeholder="Enter Staff No">
                		 			<input type="submit" name="search" class="btn btn-secondary btn-sm" value="search">
               		        </div>
               		        
                	</form>
                   
            </div>
            
            <?php 
                        echo $msg;
                        if(isset($_POST['staffno'])){
                            $sp = $_POST['staffno'];
                            //echo $reg;
                            $info = $user->getStaffInfoArray($sp);
                            if($info){
            ?>  
          
          <div class="row">
            <div class="col-md-12 grid-margin">
              <div class="d-flex1 justify-content-between flex-wrap1">
                <div class="d-flex1 align-items-end card flex-wrap1">
                  
                  
                  
                  
                   <div class="container mt-5">
                       <img src="../../images/logo-mini.png" style="display:block;margin-left:auto;margin-right:auto;margin-bottom:2px;margin-top:-30px; width:100px;" class="rounded mx-auto d-block" alt="logo">
                                <h2 class="text-center">FUD STAFF COOPERATIVE SOCIETY LIMITED</h2>
                                <h5 class="text-center">[An Interest Free Thrift and Loan Society]</h5>
                                <h5 class="text-center mb-4" style="text-decoration: underline;">UPDATE MEMBER INFORMATION</h5>
                        
                               <form class="my-form form-vertical" id="staff-update" method="POST">
                                   <input type="hidden" name="username" value="<?php echo $info['sp_no']; ?>">
                                    <div class="form-group row">
                                        <div class="col-md-6">
                                            <label for="name">First Name:</label>
                                            <input type="text" class="form-control" required id="fname" name="fname" value="<?php echo $info['fname']; ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="dept">Last Name:</label>
                                            <input type="text" class="form-control" required id="lname" name="lname" value="<?php echo $info['lname'] ?>">
                                        </div>
                                    </div>
                        
                                    <div class="form-group row">
                                        <div class="col-md-6">
                                            <label for="name">Other Name:</label>
                                            <input type="text" class="form-control" id="oname" name="oname" value="<?php echo $info['oname']; ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="dept">Dept/Unit:</label>
                                            <input type="text" class="form-control" id="dept" name="dept" value="<?php echo $info['dept'] ?>">
                                        </div>
                                    </div>
                        
                                    <div class="form-group row">
                                        <div class="col-md-6">
                                            <label for="staff-no">Staff No:</label>
                                            <input type="text" class="form-control" required id="staff_no" name="staff_no" value="<?php echo $info['sp_no'] ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="gsm-no">GSM No:</label>
                                            <input type="tel" class="form-control" id="gsm-no" name="gsm-no" value="<?php echo $info['phone_no'] ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="form-group row">
                                        <div class="col-md-6">
                                            <label for="staff-no">Staff Title:</label>
                                            <input type="text" class="form-control" id="title" name="title" value="<?php echo $info['title'] ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="gsm-no">Staff Cadre:</label>
                                            <input type="tel" class="form-control" id="cadre" name="cadre" value="<?php echo $info['cadre'] ?>">
                                        </div>
                                    </div>
                                    
                                    
                                <div class="row">
                                    
                                         <div class="form-group offset-md-5" style="display: flex; justify-content: center;">
                                            <button type="submit" class="btn btn-primary" style="font-size: 20px; padding: 10px 20px; width: 200px;">Submit</button>
                                        </div>

                                </div>
                                <div class="msg"></div>
                                </form>
                    </div>
                  
                 </div>
               
              
              </div>
            </div>
          </div>
          
          <?php    
                }
                else{
                echo "Invalid Staff Number";
            }
                
            }
            
            
        ?>
          
        </div>
        <!-- content-wrapper ends -->
        
        </div>
        <!-- main panel ends -->
        </div>
        <!-- container fluids ends -->
        
        <!-- JavaScript to enforce matching values -->
        
        <?php 
          echo $view->subFooter('../');
        ?>