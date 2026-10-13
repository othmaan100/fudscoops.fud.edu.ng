<?php
// Savings-to-shares conversion decisions: Chairman approves -> Treasurer approves (savings converted)
require_once '../config/classes/DB.php';
require_once '../config/classes/SavingsG1.php';
require_once '../config/classes/MemberG2.php';
        $db = new DB();
        $endorse = new MemberG2();
        $level = isset($_SESSION['access_level']) ? (int) $_SESSION['access_level'] : -1;
        $field = function ($name) use ($db) { return $db->cleanData(isset($_POST[$name]) ? $_POST[$name] : ''); };

        if(isset($_POST['type']) && !empty($_POST['type'])){
                 if($field('decision') =='chairman' && $level === 4){
                     echo $endorse->chairmanConversionApproval($field('conversion_id'),$field('action'),$field('comment'));
                 }else{
                     echo 'You are not allowed to take this decision. Please log in again.';
                 }
        }else if($level === 3){
            // Treasurer
            echo $endorse->processConversionShare($field('conversion_id'), $field('member_id'), $field('action'));
        }else{
            echo 'You are not allowed to take this decision. Please log in again.';
        }
?>
