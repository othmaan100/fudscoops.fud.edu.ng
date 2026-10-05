<?php

require_once '../config/classes/DB.php';
require_once '../config/classes/SavingsG1.php';
        $db = new DB();
        $savings = new SavingsG1();

         $spNo = $db->cleanData($_POST['staff_no']);
         $savings_amount = $db->cleanData($_POST['savings_amount']);
        
    
         echo $savings->updateSavingsAmount($spNo,$savings_amount);
         
             

             
                
?>
  