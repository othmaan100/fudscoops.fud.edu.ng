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
                      <h2>VIEW MEMBERS SAVINGS </h2>
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
                    <form class="form-fetch_savings" id="form-fetch_savings" >                                                 
                      <div class="row">
                        
                           <div class="col-md-4 col-sm-3 col-sx-12">
                          <div class="form-group">
                            <label for="exampleInputUsername1"> FROM </label>
                            <input type="date" name="from" id="from"  class="form-control text-dark form-control-sm">                                                               
                          </div>
                        </div>
                        
                         <div class="col-md-4 col-sm-3 col-sx-12">
                          <div class="form-group">
                            <label for="exampleInputUsername1"> TO </label>
                            <input type="date" name="to" id="to" class="form-control text-dark form-control-sm">                                                               
                          </div>
                        </div>
                          
                           <div class="col-lg-2 col-md-3 col-sm-6 col-xs-12">
                          <div class="form-group">
                                <label for="exampleInputUsername1">  </label>
                              <button type="submit" id="fetch_savings" class="form-control btn btn-sm btn-danger" name="fetch_savings" value="Fetch">
                                Fetch <i class="fa fa-upload"></i></button>
                          </div>
                       </div>
                          
                      
                        <br/>
                        <div id="msg" class="row text-center msg">
                          <h3 id="loading" hidden><img src="../../img/loading.gif" alt=""> <i>Fetching  please wait...</i></h3>
                        </div>              
                      </div>                                                    
                    </form>
                    
                    
                    
                 
                                              
                                              
                                              
                                              
                    
                    
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
        
         
   		============================================ -->
<!-- Add these CSS files in your HTML head section -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">

<!-- Add these JavaScript files before closing body tag (after jQuery) -->
<script type="text/javascript" src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap4.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
    
    
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
</script>
   </body>

</html>