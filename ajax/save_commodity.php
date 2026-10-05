<?php
require_once('../config/classes/MemberG1.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comm_name = $_POST['comm_name'] ?? null;
    $opening_date = $_POST['opening_date'] ?? null;
    $closing_date = $_POST['closing_date'] ?? null;

    $members = new MemberG1();
    $result = $members->saveCommodity($comm_name, $opening_date, $closing_date);

    if ($result == 1) {
        echo "success";
    } else {
        echo "error";
    }
}
?>
