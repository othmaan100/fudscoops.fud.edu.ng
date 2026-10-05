<?php

require_once '../config/classes/DB.php';
require_once '../config/classes/MemberG1.php';
require_once '../config/classes/User.php';
        $db = new DB();
        $member = new MemberG1();
        $user = new User();
        
             //$employeeid =$db->cleanData($_POST['employeeid']);
             $spNo= $db->cleanData($_POST['staff_no']);
             $monthly_savings= $db->cleanData($_POST['monthly_savings']);
             $next_of_kin_name= $db->cleanData($_POST['next_of_kin_name']);
             $next_of_kin_gsm= $db->cleanData($_POST['next_of_kin_gsm']);
             $next_of_kin_address= $db->cleanData($_POST['next_of_kin_address']);
             //$employeeid = $user->getEmployeeId($spNo);
        
             echo $member->registerMember($spNo,$monthly_savings,$next_of_kin_name,$next_of_kin_gsm,$next_of_kin_address);

?>
        