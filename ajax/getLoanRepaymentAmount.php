<?php
// Repayment preview for officers: flat interest (Savings Settings) added to the amount, spread over the months.
// Same calculation as MemberG3::treasurerReview / disburseLoan.
require_once '../config/classes/DB.php';
require_once '../config/classes/User.php';
require_once '../config/classes/MemberG3.php';
        $db = new DB();
        if (!isset($_SESSION['access_level']) || !in_array((int) $_SESSION['access_level'], [2, 3, 4], true)) {
            echo json_encode(array('status' => 'error', 'message' => 'Please log in again.'));
            exit;
        }
        $amount = (float) preg_replace('/[^0-9.]/', '', $db->cleanData(isset($_POST['amount']) ? $_POST['amount'] : ''));
        $tenor = (int) $db->cleanData(isset($_POST['tenor']) ? $_POST['tenor'] : 0);

        if ($amount <= 0) {
            echo json_encode(array('status' => 'error', 'message' => 'Amount cannot be empty or zero.'));
            exit;
        }
        if ($tenor <= 0) {
            echo json_encode(array('status' => 'error', 'message' => 'Tenor (duration) should be greater than 0.'));
            exit;
        }

        $rate = MemberG3::loanInterestRate();
        $interest = round($amount * $rate / 100, 2);
        $monthly = round(($amount + $interest) / $tenor, 2);
        echo json_encode(array('status' => 'success',
            'message' => 'With ' . rtrim(rtrim(number_format($rate, 2), '0'), '.') . '% interest (₦' . number_format($interest, 2) . ') the member repays ₦'
                       . number_format($amount + $interest, 2) . ': ₦' . number_format($monthly, 2) . ' a month for ' . $tenor . ' month(s).'));
?>
