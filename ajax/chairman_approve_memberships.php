<?php
// Chairman authorizes one or more membership applications (the treasurer has already fixed the amounts).
// POST sp_nos[] = staff numbers. Used by Chairman/member_list.php for single and bulk approval.
require_once '../config/classes/DB.php';
require_once '../config/classes/User.php';
require_once '../config/classes/MemberG1.php';

header('Content-Type: application/json');

if (!isset($_SESSION['access_level']) || $_SESSION['access_level'] != 4) {
    echo json_encode(['success' => false, 'message' => 'Only the Chairman can authorize membership. Please log in again.']);
    exit();
}

$staffNos = isset($_POST['sp_nos']) && is_array($_POST['sp_nos']) ? array_unique(array_filter(array_map('trim', $_POST['sp_nos']))) : [];
if (empty($staffNos)) {
    echo json_encode(['success' => false, 'message' => 'No applicants selected.']);
    exit();
}

$members = new MemberG1();
$approved = 0;
$failed = [];
foreach ($staffNos as $spNo) {
    $result = $members->secretaryApproveRegisteration($spNo, 'Proceed', '');
    if ($result === 1) {
        $approved++;
    } elseif ($result === -2) {
        $failed[] = "$spNo: not awaiting authorization (the Treasurer has not fixed the amount, or already approved)";
    } else {
        $failed[] = "$spNo: could not be approved";
    }
}

$message = "$approved membership application(s) approved.";
if ($failed) {
    $message .= ' ' . count($failed) . ' not approved: ' . implode('; ', $failed);
}
echo json_encode(['success' => empty($failed), 'approved' => $approved, 'message' => $message]);
