<?php

require_once '../config/classes/DB.php';
require_once '../config/classes/SavingsG1.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['staff_no'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$spNo = trim($_POST['staff_no']);
if (empty($spNo)) {
    echo json_encode(['success' => false, 'message' => 'Staff number missing']);
    exit();
}

$savings = new SavingsG1();
$result = $savings->secretaryApprovedUpdateSavings($spNo);

if ($result === 1) {
    echo json_encode(['success' => true, message => 'Savings update approved successfully!', msg => 1]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to approve. Please try again.', msg => 0]); 
}
?>