<?php

require_once '../config/classes/DB.php';
require_once '../config/classes/SavingsG1.php';
require_once '../config/classes/MemberG3.php';
        $db = new DB();
        $endorse = new MemberG3();
        $loan_id = $db->cleanData($_POST['loan_id']);
    
         echo $endorse->updateGuaranto($loan_id);
                
?>
