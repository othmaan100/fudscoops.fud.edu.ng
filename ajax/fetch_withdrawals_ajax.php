<?php
session_start();
require_once('../config/classes/DB.php');
require_once('../config/classes/User.php');

header('Content-Type: application/json');

// Basic auth check
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$from = $_POST['from'] ?? '';
$to = $_POST['to'] ?? '';

if (empty($from) || empty($to)) {
    echo json_encode(['success' => false, 'message' => 'Missing date range']);
    exit();
}

// Validate date format (YYYY-MM-DD)
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
    echo json_encode(['success' => false, 'message' => 'Invalid date format']);
    exit();
}

$db = new DB();
$con = $db->getConnection();

try {
    $query = "
        SELECT 
            w.*, 
            e.sp_no,
            e.fname, 
            e.lname, 
            e.oname
        FROM fudscoops_withrawals w
        INNER JOIN fudscoops_member m ON w.member_id = m.member_id
        INNER JOIN employee e ON m.employee_id = e.employee_id
        WHERE w.chairman_approval = 1
          AND w.approved_date BETWEEN :from AND :to
        ORDER BY w.approved_date DESC, w.withrawals_id DESC
    ";
    
    $stmt = $con->prepare($query);
    $stmt->bindParam(':from', $from, PDO::PARAM_STR);
    $stmt->bindParam(':to', $to, PDO::PARAM_STR);
    $stmt->execute();
    
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $data]);
    
} catch (Exception $e) {
    error_log("Withdrawals Report Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>