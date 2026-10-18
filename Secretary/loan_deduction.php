<?php
    // Loan Deductions (Secretary: read-only history; layout: config/loans/loan_deduction_desk.php)
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../auth/') || !User::isSecretary('../auth/logout.php')) {
        exit();
    }
    $role = 'secretary';
    $sideNav = 'SecretarySideNav';
    require '../config/loans/loan_deduction_desk.php';
