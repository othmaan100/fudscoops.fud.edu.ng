<?php
// Treasurer fixes the monthly savings for a membership applicant (step 1);
// the chairman then authorizes the application (ajax/secretary_member_endorse.php).
require_once('../config/classes/DB.php');
require_once('../config/classes/User.php');
require_once('../config/classes/MemberG1.php');
require_once('../config/classes/SavingsG1.php');

header('Content-Type: application/json');

// Auth check: treasurer only
if (!isset($_SESSION['access_level']) || $_SESSION['access_level'] != 3) {
    echo json_encode(['success' => false, 'message' => 'Session expired. Please log in again.']);
    exit();
}

$employeeId = isset($_POST['employee_id']) ? (int) $_POST['employee_id'] : 0;
$amount = isset($_POST['amount']) ? $_POST['amount'] : '';

if (!$employeeId) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$members = new MemberG1();
$result = $members->treasurerApproveMembership($employeeId, $amount);

$messages = [
    1  => 'Monthly savings fixed. The application has been sent to the Chairman for authorization.',
    0  => 'This applicant is no longer pending (already approved or not found).',
    -2 => 'Enter a monthly savings of at least ₦' . number_format(SavingsG1::getMinMonthlySavings()) . '.',
    -1 => 'Operation failed. Please try again.',
];

echo json_encode(['success' => $result === 1, 'message' => $messages[$result]]);
?>
