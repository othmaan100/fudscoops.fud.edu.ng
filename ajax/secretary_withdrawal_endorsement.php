<?php
//var_dump($_POST);
        require_once '../config/classes/DB.php';
        require_once '../config/classes/SavingsG1.php';
        require_once '../config/classes/MemberG1.php'; // This was missing
        
        $db = new DB();
        $member = new MemberG1();
        
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
    echo $member->chairmanApproveWithdrawal($withrawals_id);
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
