<?php

require_once '../config/classes/DB.php';
require_once '../config/classes/SavingsG1.php';
require_once '../config/classes/User.php';
require_once '../config/classes/MemberG3.php';
        $db = new DB();
        $endorse = new SavingsG1();
        $user = new User();
        $members = new MemberG3();
        if (!isset($_SESSION['access_level']) || (int) $_SESSION['access_level'] !== 3) {
            echo json_encode(['status' => 'error', 'message' => 'Only the Treasurer can record loan repayments.']);
            exit;
        }

       $loan_id = $db->cleanData($_POST['loan_id']);
        $member_id = $db->cleanData($_POST['member_id']);
        $amount_paid = $db->cleanData($_POST['amount_paid']);
        $repayment_date = $db->cleanData($_POST['repayment_date']);
        $loan_repayment_amount = $db->cleanData($_POST['loan_repayment_amount']);
        
        if (empty($amount_paid) || $amount_paid <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid amount.']);
            exit;
        }
    $response =$members->saveRepayment($loan_id,$member_id,$amount_paid,$repayment_date,$loan_repayment_amount);
    if($response == 1){
       // Tell the Treasurer when this deduction completed the loan (MemberG3::updateRepaidStatus)
       $status = $db->getConnection()->prepare("SELECT loan_status FROM fudscoops_loan WHERE loan_id = :id");
       $status->execute([':id' => $loan_id]);
       $done = $status->fetchColumn() === 'Repaid';
       echo json_encode(['status' => 'success', 'message' => $done ? 'Repayment recorded. The loan is now fully repaid.' : 'Repayment recorded successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save repayment.']);
    }
?>