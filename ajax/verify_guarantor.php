<?php
// Checks a proposed guarantor with the same rules the loan application enforces (MemberG3::guarantorError)
require_once '../config/classes/DB.php';
require_once '../config/classes/User.php';
require_once '../config/classes/MemberG3.php';
$db = new DB();
$user = new User();

if (!isset($_SESSION['access_level'], $_SESSION['username']) || $_SESSION['access_level'] != 1) {
    echo json_encode(['status' => 'error', 'message' => 'Your session has expired. Please log in again.', 'name' => '']);
    exit();
}
$spNo = $db->cleanData(isset($_POST['guarantor']) ? $_POST['guarantor'] : '');
$response = $spNo !== '' ? $user->getStaffInfoArray($spNo) : null;
$name = !empty($response) ? trim($response['title'].' '.$response['fname'].' '.$response['lname'].' '.$response['oname']) : '';

$error = (new MemberG3())->guarantorError($user->getEmployeeId($spNo), $user->getEmployeeId($_SESSION['username']));
if ($error) {
    echo json_encode(['status' => 'error', 'message' => $error, 'name' => $name]);
} else {
    echo json_encode(['status' => 'success', 'message' => 'Successfully verified!', 'name' => $name]);
}
?>
