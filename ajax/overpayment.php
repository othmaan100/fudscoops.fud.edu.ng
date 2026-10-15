<?php
// Over-repayments (config/classes/LoanControls.php): the member chooses what to do, the Treasurer settles.
// Replies 1 on success, otherwise a message.
require_once '../config/classes/DB.php';
require_once '../config/classes/User.php';
require_once '../config/classes/LoanControls.php';
$user = new User();

if (!isset($_SESSION['access_level'], $_SESSION['username'])) {
    echo 'Your session has expired. Please log in again.';
    exit();
}
$level = (int) $_SESSION['access_level'];
$action = isset($_POST['action']) ? $_POST['action'] : '';
$id = isset($_POST['overpayment_id']) ? (int) $_POST['overpayment_id'] : 0;
$post = function ($k) { return isset($_POST[$k]) ? trim(strip_tags((string) $_POST[$k])) : ''; };

if ($action === 'choose' && $level === 1) {
    $memberId = $user->getMemberId($user->getEmployeeId($_SESSION['username']));
    echo LoanControls::chooseOverpayment($id, $memberId, $post('choice'), (int) $post('target_loan_id'),
                                         $post('bank_name'), $post('account_number'), $post('account_name'));
} elseif ($action === 'settle' && $level === 3) {
    echo LoanControls::settleOverpayment($id, $post('note'), $_SESSION['username']);
} elseif (in_array($action, ['choose', 'settle'], true)) {
    echo 'You are not allowed to do this.';
} else {
    echo 'This page has changed. Please reload it and try again.';
}
