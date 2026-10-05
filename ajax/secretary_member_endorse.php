<?php

require_once '../config/classes/DB.php';
require_once '../config/classes/SavingsG1.php';
        $db = new DB();
        $member = new MemberG1();
        
    
        
         $sp_no = $db->cleanData($_POST['sp_no']);
         $member_id = $db->cleanData($_POST['member_id']);
         $action = $db->cleanData($_POST['action']);
          $comment = $db->cleanData($_POST['comment']);
         

       
      
         echo $member->secretaryApproveRegisteration($sp_no, $action, $comment);
         
 ?>            


