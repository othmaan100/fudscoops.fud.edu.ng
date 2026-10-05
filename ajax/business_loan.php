<?php

require_once '../config/classes/DB.php';
require_once '../config/classes/MemberG3.php';
require_once '../config/classes/User.php';
$db = new DB();
$member = new MemberG3();
$user = new User();
        
            
    $spNo = $db->cleanData($_POST['staff_no']);
    $amountApplied = $db->cleanData($_POST['amount-applied']);
    // 10% profit to be added
    //$percentage = 0.1*$amountApplied;
    //$amountApplied =$amountApplied+$percentage;
    $loanRepayment = $db->cleanData($_POST['loan_repayment']); 
    // emplayee id
    $guarantor_id = $user->getEmployeeId($db->cleanData($_POST['guarantor_sp_no'])); 
    
    $loanRepaymentSpecify = $db->cleanData($_POST['loan_repayment_specify']);
    $memberId = $user->getMemberId($user->getEmployeeId($spNo));
    $loanTypeId = 1; 
    $result = $member->saveLoanApplication($amountApplied, $loanRepayment, $loanRepaymentSpecify, $memberId, $loanTypeId,$guarantor_id);
    echo $result;
    
?>
        