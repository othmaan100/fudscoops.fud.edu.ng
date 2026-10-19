<?php
// Savings withdrawal upload (Treasurer/uploadWithdrawals.php). Each row is the Treasurer's approval of one withdrawal,
// made through MemberG1::treasurerApproveWithdrawal like a single approval (minimum balance, not already decided;
// the request goes to the Chairman, status 1).
// Columns: 0 Withdrawal ID (blank = paper application: a new request is recorded), 1 Staff Number, 2 Full Name,
//          3 Bank (name), 4 Account Number, 5 Account Name, 6 Approved Withdrawal Amount. Later columns are ignored.
require_once "../config/classes/DB.php";
require_once "../config/classes/User.php";
require_once "../config/classes/MemberG1.php";
require_once "../config/classes/LoanControls.php";

if (!isset($_SESSION['access_level'], $_SESSION['username']) || (int) $_SESSION['access_level'] !== 3) {
    echo '<div class="alert alert-danger">Only the Treasurer can upload withdrawals. Please log in again.</div>';
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

$user = new User();
$members = new MemberG1();
$con = (new DB())->getConnection();
$h = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
$banks = [];
foreach ($con->query("SELECT id, name FROM banks") as $b) {
    $banks[strtolower(trim($b['name']))] = $b['id'];
}

$handle = fopen($_FILES['uploadFile']['tmp_name'], 'r');
$line = 0;
$rows = '';
$count = ['approved' => 0, 'new' => 0, 'skipped' => 0];
while (($data = fgetcsv($handle, 2000, ',')) !== false) {
    $line++;
    if ($line === 1) {
        continue; // heading
    }
    $cell = function ($i) use ($data) { return isset($data[$i]) ? trim($data[$i]) : ''; };
    $id = $cell(0);
    $spNo = $cell(1);
    $name = $cell(2);
    $bank = $cell(3);
    $account = preg_replace('/\D/', '', $cell(4));
    $accountName = $cell(5);
    $amount = $cell(6);
    if ($id === '' && $spNo === '' && $amount === '') {
        continue; // blank row
    }
    // Spreadsheets drop leading zeros from account numbers
    if ($account !== '' && strlen($account) < 10) {
        $account = str_pad($account, 10, '0', STR_PAD_LEFT);
    }
    $ok = false;
    $result = '';

    if ($id !== '') {
        // Online request: approve it
        if (!ctype_digit($id)) {
            $result = 'Invalid Withdrawal ID';
        } else {
            $stmt = $con->prepare("SELECT w.withrawals_id, e.sp_no FROM fudscoops_withrawals w JOIN fudscoops_member m ON m.member_id = w.member_id
                                   JOIN employee e ON e.employee_id = m.employee_id WHERE w.withrawals_id = :id AND COALESCE(w.withrawals_is_deleted, 0) = 0");
            $stmt->execute([':id' => (int) $id]);
            $w = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$w) {
                $result = 'Withdrawal ID not found';
            } elseif ($spNo !== '' && strcasecmp(trim($w['sp_no']), $spNo) !== 0) {
                $result = 'Withdrawal #' . (int) $id . ' belongs to ' . $w['sp_no'] . ', not ' . $spNo;
            } else {
                $r = $members->treasurerApproveWithdrawal((int) $id, $amount, null, $_SESSION['username']);
                $ok = $r === 1;
                $result = $ok ? 'Approved – sent to the Chairman' : ($r === -1 ? 'Could not save' : $r);
                $count['approved'] += $ok ? 1 : 0;
            }
        }
    } else {
        // Paper application: record the request, then approve it
        $employeeId = $spNo !== '' ? $user->getEmployeeId($spNo) : null;
        $memberId = $employeeId ? $user->getMemberId($employeeId) : null;
        $amountValue = preg_replace('/[^0-9.]/', '', $amount);
        if ($spNo === '' || $account === '' || $accountName === '') {
            $result = 'Staff number, account number and account name are needed';
        } elseif (!$memberId) {
            $result = 'No member with this staff number';
        } elseif (strlen($account) !== 10) {
            $result = 'The account number must have 10 digits';
        } elseif ($amountValue === '' || (float) $amountValue <= 0) {
            $result = 'Enter the approved amount';
        } else {
            $stmt = $con->prepare("SELECT withrawals_id FROM fudscoops_withrawals WHERE member_id = :m AND chairman_approval = 0 AND status IN (0, 1)
                                     AND COALESCE(withrawals_is_deleted, 0) = 0 ORDER BY withrawals_id DESC LIMIT 1");
            $stmt->execute([':m' => $memberId]);
            if ($pending = $stmt->fetchColumn()) {
                $result = 'Already has request #' . sprintf('%04d', $pending) . ' waiting; put that Withdrawal ID in the row instead';
            } elseif ($limit = LoanControls::withdrawalLimitError($memberId, $amountValue)) {
                $result = $limit;
            } else {
                // DB opens a new connection per object, so no transaction: if the approval fails the new row is removed
                $bankId = isset($banks[strtolower($bank)]) ? $banks[strtolower($bank)] : $bank;
                $con->prepare("INSERT INTO fudscoops_withrawals (member_id, proposed_withrawal_amount, approved_withrawal_amount, withdrawal_type_id, proposed_date,
                                                                 approved_date, bank_id, account_name, acount_number, status, chairman_approval)
                               VALUES (:m, :a, 0, 0, NOW(), NOW(), :b, :n, :acc, 0, 0)")
                    ->execute([':m' => $memberId, ':a' => round((float) $amountValue, 2), ':b' => $bankId, ':n' => $accountName, ':acc' => $account]);
                $newId = (int) $con->lastInsertId();
                $r = $members->treasurerApproveWithdrawal($newId, $amountValue, 'Paper application (uploaded)', $_SESSION['username']);
                if ($r === 1) {
                    $ok = true;
                    $id = (string) $newId;
                    $result = 'Recorded as #' . sprintf('%04d', $newId) . ' and approved – sent to the Chairman';
                    $count['new']++;
                } else {
                    $con->prepare("DELETE FROM fudscoops_withrawals WHERE withrawals_id = :id")->execute([':id' => $newId]);
                    $result = $r === -1 ? 'Could not save' : $r;
                }
            }
        }
    }
    $count['skipped'] += $ok ? 0 : 1;
    $rows .= '<tr><td>' . ($line - 1) . '</td><td>' . ($id !== '' ? sprintf('%04d', (int) $id) : '–') . '</td><td>' . $h($spNo) . '</td><td style="white-space:normal">' . $h($name) . '</td>'
           . '<td style="white-space:normal">' . $h($bank) . '<div class="small text-muted">' . $h($account) . ($accountName !== '' ? ' · ' . $h($accountName) : '') . '</div></td>'
           . '<td class="num">' . $h($amount) . '</td><td class="' . ($ok ? 'text-success' : 'text-danger') . '" style="white-space:normal">' . $h($result) . '</td></tr>';
}
fclose($handle);

if ($rows === '') {
    echo '<div class="alert alert-warning">The file has no rows under the heading.</div>';
    exit();
}
echo '<div class="alert alert-' . ($count['skipped'] ? 'warning' : 'success') . '"><strong>' . $count['approved'] . '</strong> request(s) approved, <strong>' . $count['new']
   . '</strong> paper application(s) recorded and approved, <strong>' . $count['skipped'] . '</strong> skipped. Approved requests are now with the Chairman.</div>';
echo '<div class="table-responsive"><table class="table fud-table"><thead><tr><th>Row</th><th>ID</th><th>Staff number</th><th>Name</th><th>Pay into</th>'
   . '<th class="num">Amount</th><th>Result</th></tr></thead><tbody>' . $rows . '</tbody></table></div>';
