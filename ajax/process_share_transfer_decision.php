<?php
// Share transfer decisions: Secretary endorses -> Chairman approves -> Treasurer approves (shares move)
require_once '../config/classes/DB.php';
require_once '../config/classes/SavingsG1.php';
require_once '../config/classes/MemberG2.php';
        $db = new DB();
        $endorse = new MemberG2();
        $level = isset($_SESSION['access_level']) ? (int) $_SESSION['access_level'] : -1;
        $field = function ($name) use ($db) { return $db->cleanData(isset($_POST[$name]) ? $_POST[$name] : ''); };

        if(isset($_POST['type']) && !empty($_POST['type'])){
             $action = $field('action');
             $shares_id = $field('trans_sharesid');
             $transfer_id = $field('transfer_id');
             $comment = $field('comment');
             $decision = $field('decision');
                 if($decision =='chairman' && $level === 4){
                     echo $endorse->chairmanTransferApproval($shares_id,$transfer_id,$action,$comment);
                 }else if($decision=='secretary' && $level === 2){
                     echo $endorse->secretaryTransferEndorsement($shares_id,$transfer_id,$action,$comment);
                 }else{
                     echo 'You are not allowed to take this decision. Please log in again.';
                 }
        }else if($level === 3){
            // Treasurer
            echo $endorse->processTransferShare($field('trans_sharesid'),$field('transfer_id'),$field('member_id'),$field('action'));
        }else{
            echo 'You are not allowed to take this decision. Please log in again.';
        }
?>
