<?php
session_start();
require_once '../config/classes/DB.php';
require_once '../config/classes/SavingsG1.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['staff_nos']) || !is_array($_POST['staff_nos'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

// Auth check
require_once '../config/classes/User.php';
$user = new User();


$action = $_POST['action'] ?? '';
$staffNos = array_map('trim', $_POST['staff_nos']);

if (empty($staffNos) || !in_array($action, ['approve', 'reject'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

$savings = new SavingsG1();
$successCount = 0;
$errorCount = 0;

foreach ($staffNos as $staffNo) {
    if (empty($staffNo)) continue;
    
    if ($action === 'approve') {
        $result = $savings->secretaryApprovedUpdateSavings($staffNo);
    } else {
        $result = $savings->rejectUpdateSavings($staffNo); // You'll need this method
    }
    
    if ($result === 1) {
        $successCount++;
    } else {
        $errorCount++;
    }
}

if ($errorCount === 0) {
    echo json_encode([
        'success' => true,
        'message' => "Successfully $action" . ($action === 'approve' ? 'd' : 'ed') . " $successCount request(s)."
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => "$successCount succeeded, $errorCount failed."
    ]);
}
?>