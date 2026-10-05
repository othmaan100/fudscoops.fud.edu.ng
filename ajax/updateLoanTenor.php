<?php

require_once '../config/classes/DB.php';
require_once '../config/classes/User.php';
require_once '../config/classes/MemberG3.php';
        $db = new DB();
        $member = new MemberG3();
        $tenor = $db->cleanData($_POST['repayment']);
        $loan_id = $db->cleanData($_POST['loan_id']);

        echo $member->updateLoanTenor($loan_id,$tenor);
?>
        