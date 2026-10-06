<?php
require_once "../config/classes/DB.php";
require_once "../config/classes/User.php";
require_once "../config/classes/MemberG3.php";

$db = new DB();
$user = new User();
$member = new MemberG3();

if (empty($_POST['file_type'])) {
    echo '<div class="alert alert-danger">Error: Please select a Loan Type!</div>';
    exit();
}

$loanTypeId = intval($_POST['file_type']);

// ================= FILE VALIDATION ==================
if (!isset($_FILES['uploadFile']) || empty($_FILES['uploadFile']['name'])) {
    echo '<div class="alert alert-danger">Error: Please select a file first!</div>';
    exit();
}

$allowedExtensions = ["csv"];
$ext = pathinfo($_FILES['uploadFile']['name'], PATHINFO_EXTENSION);

if (!in_array($ext, $allowedExtensions)) {
    echo '<div class="alert alert-danger">Error: Only CSV files are allowed!</div>';
    exit();
}

$file_size = $_FILES['uploadFile']['size'] / 1024; // KB
if ($file_size > 500) {
    echo '<div class="alert alert-danger">Error: Maximum file size should not exceed 500KB!</div>';
    exit();
}

$tmpFile = $_FILES['uploadFile']['tmp_name'];

// Upload copy
$file = "../resources/uploads/" . $_FILES['uploadFile']['name'];
copy($tmpFile, $file);

// =================== PROCESS CSV =====================
$handle = fopen($tmpFile, 'r');
$i = 0;
$inserted = 0;
$updated = 0;
$skipped = 0;

$con = $db->getConnection();
$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$resultTable = '
<table id="loanDeductionTable" class="display table table-striped table-bordered" width="100%">
<thead>
    <tr>
        <th>SN</th>
        <th>Staff Number</th>
        <th>Repayment Amount</th>
        <th>Date</th>
        <th>Deduction Status</th>
        <th>Status</th>
    </tr>
</thead>
<tbody>
';

while (($data = fgetcsv($handle, 1000, ',')) !== false) {
    if ($i == 0) { 
        $i++;
        continue; 
    }

    // ================== CSV COLUMNS ====================
    $spNo = trim($data[0] ?? "");
    $mainDate = trim($data[1]);
     
    // Corrected date handling
    $date = DateTime::createFromFormat('m/d/Y', $mainDate);
    if ($date === false) {
        $resultTable .= "
            <tr>
                <td>$i</td>
                <td>$spNo</td>
                <td></td>
                <td>$mainDate</td>
                <td>Invalid</td>
                <td class='text-danger'>Invalid Date</td>
            </tr>";
        $skipped++;
        $i++;
        continue;
    }

    // Format the date to YYYY-MM-DD
    $formatted_date = $date->format('Y-m-d');
   
    // ================== VALIDATION =====================
    $memberId = $user->getMemberId($user->getEmployeeId($spNo));
   
    if (empty($memberId) || empty($formatted_date)) {
        $resultTable .= "
            <tr>
                <td>$i</td>
                <td>$spNo</td>
                <td></td>
                <td>$mainDate</td>
                <td>Invalid</td>
                <td class='text-danger'>Invalid Data</td>
            </tr>";
        $skipped++;
        $i++;
        continue;
    }

    // ================== CHECK IF LOAN EXISTS =====================
    $loanDate = date('Y-m-d');
    $loanStatus = "Paid";

    $stmtCheck = $con->prepare("SELECT loan_id FROM fudscoops_loan WHERE member_id = :member_id AND loan_type_id = :type");
    $stmtCheck->execute([':member_id' => $memberId, ':type' => $loanTypeId]);

    if ($stmtCheck->rowCount() > 0) {
        $loan_id = $stmtCheck->fetch(PDO::FETCH_ASSOC)['loan_id'];

        // Fetch the repayment amount
        $repaymentAmount = $member->getRepaymentAmount($loan_id, $memberId);

        // Check if a repayment for this loan exists with the same date and amount
        $stmtRepayCheck = $con->prepare("
            SELECT loan_repayment_id
            FROM fudscoops_loan_repayments
            WHERE member_id = :member_id
              AND loan_id = :loan_id
              AND loan_repayment_amount = :repayment_amount
              AND loan_repayment_date = :deduction_date
              AND status = 1
        ");
        $stmtRepayCheck->execute([
            ':member_id' => $memberId,
            ':loan_id' => $loan_id,
            ':repayment_amount' => $repaymentAmount,
            ':deduction_date' => $formatted_date
        ]);

        if ($stmtRepayCheck->rowCount() > 0) {
            // If a record exists, update it
            $repayment_id = $stmtRepayCheck->fetch(PDO::FETCH_ASSOC)['loan_repayment_id'];
            
            $stmtUpdate = $con->prepare("
                UPDATE fudscoops_loan_repayments 
                SET loan_repayment_amount = :repayment, 
                    date_paid = NOW() 
                WHERE loan_repayment_id = :repayment_id
            ");
            $stmtUpdate->execute([
                ':repayment' => $repaymentAmount,
                ':repayment_id' => $repayment_id
            ]);
            $updated++;
            $status = "Deduction updated successfully";
            $statusClass = "text-success";
        } else {
            // If no record exists, insert a new one
            $stmtInsert = $con->prepare("
                INSERT INTO fudscoops_loan_repayments 
                (member_id, loan_id, loan_repayment_amount, loan_repayment_date, amount_paid, status, date_paid)
                VALUES (:member_id, :loan_id, :repayment, :deduction_date, :amount, :status, NOW())
            ");
            $stmtInsert->execute([
                ':member_id' => $memberId,
                ':loan_id' => $loan_id,
                ':repayment' => $repaymentAmount,
                ':deduction_date' => $formatted_date,
                ':amount' => $repaymentAmount,
                ':status' => 1
            ]);
            $inserted++;
            $status = "Deduction inserted successfully";
            $statusClass = "text-info";
        }

    } else {
        // Loan not found
        $inserted++;
        $status = "Loan not found";
        $statusClass = "text-error";
    }

    // ============== TABLE ROW OUTPUT =====================
    $resultTable .= "
        <tr>
            <td>$i</td>
            <td>$spNo</td>
            <td>$repaymentAmount</td>
            <td>$mainDate</td>
            <td>$loanStatus</td>
            <td class='$statusClass'>$status</td>
        </tr>";

    $i++;
}

fclose($handle);
$resultTable .= '</tbody></table>';

// ====================== DISPLAY ==========================
echo "
<div class='alert alert-info'>
    <strong>Import Results:</strong><br>
    $inserted new loans inserted<br>
    $updated loans updated<br>
    $skipped records skipped
</div>
";

echo $resultTable;

echo "
<script>
$(document).ready(function() {
    $('#loanDeductionTable').DataTable({
        paging: true,
        searching: true,
        ordering: true,
        responsive: true
    });
});
</script>
";
?>
