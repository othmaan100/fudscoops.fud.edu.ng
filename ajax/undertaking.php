<?php
// Guarantor accepts or declines a loan they were named on (MemberG3::updateGuaranto checks it is theirs)
require_once '../config/classes/DB.php';
require_once '../config/classes/SavingsG1.php';
require_once '../config/classes/MemberG3.php';
        $db = new DB();
        $endorse = new MemberG3();

        if (!isset($_SESSION['access_level'], $_SESSION['username']) || $_SESSION['access_level'] != 1) {
            echo 'Your session has expired. Please log in again.';
            exit();
        }
         $loan_id = $db->cleanData(isset($_POST['loan_id']) ? $_POST['loan_id'] : '');
         $accept = !(isset($_POST['decision']) && $_POST['decision'] === 'decline');
         echo $endorse->updateGuaranto($loan_id, $accept);
?>
