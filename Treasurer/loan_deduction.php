<?php
    // Loan Deductions (Treasurer: records them; layout: config/loans/loan_deduction_desk.php)
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../auth/') || !User::isBur('../auth/logout.php')) {
        exit();
    }
    $role = 'treasurer';
    $sideNav = 'treasurerSideNav';
    require '../config/loans/loan_deduction_desk.php';
