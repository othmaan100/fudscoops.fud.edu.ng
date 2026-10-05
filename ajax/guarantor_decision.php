<?php

require_once '../config/classes/DB.php';
require_once '../config/classes/MemberG3.php';
require_once '../config/classes/User.php';
$db = new DB();
$member = new MemberG3();
$user = new User();
        
if (isset($_GET['loan_id'])) {
    $loanId = $_GET['loan_id'];
    $status =1;

    $db = new DB();
    $con = $db->getConnection();

    // Update the loan status in the database
    $sql = "UPDATE fudscoops_loan SET guarantor_status = :status WHERE loan_id = :loan_id";
    $stmt = $con->prepare($sql);
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $stmt->bindParam(':loan_id', $loanId, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Could not update loan status.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid parameters.']);
}
?>

?>
        