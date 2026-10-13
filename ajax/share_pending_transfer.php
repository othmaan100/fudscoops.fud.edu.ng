<?php
// Member offers shares to another member (receiver's staff number in sp_no)
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
             $spNo = $db->cleanData(isset($_POST['sp_no']) ? $_POST['sp_no'] : '');
             $worth= $db->cleanData(isset($_POST['worth']) ? $_POST['worth'] : '');
             $share_amount_word= $db->cleanData(isset($_POST['amount_word']) ? $_POST['amount_word'] : '');
             $employeeid =$user->getEmployeeId($spNo);
             echo $member->transferPendingShare($employeeid,$worth,$share_amount_word);
?>
