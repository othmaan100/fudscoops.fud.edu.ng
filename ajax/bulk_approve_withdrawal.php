<?php
session_start();
require_once('../config/classes/DB.php');
require_once('../config/classes/User.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['withdrawal_ids']) || !is_array($_POST['withdrawal_ids'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit();
}


$action = $_POST['action'] ?? '';
$ids = array_map('intval', $_POST['withdrawal_ids']);

if (empty($ids) || !in_array($action, ['approve', 'reject'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
    exit();
}

$db = new DB();
$con = $db->getConnection();

try {
    $con->beginTransaction();

    if ($action === 'approve') {
        $statusField = 'chairman_approval';
        $statusValue = 1;
        $message = 'Approved';
    } else {
        $statusField = 'chairman_approval';
        $statusValue = -1; // or 0, depending on your logic
        $message = 'Rejected';
    }

    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    $stmt = $con->prepare("UPDATE fudscoops_withrawals SET $statusField = ? WHERE withrawals_id IN ($placeholders)"); 
    
    $params = array_merge([$statusValue], $ids);
    $stmt->execute($params);

    $con->commit();

    echo json_encode([
        'success' => true,
        'message' => "Successfully $message " . count($ids) . " withdrawal request(s)."
    ]);

} catch (Exception $e) {
    $con->rollBack();
    error_log("Bulk Withdrawal $action Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred. Please try again.'
    ]);
}
?>