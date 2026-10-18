<?php
// Chairman's decision on monthly savings update requests (Chairman/update_savings.php).
//   ids[] (request ids, one or more), action=approve (goes to the Treasurer) | reject.
// Replies JSON {success, message, done, results: [{id, ok, message}]}.
require_once('../config/classes/DB.php');
require_once('../config/classes/User.php');
require_once('../config/classes/SavingsG1.php');

header('Content-Type: application/json');
if (!isset($_SESSION['access_level']) || (int) $_SESSION['access_level'] !== 4) {
    echo json_encode(['success' => false, 'message' => 'Only the Chairman can decide savings updates. Please log in again.']);
    exit();
}
$action = isset($_POST['action']) ? $_POST['action'] : '';
$ids = isset($_POST['ids']) && is_array($_POST['ids']) ? array_values(array_unique(array_filter(array_map('intval', $_POST['ids'])))) : [];
if (!$ids || !in_array($action, ['approve', 'reject'], true)) {
    echo json_encode(['success' => false, 'message' => 'Select at least one request.']);
    exit();
}
$savings = new SavingsG1();
$results = [];
$done = 0;
foreach ($ids as $id) {
    $r = $savings->chairmanDecideSavingsUpdate($id, $action === 'approve');
    $done += $r === 1 ? 1 : 0;
    $results[] = ['id' => $id, 'ok' => $r === 1, 'message' => $r === 1 ? 'Done' : (string) $r];
}
$verb = $action === 'approve' ? 'approved' : 'rejected';
$failed = count($ids) - $done;
echo json_encode(['success' => $done > 0, 'done' => $done, 'results' => $results,
                  'message' => $done . ' request' . ($done === 1 ? '' : 's') . ' ' . $verb . '.' . ($failed ? ' ' . $failed . ' could not be ' . $verb . '.' : '')]);
