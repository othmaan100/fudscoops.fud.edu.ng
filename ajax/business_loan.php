<?php
// Loan application (Business Loan and Soft Loan forms). Every application creates a new loan record;
// MemberG3::loanEligibilityError explains any refusal.
require_once '../config/classes/DB.php';
require_once '../config/classes/MemberG3.php';
require_once '../config/classes/User.php';
$db = new DB();
$member = new MemberG3();
$user = new User();

if (!isset($_SESSION['access_level'], $_SESSION['username']) || $_SESSION['access_level'] != 1) {
    echo 'Your session has expired. Please log in again.';
    exit();
}
    $post = function ($name) use ($db) { return $db->cleanData(isset($_POST[$name]) ? $_POST[$name] : ''); };

    // The member applies for themselves: staff number from the login, not the form
    $spNo = $_SESSION['username'];
    $amountApplied = $post('amount-applied');
    $loanRepayment = $post('loan_repayment');
    $loanRepaymentSpecify = $post('loan_repayment_specify');
    $guarantor_id = $user->getEmployeeId($post('guarantor_sp_no'));
    $memberId = $user->getMemberId($user->getEmployeeId($spNo));

    // Soft and Business loans are saved with their own loan type
    $loanTypeNames = ['business' => 'Business Loan', 'soft' => 'Soft Loan'];
    $loanTypeKey = $post('loan_type');
    if (!isset($loanTypeNames[$loanTypeKey])) {
        echo 'Unknown loan type.';
        exit();
    }
    $loanTypeId = $member->getLoanTypeId($loanTypeNames[$loanTypeKey]);

    echo $member->saveLoanApplication($amountApplied, $loanRepayment, $loanRepaymentSpecify, $memberId, $loanTypeId, $guarantor_id);
?>
