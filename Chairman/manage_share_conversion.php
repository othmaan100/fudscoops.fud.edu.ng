<?php
    // Share conversions awaiting the Chairman (layout: config/chairman/share_list.php)
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../auth/') || !User::isChairman('../auth/logout.php')) {
        exit();
    }
    $kind = 'conversion';
    require '../config/chairman/share_list.php';
