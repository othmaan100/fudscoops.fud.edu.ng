<?php
    // One member's record (Chairman) (layout: config/members/member_profile.php)
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../auth/') || !User::isChairman('../auth/logout.php')) {
        exit();
    }
    $role = 'chairman';
    $sideNav = 'subSideNav';
    require '../config/members/member_profile.php';
