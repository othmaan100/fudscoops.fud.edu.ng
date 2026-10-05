<?php

require_once '../config/classes/DB.php';
require_once '../config/classes/SavingsG1.php';
require_once '../config/classes/User.php';
        $db = new DB();
        $endorse = new SavingsG1();
        $user = new User();
        $amount = $db->cleanData($_POST['amount']);
        $percentage = 0.1*$amount;
        $amount = $amount + $percnetage;
        // duration
        $tenor = $db->cleanData($_POST['tenor']);
        // to determine the monthly rate
        $monthly_rate = 0.06 / 12; // Assuming an annual interest rate of 6%
        $repayment = '';
        
        if (empty($amount)) {
            $response = 'Amount cannot be empty.';
            echo json_encode(array('status' => 'error', 'message' => $response));
            return http_response_code(200);
        } else if ($amount == 0) {
            $response = 'Amount cannot be zero.';
            echo json_encode(array('status' => 'error', 'message' => $response));
            return http_response_code(200);
        }
        
        if ($tenor <= 0) {
            $response = 'Tenor (duration) should be greater than 0.';
            echo json_encode(array('status' => 'error', 'message' => $response));
            return http_response_code(200);
        }
        
        // Calculate the total number of months for repayment
            $n = $tenor;
        
        // Apply the loan repayment formula (fixed-rate loan formula)
        if ($amount > 0 && $n > 0) {
            
            // $repayment = $amount * $monthly_rate * pow(1 + $monthly_rate, $n) / (pow(1 + $monthly_rate, $n) - 1);
            $repayment = $amount/$n;
        }
        
        // Format the repayment value to two decimal places
        $repayment = number_format($repayment, 2);
        $repayment = '₦' . $repayment;
        // Add formula description
        // $formulaDescription = "The expected monthly repayment is calculated using the fixed-rate amortization formula.";
        $formulaDescription = "The expected monthly repayment is calculated using the fudscoopsformula.";
        
        // Final response message
        $response = $formulaDescription . ' The amount is ' . $repayment;
        echo json_encode(array('status' => 'success', 'message' => $response));
        return http_response_code(200);

?>
 