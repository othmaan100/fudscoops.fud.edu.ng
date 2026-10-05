<?php

require_once '../config/classes/DB.php';
require_once '../config/classes/MemberG1.php';
require_once '../config/classes/User.php';
        $db = new DB();
        $member = new MemberG1();
        $user = new User();
        
             //$employeeid =$db->cleanData($_POST['employeeid']);
             $member_id= $db->cleanData($_POST['member_id']);
             $bank_id= $db->cleanData($_POST['bank_id']);
             $acount_number= $db->cleanData($_POST['acct_no']);
             $withrawals_amount= $db->cleanData($_POST['withdrawal_amount']);
             $acct_name= $db->cleanData($_POST['acct_name']);
             //$employeeid = $user->getEmployeeId($spNo);
        
             echo $member->saveWithdrawal($member_id,$bank_id,$acct_name,$acount_number,$withrawals_amount);
             
             

             
                
?>
  