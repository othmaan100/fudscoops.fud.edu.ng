<?php
// Chairman's decisions, one request or many at once (detail pages and the bulk bar on each list, js/fud-bulk.js).
//   type     membership | savings_update | withdrawal | complete_withdrawal | share_purchase | share_transfer
//            | share_conversion | loan | commodity
//   ids[]    request ids (membership: staff numbers)
//   action   approve | decline (reason required; the member sees it). Membership can only be approved.
// Each request goes through the same method as a single decision, with its own checks, so some can fail while
// others go through. Replies JSON {success, message, done, results: [{id, ok, message}]}.
require_once('../config/classes/DB.php');
require_once('../config/classes/User.php');
require_once('../config/classes/MemberG1.php');
require_once('../config/classes/MemberG2.php');
require_once('../config/classes/MemberG3.php');
require_once('../config/classes/SavingsG1.php');
require_once('../config/classes/CommodityLoan.php');

header('Content-Type: application/json');
if (!isset($_SESSION['access_level'], $_SESSION['username']) || (int) $_SESSION['access_level'] !== 4) {
    echo json_encode(['success' => false, 'message' => 'Only the Chairman can authorize requests. Please log in again.']);
    exit();
}
$type = isset($_POST['type']) ? (string) $_POST['type'] : '';
$action = isset($_POST['action']) ? (string) $_POST['action'] : '';
$reason = isset($_POST['reason']) ? trim(strip_tags((string) $_POST['reason'])) : '';
$raw = isset($_POST['ids']) && is_array($_POST['ids']) ? $_POST['ids'] : [];
$ids = $type === 'membership'
    ? array_values(array_unique(array_filter(array_map(function ($v) { return trim((string) $v); }, $raw))))
    : array_values(array_unique(array_filter(array_map('intval', $raw))));
$by = $_SESSION['username'];

$types = ['membership', 'savings_update', 'withdrawal', 'complete_withdrawal', 'share_purchase', 'share_transfer', 'share_conversion', 'loan', 'commodity'];
if (!in_array($type, $types, true) || !in_array($action, ['approve', 'decline'], true)) {
    echo json_encode(['success' => false, 'message' => 'This page has changed. Please reload it and try again.']);
    exit();
}
if (!$ids) {
    echo json_encode(['success' => false, 'message' => 'Select at least one request.']);
    exit();
}
if ($action === 'decline' && $type === 'membership') {
    echo json_encode(['success' => false, 'message' => 'Membership applications can only be authorized here.']);
    exit();
}
// Savings updates have no reason field, so a reason is not needed there
if ($action === 'decline' && $reason === '' && $type !== 'savings_update') {
    echo json_encode(['success' => false, 'message' => 'Give the reason for declining; the member will see it.']);
    exit();
}

$approve = $action === 'approve';
$con = (new DB())->getConnection();
$decide = function ($id) use ($type, $approve, $reason, $by, $con) {
    switch ($type) {
        case 'membership':
            $r = (new MemberG1())->secretaryApproveRegisteration($id, 'Proceed', '');
            return $r === 1 ? 1 : ($r === -2 ? 'Not awaiting your authorization (the Treasurer has not fixed the amount, or it is already approved).' : 'Could not save.');
        case 'savings_update':
            return (new SavingsG1())->chairmanDecideSavingsUpdate($id, $approve);
        case 'withdrawal':
            $m = new MemberG1();
            return $approve ? $m->chairmanApproveWithdrawal($id, $by) : $m->chairmanRejectWithdrawal($id, $reason, $by);
        case 'complete_withdrawal':
            $m = new MemberG1();
            return $approve ? $m->chairmanApproveComWithdrawal($id) : $m->chairmanRejectComWithdrawal($id, $reason, $by);
        case 'share_purchase':
            $stmt = $con->prepare("SELECT member_id FROM fudscoops_shares WHERE shares_id = :id");
            $stmt->execute([':id' => $id]);
            $memberId = $stmt->fetchColumn();
            return $memberId === false ? 'Share purchase not found.' : (new MemberG2())->chairmanApproval($id, $memberId, $approve ? 'Proceed' : 'Decline', $reason);
        case 'share_transfer':
            return (new MemberG2())->chairmanTransferApproval(0, $id, $approve ? 'Proceed' : 'Decline', $reason);
        case 'share_conversion':
            return (new MemberG2())->chairmanConversionApproval($id, $approve ? 'Proceed' : 'Decline', $reason);
        case 'loan':
            $stmt = $con->prepare("SELECT member_id FROM fudscoops_loan WHERE loan_id = :id");
            $stmt->execute([':id' => $id]);
            $memberId = $stmt->fetchColumn();
            return $memberId === false ? 'Loan not found.' : (new MemberG3())->chairmanDecision($id, $memberId, $approve ? 'proceed' : 'decline', $reason);
        case 'commodity':
            return CommodityLoan::chairmanDecision($id, $approve, $reason);
    }
    return 'Unknown request type.';
};

$results = [];
$done = 0;
foreach ($ids as $id) {
    try {
        $r = $decide($id);
    } catch (Exception $e) {
        error_log("Chairman decision $type #$id: " . $e->getMessage());
        $r = 'Could not save. Please try again.';
    }
    $ok = $r === 1 || $r === '1';
    $done += $ok ? 1 : 0;
    $results[] = ['id' => $id, 'ok' => $ok, 'message' => $ok ? ($approve ? 'Authorized' : 'Declined') : ($r === -1 ? 'Could not save. Please try again.' : (string) $r)];
}
$verb = $approve ? 'authorized' : 'declined';
$failed = count($ids) - $done;
echo json_encode(['success' => $done > 0, 'done' => $done, 'results' => $results,
                  'message' => $done . ' request' . ($done === 1 ? '' : 's') . ' ' . $verb . '.' . ($failed ? ' ' . $failed . ' could not be ' . $verb . '.' : '')]);
