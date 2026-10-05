<?php
// session_start();
require_once('DB.php');
class View{
    private $db;

    public function header()
    {
        $r = '
        <!DOCTYPE html>
        <html lang="en">
        
        <head>
          <!-- Required meta tags -->
          <meta charset="utf-8">
          <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
          <title>Federal University Dutse | Staff Portal</title>
          <!-- plugins:css -->
          <link rel="stylesheet" href="vendors/mdi/css/materialdesignicons.min.css">
          <link rel="stylesheet" href="vendors/base/vendor.bundle.base.css">
          <!-- endinject -->
          <!-- plugin css for this page -->
          <link rel="stylesheet" href="vendors/datatables.net-bs4/dataTables.bootstrap4.css">
          <!-- End plugin css for this page -->
          <!-- inject:css -->
          <link rel="stylesheet" href="css/style.css">
          <link rel="stylesheet" href="css/putme.css">
          <script src="vendors/base/vendor.bundle.base.js"></script>

          <!-- endinject -->
          <link rel="shortcut icon" href="images/favicon.png" />
        </head>
        <body>
          <div class="container-scroller">
        
        ';
        return $r;
           
    }
    public function subHeader($url)
    {
        $r = '
        <!DOCTYPE html>
        <html lang="en">
        
        <head>
          <!-- Required meta tags -->
          <meta charset="utf-8">
          <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
          <title>Federal University Dutse | Staff Portal</title>
          <!-- plugins:css -->
          <link rel="stylesheet" href="'.$url.'vendors/mdi/css/materialdesignicons.min.css">
          <link rel="stylesheet" href="'.$url.'vendors/base/vendor.bundle.base.css">
          <!-- endinject -->
          <!-- plugin css for this page -->
          <link rel="stylesheet" href="'.$url.'vendors/datatables.net-bs4/dataTables.bootstrap4.css">
          <!-- End plugin css for this page -->
          <!-- inject:css -->
          <link rel="stylesheet" href="'.$url.'css/style.css">
          <link rel="stylesheet" href="'.$url.'css/putme.css">
          <script src="'.$url.'vendors/base/vendor.bundle.base.js"></script>

          <!-- endinject -->
          <link rel="shortcut icon" href="images/favicon.png" />
        </head>
        <body>
          <div class="container-scroller">
        
        ';
        return $r;
           
    }
    
    public function subPartialNav($url)
    {
        $r = '<nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
                <div class="navbar-brand-wrapper d-flex justify-content-center">
                <div class="navbar-brand-inner-wrapper d-flex justify-content-between align-items-center w-100">  
                    <span class="navbar- " href="index.html"><img src="'.$url.'images/logo-mini.png" alt="logo"/><b class="t-text" style="color: rgba(141, 58, 27, 0.63);"> STAFF PORTAL</b></span>
                    <!-- <a class="navbar-brand brand-logo-mini" href="index.html"><img src="images/logo.png" alt="logo"/></a> -->
                    <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
                    <span class="mdi mdi-sort-variant"></span>
                    </button>
                </div>  
                </div>
                <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
                
                <ul class="navbar-nav navbar-nav-right">
                    
                    
                    <li class="nav-item nav-profile dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" id="profileDropdown">
                            <img src="'.$url.'images/staffb.jpg" alt="profile"/>
                            <span class="nav-profile-name">'.$_SESSION['username'].'</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">              
                            <a class="dropdown-item" href="'.$url.'auth/logout.php">
                            <i class="mdi mdi-logout text-primary"></i>
                            Logout
                            </a>
                            <a class="dropdown-item" href="'.$url.'../change_password.php">
                            <i class="mdi mdi-logout text-primary"></i>
                            Change Password
                            </a>
                        </div>
                    </li>
                </ul>
                <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
                    <span class="mdi mdi-menu"></span>
                </button>
                </div>
            </nav>';
            return $r;
    }

    public function partialNav()
    {
        $r = '<nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
                <div class="navbar-brand-wrapper d-flex justify-content-center">
                <div class="navbar-brand-inner-wrapper d-flex justify-content-between align-items-center w-100">  
                    <span class="navbar- " href="index.html"><img src="images/logo-mini.png" alt="logo"/><b class="t-text" style="color: rgba(141, 58, 27, 0.63);">PUTME FUD</b></span>
                    <!-- <a class="navbar-brand brand-logo-mini" href="index.html"><img src="images/logo.png" alt="logo"/></a> -->
                    <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
                    <span class="mdi mdi-sort-variant"></span>
                    </button>
                </div>  
                </div>
                <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
                
                <ul class="navbar-nav navbar-nav-right">
                    
                    
                    <li class="nav-item nav-profile dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" id="profileDropdown">
                        <img src="images/staffb.jpg" alt="profile"/>
                        <span class="nav-profile-name">'.$_SESSION['username'].'</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">              
                        <a class="dropdown-item" href="auth/logout.php">
                        <i class="mdi mdi-logout text-primary"></i>
                        Logout
                        </a>
                    </div>
                    </li>
                </ul>
                <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
                    <span class="mdi mdi-menu"></span>
                </button>
                </div>
            </nav>';
            return $r;
    }
    public function sideNav()
    {
        $r = '<nav class="sidebar sidebar-offcanvas" id="sidebar">
                <ul class="nav">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                        <i class="mdi mdi-home menu-icon"></i>
                        <span class="menu-title">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="biodata.php">
                        <i class="mdi mdi-account-card-details menu-icon"></i>
                        <span class="menu-title">Bio-Data</span>
                        </a>
                    </li>
                            
                    <li class="nav-item">
                        <a class="nav-link" href="payment.php">
                        <i class="mdi mdi-cash-multiple menu-icon"></i>
                        <span class="menu-title">Payment</span>
                        </a>
                    </li>          
                    
                    <li class="nav-item">
                        <a class="nav-link" href="olevel.php">
                        <i class="mdi mdi-clipboard-check menu-icon"></i>
                        <span class="menu-title">O\'Level</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="alevel.php">
                        <i class="mdi mdi-clipboard-check menu-icon"></i>
                        <span class="menu-title">A\'Level</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="uploads.php">
                        <i class="mdi mdi-upload menu-icon"></i>
                        <span class="menu-title">Uploads</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="print_file.php">
                        <i class="mdi mdi-printer menu-icon"></i>
                        <span class="menu-title">Print</span>
                        </a>
                    </li>
                </ul>
            </nav>';
            return $r;
    
    }

    public function subSideNav()
    {
        $r = '<nav class="sidebar sidebar-offcanvas" id="sidebar">
                <ul class="nav">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                        <i class="mdi mdi-home menu-icon"></i>
                        <span class="menu-title">Dashboard</span>
                        </a>
                    </li>
                      
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="collapse" href="#ui-savings" aria-expanded="false" aria-controls="ui-savings">
                        <i class="mdi mdi mdi-credit-card-multiple menu-icon"></i>
                        <span class="menu-title">Approval</span>
                        <i class="menu-arrow"></i>
                        </a>
                        <div class="collapse" id="ui-savings">
                          <ul class="nav flex-column sub-menu">
                                <li class="nav-item"><a class="nav-link" href="member_list.php">Membership Registerations</a></li>
                                 <li class="nav-item"><a class="nav-link" href="update_savings.php">Savings Update</a></li>
                                <li class="nav-item"><a class="nav-link" href="withdrawal_endorsement.php">Savings Withdraws</a></li>
                                <li class="nav-item"><a class="nav-link" href="complete_withdrawals.php">Complete Withdrawals</a></li>
                                <li class="nav-item"><a class="nav-link" href="manage_share_purchase.php">Share Purchases</a></li>
                                <li class="nav-item"><a class="nav-link" href="manage_share_transfer.php">Share Transfers</a></li>
                                <li class="nav-item"><a class="nav-link" href="manage_share_conversion.php">Share Conversions</a></li>
                                <li class="nav-item"><a class="nav-link" href="share_holders_view.php">View Share Holders</a></li>
                                <li class="nav-item"><a class="nav-link" href="loans.php">Loans</a></li>
                                <li class="nav-item"><a class="nav-link" href="member_list.php">Sales</a></li>
                                <li class="nav-item"><a class="nav-link" href="approved_commodity_Request.php">Approved Commodity Disburstment</a></li> 
                               
                          </ul>
                        </div>
                 </li>
            
                    
                    <li class="nav-item">
                        <a class="nav-link" href="loan_deduction.php">
                        <i class="mdi mdi-upload menu-icon"></i>
                        <span class="menu-title">Loan Deduction</span>
                        </a>
                    </li>
                    
                    
                            
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                        <i class="mdi mdi-cash-multiple menu-icon"></i>
                        <span class="menu-title">Generate Report </span>
                        </a>
                    </li>  
                    
                    <li class="nav-item">
                        <a class="nav-link" href="add_user.php">
                       <i class="mdi mdi-account-plus menu-icon"></i>
                        <span class="menu-title">Add User</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="password.php">
                        <i class="mdi mdi-account-key menu-icon"></i>
                        <span class="menu-title">Password Reset</span>
                        </a>
                    </li>
                    
                  </ul>  
            </nav>';
            return $r;
    
    }
    public function SecretarySideNav()
    {
        $r = '<nav class="sidebar sidebar-offcanvas" id="sidebar">
                <ul class="nav">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                        <i class="mdi mdi-home menu-icon"></i>
                        <span class="menu-title">Dashboard</span>
                        </a>
                    </li>
                    
                    
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="collapse" href="#ui-staff" aria-expanded="false" aria-controls="ui-staff">
                        <i class="mdi mdi-account-multiple menu-icon"></i>
                        <span class="menu-title">Staff</span>
                        <i class="menu-arrow"></i>
                        </a>
                        <div class="collapse" id="ui-staff">
                          <ul class="nav flex-column sub-menu">
                                <li class="nav-item"><a class="nav-link" href="view_staff.php">View Staff</a></li>
                                <li class="nav-item"><a class="nav-link" href="share_holders_view.php">View Share Holders</a></li>
                                 <li class="nav-item"><a class="nav-link" href="savings_view.php">View Savings</a></li>
                                
                              
                               
                          </ul>
                        </div>
                      </li>
                                 
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="collapse" href="#ui-savings" aria-expanded="false" aria-controls="ui-savings">
                        <i class="mdi mdi mdi-credit-card-multiple menu-icon"></i>
                        <span class="menu-title">Endorsements</span>
                        <i class="menu-arrow"></i>
                        </a>
                        <div class="collapse" id="ui-savings">
                          <ul class="nav flex-column sub-menu">
                                <li class="nav-item"><a class="nav-link" href="member_list.php">Membership Registerations</a></li>
                                <li class="nav-item"><a class="nav-link" href="withdrawal_endorsement.php">Savings Withdraws</a></li>
                                <li class="nav-item"><a class="nav-link" href="complete_withdrawals.php">Complete Withdrawals</a></li>
                                <li class="nav-item"><a class="nav-link" href="manage_shareunit.php">Update Share Unit</a></li>
                                <li class="nav-item"><a class="nav-link" href="update_savings.php">Update Savings</a></li>
                                <li class="nav-item"><a class="nav-link" href="share_holders_view.php">View Share Holders</a></li>
                                <li class="nav-item"><a class="nav-link" href="loans.php">Loans</a></li>
                                <li class="nav-item"><a class="nav-link" href="member_list.php">Sales</a></li>
                               
                               
                          </ul>
                    </div>
                 </li>
                
                 
                    
                     <li class="nav-item">
                        <a class="nav-link" data-toggle="collapse" href="#ui-commodity" aria-expanded="false" aria-controls="ui-commodity">
                        <i class="mdi mdi mdi-credit-card-multiple menu-icon"></i>
                        <span class="menu-title">Manage Commodity</span>
                        <i class="menu-arrow"></i>
                        </a>
                        <div class="collapse" id="ui-commodity">
                          <ul class="nav flex-column sub-menu">
                                
                                <li class="nav-item"><a class="nav-link" href="add_commodity_name.php">Add Commodity Name</a></li>
                                <li class="nav-item"><a class="nav-link" href="view_commodity_name.php">View Commodity Name</a></li>
                                <li class="nav-item"><a class="nav-link" href="add_item_type.php">Add Item Type</a></li>
                                <li class="nav-item"><a class="nav-link" href="view_item_types.php">View Item Type</a></li>
                                <li class="nav-item"><a class="nav-link" href="add_new_item.php">Add New Item</a></li>
                                <li class="nav-item"><a class="nav-link" href="view_items.php">View Items</a></li>
                                <li class="nav-item"><a class="nav-link" href="schedule_commodity.php">Schedule Commodity Supply</a></li>
                                <li class="nav-item"><a class="nav-link" href="view_commodity_request.php">View Commodity Requests</a></li>
                              
                               
                          </ul>
                        </div>
                      </li>
                 
                    <li class="nav-item">
                        <a class="nav-link" href="fee.php">
                        <i class="mdi mdi-account-card-details menu-icon"></i>
                        <span class="menu-title">Schedule</span>
                        </a>
                    </li>   
                    <li class="nav-item">
                        <a class="nav-link" href="report.php">
                        <i class="mdi mdi-cash-multiple menu-icon"></i>
                        <span class="menu-title">Report</span>
                        </a>
                    </li>
                    
                    
            </nav>';
            return $r;
    
    }
    
        public function treasurerSideNav()
    {
        $r = '<nav class="sidebar sidebar-offcanvas" id="sidebar">
                <ul class="nav">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                        <i class="mdi mdi-home menu-icon"></i>
                        <span class="menu-title">Dashboard</span>
                        </a>
                    </li>
                      <li class="nav-item">
                        <a class="nav-link" href="uploadDeposit.php">
                        <i class="mdi mdi-upload menu-icon"></i>
                        <span class="menu-title">Upload Members Savings</span>
                        </a>
                    </li>
                     <li class="nav-item">
                        <a class="nav-link" href="uploadWithdrawals.php">
                        <i class="mdi mdi-bank-transfer-out menu-icon"></i>
                        <span class="menu-title">Upload Members Withdrawals</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="collapse" href="#ui-savings" aria-expanded="false" aria-controls="ui-savings">
                        <i class="mdi mdi mdi-credit-card-multiple menu-icon"></i>
                        <span class="menu-title">Authorization</span>
                        <i class="menu-arrow"></i>
                        </a>
                        <div class="collapse" id="ui-savings">
                          <ul class="nav flex-column sub-menu">
                                 <li class="nav-item"><a class="nav-link" href="member_list.php">Membership Registerations</a></li>
                                <li class="nav-item"><a class="nav-link" href="treasurer_view_approved_savings.php">Savings Update</a></li>
                                <li class="nav-item"><a class="nav-link" href="withdrawal_endorsement.php">Savings Withdraws</a></li>
                                <li class="nav-item"><a class="nav-link" href="complete_withdrawals.php">Complete Withdrawals</a></li>
                                <li class="nav-item"><a class="nav-link" href="manage_share_purchase.php">Share Purchases</a></li>
                                <li class="nav-item"><a class="nav-link" href="manage_share_transfer.php">Share Transfers</a></li>
                                <li class="nav-item"><a class="nav-link" href="manage_share_conversion.php">Share Conversions</a></li>
                                <li class="nav-item"><a class="nav-link" href="share_holders_view.php">View Share Holders</a></li>
                                <li class="nav-item"><a class="nav-link" href="loans.php">Loans</a></li>
                                <li class="nav-item"><a class="nav-link" href="member_list.php">Sales</a></li>
                                <li class="nav-item"><a class="nav-link" href="view_commodity_request.php">Commodity Requests</a></li>
                               
                          </ul>
                        </div>
                    </li>
                 
                    <li class="nav-item">
                        <a class="nav-link" href="uploadShares.php">
                        <i class="mdi mdi-account-card-details menu-icon"></i>
                        <span class="menu-title">Upload Members Shares</span>
                        </a>
                    </li>
            
                    <li class="nav-item">
                        <a class="nav-link" href="upload_members_loan.php">
                        <i class="mdi mdi-upload menu-icon"></i>
                        <span class="menu-title">Upload Mat Spp Loan</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="collapse" href="#ui-loan" aria-expanded="false" aria-controls="ui-loan">
                        <i class="mdi mdi mdi-credit-card-multiple menu-icon"></i>
                        <span class="menu-title">Loan Deductions</span>
                        <i class="menu-arrow"></i>
                        </a>
                        <div class="collapse" id="ui-loan">
                          <ul class="nav flex-column sub-menu">
                                
                                <li class="nav-item"><a class="nav-link" href="loan_deduction.php">Single Deductions</a></li>
                                <li class="nav-item"><a class="nav-link" href="batch_loan_deduction.php">Batch Deductions</a></li>
                          </ul>
                        </div>
                    </li>
                  
                      <li class="nav-item">
                        <a class="nav-link" href="upload.php">
                       <i class="mdi mdi-upload menu-icon"> </i>
                        <span class="menu-title">Upload Members</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="view_savings.php">
                       <i class="mdi mdi-bank menu-icon"></i>
                        <span class="menu-title">View Members Savings</span>
                        </a>
                    </li>
                    
                     <li class="nav-item">
                        <a class="nav-link" data-toggle="collapse" href="#ui-withdrawals" aria-expanded="false" aria-controls="ui-loan">
                        <i class="mdi mdi mdi-credit-card-multiple menu-icon"></i>
                        <span class="menu-title">Savings Withdrawals</span>
                        <i class="menu-arrow"></i>
                        </a>
                        <div class="collapse" id="ui-withdrawals">
                          <ul class="nav flex-column sub-menu">
                                <li class="nav-item"><a class="nav-link" href="uploaded_withdrawals.php">View Uploaded Withdrawals</a></li>
                                <li class="nav-item"><a class="nav-link" href="approved_withdrawals.php">View Withdrawal Approvals</a></li>
                                <li class="nav-item"><a class="nav-link" href="view_withdrawals_report.php">Withdrawal Approvals Report</a></li>
                          </ul>
                        </div>
                    </li>
                    
                    
                  
                    
                    <li class="nav-item">
                        <a class="nav-link" href="update_sp.php">
                        <i class="mdi mdi-account-card-details menu-icon"></i>
                        <span class="menu-title">Update Members Staff No</span>
                        </a>
                    </li>
                            
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                        <i class="mdi mdi-cash-multiple menu-icon"></i>
                        <span class="menu-title">Generate Report </span>
                        </a>
                    </li>          
                  </ul>  
            </nav>';
            return $r;
    
    }

    
        public function nonMemberfSideNav()
    {
        $r = '<nav class="sidebar sidebar-offcanvas" id="sidebar">
                <ul class="nav">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                        <i class="mdi mdi-home menu-icon"></i>
                        <span class="menu-title">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">
                        <i class="mdi mdi-account-check menu-icon"></i>
                        <span class="menu-title">My Profile</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="payslip.php">
                        <i class="mdi mdi mdi-credit-card-multiple menu-icon"></i>
                        <span class="menu-title">Payslips</span>
                        </a>
                    </li>
                    
                  <li class="nav-item">
                                <a class="nav-link" href="register.php">
                                <i class="mdi mdi mdi-account-circle menu-icon"></i>
                                <span class="menu-title">Register / Update Savings</span>
                                </a>
                    </li> 
            
                
                    
            </nav>';
            return $r;
    
    } 
    // public function staffSideNav()
    // {
    //     $r = '<nav class="sidebar sidebar-offcanvas" id="sidebar">
    //             <ul class="nav">
    //                 <li class="nav-item">
    //                     <a class="nav-link" href="index.php">
    //                     <i class="mdi mdi-home menu-icon"></i>
    //                     <span class="menu-title">Dashboard</span>
    //                     </a>
    //                 </li>
    //                 <li class="nav-item">
    //                     <a class="nav-link" href="/profile.php">
    //                     <i class="mdi mdi-account-check menu-icon"></i>
    //                     <span class="menu-title">My Profile</span>
    //                     </a>
    //                 </li>
    //                 <li class="nav-item">
    //                     <a class="nav-link" href="/payslip.php">
    //                     <i class="mdi mdi mdi-credit-card-multiple menu-icon"></i>
    //                     <span class="menu-title">Payslips</span>
    //                     </a>
    //                 </li>
    //                 <li class="nav-item">
    //                     <a class="nav-link" href="/R/complete_withdrawal.php">
    //                     <i class="mdi mdi mdi-account-circle menu-icon"></i>
    //                     <span class="menu-title">Complete Withrawal</span>
    //                     </a>
    //                 </li> 
                    
             
             
    //                 <li class="nav-item">
    //                     <a class="nav-link" data-toggle="collapse" href="#ui-savings" aria-expanded="false" aria-controls="ui-savings">
    //                     <i class="mdi mdi mdi-credit-card-multiple menu-icon"></i>
    //                     <span class="menu-title">Savings</span>
    //                     <i class="menu-arrow"></i>
    //                     </a>
    //                     <div class="collapse" id="ui-savings">
    //                       <ul class="nav flex-column sub-menu">
    //                         <li class="nav-item"><a class="nav-link" href="/R/savings/update_savings.php">Update Savings</a></li>
    //                         <li class="nav-item"><a class="nav-link" href="/R/savings/savings_withdrawal.php">Withdraw Savings</a></li>
    //                          <li class="nav-item"><a class="nav-link" href="/R/savings/target_savings_request.php">Target Savings request </a></li>
    //                       </ul>
    //                     </div>
    //             </li>
                
    //               <li class="nav-item">
    //                     <a class="nav-link" data-toggle="collapse" href="#ui-share" aria-expanded="false" aria-controls="ui-share">
    //                     <i class="mdi mdi mdi-credit-card-multiple menu-icon"></i>
    //                     <span class="menu-title">Share</span>
    //                     <i class="menu-arrow"></i>
    //                     </a>
    //                     <div class="collapse" id="ui-share">
    //                       <ul class="nav flex-column sub-menu">
    //                         <li class="nav-item"><a class="nav-link" href="share_purchase.php">Share Cash Purchase</a></li>
    //                         <li class="nav-item"><a class="nav-link" href="share_sale_transfer.php">Share Sale/Transfer</a></li>
    //                         <li class="nav-item"><a class="nav-link" href="share_increase.php">Share Increase</a></li>
    //                         <li class="nav-item"><a class="nav-link" href="share_view.php">View Share</a></li>
    //                         <li class="nav-item"><a class="nav-link" href="share_dividend_view.php">View Dividend</a></li>
    //                       </ul>
    //                     </div>
    //             </li>
                
    //             <li class="nav-item">
    //                     <a class="nav-link" data-toggle="collapse" href="#ui-commodity" aria-expanded="false" aria-controls="ui-commodity">
    //                     <i class="mdi mdi-food menu-icon"></i>
    //                     <span class="menu-title">Commodity</span>
    //                     <i class="menu-arrow"></i>
    //                     </a>
    //                     <div class="collapse" id="ui-commodity">
    //                       <ul class="nav flex-column sub-menu">
    //                         <li class="nav-item"><a class="nav-link" href="commodity_request.php">Commodity Request</a></li>
    //                         <li class="nav-item"><a class="nav-link" href="commodity_approval.php">View Approval</a></li>
    //                         <li class="nav-item"><a class="nav-link" href="commodity_deduction.php">View Commodity Deduction</a></li>
    //                         <li class="nav-item"><a class="nav-link" href="special_commodity.php">Special Commodity Request</a></li>
    //                       </ul>
    //                     </div>
    //             </li>
                
    //             <li class="nav-item">
    //                     <a class="nav-link" data-toggle="collapse" href="#ui-loans" aria-expanded="false" aria-controls="ui-loans">
    //                     <i class="mdi mdi mdi-credit-card-multiple menu-icon"></i>
    //                     <span class="menu-title">Loans</span>
    //                     <i class="menu-arrow"></i>
    //                     </a>
    //                     <div class="collapse" id="ui-loans">
    //                       <ul class="nav flex-column sub-menu">
    //                         <li class="nav-item"><a class="nav-link" href="business_loan.php">Business Loan Request</a></li>
    //                         <li class="nav-item"><a class="nav-link" href="soft_loan.php">Soft Loan Request</a></li>
    //                         <li class="nav-item"><a class="nav-link" href="view_loan_approval.php">View Loan Approval</a></li>
    //                         <li class="nav-item"><a class="nav-link" href="view_laon_deduction.php">View Loan Deduction</a></li>
                            
    //                       </ul>
    //                     </div>
    //             </li>
                    
    //         </nav>';
    //         return $r;
    
    // }    
    
    
    public function staffSideNav()
{
    $r = '<nav class="sidebar sidebar-offcanvas" id="sidebar">
            <ul class="nav">
                <li class="nav-item">
                    <a class="nav-link" href="/R/index.php">
                    <i class="mdi mdi-home menu-icon"></i>
                    <span class="menu-title">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/R/profile.php">
                    <i class="mdi mdi-account-check menu-icon"></i>
                    <span class="menu-title">My Profile</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/R/payslip.php">
                    <i class="mdi mdi-credit-card-multiple menu-icon"></i>
                    <span class="menu-title">Payslips</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/R/complete_withdrawal.php">
                    <i class="mdi mdi-account-circle menu-icon"></i>
                    <span class="menu-title">Complete Withdrawal</span>
                    </a>
                </li> 
                
                <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#ui-savings" aria-expanded="false" aria-controls="ui-savings">
                    <i class="mdi mdi-credit-card-multiple menu-icon"></i>
                    <span class="menu-title">Savings</span>
                    <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="ui-savings">
                      <ul class="nav flex-column sub-menu">
                        <li class="nav-item"><a class="nav-link" href="/R/savings/update_savings.php">Update Savings</a></li>
                        <li class="nav-item"><a class="nav-link" href="/R/savings/savings_withdrawal.php">Withdraw Savings</a></li>
                        <li class="nav-item"><a class="nav-link" href="/R/savings/target_savings_request.php">Target Savings Request</a></li>
                      </ul>
                    </div>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#ui-share" aria-expanded="false" aria-controls="ui-share">
                    <i class="mdi mdi-share-variant menu-icon"></i>
                    <span class="menu-title">Share</span>
                    <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="ui-share">
                      <ul class="nav flex-column sub-menu">
                        <li class="nav-item"><a class="nav-link" href="/R/share_purchase.php">Share Cash Purchase</a></li>
                        <li class="nav-item"><a class="nav-link" href="/R/share_sale_transfer.php">Share Sale/Transfer</a></li>
                        <li class="nav-item"><a class="nav-link" href="/R/share_increase.php">Share Increase</a></li>
                        <li class="nav-item"><a class="nav-link" href="/R/share_conversion.php">Share Conversion</a></li>
                        <li class="nav-item"><a class="nav-link" href="/R/share_view.php">View Share</a></li>
                        <li class="nav-item"><a class="nav-link" href="/R/share_dividend_view.php">View Dividend</a></li>
                      </ul>
                    </div>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#ui-commodity" aria-expanded="false" aria-controls="ui-commodity">
                    <i class="mdi mdi-food menu-icon"></i>
                    <span class="menu-title">Commodity</span>
                    <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="ui-commodity">
                      <ul class="nav flex-column sub-menu">
                        <li class="nav-item"><a class="nav-link" href="/R/commodity_request.php">Commodity Request</a></li>
                        <li class="nav-item"><a class="nav-link" href="/R/view_commodity_request.php">View Commodity Requests</a></li>
                        <li class="nav-item"><a class="nav-link" href="/R/commodity_deduction.php">View Commodity Deduction</a></li>
                        <li class="nav-item"><a class="nav-link" href="/R/special_commodity_request.php">Special Commodity Request</a></li>
                      </ul>
                    </div>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#ui-loans" aria-expanded="false" aria-controls="ui-loans">
                    <i class="mdi mdi-cash-multiple menu-icon"></i>
                    <span class="menu-title">Loans</span>
                    <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="ui-loans">
                      <ul class="nav flex-column sub-menu">
                        <li class="nav-item"><a class="nav-link" href="/R/business_loan.php">Business Loan Request</a></li>
                        <li class="nav-item"><a class="nav-link" href="/R/soft_loan.php">Soft Loan Request</a></li>
                        <li class="nav-item"><a class="nav-link" href="/R/view_loan_approval.php">View Loan Approval</a></li>
                        <li class="nav-item"><a class="nav-link" href="/R/view_laon_deduction.php">View Loan Deduction</a></li>
                      </ul>
                    </div>
                </li>
                
            </ul>
        </nav>';
        return $r;
}
    public function footer()
    {
        $r = '
                    <!-- partial:partials/_footer.html -->
                    <footer class="footer">
                    <div class="d-sm-flex justify-content-center justify-content-sm-between">
                        <span class="text-muted d-block text-center text-sm-left d-sm-inline-block">FUD SAD UNIT 2024 © Alright Reserved</span>
                        <span class="float-none float-sm-right d-block mt-1 mt-sm-0 text-center"> </span>
                    </div>
                    </footer>
                    <!-- partial -->
                </div>
                <!-- main-panel ends -->
                </div>
                <!-- page-body-wrapper ends -->
            </div>
            <!-- container-scroller -->

            <!-- plugins:js -->
           
            <!-- inject:js -->
            <script src="js/off-canvas.js"></script>
            <script src="js/hoverable-collapse.js"></script>
            <script src="js/template.js"></script>
            <!-- endinject -->
            <!-- Custom js for this page-->
            <script src="js/dashboard.js"></script>
            <!-- End custom js for this page-->
            <script src="js/custom.js"></script>
            </body>

            </html>

        ';
            return $r;
    
    }
    public function footer2()
    {
        $r = '
                    <!-- partial:partials/_footer.html -->
                    <footer class="footer">
                    <div class="d-sm-flex justify-content-center justify-content-sm-between">
                        <span class="text-muted d-block text-center text-sm-left d-sm-inline-block">FUD SAD UNIT 2024 © Alright Reserved</span>
                        <span class="float-none float-sm-right d-block mt-1 mt-sm-0 text-center"> </span>
                    </div>
                    </footer>
                    <!-- partial -->
                </div>
                <!-- main-panel ends -->
                </div>
                <!-- page-body-wrapper ends -->
            </div>
            <!-- container-scroller -->

            <!-- plugins:js -->
           
            <!-- inject:js -->
            <script src="../js/off-canvas.js"></script>
            <script src="../js/hoverable-collapse.js"></script>
            <script src="../js/template.js"></script>
            <!-- endinject -->
            <!-- Custom js for this page-->
            <script src="../js/dashboard.js"></script>
            <!-- End custom js for this page-->
            <script src="../js/custom.js"></script>
            </body>

            </html>

        ';
            return $r;
    
    }
    public function subFooter($url)
    {
        $r = '
                    <!-- partial:partials/_footer.html -->
                    <footer class="footer">
                    <div class="d-sm-flex justify-content-center justify-content-sm-between">
                        <span class="text-muted d-block text-center text-sm-left d-sm-inline-block">SYSTEM DEVELOPMENT & ADMINISTRATION UNIT, ICT FUD 2024 ©</span>
                        <span class="float-none float-sm-right d-block mt-1 mt-sm-0 text-center"> </span>
                    </div>
                    </footer>
                    <!-- partial -->
                </div>
                <!-- main-panel ends -->
                </div>
                <!-- page-body-wrapper ends -->
            </div>
            <!-- container-scroller -->

            <!-- plugins:js -->
           
            <!-- inject:js -->
            <script src="'.$url.'js/off-canvas.js"></script>
            <script src="'.$url.'js/hoverable-collapse.js"></script>
            <script src="'.$url.'js/template.js"></script>
            <!-- endinject -->
            <!-- Custom js for this page-->
            <script src="'.$url.'js/dashboard.js"></script>
            <!-- End custom js for this page-->
            <script src="'.$url.'js/custom.js"></script>
            </body>

            </html>

        ';
            return $r;
    
    }

    public  function loadType($selected = '') {
        $r_v = '<option value="">Not selected</option>';
        
                if ($selected == 'Individual') {
                    $r_v .= 
                    "<option value='Individual' selected>Individual</option>
                     <option value='All'>All</option>
                     

                    ";                
                }
                elseif ($selected =='All') {
                    $r_v .= "
                    <option value='Individual' >Individual</option>
                     <option value='All' selected>All</option>
                     
                    ";
               }else {
                    $r_v .= "
                    <option value='UTME' >Individual</option>
                     <option value='DE'>All</option>
                     

                    ";
                }
        
        return $r_v;
    }
    public static function loadCountries($id = 0) {
        $r_v = '<select class="form-control form-control-sm text-dark" name="country" id="country">';
        $db = new DB();

        $con = $db->getConnection();
        $q = "SELECT * FROM countries ORDER BY name";
        
        $stm = $con->prepare($q);
        $stm->execute();
        $total_rows_found = $stm->rowCount();
        if ($total_rows_found >= 1) {
            while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
                if ($row['id'] == $id) {
                    $r_v .= "<option value='" . $row['id'] . "' selected='selected'>" . $row['name'] . "</option>";
                } else {
                    $r_v .= "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                }
            }
        } else {
            $r_v .= "<option value = '-1'>Not available</option>";
        }//end if ($total_rows_found == 1)
        $con = null;
        $r_v .='</select>';
        return $r_v;
    }
    public static function loadStates($country=0,$id = 0) {
        $r_v = '<select  class="form-control form-control-sm text-dark" name="state" id="state">';
        $db = new DB();

        $con = $db->getConnection();
        $q = "SELECT * FROM state WHERE  ORDER BY name";
        
        $stm = $con->prepare($q);
        $stm->execute();
        $total_rows_found = $stm->rowCount();
        if ($total_rows_found >= 1) {
            while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
                if ($row['id'] == $id) {
                    $r_v .= "<option value='" . $row['id'] . "' selected='selected'>" . $row['name'] . "</option>";
                } else {
                    $r_v .= "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                }
            }
        } else {
            $r_v .= "<option value = '-1'>Not available</option>";
        }//end if ($total_rows_found == 1)
        $con = null;
        $r_v .='</select>';
        return $r_v;
    }
    public static function loadLga($state=0,$id = 0) {
        $r_v = '<select  class="form-control text-dark form-control-sm" name="lga" id="lga">';
        $db = new DB();

        $con = $db->getConnection();
        $q = "SELECT * FROM lga WHERE state_id='$state' ORDER BY name";
        
        $stm = $con->prepare($q);
        $stm->execute();
        $total_rows_found = $stm->rowCount();
        if ($total_rows_found >= 1) {
            while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
                if ($row['id'] == $id) {
                    $r_v .= "<option value='" . $row['id'] . "' selected='selected'>" . $row['name'] . "</option>";
                } else {
                    $r_v .= "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                }
            }
        } else {
            $r_v .= "<option value = '-1'>Not available</option>";
        }//end if ($total_rows_found == 1)
        $con = null;
        $r_v .='</select>';
        return $r_v;
    }
    public static function loadGender($selected = 0) {
        $r_v = '<select  class="form-control form-control-sm text-dark" name="state" id="state">';
        
                    $r_v .= "<option value='M' selected='selected'>Male</option>
                    <option value='F' >Female</option>
                    ";
                
        
        $r_v .='</select>';
        return $r_v;
    }
    public static function loadMonths($id = 0) {
        $r_v = '';
        $db = new DB();

        $con = $db->getConnection();
        $q = "SELECT * FROM month";
        
        $stm = $con->prepare($q);
        $stm->execute();
        $total_rows_found = $stm->rowCount();
        if ($total_rows_found >= 1) {
            while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
                if ($row['month_id'] == $id) {
                    $r_v .= "<option value='" . $row['month_id'] . "' selected='selected'>" . $row['month_name'] . "</option>";
                } else {
                    $r_v .= "<option value='" . $row['month_id'] . "'>" . $row['month_name'] . "</option>";
                }
            }
        } else {
            $r_v .= "<option value = '-1'>Not available</option>";
        }//end if ($total_rows_found == 1)
        $con = null;
        return $r_v;
    }
    public static function loadBanks($id = 0) {
        $r_v = '<select class="form-control form-control-sm text-dark" name="bank" id="bank" required>';
        $db = new DB();

        $con = $db->getConnection();
        $q = "SELECT * FROM banks ORDER BY name";
        
        $stm = $con->prepare($q);
        $stm->execute();
        $total_rows_found = $stm->rowCount();
        if ($total_rows_found >= 1) {
            while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
                if ($row['id'] == $id) {
                    $r_v .= "<option value='" . $row['id'] . "' selected='selected'>" . $row['name'] . "</option>";
                } else {
                    $r_v .= "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                }
            }
        } else {
            $r_v .= "<option value = '-1'>Not available</option>";
        }//end if ($total_rows_found == 1)
        $con = null;
        $r_v .='</select>';
        return $r_v;
    }
    
    public static function loadBanksSharePurchase($id = 0) {
        $r_v = '<select class="form-control form-control-sm text-dark" name="bank" id="bank" required>';
        $db = new DB();

        $con = $db->getConnection();
        $q = "SELECT * FROM banks WHERE name = 'FUD Microfinance Bank' ORDER BY name";
        
        $stm = $con->prepare($q);
        $stm->execute();
        $total_rows_found = $stm->rowCount();
        if ($total_rows_found >= 1) {
            while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
                if ($row['id'] == $id) {
                    $r_v .= "<option value='" . $row['id'] . "' selected='selected'>" . $row['name'] . "</option>";
                } else {
                    $r_v .= "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                }
            }
        } else {
            $r_v .= "<option value = '-1'>Not available</option>";
        }//end if ($total_rows_found == 1)
        $con = null;
        $r_v .='</select>';
        return $r_v;
    }
    
    public static function loadCommodityName($commodity_name_id = 0) 
    {
        $r_v = '<select class="form-control" name="commodity_name_id" id="commodity_name_id" required>';
        $r_v .= "<option value=''>Select Commodity Name</option>"; // Default option
    
        $db = new DB();
        $con = $db->getConnection();
    
        $q = "SELECT * FROM fudscoops_commodity_name ORDER BY created_at DESC";
        $stm = $con->prepare($q);
        $stm->execute();
    
        if ($stm->rowCount() >= 1) {
            while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
                if ($row['commodity_name_id'] == $commodity_name_id) {
                    $r_v .= "<option value='" . $row['commodity_name_id'] . "' selected='selected'>" . $row['commodity_name'] . "</option>";
                } else {
                    $r_v .= "<option value='" . $row['commodity_name_id'] . "'>" . $row['commodity_name'] . "</option>";
                }
            }
        } else {
            $r_v .= "<option value='-1'>Not available</option>";
        }
    
        $con = null;
        $r_v .= '</select>';
        return $r_v;
    }
    
    
    public static function loadCommodityItem($commodity_item_id = 0) 
    {
        $r_v = '<select class="form-control" name="commodity_item_id[0]" id="commodity_item_id" onchange="setPriceAndLimit(this, 0)" required>';
        $r_v .= "<option value=''>-- Select Item --</option>"; // Default option
    
        $db = new DB();
        $con = $db->getConnection();
    
        $q = "SELECT * FROM fudscoops_commodity_Items ORDER BY commodity_item";
        $stm = $con->prepare($q);
        $stm->execute();
    
        if ($stm->rowCount() >= 1) {
            while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
                if ($row['commodity_item_id'] == $commodity_item_id) {
                        $r_v .= "<option value='" . $row['commodity_item_id'] . "' data-price='" . $row['unit_price'] . "' data-max='" . $row['quantity'] . "' selected='selected'>" . $row['commodity_item'] . "</option>";
                } else {
                        $r_v .= "<option value='" . $row['commodity_item_id'] . "' data-price='" . $row['unit_price'] . "' data-max='" . $row['quantity'] . "'>" . $row['commodity_item'] . "</option>";
                }

            }
        } else {
            $r_v .= "<option value='-1'>No available Item</option>";
        }
    
        $con = null;
        $r_v .= '</select>';
        return $r_v;
    }



   
}


