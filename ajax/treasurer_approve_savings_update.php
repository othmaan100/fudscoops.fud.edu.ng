<?php
// Treasurer approves a single savings update request with the final (approved) amount
require_once '../config/classes/DB.php';
require_once '../config/classes/User.php';
require_once '../config/classes/SavingsG1.php';

header('Content-Type: application/json');

if (!isset($_SESSION['access_level']) || $_SESSION['access_level'] != 3) {
    echo json_encode(['success' => false, 'message' => 'Session expired. Please log in again.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['update_id'], $_POST['amount'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$savings = new SavingsG1();
$result = $savings->approveSavingsUpdateRequest((int) $_POST['update_id'], $_POST['amount']);

$messages = [
    1  => 'Savings update approved. Member monthly savings updated.',
    0  => 'This request has already been processed.',
    -2 => 'Enter a valid amount of at least ₦' . number_format(SavingsG1::getMinMonthlySavings()) . '.',
    -1 => 'Failed to approve. Please try again.',
];

echo json_encode(['success' => $result === 1, 'message' => $messages[$result]]);
