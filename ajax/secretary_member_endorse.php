<?php

require_once '../config/classes/DB.php';
require_once '../config/classes/User.php';
require_once '../config/classes/SavingsG1.php';
        $db = new DB();
        $member = new MemberG1();

        // Only the chairman authorizes membership (after the treasurer has fixed the amount)
        if (!isset($_SESSION['access_level']) || $_SESSION['access_level'] != 4) {
            echo -3;
            exit();
        }

         $sp_no = $db->cleanData($_POST['sp_no']);
         $member_id = $db->cleanData($_POST['member_id']);
         $action = $db->cleanData($_POST['action']);
          $comment = $db->cleanData($_POST['comment']);

         echo $member->secretaryApproveRegisteration($sp_no, $action, $comment);

 ?>
