<?php
// Loan decisions from the loan details page (MemberG3::loanDetailsPage).
// Treasurer: recommend (fix amount/period) or decline, then record the payment (disburse).
// Chairman: authorize or decline the Treasurer's figures, or return a decided loan to the Treasurer.
// The Secretary only views loans. Replies 1 on success, otherwise a message.
require_once '../config/classes/DB.php';
require_once '../config/classes/SavingsG1.php';
require_once '../config/classes/MemberG3.php';
        $db = new DB();
        $loans = new MemberG3();
        $level = isset($_SESSION['access_level']) ? (int) $_SESSION['access_level'] : -1;
        $field = function ($name) use ($db) { return $db->cleanData(isset($_POST[$name]) ? $_POST[$name] : ''); };

        $action = $field('action');
        $loanId = (int) $field('loan_id');
        $memberId = (int) $field('member_id');
        $treasurer = ['recommend', 'treasurer_decline', 'disburse'];
        $chairman = ['authorize', 'chairman_decline', 'unlock'];

        if (in_array($action, $treasurer, true) && $level !== 3) {
            echo 'Only the Treasurer can take this step. Please log in again.';
        } elseif (in_array($action, $chairman, true) && $level !== 4) {
            echo 'Only the Chairman can take this step. Please log in again.';
        } elseif ($action === 'recommend' || $action === 'treasurer_decline') {
            echo $loans->treasurerReview($loanId, $memberId, $field('amount'), $field('tenor'), $field('comment'), $action === 'recommend');
        } elseif ($action === 'disburse') {
            echo $loans->disburseLoan($loanId, $field('date'), $field('reference'), $_SESSION['username']);
        } elseif ($action === 'authorize' || $action === 'chairman_decline') {
            echo $loans->chairmanDecision($loanId, $memberId, $action === 'authorize' ? 'Proceed' : 'Decline', $field('comment'));
        } elseif ($action === 'unlock') {
            echo $loans->chairmanUnlockLoandDecision($loanId, $memberId);
        } else {
            // Old decision forms (before 2026-10-13) no longer apply
            echo 'This page has changed. Please reload it and try again.';
        }
?>
