<?php

require_once '../config/classes/DB.php';
require_once '../config/classes/SavingsG1.php';
require_once '../config/classes/MemberG3.php';
        $endorse = new MemberG3();
 $loan_id = $_POST['loan_id'];
 $member_id = $_POST['member_id'];
  echo $endorse->chairmanUnlockLoandDecision($loan_id,$member_id);

?>
