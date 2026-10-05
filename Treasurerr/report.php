<?php 
        require_once('../config/classes/View.php');
        require_once('../config/classes/User.php');
        require_once('../config/classes/Payslip.php');
        $view = new View();
        $user = new User();
        $payslip = new Payslip();

        //$user->is_authenticated('../auth/');
        //$user->isICT('../auth/');
        //$info = $putme->getCandidateInfoArray($_SESSION['username']);
        //$alevel = $putme->getAlevelInfoArray($_SESSION['username']);
        // var_dump($sittin1);die();
        echo $view->subHeader('../');
        ?>
        <!-- partial:partials/_navbar.html -->
        <?php  echo $view->subPartialNav('../')?>
        <!-- partial -->
        <div class="container-fluid page-body-wrapper">
          <!-- partial:partials/_sidebar.html -->
          <?php  echo $view->ICTSideNav()?>
      <!-- partial -->
      <div class="main-panel">
          <div class="content-wrapper">
            <div class="row">
              <div class="col-md-12 grid-margin">
                <div class="d-flex justify-content-between flex-wrap">
                  <div class="d-flex align-items-end card stretch-card flex-wrap">
                    <div class="mr-md-5 card-body mr-xl-5">
                      <h2>GENERATE STAFF REPORT</h2>
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
                        <form class="my-form form-vertical" method="post" action="report_unregistered.php" >                                                  
                          <div class="row">
                            
                            <div class="col-md-3 col-sm-6 col-sx-12">
                              <div class="form-group">
                                <label for="exampleInputUsername1">Type</label>
                                <select name="type" id="type" class="form-control text-dark form-control-sm">
                                  <?php echo $view->loadType($_POST['type'] )?>
                                </select>
                              </div>
                            </div>
                            <div class="col-md-5 col-sm-6 col-sx-12">
                              <div class="form-group"><br/>
                                <button type="submit" class="btn btn-md btn-danger"  name="generate" id="generate">
                                  Generate
                                </button>
                              </div>
                            </div>
                            
                          </div>                                                    
                        </form>
                      </div>
                      <?php 
                        if (isset($_POST['generate'])) {
                          echo $payslip->generateReport($_POST['type']);
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
           <script>
          $(document).ready( function () {
            $('#myTable').DataTable( {
                  dom: 'Bfrtip',
                  buttons: [
                      // 'copyHtml5',
                      'excelHtml5',
                      'csvHtml5',
                      'pdfHtml5'
                  ]
              } );
          } );
        </script>
        