<?php
// Commodity deduction CSV upload (Treasurer/commodity_deduction.php).
// Columns: Staff Number, Repayment Month (mm/dd/yyyy), Amount Deducted (blank = the monthly deduction),
// Request # (optional: which request; blank = the member's oldest being repaid). Further columns are ignored.
require_once '../config/classes/DB.php';
require_once '../config/classes/User.php';
require_once '../config/classes/CommodityLoan.php';

if (!isset($_SESSION['access_level']) || (int) $_SESSION['access_level'] !== 3) {
    echo '<div class="alert alert-danger">Only the Treasurer can upload commodity deductions. Please log in again.</div>';
    exit();
}
if (empty($_FILES['uploadFile']['tmp_name']) || strtolower(pathinfo($_FILES['uploadFile']['name'], PATHINFO_EXTENSION)) !== 'csv') {
    echo '<div class="alert alert-danger">Choose a CSV file.</div>';
    exit();
}
if ($_FILES['uploadFile']['size'] > 500 * 1024) {
    echo '<div class="alert alert-danger">The file must not be larger than 500KB.</div>';
    exit();
}

$h = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
$handle = fopen($_FILES['uploadFile']['tmp_name'], 'r');
$rows = '';
$saved = 0;
$skipped = 0;
$repaid = 0;
$line = 0;
while (($data = fgetcsv($handle, 1000, ',')) !== false) {
    $line++;
    if ($line === 1) {
        continue; // heading
    }
    $spNo = trim(isset($data[0]) ? $data[0] : '');
    $monthText = trim(isset($data[1]) ? $data[1] : '');
    $amountText = trim(isset($data[2]) ? $data[2] : '');
    $requestText = trim(isset($data[3]) ? $data[3] : ''); // optional Request # (prefilled file); later columns are ignored
    if ($spNo === '' && $monthText === '') {
        continue;
    }
    $parts = explode('/', $monthText);
    $status = '';
    $amount = '';
    if (count($parts) !== 3 || !checkdate((int) $parts[0], (int) $parts[1], (int) $parts[2])) {
        $status = 'Invalid date (use mm/dd/yyyy)';
    } elseif ($requestText !== '' && (!ctype_digit($requestText) || !($app = CommodityLoan::application((int) $requestText))
                                      || strcasecmp(trim($app['sp_no']), $spNo) !== 0)) {
        $status = 'Request #' . $requestText . ' does not belong to this staff number';
    } elseif (!($appId = $requestText !== '' ? (int) $requestText : CommodityLoan::openRequestForStaff($spNo))) {
        $status = 'No commodity being repaid by this staff number';
    } else {
        $app = CommodityLoan::application($appId);
        $amount = $amountText !== '' ? $amountText : number_format(min($app['monthly_amount'], $app['balance']), 2, '.', '');
        $date = sprintf('%04d-%02d-01', $parts[2], $parts[0]);
        $result = CommodityLoan::saveRepayment($appId, $date, $amount, $_SESSION['username']);
        if ($result === 1) {
            $saved++;
            $status = 'Recorded';
            if (CommodityLoan::application($appId)['status'] === 'repaid') {
                $repaid++;
                $status .= ' – fully repaid';
            }
        } else {
            $status = $result;
        }
    }
    if (strpos($status, 'Recorded') !== 0) {
        $skipped++;
    }
    $class = strpos($status, 'Recorded') === 0 ? 'text-success' : 'text-danger';
    $rows .= '<tr><td>' . ($line - 1) . '</td><td>' . $h($spNo) . '</td><td>' . $h($monthText) . '</td><td class="num">' . $h($amount)
           . '</td><td class="' . $class . '">' . $h($status) . '</td></tr>';
}
fclose($handle);

echo '<div class="alert alert-info"><strong>' . $saved . '</strong> deduction(s) recorded, <strong>' . $skipped . '</strong> skipped'
   . ($repaid ? ', <strong>' . $repaid . '</strong> request(s) now fully repaid' : '') . '. Reload the page to see the balances.</div>';
echo '<div class="table-responsive"><table class="table fud-table"><thead><tr><th>Row</th><th>Staff number</th><th>Month</th>'
   . '<th class="num">Amount (₦)</th><th>Result</th></tr></thead><tbody>' . $rows . '</tbody></table></div>';
