<?php
    // Soft Loan application (form: config/loans/loan_application_form.php; submitted to ajax/business_loan.php)
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    $view = new View();
    $user = new User();
    $user->is_authenticated('../auth/');
    $user->isStaff('../auth/logout.php');

    $loanTypeName = 'Soft Loan';
    $loanTypeKey = 'soft';
    require '../config/loans/loan_application_form.php';
