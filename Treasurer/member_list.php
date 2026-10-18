<?php
    // Members (Treasurer) (layout: config/members/member_list.php)
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../auth/') || !User::isBur('../auth/logout.php')) {
        exit();
    }
    $role = 'treasurer';
    $sideNav = 'treasurerSideNav';
    require '../config/members/member_list.php';
