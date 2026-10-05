    <?php 
        require_once('../config/classes/View.php');
        require_once('../config/classes/User.php');

        $view = new View();
        $user = new User();
        $username = $_SESSION['username'];
        $legacy = str_replace("/","",$username);
        $info = $user ->getStaffInfoArray($username);

       
        $user->is_authenticated('auth/');
        $user->isStaff('../auth/logout.php');
        
        if (isset($_POST['uploadPass'])) {
          $file = $_FILES['file'] ;
          $spNo = $_SESSION['username'];
          $sitting = '';
          $role = "Passport";
          
          $msg =  $user->uploadPassport($spNo);
        }
        $passportUpload = $user->getUploadsInfoArray($_SESSION['username'],'Passport');
        if(count($passportUpload) ==0){
             $passportUpload['file'] = '../images/pic.jpg';
        }

// die();
        echo $view->subHeader('../');
    ?>
    <!-- partial:partials/_navbar.html -->
    <?php  echo $view->subPartialNav('../')?>
    <!-- partial -->
    <div class="container-fluid page-body-wrapper">
      <!-- partial:partials/_sidebar.html -->
      <?php  echo $view->nonMemberfSideNav()?>      
      <!-- partial -->
      <div class="main-panel">
          <div class="content-wrapper">
            <div class="row">
              <div class="col-md-6 grid-margin">
                <div class="d-flex justify-content-between flex-wrap">
                  <div class="d-flex align-items-end card stretch-card flex-wrap">
                    <div class="mr-md-2 card-body mr-xl-2">
                      <h2>
                      <i class="mdi mdi-account menu-icon"></i> MY PROFILE</h2>
                    </div>
                  </div>
                  
                </div>
              </div>
              <div class="col-md-6 grid-margin">
                <div class="d-flex justify-content-between flex-wrap">
                  <div class="d-flex align-items-end card stretch-card flex-wrap">
                    <div class="mr-md-2 card-body bg-warning mr-xl-2">
                      <h5>Important Notice</h5>
                      <small><li>Staff who do not have corporate mail should proceed and update their profile. Their profile information on this portal will be used to create the email for them.</li></small>
                      <small><li>Staff who have corporate mail should just input their corporate mail, and indicate whether it is active or needs password reset. They should as well update their profile information on this portal.</li></small>
                    </div>
                  </div>
                  
                </div>
              </div>
            </div>

            <div class="col-md-12 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <div class="row">
                      <div class="col-md-9">
                          <div class="form-group">
                                      <label for="exampleInputUsername1">SELECT CADRE:</label>
                                      <select class="form-control form-control-sm" id="type">
                                          <option></option>
                                          <option value="1">TEACHING STAFF</option>
                                          <option value="2">NON-TEACHING STAFF</option>
                                      </select>
                                    </div>
                           <form class="my-form form-vertical" hidden id="form-profile1">
                                <h3>ACADEMIC STAFF TEMPLATE</h3>
                                <div class="row">
                                  <div class="col-md-6">
                                    <div class="form-group">
                                      <label for="exampleInputUsername1">STAFF NO.</label>
                                      <input type="text" class="form-control form-control-sm" readonly  value="<?php echo $_SESSION['username'] ?>" name="sp" id="sp" placeholder="">
                                    </div>
                                    <div class="form-group">
                                      <label for="exampleInputUsername1">Othernames</label>
                                      <input type="text" class="form-control form-control-sm" readonly name="oname"  value="<?php echo $info['oname']?>"  id="othername" placeholder="">
                                    </div>
                                    <div class="form-group">
                                      <label for="exampleInputUsername1">Gender</label>
                                        <input type="text" class="form-control form-control-sm" name="gender" value="<?php echo $info['gender']?>"  id="gender" placeholder="">
                                                          </div>
                                    <div class="form-group">
                                      <label for="exampleInputUsername1">Personal Email</label>
                                      <input type="email" class="form-control form-control-sm"   name="email" value="<?php echo $info['email']?>" id="email" >
                                    </div>
                                    
                                    <div class="form-group">
                                      <label for="exampleInputUsername1">Cadre</label>
                                      <input type="text" class="form-control form-control-sm"   name="cadre" value="<?php echo $info['cadre']?>" id="cadre" >
                                    </div>
                                    <div class="form-group">
                                      <label for="exampleInputUsername1">Corporate Email Status</label>
                                        <select class="form-control form-control-sm" style="color:black" name="estatus">
                                            <?php 
                                                if($info['email_status'] =='active'){
                                                    echo '
                                                        <option value="active" selected>Active</option>
                                                        <option value="reset">Needs Reset</option>
                                                        <option value="not-exist">I don\'t have</option>
                                                    ';
                                                }
                                                elseif($info['email_status'] =='reset'){
                                                     echo '
                                                        <option value="active">Active</option>
                                                        <option value="reset" selected>Needs Reset</option>
                                                        <option value="not-exist">I don\'t have</option>
                                                    ';
                                                }
                                                elseif($info['email_status'] =='not-exist'){
                                                     echo '
                                                        <option value="active">Active</option>
                                                        <option value="reset">Needs Reset</option>
                                                        <option value="not-exist" selected>I don\'t have</option>
                                                    ';
                                                }else{
                                                     echo '
                                                        <option value="active">Active</option>
                                                        <option value="reset">Needs Reset</option>
                                                        <option value="not-exist">I don\'t have</option>
                                                    ';
                                                }
                                                
                                            ?>
                                        </select> 
                                    </div>
                                    <div class="form-group">
                                      <label for="exampleInputUsername1">Office No.</label>
                                      <input type="text" class="form-control form-control-sm"  name="office" value="<?php echo $info['office_no']?>" id="office" placeholder="" >
                                    </div>
                                    <div class="form-group">
                                      <label for="exampleInputUsername1">Area of Specialization</label>
                                      <input type="text" class="form-control form-control-sm"   name="specialization" value="<?php echo $info['specialization']?>" id="specialization" placeholder="" >
                                    </div>
                                  </div>
                                  <div class="col-lg-6">
                                    <div class="form-group">
                                      <label for="exampleInputUsername1">First Name</label>
                                      <input type="text" class="form-control form-control-sm" readonly  name="fname" value="<?php echo $info['fname']?>" id="firstname" placeholder="" >
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputUsername1">Last Name</label>
                                        <input type="text" class="form-control form-control-sm" readonly  name="lname" value="<?php echo $info['lname']?>" id="lastname" placeholder="" >
                                      </div>
                                    <div class="form-group">
                                      <label for="exampleInputUsername1">Date Of Birth </label>
                                      <input type="text" class="form-control form-control-sm" readonly  value="<?php echo $info['dob']?>" id="dob" name="dob" placeholder="" >
                                    </div>
                                    <div class="form-group">
                                      <label for="exampleInputUsername1">Phone</label>
                                      <input type="text" class="form-control form-control-sm"   value="<?php echo $info['phone_no']?>" name="phone" id="phone" placeholder="" >
                                    </div>
                                    
                                    <div class="form-group">
                                      <label for="exampleInputUsername1">Academic Rank</label>
                                      <input type="text" class="form-control form-control-sm"   value="<?php echo $info['rank']?>" name="rank" id="rank" placeholder="" >
                                    </div>
                                    <div class="form-group">
                                      <label for="exampleInputUsername1">Corporate Email (Optional)</label>
                                      <input type="email" class="form-control form-control-sm"   value="<?php echo $info['corporate_mail']?>" name="cemail" id="cemail" placeholder="It'll will be created if you dont have " >
                                    </div>
                                    <div class="form-group">
                                      <label for="exampleInputUsername1">Department</label>
                                      <input type="text" class="form-control form-control-sm"   value="<?php echo $info['dept']?>" name="department" id="department" placeholder="" >
                                    </div>
                                    <div class="form-group">
                                      <label for="exampleInputUsername1">Academic Qualification</label>
                                      <input type="text" class="form-control form-control-sm"  value="<?php echo $info['qualification']?>"  name="qualification" value="" id="qualification" placeholder="">
                                    </div>
                                  </div>
                                </div>
                                <div class="form-group">
                                      <label for="exampleInputUsername1">Publications/Research (Google Scholer/Research Gate)</label>
                                      <textarea class="form-control form-control-sm" id="publication" name="publication"><?php echo $info['publication']?></textarea>
                                    </div>
                                    <div class="msg"></div>
                                <!-- end row -->
                                <div class="row">
                                  <div class="form-group offset-md-5">
                                      <button type="submit" rel="form-profile1" class="btn btn-outline-primary btn-icon-text updateProfile">
                                              <i class="mdi mdi-file-check btn-icon-prepend"></i>
                                              Save profile
                                      </button> 
                                  </div>
                                </div>                      
                            </form>
                           <form class="my-form form-vertical" hidden id="form-profile2">
                              
                                <h3>NON-TEACHING STAFF TEMPLATE</h3>
                                <div class="row">
                                    
                                  <div class="col-md-6">
                                    <div class="form-group">
                                      <label for="exampleInputUsername1">STAFF NO.</label>
                                      <input type="text" class="form-control form-control-sm"  readonly  value="<?php echo $_SESSION['username'] ?>" name="sp" id="sp" placeholder="">
                                    </div>
                                    <div class="form-group">
                                      <label for="exampleInputUsername1">Othernames</label>
                                      <input type="text" class="form-control form-control-sm" readonly required  value="<?php echo $info['oname']?>" name="oname"  id="oname" placeholder="">
                                    </div>
                                    <div class="form-group">
                                      <label for="exampleInputUsername1">Gender</label>
                                        <input type="text" class="form-control form-control-sm" required  value="<?php echo $info['gender']?>" name="gender"  id="gender" placeholder="">
                                                          </div>
                                    <div class="form-group">
                                      <label for="exampleInputUsername1">Personal Email</label>
                                      <input type="email" class="form-control form-control-sm" required  name="email" value="<?php echo $info['email']?>" id="email" >
                                    </div>
                                    
                                    <div class="form-group">
                                      <label for="exampleInputUsername1">Cadre</label>
                                      <input type="text" class="form-control form-control-sm" required   name="cadre" value="<?php echo $info['cadre']?>" id="cadre" >
                                    </div>
                                    <div class="form-group">
                                      <label for="exampleInputUsername1">Corporate Email Status</label>
                                        <select class="form-control form-control-sm" style="color:black" name="estatus">
                                            <?php 
                                                if($info['email_status'] =='active'){
                                                    echo '
                                                        <option value="active" selected>Active</option>
                                                        <option value="reset">Needs Reset</option>
                                                        <option value="not-exist">I don\'t have</option>
                                                    ';
                                                }
                                                elseif($info['email_status'] =='reset'){
                                                     echo '
                                                        <option value="active">Active</option>
                                                        <option value="reset" selected>Needs Reset</option>
                                                        <option value="not-exist">I don\'t have</option>
                                                    ';
                                                }
                                                elseif($info['email_status'] =='not-exist'){
                                                     echo '
                                                        <option value="active">Active</option>
                                                        <option value="reset">Needs Reset</option>
                                                        <option value="not-exist" selected>I don\'t have</option>
                                                    ';
                                                }else{
                                                     echo '
                                                        <option value="active">Active</option>
                                                        <option value="reset">Needs Reset</option>
                                                        <option value="not-exist">I don\'t have</option>
                                                    ';
                                                }
                                                
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                      <label for="exampleInputUsername1">Office No.</label>
                                      <input type="text" class="form-control form-control-sm"  name="office" value="<?php echo $info['office_no']?>" id="office" placeholder="" >
                                    </div>
                                    <div class="form-group" hidden>
                                      <label for="exampleInputUsername1" >Area of Specialization</label>
                                      <input type="text" class="form-control form-control-sm"  name="specialization" value="<?php echo $info['specialization']?>" id="specialization" placeholder="" >
                                    </div>
                                  </div>
                                  <div class="col-lg-6">
                                    <div class="form-group">
                                      <label for="exampleInputUsername1">First Name</label>
                                      <input type="text" class="form-control form-control-sm" required readonly  name="fname" value="<?php echo $info['fname']?>" id="firstname" placeholder="" >
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputUsername1">Last Name</label>
                                        <input type="text" class="form-control form-control-sm" readonly required  name="lname" value="<?php echo $info['lname']?>" id="lastname" placeholder="" >
                                      </div>
                                    <div class="form-group">
                                      <label for="exampleInputUsername1">Date Of Birth </label>
                                      <input type="text" class="form-control form-control-sm" readonly value="<?php echo $info['dob']?>" id="dob" name="dob" placeholder="" >
                                    </div>
                                   
                                    <div class="form-group">
                                      <label for="exampleInputUsername1">Phone</label>
                                      <input type="text" class="form-control form-control-sm" required  value="<?php echo $info['phone_no']?>" name="phone" id="phone" placeholder="" >
                                    </div>
                                    <div class="form-group">
                                      <label for="exampleInputUsername1">Rank</label>
                                      <input type="text" class="form-control form-control-sm"   value="<?php echo $info['rank']?>" name="rank" id="rank" placeholder="" >
                                    </div>
                                    <div class="form-group">
                                      <label for="exampleInputUsername1">Corporate Email (Optional)</label>
                                      <input type="email" class="form-control form-control-sm"   value="<?php echo $info['corporate_mail']?>" name="cemail" id="cemail" placeholder="Those that have. " >
                                    </div>
                                    <div class="form-group">
                                      <label for="exampleInputUsername1">Department</label>
                                      <input type="text" class="form-control form-control-sm"   value="<?php echo $info['dept']?>" name="department" id="department" placeholder="" >
                                    </div>
                                    <div class="form-group" hidden>
                                      <label for="exampleInputUsername1">Publications/Research</label>
                                      <input type="text" class="form-control form-control-sm"  value="<?php echo $info['publication']?>"  name="research" value="" id="research" placeholder="">
                                    </div>
                                  </div>
                                </div>
                                <div class="form-group" >
                                      <label for="exampleInputUsername1">Academic Qualification</label>
                                      <textarea class="form-control form-control-sm" name="qualification" id="qualification"><?php echo $info['qualification']?></textarea>
                                    </div>
                                <div class="msg"></div>
                                <!-- end row -->
                                <div class="row">
                                    
                                  <div class="form-group offset-md-5">
                                      <button type="submit" class="btn btn-outline-primary btn-icon-text updateProfile" rel="form-profile1">
                                              <i class="mdi mdi-file-check btn-icon-prepend"></i>
                                              Save profile
                                      </button> 
                                  </div>
                                </div>                      
                            </form>
                      </div>
                      <div class="panel col-md-3">
                                <div class="">
                                  <h5>UPLOAD PASSPORT HERE</h5>
                                    <form action="" method="post" name="" enctype="multipart/form-data">
                                      <div id="img2">
                                        <img src="<?php echo $passportUpload['file'] ?>" style="height:150px;width:250px;" name="img-pass" id="img-pass" class="img-responsive img-thumbnail" alt=""/>
                                      </div>
                                      
                                      <input type="file" class="form-control form-control-sm" name="file" id="file" aria-describedby="helpId" placeholder="">   
                                      <input type="hidden" id="type" value="passport">
                                      <input type="hidden" id="spno" value="'.$_SESSION['username'].'">
                                      <button type="submit" class="btn btn-primary btn-sm col-12" name="uploadPass" id="uploadPass">
                                              <i class="mdi mdi-upload  icon-sm"></i> Upload Passport
                                      </button>
                                    </form>
                                
                                <div class="msg-passport"></div>
                            </div><!-- end row -->
                      </div>
                  </div>
                 
                </div>
              </div>
            </div>
          </div>
        <!-- content-wrapper ends -->
        <?php 
          echo $view->footer2();
        ?>