<?php
    // Loans (layout: config/loans/officer_loans.php)
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../auth/') || !User::isBur('../auth/logout.php')) {
        exit();
    }
    $role = 'treasurer';
    $sideNav = 'treasurerSideNav';
    require '../config/loans/officer_loans.php';
