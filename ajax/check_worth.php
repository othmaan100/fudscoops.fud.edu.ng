<?php

require_once '../config/classes/DB.php';
require_once '../config/classes/MemberG2.php';
require_once '../config/classes/User.php';
$db = new DB();
$member = new MemberG2();
$user = new User();
         //$spNo = $db->cleanData($_POST['buyer']);
         $worth = $db->cleanData($_POST['worth']);
        
        echo $member->checkWorth($worth);
?>

