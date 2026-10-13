<?php
// Member buys shares (cash). Each purchase is a new record, approved by Secretary -> Chairman -> Treasurer.
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
             // The member buys for themselves: staff number from the login, not the form
             $spNo = $_SESSION['username'];
             $share_amount= $db->cleanData(isset($_POST['share_amount']) ? $_POST['share_amount'] : '');
             $bank_id= $db->cleanData(isset($_POST['bank']) ? $_POST['bank'] : '');
             $tellerno= $db->cleanData(isset($_POST['tellerno']) ? $_POST['tellerno'] : '');
             $amount_paid= $db->cleanData(isset($_POST['amount_paid']) ? $_POST['amount_paid'] : '');
             $share_amount_word= $db->cleanData(isset($_POST['share_amount_word']) ? $_POST['share_amount_word'] : '');
             $employeeid =$user->getEmployeeId($spNo);
             echo $member->PurchaseShare($employeeid,$share_amount,$bank_id,$tellerno,$amount_paid,$share_amount_word);
?>
