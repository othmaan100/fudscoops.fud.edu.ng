<?php
// Receiver accepts a share transfer offered to them (MemberG2::acceptShare checks it is theirs)
require_once '../config/classes/DB.php';
require_once '../config/classes/MemberG2.php';
require_once '../config/classes/User.php';
$db = new DB();
$member = new MemberG2();

if (!isset($_SESSION['access_level'], $_SESSION['username']) || $_SESSION['access_level'] != 1) {
    echo -1;
    exit();
}
             $transferId = $db->cleanData(isset($_POST['transfer_id']) ? $_POST['transfer_id'] : '');
             echo $member->acceptShare($transferId);
?>
