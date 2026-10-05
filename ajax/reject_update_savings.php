<?php

require_once '../config/classes/DB.php';
require_once '../config/classes/SavingsG1.php';
        $db = new DB();
        $savings = new SavingsG1();
   
        
        
$spNo = trim($_POST['staff_no']);
         
          if (empty($spNo)) {
    echo json_encode(['success' => false, 'message' => 'Staff number missing']);
    exit();
}
        
    
          $result=$savings->secretaryRejectUpdateSavings($spNo);
         
if ($result === 1) {
    echo json_encode(['success' => true, message => 'Savings update rejected successfully!', msg => 1]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to rejected. Please try again.', msg => 0]); 
}

             
                
?>