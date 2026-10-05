<?php

require_once '../config/classes/DB.php';
require_once '../config/classes/MemberG2.php';
require_once '../config/classes/User.php';
$db = new DB();
$member = new MemberG2();
$user = new User();
        
            
             //$employeeId= $db->cleanData($_POST['employee_id']);
             $spNo = $db->cleanData($_POST['staff_no']);
             $share_amount= $db->cleanData($_POST['share_amount']);
             $bank_id= $db->cleanData($_POST['bank']);
             $tellerno= $db->cleanData($_POST['tellerno']);
             $amount_paid= $db->cleanData($_POST['amount_paid']);
             $share_amount_word= $db->cleanData($_POST['share_amount_word']);
             $employeeid =$user->getEmployeeId($spNo);
             //$employeeid = $user->getEmployeeId($spNo);
        
             echo $member->PurchaseShare($employeeid,$share_amount,$bank_id,$tellerno,$amount_paid,$share_amount_word);

?>
        