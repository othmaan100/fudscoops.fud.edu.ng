<?php
    // Loan Deductions (Chairman: read-only history; layout: config/loans/loan_deduction_desk.php)
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../auth/') || !User::isChairman('../auth/logout.php')) {
        exit();
    }
    $role = 'chairman';
    $sideNav = 'subSideNav';
    require '../config/loans/loan_deduction_desk.php';
