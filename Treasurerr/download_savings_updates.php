<?php
// Download the savings update requests awaiting the treasurer, for offline processing.
// The Approved Amount column is pre-filled with the requested amount; the treasurer edits it
// (or clears it to leave a request pending) and uploads the file on treasurer_view_approved_savings.php
require_once('../config/classes/User.php');
require_once('../config/classes/SavingsG1.php');

if (!User::is_authenticated('../auth/') || !User::isBur('../auth/logout.php')) {
    exit();
}

$savings = new SavingsG1();
$requests = $savings->getChairmanApprovedSavingsUpdates();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="savings_update_requests_' . date('Y-m-d') . '.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, SavingsG1::treasurerSavingsCsvHeader());
foreach ($requests as $request) {
    fputcsv($out, [
        $request['id'],
        $request['sp_no'],
        preg_replace('/\s+/', ' ', trim($request['fname'] . ' ' . $request['oname'] . ' ' . $request['lname'])),
        $request['dept'],
        date('d/m/Y', strtotime($request['update_proposed_date'])),
        $request['proposed_monthly_savings'],
        $request['savings_update_amount'],
        $request['savings_update_amount'],
    ]);
}
fclose($out);
exit();
