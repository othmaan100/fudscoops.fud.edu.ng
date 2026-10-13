<?php
// Chairman unlocks a decided loan so the Treasurer can change the decision
require_once '../config/classes/DB.php';
require_once '../config/classes/SavingsG1.php';
require_once '../config/classes/MemberG3.php';
        $endorse = new MemberG3();
        if (!isset($_SESSION['access_level']) || (int) $_SESSION['access_level'] !== 4) {
            echo 'Only the Chairman can unlock a loan decision.';
            exit();
        }
 $loan_id = isset($_POST['loan_id']) ? $_POST['loan_id'] : '';
 $member_id = isset($_POST['member_id']) ? $_POST['member_id'] : '';
  echo $endorse->chairmanUnlockLoandDecision($loan_id,$member_id);
?>
