<?php
    require_once '../config/classes/User.php'; // Ensure you include the class where numberToWords is defined
    require_once '../config/classes/DB.php';
    require_once '../config/classes/MemberG2.php';
    $db = new DB();
    
    if (isset($_POST['share_amount'])) {
        $user = new User();
        $worth = floatval($_POST['share_amount']); // Ensure input is numeric
        echo $user->numberToWords($worth);
    }
?>
