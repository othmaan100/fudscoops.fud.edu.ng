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
      <div class="main-panel">
          <div class="content-wrapper">
            <div class="row">
              <div class="col-md-12 grid-margin">
                <div class="d-flex justify-content-between flex-wrap">
                  <div class="d-flex align-items-end card stretch-card flex-wrap">
                    <div class="mr-md-5 card-body mr-xl-5">
                      <h2>UPLOAD STAFF</h2>
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
                        <form class="form-vertical" target="_blank" id=""  enctype="multipart/form-data"  >                                                  
                          <div class="row">
                            
                            <div class="col-md-3 col-sm-6 col-sx-12">
                              <div class="form-group">
                                <label for="exampleInputUsername1">FILE</label>
                                <input type="file" id="uploadFile" required name="uploadFile" class="form-control form-control-sm" />
                              </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-sx-12">
                              <div class="form-group"><br/>
                                <button type="submit" class="btn btn-lg btn-danger"  name="upload_members" id="upload_members" >
                                  UPLOAD
                                </button>
                              </div>
                            </div>
                            
                              <div class="col-lg-4 mt-4">
                                <div class="panel panel-warning">
                                    <div class="panel-heading">Guide</div>
                                    <div class="panel-body">
                                        <ol>
                                            <li>
                                                <a href="../Members_uploads.csv" download class="text-primary">Download</a> Template.
                                            </li>
                                            <li>Always follow the above template to upload your staff.</li>
                                            <li>Ensure your file has a <b class="text-danger">.csv</b> extension.</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                            
                             <div id="msg" class="row text-center">
                              <h3 id="loading" hidden><img src="../../img/loading.gif" alt=""> <i>Uploading results please wait...</i></h3>
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