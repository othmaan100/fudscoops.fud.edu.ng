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
<table id="loanTable" class="display table table-striped table-bordered" width="100%">
<thead>
    <tr>
        <th>SN</th>
        <th>Staff Number</th>
        <th>Loan Amount</th>
        <th>Repayment Amount</th>
        <th>Loan Tenor (Months)</th>
        <th>Loan Status</th>
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
    $loanAmount = floatval($data[1] ?? 0);
    $guarantorSpNo = trim($data[2] ?? "");
    $loanTenor = intval($data[3] ?? 0);

    $itermName = trim($data[4] ?? "");
    $supplierName = trim($data[5] ?? "");
    $supplierAddress = trim($data[6] ?? "");
    $supplierGsm = trim($data[7] ?? "");
    $supplierBankName = trim($data[8] ?? "");
    $supplierAccountNumber = trim($data[9] ?? "");

    // ================== VALIDATION =====================
    $memberId = $user->getMemberId($user->getEmployeeId($spNo));
    $guarantorId = $user->getEmployeeId($guarantorSpNo);
    $supplierBank = $member->getBankId($supplierBankName);

    // repayment calculation: amount / tenor
    $repaymentAmount = 0;
    if ($loanAmount > 0 && $loanTenor > 0) {
        $repaymentAmount = $loanAmount / $loanTenor;
    }
  

    if (empty($memberId) || $loanAmount <= 0 || $loanTenor <= 0) {
        $resultTable .= "
            <tr>
                <td>$i</td>
                <td>$spNo</td>
                <td>$loanAmount</td>
                <td>$repaymentAmount</td>
                <td>$loanTenor</td>
                <td>Invalid</td>
                <td class='text-danger'>Invalid Data</td>
            </tr>";
        $skipped++;
        $i++;
        continue;
    }

    // ================== CHECK IF LOAN EXISTS =====================
    $loanDate = date('Y-m-d');
    $loanStatus = "Approved";

    $stmtCheck = $con->prepare("SELECT loan_id FROM fudscoops_loan WHERE member_id = :member_id");
    $stmtCheck->execute([':member_id' => $memberId]);

    // ===========================================================
    // ================ UPDATE EXISTING LOAN ======================
    // ===========================================================
    if ($stmtCheck->rowCount() > 0) {
        $loan_id = $stmtCheck->fetch(PDO::FETCH_ASSOC)['loan_id'];

        $sqlUpdate = "
            UPDATE fudscoops_loan
            SET loan_amount = :loan_amount,
                loan_date = :loan_date,
                loan_status = :loan_status,
                loan_tenor = :loan_tenor,
                loan_type_id = :loan_type_id,
                guarantor_employee_id = :guarantor,
                amount_recommended = :amount_recommended
            WHERE loan_id = :loan_id
        ";

        $stmtUpdate = $con->prepare($sqlUpdate);

        $stmtUpdate->execute([
            ':loan_amount' => $loanAmount,
            ':loan_date' => $loanDate,
            ':loan_status' => $loanStatus,
            ':loan_tenor' => $loanTenor,
            ':loan_type_id' => $loanTypeId,
            ':guarantor' => $guarantorId,
            ':loan_id' => $loan_id,
            ':amount_recommended' => $loanAmount
        ]);

        // ========== Insert Repayment for Updated Loan ==========
        $stmtRepay = $con->prepare("
            INSERT INTO fudscoops_loan_repayments 
            (member_id, loan_id, loan_repayment_amount, loan_repayment_date)
            VALUES (:member_id, :loan_id, :repayment, NOW())
        ");
        $stmtRepay->execute([
            ':member_id' => $memberId,
            ':loan_id' => $loan_id,
            ':repayment' => $repaymentAmount
        ]);

        $updated++;
        $status = "Loan updated successfully";
        $statusClass = "text-success";
  
    } 
    
    // ===========================================================
    // ==================== INSERT NEW LOAN =======================
    // ===========================================================
    else {

        $sqlInsert = "
            INSERT INTO fudscoops_loan 
            (loan_amount, loan_date, loan_status, loan_tenor, loan_type_id, member_id, 
            guarantor_employee_id, iterm_name, guarantor_status, guarantor_approved_date, amount_recommended)
            VALUES 
            (:loan_amount, :loan_date, :loan_status, :loan_tenor, :loan_type_id, :member_id,
            :guarantor, :iterm_name, 1, NOW(), :amount_recommended)
        ";

        $stmtInsert = $con->prepare($sqlInsert);

        $stmtInsert->execute([
            ':loan_amount' => $loanAmount,
            ':loan_date' => $loanDate,
            ':loan_status' => $loanStatus,
            ':loan_tenor' => $loanTenor,
            ':loan_type_id' => $loanTypeId,
            ':member_id' => $memberId,
            ':guarantor' => $guarantorId,
            ':iterm_name' => $itermName,
            ':amount_recommended' => $loanAmount,
        ]);

        $loan_id = $con->lastInsertId();

        // ============ INSERT SUPPLIER =================
        $stmtSupplier = $con->prepare("
            INSERT INTO fudscoops_suppliers
            (supplier_name, address, gsm, account_number, bank_id, loan_id, bank_name, date)
            VALUES (:supplier_name, :address, :gsm, :account_number, :bank_id, :loan_id, :bank_name, NOW())
        ");

        $stmtSupplier->execute([
            ':supplier_name' => $supplierName,
            ':address' => $supplierAddress,
            ':gsm' => $supplierGsm,
            ':account_number' => $supplierAccountNumber,
            ':bank_id' => $supplierBank,
            ':bank_name' => $supplierBankName,
            ':loan_id' => $loan_id
        ]);

        // =========== INSERT REPAYMENT ==================
        $stmtRepay = $con->prepare("
            INSERT INTO fudscoops_loan_repayments 
            (member_id, loan_id, loan_repayment_amount, loan_repayment_date)
            VALUES (:member_id, :loan_id, :repayment, NOW())
        ");

        $stmtRepay->execute([
            ':member_id' => $memberId,
            ':loan_id' => $loan_id,
            ':repayment' => $repaymentAmount
        ]);

        $inserted++;
        $status = "Loan inserted successfully";
        $statusClass = "text-success";
    }

    // ============== TABLE ROW OUTPUT =====================
    $resultTable .= "
        <tr>
            <td>$i</td>
            <td>$spNo</td>
            <td>$loanAmount</td>
            <td>$repaymentAmount</td>
            <td>$loanTenor</td>
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
    $('#loanTable').DataTable({
        paging: true,
        searching: true,
        ordering: true,
        responsive: true
    });
});
</script>
";
?>
