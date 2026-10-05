<?php

require_once '../config/classes/DB.php';
require_once '../config/classes/SavingsG1.php';
        $db = new DB();
        $savings = new SavingsG1();
        
         $spNo = $db->cleanData($_POST['staff_no']);
         $bank_id = $db->cleanData($_POST['bank_id']);
         $acct_no = $db->cleanData($_POST['acct_no']);
         $acct_name = $db->cleanData($_POST['acct_name']);
        
    
         echo $savings->completeWithdrawal($spNo, $bank_id, $acct_no,$acct_name);
         
             

             
                
?>
  