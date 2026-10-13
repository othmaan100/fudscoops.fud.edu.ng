<?php
// Live check of the amount typed on the loan application form (R/business_loan.php, R/soft_loan.php).
// Uses the same limit as MemberG3::loanEligibilityError (times savings balance, less loans still owed),
// which decides again when the form is submitted.
require_once '../config/classes/DB.php';
require_once '../config/classes/User.php';
require_once '../config/classes/MemberG3.php';
$user = new User();
$loans = new MemberG3();

header('Content-Type: application/json');
if (!isset($_SESSION['access_level'], $_SESSION['username']) || $_SESSION['access_level'] != 1) {
    echo json_encode(['status' => 'error', 'message' => 'Your session has expired. Please log in again.']);
    exit;
}

$raw = preg_replace('/[^0-9.]/', '', isset($_POST['amount']) ? (string) $_POST['amount'] : '');
$amount = ($raw === '' || !is_numeric($raw)) ? 0 : (float) $raw;
if ($amount <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Enter the amount you want to borrow.']);
    exit;
}

$typeNames = ['business' => 'Business Loan', 'soft' => 'Soft Loan'];
$typeKey = isset($_POST['loan_type']) && isset($typeNames[$_POST['loan_type']]) ? $_POST['loan_type'] : 'business';
$memberId = $user->getMemberId($user->getEmployeeId($_SESSION['username']));
$ctx = $loans->loanApplicationContext($memberId, $typeNames[$typeKey]);

if ($ctx['open_loan']) {
    echo json_encode(['status' => 'error', 'message' => 'You already have a ' . $typeNames[$typeKey] . ' that is not fully repaid.']);
} elseif ($ctx['limit'] !== null && $amount > $ctx['limit']) {
    echo json_encode(['status' => 'error', 'message' => 'Above your limit. You can borrow up to ₦' . number_format($ctx['limit']) . '.']);
} else {
    echo json_encode(['status' => 'success', 'message' => 'Within your limit.']);
}
