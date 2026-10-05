<?php

require_once '../config/classes/DB.php';
require_once '../config/classes/SavingsG1.php';
        $db = new DB();
        $endorse = new SavingsG1();
        $spNo = $db->cleanData($_POST['staff_no']);
    
         echo $endorse->secretaryLoanEndorsement($spNo);
                
?>
