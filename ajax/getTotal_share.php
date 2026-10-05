<?php

require_once '../config/classes/DB.php';
require_once '../config/classes/SavingsG1.php';
require_once '../config/classes/User.php';
        $db = new DB();
        $endorse = new SavingsG1();
        $user = new User();
      $amount = $db->cleanData($_POST['amount']);
    $share_amount = $user->getShareAmount($_SESSION['username']);

// Validate input
if (empty($amount)) {
    $response = 'Amount cannot be empty.';
    echo json_encode(['status' => 'error', 'message' => $response]);
    http_response_code(200);
    exit;
}

if (!is_numeric($amount)) {
    $response = 'Amount must be a number.';
    echo json_encode(['status' => 'error', 'message' => $response]);
    http_response_code(200);
    exit;
}

if ($amount == 0) {
    $response = 'Amount cannot be zero.';
    echo json_encode(['status' => 'error', 'message' => $response]);
    http_response_code(200);
    exit;
}

if ($share_amount == 0) {
    $response = 'You have 0 shares; you cannot apply for this loan.';
    echo json_encode(['status' => 'error', 'message' => $response]);
    http_response_code(200);
    exit;
}

// Eligibility check
if ($amount <= $share_amount * 3) {
    $response = 'You are eligible. 10% profit will be applied.';
    echo json_encode(['status' => 'success', 'message' => $response]);
} else {
    $response = 'You are not eligible. The amount must not exceed ' . ($share_amount * 3) . '.';
    echo json_encode(['status' => 'error', 'message' => $response]);
}

// Always return 200 so jQuery `success` executes
http_response_code(200);
exit;
?>