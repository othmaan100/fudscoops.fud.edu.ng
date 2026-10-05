<?php
    
    require_once '../config/classes/DB.php';
    
    if (!isset($_POST['update_id']) || !isset($_POST['amount'])) {
        echo json_encode(['success' => false, 'message' => 'Missing data']);
        exit();
    }
    
    $con = (new DB())->getConnection();
    $amount = floatval($_POST['amount']);
    $updateId = intval($_POST['update_id']);
    
    try {
        $con->beginTransaction();
    
        // First update: approve savings update record
        $stmt = $con->prepare("
            UPDATE fudscoops_update_savings 
            SET treasurer_approved_amount = :amount, 
                is_treasurer_approved = 1, 
                approved_at = NOW()
            WHERE id = :id
        ");
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':id', $updateId);
        $stmt->execute();
    
        if ($stmt->rowCount() > 0) {
            // Get employee_id for this update record
            $stmtGet = $con->prepare("SELECT employee_id FROM fudscoops_update_savings WHERE id = :id");
            $stmtGet->bindParam(':id', $updateId);
            $stmtGet->execute();
            $employeeId = $stmtGet->fetchColumn();
    
            if ($employeeId) {
                // Second update: update member's proposed savings
                $sql2 = "
                    UPDATE fudscoops_member 
                    SET proposed_monthly_savings = :amount, savings_amount_update= NOW()
                    WHERE employee_id = :employee_id
                ";
                $stmt2 = $con->prepare($sql2);
                $stmt2->bindParam(':amount', $amount);
                $stmt2->bindParam(':employee_id', $employeeId);
                $stmt2->execute();
    
                $con->commit();
                echo json_encode(['success' => true, 'message' => 'Savings update approved successfully!']);
                exit();
            }
        }
    
        $con->rollBack();
        echo json_encode(['success' => false, 'message' => 'Update failed']);
    } catch (Exception $e) {
        $con->rollBack();
        error_log("Error in updateTreasurerApprovedAmount: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>
