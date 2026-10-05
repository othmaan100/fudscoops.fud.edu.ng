<?php
session_start();
header('Content-Type: application/json');



// 2. Check if ID is provided
if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid withdrawal ID.']);
    exit;
}

$withdrawalId = (int)$_POST['id'];

try {
    // 3. Include your database connection class
    require_once('../config/classes/DB.php'); // Adjust path as needed
    $db = new DB();
    $con = $db->getConnection();

    // 4. Prepare and execute the DELETE query
    // ⚠️ IMPORTANT: Change 'fudscoops_withdrawals' and 'id' to match your actual table and primary key column name
    $query = "DELETE FROM fudscoops_withrawals WHERE withrawals_id  = :id";
    $stmt = $con->prepare($query);
    $stmt->bindParam(':id', $withdrawalId, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Record deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Record not found or already deleted.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to execute delete query.']);
    }

} catch (PDOException $e) {
    // Log the error internally, don't expose details to the user
    error_log("Delete Withdrawal Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'A database error occurred.']);
}
?>