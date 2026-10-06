<?php 
        require_once('../config/classes/View.php');
        require_once('../config/classes/User.php');
        // require_once('../config/classes/Putme.php');
        $view = new View();
        $user = new User();
        // $putme = new Putme();

        $user->is_authenticated('../auth/');
        $user->isBur('../auth/logout.php');
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
        <div class="row">
        <div class="row">
              <div class="col-md-12 grid-margin">
                <div class="d-flex justify-content-between flex-wrap">
                  <div class="d-flex align-items-end card stretch-card flex-wrap">
                    <div class="mr-md-5 card-body mr-xl-5">
                      <h2>UPLOAD MEMBERS SAVINGS </h2>
                    </div>                  
                  </div>
                </div>
              </div>
        </div>
        <div class="col-md-12 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="row">
                        <div class="col-md-12 card">
                            <a class="btn btn-sm btn-success" href='downloadSavingsRecord.php'>Download Staff Savings List</a>
                 
                        </div>
                    </div>
                  <div class="col-md-12 card">
                    <form class="form-add" role="form" id="form-upload" enctype="multipart/form-data">                                                 
                      <div class="row">
                        
                            <!--<input type="hidden" name="legacy" value="<?php //echo $legacy?>"  placeholder="SPR0000" id="legacy" class="form-control text-dark form-control-sm">                                                               -->
                          
                        <div class="col-md-4 col-sm-3 col-sx-12">
                          <div class="form-group">
                            <label for="exampleInputUsername1"> Month: </label>
                            <select  class="form-control text-dark form-control-sm" name="month" id="month">
                              <?php echo $view->loadMonths(date('m')) ?>
                              <!-- <option value="">Salis</option> -->
                            </select>
                            
                          </div>
                        </div>
                        <div class="col-md-4 col-sm-3 col-sx-12">
                          <div class="form-group">
                            <label for="exampleInputUsername1"> Year</label>
                            <input type="number" name="year" id="year" value="<?php echo date('Y') ?>" class="form-control text-dark form-control-sm">                                                               
                          </div>
                        </div>
                        
                        <div class="col-md-4 col-sm-3 col-sx-12">
                          <div class="col-lg-3"><h6>Choose a File:</h6></div>
                          <div class="col-lg-9">                        
                                <input type="file" required name="uploadFile" id="uploadFile" class="form-control" placeholder="Choose a file"/>
                           </div>
                        </div> 
                    
                      <div class="col-lg-2 col-md-3 col-sm-6 col-xs-12">
                          <div class=" ">
                              <button type="submit" id="upload_savings" class="btn btn-sm btn-danger" name="upload_savings" value="Upload">
                                Upload <i class="fa fa-upload"></i></button>
                          </div>
                       </div>
                        <br/>
                        <div id="msg" class="row text-center">
                          <h3 id="loading" hidden><img src="../../img/loading.gif" alt=""> <i>Uploading results please wait...</i></h3>
                        </div>              
                      </div>                                                    
                    </form>
                    
                    
                    
                    <div class="col-lg-3">    
                    <div class="panel panel-warning">
                      <div class="panel-heading">Guide</div>
                      <div class="panel-body">
                        <ol>
                          <li><a href="../../savings_template.csv" download class="text-primary" style="color:blue;">Download</a> Tempalate. </li>
                          <li>Always follow the above template to upload your Deposit</li>
                          <li>Ensure your excel file has a <b class="text-danger">.csv</b> extension</li>
                        </ol>
                      </div>
                    </div>
                  </div> 
                                              
                                              
                                              
                                              
                    
                    
                  </div>  <!--col-md-12 card Ends-->
                  
                </div>  <!--roow Ends-->
          </div>
        </div>
        </div>
        
         </div>
        </div>
        </div>
        
    </div>

        
        <!-- content-wrapper ends -->
        
        
        
        
        <?php 
          echo $view->subFooter('../');
        ?>