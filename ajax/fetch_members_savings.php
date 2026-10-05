<?php
 require_once '../config/classes/MemberG1.php';
  require_once '../config/classes/SavingsG1.php';


 $savings = new SavingsG1();

// Check if request is AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die("Invalid request");
}

// Get date range from POST data
$start_date = $_POST['from'] ?? '';
$end_date = $_POST['to'] ?? '';

if (empty($start_date) || empty($end_date)) {
    echo '<div class="alert alert-danger">Please select both date ranges</div>';
    exit;
}


echo $savings->fetchMembersSavings($start_date,$end_date);


?>