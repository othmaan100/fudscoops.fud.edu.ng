<?php

require_once '../config/classes/DB.php';
require_once '../config/classes/MemberG2.php';
require_once '../config/classes/User.php';

        $db = new DB();
        $user = new User();

        $spno = $db->cleanData($_POST['staff_no']);
        $firstname = $db->cleanData($_POST['fname']);
        $othernames = $db->cleanData($_POST['oname']);
        $lastname = $db->cleanData($_POST['lname']);
        $username = $db->cleanData($_POST['username']);
        $phone = $db->cleanData($_POST['gsm-no']);
        $dept = $db->cleanData($_POST['dept']);
        $cadre = $db->cleanData($_POST['cadre']);
        $title = $db->cleanData($_POST['title']);

        echo $user->updateAdminProfile($username,$spno,$firstname,$othernames,$lastname,$dept,$phone,$cadre,$title);

?>
