<?php
session_start();
require_once('../config/classes/DB.php');
require_once('../config/classes/User.php');

// Auth check
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$user = new User();


// Validate inputs
$employeeId = $_POST['employee_id'] ?? null;
$action = $_POST['action'] ?? '';
$amount = $_POST['amount'] ?? null;

if (!$employeeId || !in_array($action, ['approve', 'update'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$db = new DB();
$con = $db->getConnection();

try {
    if ($action === 'approve') {
        // Only set treasurer_approval = 1
        $sql = "UPDATE fudscoops_member 
                SET treasurer_approval = 1 
                WHERE employee_id = :employee_id";
        $stmt = $con->prepare($sql);
        $stmt->bindParam(':employee_id', $employeeId, PDO::PARAM_STR);
        
    } else {
        // Update amount AND set treasurer_approval = 1
        if (!is_numeric($amount) || $amount <= 0) {
            throw new Exception('Invalid amount');
        }
        
        $sql = "UPDATE fudscoops_member 
                SET proposed_monthly_savings = :amount, treasurer_approval = 1 
                WHERE employee_id = :employee_id";
        $stmt = $con->prepare($sql);
        $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
        $stmt->bindParam(':employee_id', $employeeId, PDO::PARAM_STR);
    }
    
    if ($stmt->execute()) {
        $msg = ($action === 'approve') 
            ? 'Membership approved successfully!' 
            : 'Savings amount updated and membership approved!';
            
        echo json_encode(['success' => true, 'message' => $msg]);
    } else {
        throw new Exception('Database update failed');
    }
    
} catch (Exception $e) {
    error_log("Member Approval Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Operation failed. Please try again.']);
}
?>