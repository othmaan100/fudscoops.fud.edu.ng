<?php

require_once '../config/classes/DB.php';
require_once '../config/classes/MemberG2.php';
require_once '../config/classes/User.php';
$db = new DB();
$member = new MemberG2();
$user = new User();

    $username = $db->cleanData($_POST['username']);
    
    echo $user->resetPassword($username);
?>

