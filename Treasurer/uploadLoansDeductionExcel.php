<?php
// Batch loan deduction upload (Treasurer/batch_loan_deduction.php).
// CSV columns: Staff Number, Repayment Month (mm/dd/yyyy), Amount Deducted (blank = the full monthly instalment).
// Each row goes to the member's oldest loan of the chosen type that is being repaid; a deduction for a loan already
// fully repaid is recorded on it and flagged as an overpayment. Re-uploading a month replaces that month.
require_once "../config/classes/DB.php";
require_once "../config/classes/User.php";
require_once "../config/classes/MemberG3.php";
require_once "../config/classes/LoanControls.php";

$db = new DB();
$user = new User();
$member = new MemberG3();
$h = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
$fail = function ($text) { echo '<div class="fud-notice mt-3"><i class="mdi mdi-alert-circle"></i><span>' . $text . '</span></div>'; exit(); };

// Only the Treasurer may upload loans or deductions
if (!isset($_SESSION['access_level']) || (int) $_SESSION['access_level'] !== 3) {
    $fail('Only the Treasurer can upload this file. Please log in again.');
}
$loanTypeId = isset($_POST['file_type']) ? (int) $_POST['file_type'] : 0;
$con = $db->getConnection();
$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt = $con->prepare("SELECT loan_type_name FROM fudscoops_loan_type WHERE loan_type_id = :id");
$stmt->execute([':id' => $loanTypeId]);
$loanTypeName = $stmt->fetchColumn();
if (!$loanTypeName) {
    $fail('Choose the loan type first.');
}
if (empty($_FILES['uploadFile']['tmp_name'])) {
    $fail('Choose the CSV file to upload.');
}
if (strtolower(pathinfo($_FILES['uploadFile']['name'], PATHINFO_EXTENSION)) !== 'csv') {
    $fail('Only CSV files can be uploaded. Save the spreadsheet as CSV (comma delimited) and try again.');
}
if ($_FILES['uploadFile']['size'] > 500 * 1024) {
    $fail('The file is larger than 500KB. Split it into smaller files.');
}
$tmpFile = $_FILES['uploadFile']['tmp_name'];

// Keep a copy of the file (safe name: date, time and the original name without its folder)
$copyName = date('Ymd_His') . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', basename($_FILES['uploadFile']['name']));
@copy($tmpFile, '../resources/uploads/' . $copyName);

// =================== PROCESS CSV =====================
$handle = fopen($tmpFile, 'r');
$line = 0;
$rows = [];
$count = ['recorded' => 0, 'updated' => 0, 'skipped' => 0, 'repaid' => 0, 'overpaid' => 0];
$monthTotals = []; // loan|month => amount (a month repeated in the file replaces the earlier row)
$findLoan = $con->prepare("SELECT l.loan_id FROM fudscoops_loan l
                           WHERE l.member_id = :member_id AND l.loan_type_id = :type AND l.loan_status IN ('Disbursed', 'Approved')
                           ORDER BY l.loan_id ASC LIMIT 1");
$findRepaid = $con->prepare("SELECT l.loan_id FROM fudscoops_loan l
                             WHERE l.member_id = :member_id AND l.loan_type_id = :type AND l.loan_status = 'Repaid' ORDER BY l.loan_id DESC LIMIT 1");
$monthExists = $con->prepare("SELECT COUNT(*) FROM fudscoops_loan_repayments WHERE loan_id = :id AND status = 1 AND COALESCE(is_deleted, 0) = 0
                              AND DATE_FORMAT(loan_repayment_date, '%Y-%m') = DATE_FORMAT(:d, '%Y-%m')");
$staffName = $con->prepare("SELECT TRIM(CONCAT_WS(' ', title, fname, lname, oname)) FROM employee WHERE sp_no = :sp LIMIT 1");

while (($data = fgetcsv($handle, 1000, ',')) !== false) {
    $line++;
    if ($line === 1) {
        continue; // heading
    }
    $spNo = trim(isset($data[0]) ? $data[0] : '');
    $monthText = trim(isset($data[1]) ? $data[1] : '');
    $amountText = trim(isset($data[2]) ? $data[2] : '');
    if ($spNo === '' && $monthText === '' && $amountText === '') {
        continue; // empty line
    }
    $row = ['line' => $line, 'sp' => $spNo, 'name' => '', 'loan' => '', 'loan_id' => 0, 'month' => $monthText, 'amount' => null,
            'source' => '', 'balance' => null, 'result' => 'skipped', 'message' => '', 'notes' => []];
    $staffName->execute([':sp' => $spNo]);
    $row['name'] = (string) $staffName->fetchColumn();

    // Date: strict mm/dd/yyyy (13/40/2026 must not roll over into another month)
    $parts = explode('/', $monthText);
    if (count($parts) !== 3 || !checkdate((int) $parts[0], (int) $parts[1], (int) $parts[2])) {
        $row['message'] = 'Invalid date – use mm/dd/yyyy';
        $rows[] = $row;
        $count['skipped']++;
        continue;
    }
    $date = sprintf('%04d-%02d-%02d', $parts[2], $parts[0], $parts[1]);
    $row['month'] = date('F Y', strtotime($date));

    $employeeId = $user->getEmployeeId($spNo);
    $memberId = empty($employeeId) ? 0 : $user->getMemberId($employeeId);
    if (empty($memberId)) {
        $row['message'] = 'Staff number not found or not a member';
        $rows[] = $row;
        $count['skipped']++;
        continue;
    }

    $findLoan->execute([':member_id' => $memberId, ':type' => $loanTypeId]);
    $loanId = (int) $findLoan->fetchColumn();
    $afterRepaid = false;
    if (!$loanId) {
        $findRepaid->execute([':member_id' => $memberId, ':type' => $loanTypeId]);
        $loanId = (int) $findRepaid->fetchColumn();
        $afterRepaid = (bool) $loanId;
    }
    if (!$loanId) {
        $row['message'] = 'No ' . $loanTypeName . ' being repaid';
        $rows[] = $row;
        $count['skipped']++;
        continue;
    }
    $row['loan_id'] = $loanId;
    $row['loan'] = $loanTypeName . ' #' . $loanId;

    $monthly = round((float) $member->getRepaymentAmount($loanId, $memberId), 2);
    $fileAmount = preg_replace('/[^0-9.]/', '', $amountText);
    if ($fileAmount !== '' && is_numeric($fileAmount) && (float) $fileAmount > 0) {
        $row['amount'] = round((float) $fileAmount, 2);
        $row['source'] = 'file';
    } else {
        $row['amount'] = $monthly;
        $row['source'] = 'instalment';
    }

    $monthExists->execute([':id' => $loanId, ':d' => $date]);
    $existed = $monthExists->fetchColumn() > 0;
    if ($member->saveRepayment($loanId, $memberId, $row['amount'], $date, $monthly, $afterRepaid) !== 1) {
        $row['message'] = 'Could not record the deduction';
        $rows[] = $row;
        $count['skipped']++;
        continue;
    }
    $row['result'] = $existed ? 'updated' : 'recorded';
    $row['message'] = $existed ? 'Month updated' : 'Recorded';
    $count[$row['result']]++;
    $monthTotals[$loanId . '|' . substr($date, 0, 7)] = $row['amount'];

    $loan = $con->query("SELECT loan_status, total_payable, amount_recommended, loan_amount,
                                (SELECT COALESCE(SUM(amount_paid), 0) FROM fudscoops_loan_repayments
                                  WHERE loan_id = " . (int) $loanId . " AND status = 1 AND COALESCE(is_deleted, 0) = 0) repaid
                         FROM fudscoops_loan WHERE loan_id = " . (int) $loanId)->fetch(PDO::FETCH_ASSOC);
    $payable = (float) ($loan['total_payable'] ?: ($loan['amount_recommended'] ?: $loan['loan_amount']));
    $row['balance'] = max(0, $payable - $loan['repaid']);
    if ($afterRepaid) {
        $row['notes'][] = ['Loan was already repaid', 'wait'];
    } elseif ($loan['loan_status'] === 'Repaid') {
        $row['notes'][] = ['Now fully repaid', 'ok'];
        $count['repaid']++;
    }
    foreach (LoanControls::overpayments(null, $memberId) as $op) {
        if ((int) $op['loan_id'] === $loanId) {
            $row['notes'][] = ['Overpaid ₦' . number_format($op['amount'], 2) . ' – flagged', 'no'];
            $count['overpaid']++;
        }
    }
    $rows[] = $row;
}
fclose($handle);

if (!$rows) {
    $fail('The file has no rows under the heading line.');
}
$total = array_sum($monthTotals);
$problems = $count['skipped'];
?>
<div class="fud-upload-result mt-4" id="uploadResult">
  <div class="fud-notice <?php echo $problems ? '' : 'success'; ?>">
    <i class="mdi <?php echo $problems ? 'mdi-alert-circle' : 'mdi-check-circle'; ?>"></i>
    <span><strong><?php echo $h($_FILES['uploadFile']['name']); ?></strong> (<?php echo $h($loanTypeName); ?>):
      <?php echo count($rows); ?> row(s) read, ₦<?php echo number_format($total, 2); ?> recorded<?php echo $problems ? ', ' . $problems . ' row(s) need attention' : ''; ?>.</span>
  </div>

  <div class="fud-stats">
    <div class="fud-stat"><div class="fud-stat-icon fud-green"><i class="mdi mdi-check-circle"></i></div>
      <div><div class="fud-stat-label">Recorded</div><div class="fud-stat-value"><?php echo $count['recorded']; ?></div><div class="fud-stat-sub">new months</div></div></div>
    <div class="fud-stat"><div class="fud-stat-icon fud-blue"><i class="mdi mdi-refresh"></i></div>
      <div><div class="fud-stat-label">Updated</div><div class="fud-stat-value"><?php echo $count['updated']; ?></div><div class="fud-stat-sub">month uploaded again</div></div></div>
    <div class="fud-stat"><div class="fud-stat-icon fud-purple"><i class="mdi mdi-check-decagram"></i></div>
      <div><div class="fud-stat-label">Now fully repaid</div><div class="fud-stat-value"><?php echo $count['repaid']; ?></div></div></div>
    <div class="fud-stat"><div class="fud-stat-icon <?php echo $problems ? 'fud-rose' : 'fud-green'; ?>"><i class="mdi mdi-alert-circle"></i></div>
      <div><div class="fud-stat-label">Not recorded</div><div class="fud-stat-value"><?php echo $problems; ?></div>
        <div class="fud-stat-sub"><?php echo $count['overpaid'] ? $count['overpaid'] . ' overpayment(s) flagged' : 'rows with a problem'; ?></div></div></div>
  </div>

  <div class="fud-section">
    <div class="fud-section-head"><h5>Row by row</h5>
      <?php if ($count['overpaid']) { ?><a href="overpayments.php">Overpayments <i class="mdi mdi-arrow-right"></i></a><?php } ?></div>
    <div class="fud-section-body">
      <div class="fud-tabs" id="resultFilter">
        <a href="#" class="active" data-filter="all">All <span><?php echo count($rows); ?></span></a>
        <a href="#" data-filter="problem">Not recorded <span><?php echo $problems; ?></span></a>
        <a href="#" data-filter="recorded">Recorded <span><?php echo $count['recorded']; ?></span></a>
        <a href="#" data-filter="updated">Updated <span><?php echo $count['updated']; ?></span></a>
        <a href="#" data-filter="flag">Repaid or overpaid <span><?php echo $count['repaid'] + $count['overpaid']; ?></span></a>
      </div>
      <div class="table-responsive">
        <table class="table fud-table" id="uploadRows">
          <thead><tr><th>Row</th><th>Member</th><th>Loan</th><th>Month</th><th class="num">Amount (₦)</th><th class="num">Balance after (₦)</th><th>Result</th></tr></thead>
          <tbody>
          <?php foreach ($rows as $r) {
              $tags = [$r['result'] === 'skipped' ? 'problem' : $r['result']];
              if ($r['notes']) { $tags[] = 'flag'; }
              $pill = $r['result'] === 'skipped' ? 'no' : 'ok'; ?>
            <tr data-tags="<?php echo implode(' ', $tags); ?>" class="<?php echo $r['result'] === 'skipped' ? 'fud-row-problem' : ''; ?>">
              <td class="text-muted"><?php echo (int) $r['line']; ?></td>
              <td><?php echo $r['name'] !== '' ? $h($r['name']) : '<span class="text-muted">Unknown</span>'; ?><div class="text-muted small"><?php echo $h($r['sp'] !== '' ? $r['sp'] : '(blank)'); ?></div></td>
              <td><?php echo $r['loan_id'] ? '<a href="view_loan_deduction_details.php?loan_id=' . (int) $r['loan_id'] . '">' . $h($r['loan']) . '</a>' : '–'; ?></td>
              <td><?php echo $h($r['month']); ?></td>
              <td class="num"><?php echo $r['amount'] !== null ? number_format($r['amount'], 2) : '–'; ?>
                <?php if ($r['source'] === 'instalment') { ?><div class="text-muted small">monthly instalment</div><?php } ?></td>
              <td class="num"><?php echo $r['balance'] !== null ? number_format($r['balance'], 2) : '–'; ?></td>
              <td><span class="fud-pill <?php echo $pill; ?>"><?php echo $h($r['message']); ?></span>
                <?php foreach ($r['notes'] as $n) { ?><div><span class="fud-pill <?php echo $n[1]; ?>"><?php echo $h($n[0]); ?></span></div><?php } ?></td>
            </tr>
          <?php } ?>
          </tbody>
        </table>
      </div>
      <p class="text-muted small mb-0 mt-2">Correct any rows that were not recorded in the file and upload it again; months already recorded are simply updated.</p>
    </div>
  </div>
</div>
<script>
(function () {
    var filter = document.getElementById('resultFilter');
    if (!filter) { return; }
    filter.addEventListener('click', function (e) {
        var link = e.target.closest('a[data-filter]');
        if (!link) { return; }
        e.preventDefault();
        filter.querySelectorAll('a').forEach(function (a) { a.classList.toggle('active', a === link); });
        var f = link.getAttribute('data-filter');
        document.querySelectorAll('#uploadRows tbody tr').forEach(function (tr) {
            tr.style.display = f === 'all' || (' ' + tr.getAttribute('data-tags') + ' ').indexOf(' ' + f + ' ') !== -1 ? '' : 'none';
        });
    });
    document.getElementById('uploadResult').scrollIntoView({ behavior: 'smooth' });
})();
</script>
