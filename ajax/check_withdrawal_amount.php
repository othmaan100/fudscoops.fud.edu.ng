<?php

require_once '../config/classes/DB.php';
require_once '../config/classes/MemberG1.php';
require_once '../config/classes/User.php';
$db = new DB();
$member = new MemberG1();
$user = new User();
         //$spNo = $db->cleanData($_POST['buyer']);
         $withdrawal_amount = $db->cleanData($_POST['withdrawal_amount']);
        
        echo $member->checkWithdrawalAmount($withdrawal_amount);
?>

