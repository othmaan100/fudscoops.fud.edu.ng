<?php

require_once '../config/classes/DB.php';
require_once '../config/classes/SavingsG1.php';
require_once '../config/classes/MemberG3.php';
        $db = new DB();
        $endorse = new MemberG3();
        if(isset($_POST['type']) && !empty($_POST['type'])){
             $action = $db->cleanData($_POST['action']);
             $loan_id = $db->cleanData($_POST['loan_id']);
             $comment = $db->cleanData($_POST['comment']);
             $decision = $db->cleanData($_POST['decision']);
             $member_id = $db->cleanData($_POST['member_id']);
                    //chairman decision
                 if($decision =='chairman'){
                     echo $endorse->chairmanDecision($loan_id,$member_id,$action,$comment);
                 }else if($decison=='secretary'){
                     // this is for secreatry
                 echo $endorse->toBeProceed($loan_id,$member_id,$action,$comment);
                 }
        }else{
            //treasurer decision
            $loan_id = $db->cleanData($_POST['loan_id']);
            $member_id = $db->cleanData($_POST['member_id']);
            $tenor = $db->cleanData($_POST['laon_tenor']);
            $amount = $db->cleanData($_POST['amount_recommended']);
            $action = $db->cleanData($_POST['action']);
            
            //calculating repayment
            // to determine the monthly rate
            $monthly_rate = 0.06 / 12; // Assuming an annual interest rate of 6%
             $repayment = '';
             $repayment = $amount * $monthly_rate * pow(1 + $monthly_rate, $tenor) / (pow(1 + $monthly_rate, $tenor) - 1);
             // Format the repayment value to two decimal places
                $repayment = number_format($repayment, 2);
             echo $endorse->processLoan($loan_id,$member_id,$repayment,$action,$amount);
        
        }
                
?>
