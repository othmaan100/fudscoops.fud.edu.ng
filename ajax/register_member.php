<?php

require_once '../config/classes/DB.php';
require_once '../config/classes/MemberG1.php';
require_once '../config/classes/User.php';
        $db = new DB();
        $member = new MemberG1();
        $user = new User();

        // Applicants can only register themselves: use the logged-in staff number, not the posted one
        if (empty($_SESSION['username'])) {
            echo json_encode([-1, 'Your session has expired. Please log in again.']);
            exit();
        }
        $spNo = $_SESSION['username'];

             $monthly_savings= $db->cleanData(isset($_POST['monthly_savings']) ? $_POST['monthly_savings'] : '');
             $next_of_kin_name= $db->cleanData(isset($_POST['next_of_kin_name']) ? $_POST['next_of_kin_name'] : '');
             $next_of_kin_gsm= $db->cleanData(isset($_POST['next_of_kin_gsm']) ? $_POST['next_of_kin_gsm'] : '');
             $next_of_kin_address= $db->cleanData(isset($_POST['next_of_kin_address']) ? $_POST['next_of_kin_address'] : '');

        try {
             echo $member->registerMember($spNo,$monthly_savings,$next_of_kin_name,$next_of_kin_gsm,$next_of_kin_address);
        } catch (Exception $e) {
             error_log("register_member: " . $e->getMessage());
             echo json_encode([-1, 'Cannot register. Contact system admin.']);
        }

?>
