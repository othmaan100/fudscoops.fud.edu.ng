<?php

require_once '../config/classes/DB.php';
require_once '../config/classes/User.php';
        $db = new DB();
        $user = new User();
        $gender = $db->cleanData($_POST['gender']);
        $sp = $db->cleanData($_POST['sp']);
        $fname = $db->cleanData($_POST['fname']);
        $lname = $db->cleanData($_POST['lname']);
        $oname = $db->cleanData($_POST['oname']);
        $dob = $db->cleanData($_POST['dob']);
        $phone = $db->cleanData($_POST['phone']);
        $email = $db->cleanData($_POST['email']);
        $office = $db->cleanData($_POST['office']);
        $department = $db->cleanData($_POST['department']);
        $rank = $db->cleanData($_POST['rank']);
        $cemail = $db->cleanData($_POST['cemail']);
        $specialization = $db->cleanData($_POST['specialization']);
        $type = $db->cleanData($_POST['type']);
        $publication = $db->cleanData($_POST['publication']);
        $estatus = $db->cleanData($_POST['estatus']);
        $qualification = $db->cleanData($_POST['qualification']);
        $cadre = $db->cleanData($_POST['cadre']);

        echo $user-> updateProfile($sp,$gender,$dob,$fname,$lname,$oname,$phone,$email,$office,$department,$rank,$cemail,$specialization,$type,$publication,$estatus,$qualification,$cadre);
?>
        