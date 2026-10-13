<?php
// Chairman or Treasurer changes the repayment period of a loan that is still pending
require_once '../config/classes/DB.php';
require_once '../config/classes/User.php';
require_once '../config/classes/MemberG3.php';
        $db = new DB();
        $member = new MemberG3();
        $level = isset($_SESSION['access_level']) ? (int) $_SESSION['access_level'] : -1;
        if (!in_array($level, [3, 4], true)) {
            echo 'Only the Chairman or Treasurer can change the repayment period.';
            exit();
        }
        $tenor = $db->cleanData(isset($_POST['repayment']) ? $_POST['repayment'] : '');
        $loan_id = $db->cleanData(isset($_POST['loan_id']) ? $_POST['loan_id'] : '');

        $stmt = $db->getConnection()->prepare("SELECT l.loan_status, t.loan_type_max_tenor FROM fudscoops_loan l
                                               LEFT JOIN fudscoops_loan_type t ON t.loan_type_id = l.loan_type_id WHERE l.loan_id = :id");
        $stmt->execute([':id' => $loan_id]);
        $loan = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$loan || $loan['loan_status'] !== 'pending') {
            echo 'The repayment period can only be changed before the Treasurer decides the loan.';
            exit();
        }
        if (!ctype_digit((string) $tenor) || (int) $tenor < 1 || ($loan['loan_type_max_tenor'] > 0 && (int) $tenor > (int) $loan['loan_type_max_tenor'])) {
            echo 'Choose a repayment period of 1 to ' . (int) $loan['loan_type_max_tenor'] . ' months.';
            exit();
        }
        echo $member->updateLoanTenor($loan_id,$tenor);
?>
