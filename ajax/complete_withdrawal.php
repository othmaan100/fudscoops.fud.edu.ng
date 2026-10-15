<?php
// Member's complete withdrawal request (R/complete_withdrawal.php). SavingsG1::completeWithdrawal checks
// loans, commodity and guarantees first (LoanControls::leavingObligations).
require_once '../config/classes/DB.php';
require_once '../config/classes/SavingsG1.php';
        $db = new DB();
        $savings = new SavingsG1();

         if (!isset($_SESSION['access_level'], $_SESSION['username']) || (int) $_SESSION['access_level'] !== 1) {
             echo 'Your session has expired. Please log in again.';
             exit();
         }
         // The member withdraws for themselves: staff number from the login, not the form
         $spNo = $_SESSION['username'];
         $bank_id = $db->cleanData(isset($_POST['bank_id']) ? $_POST['bank_id'] : '');
         $acct_no = $db->cleanData(isset($_POST['acct_no']) ? $_POST['acct_no'] : '');
         $acct_name = $db->cleanData(isset($_POST['acct_name']) ? $_POST['acct_name'] : '');
         $settle = !empty($_POST['settle_from_savings']);

         echo $savings->completeWithdrawal($spNo, $bank_id, $acct_no, $acct_name, $settle);
?>
