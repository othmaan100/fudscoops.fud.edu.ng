<?php

require_once '../config/classes/DB.php';
require_once '../config/classes/SavingsG1.php';
        $db = new DB();
        $savings = new SavingsG1();
        $spNo = $db->cleanData($_POST['staff_no']);
        $amount = $db->cleanData($_POST['amount']);
        
    
         echo $savings->secretaryApprovedCompleteWithdrawal($spNo, $amount);
         
                
?>