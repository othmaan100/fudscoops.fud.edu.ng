<?php

require_once '../classes/DB.php';
require_once '../classes/Putme.php';
        $db = new DB();
        $putme = new Putme();
        $phone = $db->cleanData($_POST['phone']);
        $raddress = $db->cleanData($_POST['raddress']);
        $paddress = $db->cleanData($_POST['paddress']);
        $email = $db->cleanData($_POST['email']);
        $nok_phone = $db->cleanData($_POST['nokphone']);
        $nok_address = $db->cleanData($_POST['nokAddress']);
        $jambNo = $db->cleanData($_POST['jamb']);
        $relationship = $db->cleanData($_POST['relationship']);
        $status = $db->cleanData($_POST['status']);
        $nok = $db->cleanData($_POST['nok']);
        // $orderId = $db->cleanData($_POST['orderId']);

        echo $putme-> updateContact($jambNo,$phone,$email,$raddress,$paddress,$nok,$nok_phone,$nok_address,$relationship,$status);
?>
        