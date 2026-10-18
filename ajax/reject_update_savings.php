<?php

require_once '../config/classes/DB.php';
require_once '../config/classes/User.php';
require_once '../config/classes/SavingsG1.php';

// Secretary or Chairman only (Secretary/update_savings.php, the old Chairman page)
if (!isset($_SESSION['access_level']) || !in_array((int) $_SESSION['access_level'], [2, 4], true)) {
    echo json_encode(['success' => false, 'message' => 'Please log in again.']);
    exit();
}
        $db = new DB();
        $savings = new SavingsG1();
   
        
        
$spNo = trim(isset($_POST['staff_no']) ? $_POST['staff_no'] : '');
         
          if (empty($spNo)) {
    echo json_encode(['success' => false, 'message' => 'Staff number missing']);
    exit();
}
        
    
          $result=$savings->secretaryRejectUpdateSavings($spNo);
         
if ($result === 1) {
    echo json_encode(['success' => true, 'message' => 'Savings update rejected successfully!', 'msg' => 1]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to rejected. Please try again.', 'msg' => 0]); 
}

             
                
?>