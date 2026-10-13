<?php
// Loan decisions: Chairman approves/declines -> Treasurer fixes the amount and approves/declines.
// The Secretary only views loans (MemberG3::toBeProceed refuses).
require_once '../config/classes/DB.php';
require_once '../config/classes/SavingsG1.php';
require_once '../config/classes/MemberG3.php';
        $db = new DB();
        $endorse = new MemberG3();
        $level = isset($_SESSION['access_level']) ? (int) $_SESSION['access_level'] : -1;
        $field = function ($name) use ($db) { return $db->cleanData(isset($_POST[$name]) ? $_POST[$name] : ''); };

        if(isset($_POST['type']) && !empty($_POST['type'])){
             if($field('decision') == 'chairman' && $level === 4){
                 echo $endorse->chairmanDecision($field('loan_id'), $field('member_id'), $field('action'), $field('comment'));
             }else{
                 echo 'Only the Chairman can take this decision. The Secretary can view loans but not decide on them.';
             }
        }else if($level === 3){
            // Treasurer decision
            $tenor = (int) $field('laon_tenor');
            $amount = (float) preg_replace('/[^0-9.]/', '', $field('amount_recommended'));

            // Monthly repayment: unchanged formula (6% annual rate), stored as a plain number (no thousands separator)
            $repayment = 0;
            if ($tenor > 0 && $amount > 0) {
                $monthly_rate = 0.06 / 12;
                $repayment = round($amount * $monthly_rate * pow(1 + $monthly_rate, $tenor) / (pow(1 + $monthly_rate, $tenor) - 1), 2);
            }
            echo $endorse->processLoan($field('loan_id'), $field('member_id'), $repayment, $field('action'), $amount);
        }else{
            echo 'You are not allowed to take this decision. Please log in again.';
        }
?>
