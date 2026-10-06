<?php 
        require_once('../config/classes/View.php');
        require_once('../config/classes/User.php');

        $view = new View();
        $user = new User();
        
        echo $view->subHeader('../');
        $user->is_authenticated('../auth/');
        $user->isBur('../auth/logout.php');

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
                      <h2>GENERATE PAYSLIPS </h2>
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
                    <form class="my-form form-vertical" target="_blank"method="post" action="slip.php" >                                                  
                      <div class="row">
                        <div class="col-md-3 col-sm-3 col-sx-12">
                          <div class="form-group">
                            <label for="exampleInputUsername1"> Legacy ID:</label>
                            <input type="text" name="legacy" placeholder="SPR0000" id="legacy" class="form-control text-dark form-control-sm">                                                               
                          </div>
                        </div>
                        <div class="col-md-3 col-sm-3 col-sx-12">
                          <div class="form-group">
                            <label for="exampleInputUsername1"> Month: </label>
                            <select  class="form-control text-dark form-control-sm" name="month" id="month">
                              <?php echo $view->loadMonths(date('m')) ?>
                              <!-- <option value="">Salis</option> -->
                            </select>
                            
                          </div>
                        </div>
                        <div class="col-md-3 col-sm-3 col-sx-12">
                          <div class="form-group">
                            <label for="exampleInputUsername1"> Year</label>
                            <input type="number" name="year" id="year" value="<?php echo date('Y') ?>" class="form-control text-dark form-control-sm">                                                               
                          </div>
                        </div>
                        
                        <div class="col-md-3 col-sm-3 col-sx-12">
                          <div class="form-group"><br/>
                            <button type="submit" class="btn btn-md btn-danger"  name="generate" id="generate">
                            <i class="mdi mdi-cog menu-icon"></i>
                            Generate
                            </button>
                          </div>
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