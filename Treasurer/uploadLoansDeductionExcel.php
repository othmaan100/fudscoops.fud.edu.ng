<?php
require_once "../config/classes/DB.php";
require_once "../config/classes/User.php";
require_once "../config/classes/MemberG3.php";

$db = new DB();
$user = new User();
$member = new MemberG3();

// Only the Treasurer may upload loans or deductions
if (!isset($_SESSION['access_level']) || (int) $_SESSION['access_level'] !== 3) {
    echo '<div class="alert alert-danger">Only the Treasurer can upload this file. Please log in again.</div>';
    exit();
}

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
     
    // Corrected date handling (strict: 13/40/2026 must not roll over into another month)
    $date = DateTime::createFromFormat('m/d/Y', $mainDate);
    $dateParts = explode('/', $mainDate);
    if ($date === false || count($dateParts) !== 3 || !checkdate((int) $dateParts[0], (int) $dateParts[1], (int) $dateParts[2])) {
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
    $employeeId = $user->getEmployeeId($spNo);
    $memberId = empty($employeeId) ? 0 : $user->getMemberId($employeeId);

    if (empty($memberId) || empty($formatted_date)) {
        $resultTable .= "
            <tr>
                <td>$i</td>
                <td>$spNo</td>
                <td></td>
                <td>$mainDate</td>
                <td>Invalid</td>
                <td class='text-danger'>Staff number not found or not a member</td>
            </tr>";
        $skipped++;
        $i++;
        continue;
    }

    // ================== FIND THE LOAN AND RECORD THE DEDUCTION =====================
    // Deductions go to the member's open (paid out, not fully repaid) loan of this type.
    // Column 3 (optional) is the amount actually deducted; blank = the full monthly instalment.
    $loanStatus = "";
    $repaymentAmount = "";
    $stmtCheck = $con->prepare("SELECT l.loan_id FROM fudscoops_loan l
         WHERE l.member_id = :member_id AND l.loan_type_id = :type AND l.loan_status IN ('Disbursed', 'Approved')
         ORDER BY l.loan_id ASC LIMIT 1");
    $stmtCheck->execute([':member_id' => $memberId, ':type' => $loanTypeId]);
    $loan_id = $stmtCheck->fetchColumn();

    if ($loan_id) {
        $monthly = round((float) $member->getRepaymentAmount($loan_id, $memberId), 2);
        $fileAmount = isset($data[2]) ? preg_replace('/[^0-9.]/', '', $data[2]) : '';
        $repaymentAmount = ($fileAmount !== '' && is_numeric($fileAmount) && (float) $fileAmount > 0) ? round((float) $fileAmount, 2) : $monthly;

        // Same rules as the single-deduction page: one deduction per loan per month (re-uploading a month
        // updates it), and the loan is marked fully repaid when the deductions reach the total
        $before = $con->prepare("SELECT COUNT(*) FROM fudscoops_loan_repayments WHERE loan_id = :id AND status = 1 AND COALESCE(is_deleted, 0) = 0
                                 AND DATE_FORMAT(loan_repayment_date, '%Y-%m') = DATE_FORMAT(:d, '%Y-%m')");
        $before->execute([':id' => $loan_id, ':d' => $formatted_date]);
        $existed = $before->fetchColumn() > 0;

        if ($member->saveRepayment($loan_id, $memberId, $repaymentAmount, $formatted_date, $monthly) === 1) {
            $existed ? $updated++ : $inserted++;
            $status = $existed ? "Deduction updated" : "Deduction recorded";
            $statusClass = $existed ? "text-success" : "text-info";
            $st = $con->prepare("SELECT loan_status FROM fudscoops_loan WHERE loan_id = :id");
            $st->execute([':id' => $loan_id]);
            if ($st->fetchColumn() === 'Repaid') {
                $loanStatus = "Fully repaid";
                $status .= " – loan fully repaid";
            } else {
                $loanStatus = "Repaying";
            }
        } else {
            $skipped++;
            $status = "Could not record the deduction";
            $statusClass = "text-danger";
        }
    } else {
        // No loan of this type being repaid
        $skipped++;
        $status = "No loan of this type being repaid";
        $statusClass = "text-danger";
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
    $inserted deductions recorded<br>
    $updated deductions updated<br>
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
