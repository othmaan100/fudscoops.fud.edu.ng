<?php

    require_once '../config/classes/DB.php';
    require_once '../config/classes/MemberG1.php';
    require_once '../config/classes/User.php';
    
    $db = new DB();
    $member = new MemberG1();
    $user = new User();
    
    // Debugging: Print the posted data
   // var_dump($_POST);
    
    // Extracting POST data safely
    $amountApplied = $_POST['amount'] ?? null;
    $savingPeriod = $_POST['periodSave'] ?? null;
    $withdrawPeriod = $_POST['periodWithdraw'] ?? null;
    $grade = $_POST['grade'] ?? null;
    $guarantorSpno = $_POST['guarantor_sp_no'] ?? null;
    $memberId = $_POST['member_id'] ?? null;
    
    
    $employ_id =  $user->getEmployeeId($guarantorSpno);
    $guarantorId =  $member->getMemberId($employ_id );
    
    // Ensure all required fields are present
    if (!$amountApplied || !$savingPeriod || !$withdrawPeriod || !$grade || !$guarantorId || !$memberId) {
        echo json_encode(["status" => -1, "message" => "Error: Missing required fields."]);
        exit;
    }
    
    // Convert to integer for validation
    $savingPeriod = (int) $savingPeriod;
    $withdrawPeriod = (int) $withdrawPeriod;
    
    // Server-side Validation (Based on JS Logic)
    if ($grade === "SP" && ($savingPeriod < 24 || $withdrawPeriod < 24)) {
        echo json_encode(["status" => -1, "message" => "For Senior Staff (SP), both Period to Save and Period to Withdraw must be at least 24 months."]);
        exit;
    } elseif ($grade === "JP" && ($savingPeriod < 12 || $withdrawPeriod < 12)) {
        echo json_encode(["status" => -1, "message" => "For Junior Staff (JP), both Period to Save and Period to Withdraw must be at least 12 months."]);
        exit;
    }
    
    // Call the function to add the record
    $result = $member->addTargetSavingRequest($db, $amountApplied, $savingPeriod, $withdrawPeriod, $guarantorId ,$memberId );
    
    if (is_string($result) && json_decode($result) !== null) {
        echo $result;
        exit;
    }
    
    if ($result === 1) {
        echo json_encode(["status" => 1, "message" => "Application request submitted successfully."]);
    } else {
        echo json_encode(["status" => -1, "message" => "Error! Could not process the request."]);
    }

?>
