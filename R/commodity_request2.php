<?php 
        require_once('../config/classes/View.php');
        require_once('../config/classes/User.php');
        // require_once('../config/classes/Putme.php');
        require_once '../config/classes/Commodity.php';

        $view = new View();
        $user = new User();
        $commodity = new Commodity();

        $user->is_authenticated('../auth/');
        $user->isStaff('../auth/logout.php');
        
        $staff_info =  $user->getStaffInformation($_SESSION['username']);
        $employeeid = $user->getEmployeeId($_SESSION['username']);
        $memberid = $user->getMemberId($employeeid);
        $share_info = $user->getMemberShareInformation($memberid);
        $share_unit = $user->getShareUnitInfoArray();
        $date = date('Y-m-d', strtotime($share_info['date']));
        
    
        //$payment = $putme->getPaymentInfoArray($_SESSION['username']);
        
        echo $view->subHeader('../');
    ?>
    <!-- partial:partials/_navbar.html -->
    <?php  echo $view->subPartialNav('../')?>
    <!-- partial -->
    <div class="container-fluid page-body-wrapper">
      <!-- partial:partials/_sidebar.html -->
      <?php  echo $view->staffSideNav()?>
      
      <!-- partial -->
      <div class="main-panel">
        <div class="content-wrapper">
          
          <div class="row">
            <div class="col-md-12 grid-margin">
              <div class="d-flex justify-content-between flex-wrap">
                <div class="d-flex align-items-end card flex-wrap">
                  
                  
                  
                  
                   <div class="container mt-5">
                       <img src="../../images/logo-mini.png" style="display:block;margin-left:auto;margin-right:auto;margin-bottom:2px;margin-top:-30px; width:100px;" class="rounded mx-auto d-block" alt="logo">
                                <h2 class="text-center">FUD STAFF COOPERATIVE SOCIETY LIMITED</h2>
                                <h5 class="text-center">[An Interest Free Thrift and Loan Society]</h5>
                                <h5 class="text-center mb-4" style="text-decoration: underline;">COMMODITY LOAN APPLICATION FORM</h5>
                                
                                  <?php
                                
                                 if ($commodity->checkSchedule()) { 
                                           $timeout = $commodity->getSchedulesTimeOut();
                                            $timeoutid = $commodity->getSchedulesTimeOutId();
                                            $items = Commodity::getCommoditySupplyItems($timeoutid);
                                            $itemsview = Commodity::getCommoditySupplyItemsPreview($timeoutid);
                                            
                                            echo "<h3>Commodity Request Loan closes in</h3>
                                                    <input type='hidden' name='time' id='time' value='$timeout'>
                                                    
                                                    <h1 class='row alert alert-danger'>
                                                      <i class='fa fa-clock-o' aria-hidden='true'></i> <span id='demo'></span>
                                                    </h1>
                                                    
                                                    <form action='#' id='form-course-reg' data-semester='2' class='form-course-reg' method='post'>
                                                      <input type='hidden' id='dept' value=''>
                                                    
                                                      <div class='table-wrapper'>
                                                        <div class='col-lg-3 col-md-3'>
                                                          <input type='text' disabled class='form-control dt-tb text-success' value='List of Available Items'/>
                                                        </div>
                                                    
                                                        <table id='table' class='table t2 table-bordered table-responsive crf-frm' style='width:100%; overflow:scroll;'>
                                                          <thead>
                                                            <tr>
                                                              <th data-field='id'>S/N</th>
                                                              <th data-field='state' data-checkbox=''><i class='fa fa-check'></i></th>
                                                              <th data-field='item' data-editable='true'>Item</th>
                                                              <th data-field='quantity' data-editable='true'>Quantity Request</th>
                                                            </tr>
                                                          </thead>
                                                          <tbody>
                                                            $items
                                                          </tbody>
                                                        </table>
                                                      </div>
                                                    </form>";
                                                    
                                            
                                            
                                            echo '<table id="table-r" class="table table-bordered" width="400">
                                                    <thead>
                                                        <tr>
                                                            <th data-field="id">S/N</th>
                                                            <th data-field="state" data-checkbox=""><i class="fa fa-check"></i></th>
                                                            <th data-field="item" data-editable="true">Item</th>
                                                            <th data-field="quantity" data-editable="true">Quantity Request</th>
                                                        </tr>
                                                    </thead> 
                                                    <tbody class="">
                                                        ' . $items . '
                                                    </tbody>
                                                </table>
                                                <button id="save-reg" rel="' . $level . '" class="btn btn-sm btn-primary col-lg-8">
                                                    <img src="../img/loading.gif" hidden id="loader" alt=""> 
                                                    <b id="btxt"> <i class="fa fa-print"></i> Save & Print</b> 
                                                </button>';  

                                        }
                                ?>
                        

                            

                    </div>
                  
                  
                  
                  
                  
                  
                                   
                </div>
               
              
              </div>
            </div>
          </div>   
        </div>
        <!-- content-wrapper ends -->
        
        </div>
        <!-- main panel ends -->
        </div>
        <!-- container fluids ends -->
        
        <?php 
          echo $view->subFooter('../');
        ?>
        
        <script>
    // Set the date we're counting down to
    var myTime = $('#time').val();
    var countDownDate = new Date(myTime+'').getTime();
    // alert(myTime)      
    // Update the count down every 1 second
          var x = setInterval(function() {

            // Get today's date and time
            var now = new Date().getTime();

            // Find the distance between now and the count down date
            var distance = countDownDate - now;

            // Time calculations for days, hours, minutes and seconds
            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

            // Display the result in the element with id="demo"
            document.getElementById("demo").innerHTML = days + " Day(s):" + hours + "Hour(s):"
            + minutes + " Minute(s):" + seconds + " Seconds ";

            // If the count down is finished, write some text
            if (distance < 0) {
              clearInterval(x);
              document.getElementById("demo").innerHTML = "CLOSED";
            }
          }, 1000);
    </script>