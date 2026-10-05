<?php

require_once '../config/classes/DB.php';
require_once '../config/classes/MemberG2.php';
require_once '../config/classes/User.php';
$db = new DB();
$member = new MemberG2();
$user = new User();
        
            
             //$employeeId= $db->cleanData($_POST['employee_id']);
             $spNo = $db->cleanData($_POST['staff_no']);
             $worth= $db->cleanData($_POST['amount_paid']);
             $share_amount_word= $db->cleanData($_POST['amount_word']);
             $employeeid =$user->getEmployeeId($spNo);
             //$employeeid = $user->getEmployeeId($spNo);
        
             echo $member->conversionPendingShare($employeeid,$worth,$share_amount_word);

?>