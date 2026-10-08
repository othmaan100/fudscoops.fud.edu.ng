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
          <link rel="stylesheet" href="css/sidebar.css">
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
          <link rel="stylesheet" href="'.$url.'css/sidebar.css">
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

    // ===================== Sidebar menus =====================
    // Every role menu is described as data and rendered by renderSidebar(), so all menus share the
    // same markup and styling (css/sidebar.css). Menu entry formats:
    //   link:  ['Label', 'mdi-icon', 'page.php']                      (optional 4th element: badge text)
    //   group: ['Label', 'mdi-icon', 'collapse-id', [['Label', 'page.php'], ...]]
    // The current page is highlighted by js/template.js, which also opens its group.

    private function sidebarLink($item)
    {
        $label = htmlspecialchars($item[0]);
        $icon = $item[1];
        $href = $item[2];
        $badge = isset($item[3]) ? '<span class="badge fud-badge">' . htmlspecialchars($item[3]) . '</span>' : '';
        $disabled = $href === '#' ? ' fud-disabled' : '';
        return '
                    <li class="nav-item">
                        <a class="nav-link' . $disabled . '" href="' . $href . '" title="' . $label . '">
                        <i class="mdi ' . $icon . ' menu-icon"></i>
                        <span class="menu-title">' . $label . '</span>' . $badge . '
                        </a>
                    </li>';
    }

    private function sidebarGroup($item)
    {
        list($label, $icon, $id, $children) = $item;
        $label = htmlspecialchars($label);
        $links = '';
        foreach ($children as $child) {
            $links .= '
                                <li class="nav-item"><a class="nav-link" href="' . $child[1] . '">' . htmlspecialchars($child[0]) . '</a></li>';
        }
        return '
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="collapse" href="#' . $id . '" aria-expanded="false" aria-controls="' . $id . '" title="' . $label . '">
                        <i class="mdi ' . $icon . ' menu-icon"></i>
                        <span class="menu-title">' . $label . '</span>
                        <i class="menu-arrow"></i>
                        </a>
                        <div class="collapse" id="' . $id . '">
                            <ul class="nav flex-column sub-menu">' . $links . '
                            </ul>
                        </div>
                    </li>';
    }

    // $sections: ['Section heading' => [menu entries...], ...]
    private function renderSidebar($roleLabel, $roleIcon, $sections)
    {
        $username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : '';
        $r = '<nav class="sidebar sidebar-offcanvas fud-sidebar" id="sidebar">
                <div class="fud-sidebar-profile">
                    <div class="fud-avatar"><i class="mdi ' . $roleIcon . '"></i></div>
                    <div class="fud-profile-text">
                        <span class="fud-profile-name">' . $username . '</span>
                        <span class="fud-role-chip">' . htmlspecialchars($roleLabel) . '</span>
                    </div>
                </div>
                <ul class="nav">';
        foreach ($sections as $heading => $items) {
            $r .= '
                    <li class="nav-item nav-category"><span>' . htmlspecialchars($heading) . '</span></li>';
            foreach ($items as $item) {
                $r .= is_array($item[3] ?? null) ? $this->sidebarGroup($item) : $this->sidebarLink($item);
            }
        }
        $r .= '
                </ul>
            </nav>';
        return $r;
    }

    // Chairman
    public function subSideNav()
    {
        return $this->renderSidebar('Chairman', 'mdi-account-tie', [
            'Main' => [
                ['Dashboard', 'mdi-view-dashboard-outline', 'index.php'],
            ],
            'Approvals' => [
                ['Members & Savings', 'mdi-account-group', 'ui-savings', [
                    ['Membership Registrations', 'member_list.php'],
                    ['Savings Update', 'update_savings.php'],
                    ['Savings Withdrawals', 'withdrawal_endorsement.php'],
                    ['Complete Withdrawals', 'complete_withdrawals.php'],
                ]],
                ['Shares', 'mdi-chart-pie', 'ui-shares', [
                    ['Share Purchases', 'manage_share_purchase.php'],
                    ['Share Transfers', 'manage_share_transfer.php'],
                    ['Share Conversions', 'manage_share_conversion.php'],
                    ['View Share Holders', 'share_holders_view.php'],
                ]],
                ['Loans', 'mdi-cash-multiple', 'loans.php'],
                ['Commodity Disbursement', 'mdi-cart-outline', 'approved_commodity_Request.php'],
            ],
            'Operations' => [
                ['Loan Deduction', 'mdi-file-document-box', 'loan_deduction.php'],
                ['Generate Report', 'mdi-file-chart', 'reports.php'],
            ],
            'Administration' => [
                ['Add User', 'mdi-account-plus', 'add_user.php'],
                ['Password Reset', 'mdi-lock-reset', 'password.php'],
            ],
        ]);
    }

    // Secretary-General
    public function SecretarySideNav()
    {
        return $this->renderSidebar('Secretary-General', 'mdi-clipboard-text', [
            'Main' => [
                ['Dashboard', 'mdi-view-dashboard-outline', 'index.php'],
                ['Staff', 'mdi-account-multiple', 'ui-staff', [
                    ['View Staff', 'view_staff.php'],
                    ['View Share Holders', 'share_holders_view.php'],
                    ['View Savings', 'savings_view.php'],
                ]],
            ],
            'Endorsements' => [
                ['Members & Savings', 'mdi-account-group', 'ui-savings', [
                    ['Membership Registrations', 'member_list.php'],
                    ['Savings Withdrawals', 'withdrawal_endorsement.php'],
                    ['Complete Withdrawals', 'complete_withdrawals.php'],
                    ['Update Savings', 'update_savings.php'],
                ]],
                ['Update Share Unit', 'mdi-chart-pie', 'manage_shareunit.php'],
                ['Loans', 'mdi-cash-multiple', 'loans.php'],
            ],
            'Commodity' => [
                ['Manage Commodity', 'mdi-cart-outline', 'ui-commodity', [
                    ['Add Commodity Name', 'add_commodity_name.php'],
                    ['View Commodity Names', 'view_commodity_name.php'],
                    ['Add Item Type', 'add_item_type.php'],
                    ['View Item Types', 'view_item_types.php'],
                    ['Add New Item', 'add_new_item.php'],
                    ['View Items', 'view_items.php'],
                    ['Schedule Commodity Supply', 'schedule_commodity.php'],
                    ['View Commodity Requests', 'view_commodity_request.php'],
                ]],
            ],
            'Reports' => [
                ['Schedule', 'mdi-calendar-clock', 'fee.php'],
                ['Generate Report', 'mdi-file-chart', 'reports.php'],
            ],
        ]);
    }

    // Treasurer
    public function treasurerSideNav()
    {
        return $this->renderSidebar('Treasurer', 'mdi-bank', [
            'Main' => [
                ['Dashboard', 'mdi-view-dashboard-outline', 'index.php'],
            ],
            'Authorization' => [
                ['Members & Savings', 'mdi-account-group', 'ui-savings', [
                    ['Membership Registrations', 'member_list.php'],
                    ['Savings Update', 'treasurer_view_approved_savings.php'],
                    ['Savings Withdrawals', 'withdrawal_endorsement.php'],
                    ['Complete Withdrawals', 'complete_withdrawals.php'],
                ]],
                ['Shares', 'mdi-chart-pie', 'ui-shares', [
                    ['Share Purchases', 'manage_share_purchase.php'],
                    ['Share Transfers', 'manage_share_transfer.php'],
                    ['Share Conversions', 'manage_share_conversion.php'],
                    ['View Share Holders', 'share_holders_view.php'],
                ]],
                ['Loans', 'mdi-cash-multiple', 'loans.php'],
                ['Commodity Requests', 'mdi-cart-outline', 'view_commodity_request.php'],
            ],
            'Uploads' => [
                ['Uploads', 'mdi-cloud-upload', 'ui-uploads', [
                    ['Members', 'upload.php'],
                    ['Members Savings', 'uploadDeposit.php'],
                    ['Members Withdrawals', 'uploadWithdrawals.php'],
                    ['Members Shares', 'uploadShares.php'],
                    ['Mat/Spp Loan', 'upload_members_loan.php'],
                ]],
                ['Loan Deductions', 'mdi-file-document-box', 'ui-loan', [
                    ['Single Deductions', 'loan_deduction.php'],
                    ['Batch Deductions', 'batch_loan_deduction.php'],
                ]],
            ],
            'Records' => [
                ['Members Savings', 'mdi-wallet', 'view_savings.php'],
                ['Savings Withdrawals', 'mdi-bank-transfer-out', 'ui-withdrawals', [
                    ['View Uploaded Withdrawals', 'uploaded_withdrawals.php'],
                    ['View Withdrawal Approvals', 'approved_withdrawals.php'],
                    ['Withdrawal Approvals Report', 'view_withdrawals_report.php'],
                ]],
                ['Generate Report', 'mdi-file-chart', 'reports.php'],
            ],
            'Settings' => [
                ['Savings Settings', 'mdi-tune', 'savings_settings.php'],
                ['Update Members Staff No', 'mdi-account-card-details', 'update_sp.php'],
            ],
        ]);
    }

    // Logged-in staff who are not yet members (UR/)
    public function nonMemberfSideNav()
    {
        return $this->renderSidebar('Applicant', 'mdi-account-clock', [
            'Main' => [
                ['Dashboard', 'mdi-view-dashboard-outline', 'index.php'],
                ['My Profile', 'mdi-account-check', 'profile.php'],
                ['Payslips', 'mdi-receipt', 'payslip.php'],
            ],
            'Membership' => [
                ['Register / Update Savings', 'mdi-account-plus', 'register.php'],
            ],
        ]);
    }

    // Members (R/)
    public function staffSideNav()
    {
        return $this->renderSidebar('Member', 'mdi-account-circle', [
            'Main' => [
                ['Dashboard', 'mdi-view-dashboard-outline', '/R/index.php'],
                ['My Profile', 'mdi-account-check', '/R/profile.php'],
                ['Payslips', 'mdi-receipt', '/R/payslip.php'],
            ],
            'My Cooperative' => [
                ['Savings', 'mdi-wallet', 'ui-savings', [
                    ['Update Savings', '/R/savings/update_savings.php'],
                    ['Withdraw Savings', '/R/savings/savings_withdrawal.php'],
                    ['Target Savings Request', '/R/savings/target_savings_request.php'],
                    ['Complete Withdrawal', '/R/complete_withdrawal.php'],
                ]],
                ['Shares', 'mdi-chart-pie', 'ui-share', [
                    ['Share Cash Purchase', '/R/share_purchase.php'],
                    ['Share Sale/Transfer', '/R/share_sale_transfer.php'],
                    ['Share Increase', '/R/share_increase.php'],
                    ['Share Conversion', '/R/share_conversion.php'],
                    ['View Share', '/R/share_view.php'],
                    ['View Dividend', '/R/share_dividend_view.php'],
                ]],
                ['Commodity', 'mdi-cart-outline', 'ui-commodity', [
                    ['Commodity Request', '/R/commodity_request.php'],
                    ['View Commodity Requests', '/R/view_commodity_request.php'],
                    ['View Commodity Deduction', '/R/commodity_deduction.php'],
                    ['Special Commodity Request', '/R/special_commodity_request.php'],
                ]],
                ['Loans', 'mdi-cash-multiple', 'ui-loans', [
                    ['Business Loan Request', '/R/business_loan.php'],
                    ['Soft Loan Request', '/R/soft_loan.php'],
                    ['View Loan Approval', '/R/view_loan_approval.php'],
                    ['View Loan Deduction', '/R/view_laon_deduction.php'],
                ]],
            ],
        ]);
    }

    // Some pages call ICTSideNav(), which did not exist (fatal error). Show the logged-in user's menu.
    public function ICTSideNav()
    {
        $level = isset($_SESSION['access_level']) ? (int) $_SESSION['access_level'] : -1;
        switch ($level) {
            case 4: return $this->subSideNav();
            case 3: return $this->treasurerSideNav();
            case 2: return $this->SecretarySideNav();
            case 1: return $this->staffSideNav();
            default: return $this->nonMemberfSideNav();
        }
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


