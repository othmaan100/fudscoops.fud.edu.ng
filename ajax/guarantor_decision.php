<?php
// Not used by the pages (guarantors decide on R/undertaking.php -> ajax/undertaking.php). Kept for any old
// links, but it now only records the decision of the logged-in guarantor named on the loan.
require_once '../config/classes/DB.php';
require_once '../config/classes/MemberG3.php';

if (!isset($_SESSION['access_level'], $_SESSION['username']) || $_SESSION['access_level'] != 1 || !isset($_GET['loan_id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request.']);
    exit();
}
$accept = !(isset($_GET['status']) && $_GET['status'] === 'decline');
$result = (new MemberG3())->updateGuaranto((int) $_GET['loan_id'], $accept);
echo json_encode($result === 1 ? ['success' => true] : ['success' => false, 'error' => $result]);
?>
