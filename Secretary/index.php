<?php
// Secretary dashboard: the page is shared with the other officers (config/dashboards/officer_dashboard.php)
require_once('../config/classes/User.php');
$user = new User();
if (!User::is_authenticated('../auth/') || !User::isSecretary('../auth/logout.php')) {
    exit();
}
$dashRole = 'secretary';
require_once('../config/dashboards/officer_dashboard.php');
