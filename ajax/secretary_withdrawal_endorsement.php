<?php
//var_dump($_POST);
        require_once '../config/classes/DB.php';
        require_once '../config/classes/SavingsG1.php';
        require_once '../config/classes/MemberG1.php'; // This was missing
        
        $db = new DB();
        $member = new MemberG1();
        
        // Officers only (2 Secretary, 3 Treasurer, 4 Chairman): this approves withdrawals
        if (!isset($_SESSION['access_level']) || !in_array((int) $_SESSION['access_level'], [2, 3, 4], true)) {
            echo 'Please log in again.';
            exit;
        }
        $withrawals_id = $db->cleanData($_POST['withrawals_id'] ?? null);
        $approved_amount = $db->cleanData($_POST['approved_amount'] ?? null);
        $approve = $db->cleanData($_POST['approve'] ?? null);
        $withdrawaltype = $db->cleanData($_POST['withdrawaltype'] ?? null);
        
        
   

// Withdrawal ID is ALWAYS required
if (!$withrawals_id) {
    echo "Missing withdrawal ID.";
    exit;
}

/**
 * ===============================
 * COOPERATIVE WITHDRAWALS
 * ===============================
 */
if (!empty($withdrawaltype)) {

    // Chairman approval (ONLY needs approve)
    if (!empty($approve)) {
        if ((int) $_SESSION['access_level'] !== 4) {
            echo 'Only the Chairman can give the final approval.';
            exit;
        }
        echo $member->chairmanApproveComWithdrawal($withrawals_id);
        exit;
    }

    // Treasurer approval (needs approved_amount)
    if ($approved_amount !== null) {
        echo $member->treasurerApproveComWithdrawal($withrawals_id, $approved_amount);
        exit;
    }

    echo "Missing required parameters.";
    exit;
}

/**
 * =============================== 
 * NORMAL WITHDRAWALS
 * ===============================
 */

// Chairman approval (ONLY needs approve)
if (!empty($approve)) {
    if ((int) $_SESSION['access_level'] !== 4) {
        echo 'Only the Chairman can give the final approval.';
        exit;
    }
    echo $member->chairmanApproveWithdrawal($withrawals_id, isset($_SESSION['username']) ? $_SESSION['username'] : null);
    exit;
}

// Treasurer approval (needs approved_amount)
if ($approved_amount !== null) {
    echo $member->treasurerApproveWithdrawal($withrawals_id, $approved_amount);
    exit;
}

echo "Missing required parameters.";
exit;

        
        
?>
