<?php

require_once '../config/classes/DB.php';
require_once '../config/classes/MemberG2.php';
require_once '../config/classes/User.php';
$db = new DB();
$member = new MemberG2();
$user = new User();
        
            
             //$employeeId= $db->cleanData($_POST['employee_id']);
             $transferId = $db->cleanData($_POST['transfer_id']);
             
             echo $member->acceptShare($transferId);

?>