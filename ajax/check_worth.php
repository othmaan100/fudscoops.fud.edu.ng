<?php

require_once '../config/classes/DB.php';
require_once '../config/classes/MemberG2.php';
require_once '../config/classes/User.php';
$db = new DB();
$member = new MemberG2();
if (!isset($_SESSION['access_level'], $_SESSION['username']) || $_SESSION['access_level'] != 1) { echo -1; exit(); }
$user = new User();
         //$spNo = $db->cleanData($_POST['buyer']);
         $worth = $db->cleanData($_POST['worth']);
        
        echo $member->checkWorth($worth);
?>

