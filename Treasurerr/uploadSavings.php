<?php

require_once '../../classes/Generic.php';
require_once '../../classes/Security.php';
require_once '../../classes/DropDown.php';
require_once '../../classes/Admin.php';
require_once '../../classes/CourseRegistration.php';

Security::isLoggedIn('../../');
// Security::isLecturer();

// $session = Generic::getCurrentSession();
// $id = $_SESSION['username'];
// $sid = Generic::getStaffId($id);



?>
<!doctype html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Index | Smart Result</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- favicon
		============================================ -->
    <link rel="shortcut icon" type="image/x-icon" href="../../img/favicon.ico">
    <!-- Google Fonts
		============================================ -->
    <link href="s://fonts.googleapis.com/css?family=Roboto:100,300,400,700,900" rel="stylesheet">
    <!-- Bootstrap CSS
		============================================ -->
    <link rel="stylesheet" href="../../css/bootstrap.min.css">
    <!-- Bootstrap CSS
		============================================ -->
    <link rel="stylesheet" href="../../css/font-awesome.min.css">
    <!-- owl.carousel CSS
		============================================ -->
    <link rel="stylesheet" href="../../css/owl.carousel.css">
    <link rel="stylesheet" href="../../css/owl.theme.css">
    <link rel="stylesheet" href="../../css/owl.transitions.css">
    <!-- animate CSS
		============================================ -->
    <link rel="stylesheet" href="../../css/animate.css">
    <!-- normalize CSS
		============================================ -->
    <link rel="stylesheet" href="../../css/normalize.css">
    <!-- meanmenu icon CSS
		============================================ -->
    <link rel="stylesheet" href="../../css/meanmenu.min.css">
    <!-- main CSS
		============================================ -->
    <link rel="stylesheet" href="../../css/main.css">
    <!-- educate icon CSS
		============================================ -->
    <link rel="stylesheet" href="../../css/educate-custon-icon.css">
    <!-- morrisjs CSS
		============================================ -->
    <link rel="stylesheet" href="../../css/morrisjs/morris.css">
    <!-- mCustomScrollbar CSS
		============================================ -->
    <link rel="stylesheet" href="../../css/scrollbar/jquery.mCustomScrollbar.min.css">
    <!-- metisMenu CSS
		============================================ -->
    <link rel="stylesheet" href="../../css/metisMenu/metisMenu.min.css">
    <link rel="stylesheet" href="../../css/metisMenu/metisMenu-vertical.css">
    <!-- calendar CSS
    ============================================ -->
    <link rel="stylesheet" href="../../css/select2/select2.min.css">
    <!-- chosen CSS
		============================================ -->
    <link rel="stylesheet" href="../../css/chosen/bootstrap-chosen.css">

    <!-- style CSS
		============================================ -->
    <link rel="stylesheet" href="../../style.css">
    <!-- responsive CSS
		============================================ -->
    <link rel="stylesheet" href="../../css/responsive.css">
    <!-- modernizr JS
		============================================ -->
    <script src="../../js/vendor/modernizr-2.8.3.min.js"></script>
</head>

<body>
    <!--[if lt IE 8]>
		<p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
	<![endif]-->
    <!-- Start Left menu area -->
    <div class="left-sidebar-pro">
    <?php echo Generic::ICTNav();?>

    </div>
    <!-- End Left menu area -->
    <!-- Start Welcome area -->
    <div class="all-content-wrapper">
        <!--<div class="container-fluid">-->
        <!--    <div class="row">-->
        <!--        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">-->
        <!--            <div class="logo-pro">-->
        <!--                <a href="index.php"><img class="main-logo" src="../../img/logo/logo.png" alt="" /></a>-->
        <!--            </div>-->
        <!--        </div>-->
        <!--    </div>-->
        <!--</div>-->
        <div class="header-advance-area">
            <div class="header-top-area">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <div class="header-top-wraper">
                                <div class="row">
                                    <div class="col-lg-1 col-md-0 col-sm-1 col-xs-12">
                                        <div class="menu-switcher-pro">
                                            <button type="button" id="sidebarCollapse" class="btn bar-button-pro header-drl-controller-btn btn-info navbar-btn">
													<i class="educate-icon educate-nav"></i>
												</button>
                                        </div>
                                    </div>
                                    <div class="col-lg-5 col-md-7 col-sm-6 col-xs-12">
                                        <div class="header-top-menu tabl-d-n">
                                            <ul class="nav navbar-nav mai-top-nav">
                                                <li class="nav-item"><a href="#" class="nav-link">ICT</a>
                                                </li>
                                                
                                            </ul>
                                          </div>
                                    </div>
                                    <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12">
                                        <div class="header-right-info">
                                            <ul class="nav navbar-nav mai-top-nav header-right-menu">
                                                
                                                <li class="nav-item">
                                                    <a href="#" data-toggle="dropdown" role="button" aria-expanded="false" class="nav-link dropdown-toggle">
                                                    <img src="../../img/product/pro4.jpg" alt="" />
                                                    <span class="admin-name"><?php echo strtoupper($id)?></span>
                                                    <i class="fa fa-angle-down edu-icon edu-down-arrow"></i>
                                                  </a>
                                                    <ul role="menu" class="dropdown-header-top author-log dropdown-menu animated zoomIn">
                                                        <li><a href="#"><span class="edu-icon edu-home-admin author-log-ic"></span>Change Password</a>
                                                        </li>
                                                        <li><a href="#"><span class="edu-icon edu-user-rounded author-log-ic"></span>My Profile</a>
                                                        </li>
                                                        
                                                        </li>
                                                        <li><a href="../../account/logout.php"><span class="edu-icon edu-locked author-log-ic"></span>Log Out</a>
                                                        </li>
                                                    </ul>
                                                </li>
                                                <li></li>
                                            
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="col-lg-1 col-md-0 col-sm-1 col-xs-12"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Mobile Menu start -->
            <div class="mobile-menu-area">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <div class="mobile-menu">
                            <?php echo Generic::ICTNavMobile();?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Mobile Menu end -->
            <div class="breadcome-area">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <div class="breadcome-list">
                                <div class="row">
                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                        <div class="breadcome-heading">
                                            
                                            <h5>Upload New Courses</h5>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                        <ul class="breadcome-menu">
                                            <li><a href="#">Home/ Course_Upload</a> <span class="bread-slash">/</span>
                                            </li>
                                            
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="courses-area mg-b-15">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <div class="white-box">
                        <div class="sparkline13-graph">
                                      
                                            
                                    </div>
                            <div class="data-table-areal mg-b-15">
                            <div class="container-fluid">
                                <div class="row">

                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <div class="sparkline13-list">
                                            <div class="sparkline13-hd">
                                            
                                            
                                            <div class="row col-lg-12"> 
                                              <div class="col-lg-9">
                                              <form class="form-add" role="form" id="form-upload" enctype="multipart/form-data">
                                              <div class="row">
        
                                              <div class="col-lg-5 col-md-3 col-sm-6 col-xs-12">
                                                  <div class="col-lg-4"><h6>Select Department:</h6></div>
                                                  <div class="col-lg-8">
                                                      <select required data-placeholder="Choose a department..." name="department" id="department" class="chosen-select" tabindex="-1">
                                                          <?php echo DropDown::loadDepartment()?>
                                                        </select>
                                                  </div>
                                              </div>
                                            <div class="col-lg-5">
                                              <div class="col-lg-3"><h6>Choose a File:</h6></div>
                                              <div class="col-lg-9">                        
                                              <input type="file" required name="uploadFile" id="uploadFile" class="form-control" placeholder="Choose a file"/>
                                              <!--<input type="text" name="session" hidden="hidden" value="<?php echo $session?>"  id="session" class="form-control hidden" placeholder="Choose a file"/>-->
                                              <!--<input type="text" name="staff" hidden="hidden" id="staff" value="<?php echo $id?>" class="form-control hidden" placeholder="Choose a file"/>-->

                                              </div>

                                            </div>                                              
                                            <div class="col-lg-2 col-md-3 col-sm-6 col-xs-12">
                                              <!--<input type="text" hidden name="id" id="id" value="<?php echo $id?>">-->
                                              <!-- <input type="text" hidden name="type" id="type" value="course_reg"> -->
                                              <div class=" ">
                                                  <button type="submit" id="upload_course" class="btn btn-sm btn-danger" name="upload_course">
                                                    Upload <i class="fa fa-upload"></i></button>
                                              </div>
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
                                                      <li><a href="../../course_template.csv" download class="text-primary" style="color:blue;">Download</a> Tempalate. </li>
                                                      <li>Always follow the above template to upload your exams</li>
                                                      <li>Ensure your excel file has a <b class="text-danger">.csv</b> extension</li>
                                                    </ol>
                                                  </div>
                                                </div>
                                              </div>                                           
                                                                                                                                         
                                          </div>
                                        
                                        </div><hr/>
                                        <hr/>
                                            </div><br/>
                                            
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                </div>
                </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
        <div class="footer-copyright-area">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="footer-copy-right">
                            <p>FUD - ICT © 2023 v2.0 All rights reserved.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="WarningModalalert" data-backdrop='static' data-keyboard='false' class="modal modal-edu-general Customwidth-popup-WarningModal fade" role="dialog">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-close-area modal-close-df">
                    <a class="close"  href="upload_result.php"><i class="fa fa-close"></i></a>
                </div><br/>
                <div class="modal-heading">
                  <div class="container">
                  <h5>Add individual result for <b class="bg-danger"><?php echo Generic::getSessionText($session)?></b></h5>
                  </div>

                </div>
                <div class="modal-body">
                  <div class="row">
                    <div class="col-lg-5 col-md-3 col-sm-6 col-xs-12">
                      <div class="col-lg-4"><h6>Course:</h6></div>
                      <div class="col-lg-8">
                        <select data-placeholder="Choose a course..." name="course" id="course" class="chosen-select" tabindex="-1">
                            <?php echo DropDown::loadMyCourses($id,$session)?>
                          </select>
                      </div>
                    </div>
                    <div class="col-lg-5">
                    <div class="col-lg-3"><h6>RegNo:</h6></div>
                    <div class="col-lg-9">                        
                    <input type="text" name="regno" value=""  id="regno" class="form-control" placeholder="Matric No."/>
                    <input type="text" name="session" hidden="hidden" value="<?php echo $session?>"  id="session" class="form-control hidden" placeholder="Choose a file"/>
                    <input type="text" name="staff" hidden="hidden" id="staff" value="<?php echo $id?>" class="form-control hidden" placeholder="Choose a file"/>
                    <input type="text" name="type" hidden="hidden" id="type" value="lecturer"/>

                    </div>
                    </div>
                    <div class="col-lg-1">
                    <button id="verify" class="btn btn-sm btn-warning">Verify</button>
                    </div>                                                     
                </div>
                <div id="verifyContent"></div>
              
                </div>
                <div class="modal-footer warning-md">
                    <a class="btn btn-sm btn-danger" href="upload_result.php">Close</a>
                </div>
            </div>
        </div>
    </div>
    <!-- jquery
		============================================ -->
    <script src="../../js/vendor/jquery-1.12.4.min.js"></script>
    <!-- bootstrap JS
		============================================ -->
    <script src="../../js/bootstrap.min.js"></script>
    <!-- wow JS
		============================================ -->
    <script src="../../js/wow.min.js"></script>
    <!-- price-slider JS
		============================================ -->
    <script src="../../js/jquery-price-slider.js"></script>
    <!-- meanmenu JS
		============================================ -->
    <script src="../../js/jquery.meanmenu.js"></script>
    <!-- owl.carousel JS
		============================================ -->
    <script src="../../js/owl.carousel.min.js"></script>
    <!-- sticky JS
		============================================ -->
    <script src="../../js/jquery.sticky.js"></script>
    <!-- scrollUp JS
		============================================ -->
    <script src="../../js/jquery.scrollUp.min.js"></script>
    <!-- counterup JS
		============================================ -->
    <script src="../../js/counterup/jquery.counterup.min.js"></script>
    <script src="../../js/counterup/waypoints.min.js"></script>
    <script src="../../js/counterup/counterup-active.js"></script>
    <!-- mCustomScrollbar JS
		============================================ -->
    <script src="../../js/scrollbar/jquery.mCustomScrollbar.concat.min.js"></script>
    <script src="../../js/scrollbar/mCustomScrollbar-active.js"></script>
    <!-- metisMenu JS
		============================================ -->
    <script src="../../js/metisMenu/metisMenu.min.js"></script>
    <script src="../../js/metisMenu/metisMenu-active.js"></script>
    <!-- morrisjs JS
		============================================ -->
    <script src="../../js/morrisjs/raphael-min.js"></script>
    <script src="../../js/morrisjs/morris.js"></script>
    <script src="../../js/morrisjs/morris-active.js"></script>
    <!-- morrisjs JS
		============================================ -->
    <script src="../../js/sparkline/jquery.sparkline.min.js"></script>
    <script src="../../js/sparkline/jquery.charts-sparkline.js"></script>
    <script src="../../js/sparkline/sparkline-active.js"></script>
 <!-- chosen JS
		============================================ -->
    <script src="../../js/chosen/chosen.jquery.js"></script>
    <script src="../../js/chosen/chosen-active.js"></script>
    <!-- select2 JS
		============================================ -->
    <script src="../../js/select2/select2.full.min.js"></script>
    <script src="../../js/select2/select2-active.js"></script>
    
    <!-- plugins JS
		============================================ -->
    <script src="../../js/plugins.js"></script>
    <!-- main JS
		============================================ -->
    <script src="../../js/main.js"></script>
    <!-- Queries
    ============================================ -->
    <script src="../../js/queries.js"></script>

     <!-- data table JS
		============================================ -->
    <script src="../../js/data-table/bootstrap-table.js"></script>
    <script src="../../js/data-table/data-table-active.js"></script>
    <script src="">
      $(document).ready(function (e) {
        alert('ok')
      })
    </script>
   </body>

</html>