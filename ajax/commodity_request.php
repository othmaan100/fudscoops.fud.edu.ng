<?php

require_once '../config/classes/DB.php';
require_once '../config/classes/MemberG2.php';
require_once '../config/classes/User.php';

$db = new DB();
$member = new MemberG2();
$user = new User();

        // Start session
        session_start();
        
        // Fix typo: correct session variable
        $employeeId = $user->getEmployeeId($_SESSION['username']);
        $member_id = $user->getMemberId($employeeId);
        
        // Initialize arrays
        $commodity_ids = $_POST['commodity_id'] ?? [];
        $quantities_applied = $_POST['quantity_applied'] ?? [];
        $unit_prices = $_POST['unit_price'] ?? [];
        $total_amounts = $_POST['total_amount'] ?? [];
        
        // Process each commodity
        foreach ($commodity_ids as $index => $commodity_id) {
            $commodity_id = $db->cleanData($commodity_id);
            $quantity_applied = $db->cleanData($quantities_applied[$index]);
            $unit_price = $db->cleanData($unit_prices[$index]);
            $total_amount = $db->cleanData($total_amounts[$index]);
        
            // Optional: Skip empty commodity selection
            if (empty($commodity_id) || empty($quantity_applied)) {
                continue;
            }
        
            // Call the MemberG2 method for each commodity
            $result = $member->RequestCommodity($member_id, $commodity_id, $quantity_applied, $unit_price, $total_amount);
        
            if ($result != 1) {
                echo json_encode(['success' => false, 'message' => 'Failed to submit commodity request.']);
                exit;
            }
        }
        
        // If all succeed
        echo json_encode(['success' => true]);
?>
