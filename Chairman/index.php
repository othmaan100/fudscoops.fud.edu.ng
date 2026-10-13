<?php
// Chairman dashboard: the page is shared with the other officers (config/dashboards/officer_dashboard.php)
require_once('../config/classes/User.php');
$user = new User();
if (!User::is_authenticated('../auth/') || !User::isChairman('../auth/logout.php')) {
    exit();
}
$dashRole = 'chairman';
require_once('../config/dashboards/officer_dashboard.php');
