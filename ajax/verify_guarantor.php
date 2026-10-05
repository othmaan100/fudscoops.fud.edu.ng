<?php

require_once '../config/classes/DB.php';
require_once '../config/classes/User.php';

$db = new DB();
$user = new User();

$spNo = $db->cleanData($_POST['guarantor']);

$response = $user->getStaffInfoArray($spNo);

if (!empty($response)) {
    $staffNo = $response['staff_no'];
    $staffName = $response['title'].' '.$response['fname'].' '.$response['lname'].' '.$response['oname']; 

    // Check if the first two letters of $staffNo are "JP"
    if (substr($staffNo, 0, 2) =='JP') {
        echo json_encode(array(
            'status' => 'success',
            'message' => 'This staff is not eligible to be a guarantor',
            'name' => $staffName // Include the name in the response
        ));
    } else {
        echo json_encode(array(
            'status' => 'success',
            'message' => 'Successfully verified!',
            'name' => $staffName // Include the name in the response
        ));
        
    }
} else {
    echo json_encode(array(
        'status' => 'error',
        'message' => 'Guarantor not found',
        'name' => '' // No name available
    ));
}
?>
