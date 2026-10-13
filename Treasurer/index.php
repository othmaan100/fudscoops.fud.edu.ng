<?php
// Treasurer dashboard: the page is shared with the other officers (config/dashboards/officer_dashboard.php)
require_once('../config/classes/User.php');
$user = new User();
if (!User::is_authenticated('../auth/') || !User::isBur('../auth/logout.php')) {
    exit();
}
$dashRole = 'treasurer';
require_once('../config/dashboards/officer_dashboard.php');
