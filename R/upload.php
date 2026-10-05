<?php 
        require_once('../config/classes/View.php');
        require_once('../config/classes/User.php');
        // require_once('../config/classes/Putme.php');
        $view = new View();
        $user = new User();
        // $putme = new Putme();

        // $user->is_authenticated('auth/');
        // $user->isBur('../auth/logout.php');
        //$payment = $putme->getPaymentInfoArray($_SESSION['username']);
        
        echo $view->subHeader('../');
    ?>
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
        <div class="row">
              <div class="col-md-12 grid-margin">
                <div class="d-flex justify-content-between flex-wrap">
                  <div class="d-flex align-items-end card stretch-card flex-wrap">
                    <div class="mr-md-5 card-body mr-xl-5">
                      <h2>UPLOAD PAYSLIPS </h2>
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
                    <form class="my-form form-vertical" target="_blank"method="post" enctype="multipart/form-data" action="processUpload.php" >                                                  
                      <div class="row">
                        <div class="col-md-6 col-sm-6 col-sx-12">
                          <div class="form-group">
                            <label for="exampleInputUsername1"> Select a file (<span class="text-danger">.pdf only</span>)</label>
                            <input type="file" name="file" id="file" class="form-control text-dark form-control-sm">                                                               
                          </div>
                        </div>
                        <div class="col-md-5 col-sm-6 col-sx-12">
                          <div class="form-group"><br/>
                            <button type="submit" class="btn btn-md btn-danger"  name="generate" id="generate">
                            <i class="mdi mdi-upload menu-icon"></i>
                            Upload
                            </button>
                          </div>
                        </div>                            
                      </div>                                                    
                    </form>
                  </div>
                  <?php 
                    if (isset($_POST['generate'])) {
                      echo $putme->generatePaymentReport($_POST['session'],$_POST['type']);
                    }
                  ?>
                </div>
          </div>
        </div>
        </div>
        
            <!-- content-wrapper ends -->
        <?php 
          echo $view->subFooter('../');
        ?>