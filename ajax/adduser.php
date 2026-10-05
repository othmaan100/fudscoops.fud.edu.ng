<?php

require_once '../config/classes/DB.php';
require_once '../config/classes/SavingsG1.php';
        $db = new DB();
        $savings = new SavingsG1();
        
         $role = $db->cleanData($_POST['role']);
         $user_name = $db->cleanData($_POST['user_name']);
        
    
         echo $savings->adduser($role, $user_name);
         
             

             
                
?>
  