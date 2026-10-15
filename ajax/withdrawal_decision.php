<?php
// Treasurer's decision on a savings withdrawal request (Treasurer/view_withdrawal_details.php).
//   action=approve (amount, comment) -> sends it to the Chairman; action=reject (comment = reason).
// Replies JSON {ok, message}.
require_once '../config/classes/DB.php';
require_once '../config/classes/User.php';
require_once '../config/classes/MemberG1.php';

header('Content-Type: application/json');
if (!isset($_SESSION['access_level'], $_SESSION['username']) || (int) $_SESSION['access_level'] !== 3) {
    echo json_encode(['ok' => false, 'message' => 'Only the Treasurer can decide withdrawal requests. Please log in again.']);
    exit();
}
$action = isset($_POST['action']) ? $_POST['action'] : '';
$id = isset($_POST['withrawals_id']) ? (int) $_POST['withrawals_id'] : 0;
$comment = isset($_POST['comment']) ? strip_tags((string) $_POST['comment']) : '';
$member = new MemberG1();

if ($action === 'approve') {
    $result = $member->treasurerApproveWithdrawal($id, isset($_POST['amount']) ? $_POST['amount'] : '', $comment, $_SESSION['username']);
    echo json_encode($result === 1 ? ['ok' => true, 'message' => 'Approved and sent to the Chairman.'] : ['ok' => false, 'message' => $result === -1 ? 'Could not save. Please try again.' : $result]);
} elseif ($action === 'reject') {
    $result = $member->treasurerRejectWithdrawal($id, $comment, $_SESSION['username']);
    echo json_encode($result === 1 ? ['ok' => true, 'message' => 'Request rejected. The member will see your reason.'] : ['ok' => false, 'message' => $result]);
} else {
    echo json_encode(['ok' => false, 'message' => 'This page has changed. Please reload it and try again.']);
}
