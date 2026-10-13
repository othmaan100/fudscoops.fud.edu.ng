<?php
// Member asks to convert savings to shares
require_once '../config/classes/DB.php';
require_once '../config/classes/MemberG2.php';
require_once '../config/classes/User.php';
$db = new DB();
$member = new MemberG2();
$user = new User();

if (!isset($_SESSION['access_level'], $_SESSION['username']) || $_SESSION['access_level'] != 1) {
    echo 'Your session has expired. Please log in again.';
    exit();
}
             // The member converts their own savings: staff number from the login, not the form
             $spNo = $_SESSION['username'];
             $worth= $db->cleanData(isset($_POST['amount_paid']) ? $_POST['amount_paid'] : '');
             $share_amount_word= $db->cleanData(isset($_POST['amount_word']) ? $_POST['amount_word'] : '');
             $employeeid =$user->getEmployeeId($spNo);
             echo $member->conversionPendingShare($employeeid,$worth,$share_amount_word);
?>
