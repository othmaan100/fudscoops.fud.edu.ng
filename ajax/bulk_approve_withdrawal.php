<?php
// Chairman's decision on savings withdrawals the Treasurer approved (Chairman/withdrawal_endorsement.php).
//   withdrawal_ids[] (one or more), action=approve | reject (reason required; the member sees it).
// Each request is checked on its own (the minimum savings balance must stay), so some can fail while others go through.
// Replies JSON {success, message, done, results: [{id, ok, message}]}.
require_once('../config/classes/DB.php');
require_once('../config/classes/User.php');
require_once('../config/classes/MemberG1.php');

header('Content-Type: application/json');

if (!isset($_SESSION['access_level'], $_SESSION['username']) || (int) $_SESSION['access_level'] !== 4) {
    echo json_encode(['success' => false, 'message' => 'Only the Chairman can decide withdrawal requests. Please log in again.']);
    exit();
}
$action = isset($_POST['action']) ? $_POST['action'] : '';
$ids = isset($_POST['withdrawal_ids']) && is_array($_POST['withdrawal_ids']) ? array_values(array_unique(array_filter(array_map('intval', $_POST['withdrawal_ids'])))) : [];
$reason = isset($_POST['reason']) ? trim(strip_tags((string) $_POST['reason'])) : '';

if (!$ids || !in_array($action, ['approve', 'reject'], true)) {
    echo json_encode(['success' => false, 'message' => 'Select at least one request.']);
    exit();
}
if ($action === 'reject' && $reason === '') {
    echo json_encode(['success' => false, 'message' => 'Give the reason for rejecting; the member will see it.']);
    exit();
}

$member = new MemberG1();
$results = [];
$done = 0;
foreach ($ids as $id) {
    try {
        $r = $action === 'approve'
            ? $member->chairmanApproveWithdrawal($id, $_SESSION['username'])
            : $member->chairmanRejectWithdrawal($id, $reason, $_SESSION['username']);
    } catch (Exception $e) {
        error_log("Chairman withdrawal $action #$id: " . $e->getMessage());
        $r = 'Could not save. Please try again.';
    }
    $ok = $r === 1;
    $done += $ok ? 1 : 0;
    $results[] = ['id' => $id, 'ok' => $ok, 'message' => $ok ? ($action === 'approve' ? 'Approved' : 'Rejected') : ($r === -1 ? 'Could not save. Please try again.' : (string) $r)];
}

$verb = $action === 'approve' ? 'approved' : 'rejected';
$failed = count($ids) - $done;
$message = $done . ' request' . ($done === 1 ? '' : 's') . ' ' . $verb . '.';
if ($failed) {
    $message .= ' ' . $failed . ' could not be ' . $verb . '.';
}
echo json_encode(['success' => $done > 0, 'message' => $message, 'done' => $done, 'results' => $results]);
