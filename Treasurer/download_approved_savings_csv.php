<?php
require_once('../config/classes/SavingsG1.php');
$savings = new SavingsG1();
$data = $savings->getChairmanApprovedSavingsUpdates();

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="approved_savings_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Staff No', 'Name', 'Proposed Amount', 'Department']);

foreach ($data as $row) {
    fputcsv($output, [
        $row['sp_no'],
        $row['fname'] . ' ' . $row['lname'],
        $row['savings_update_amount'],
        $row['dept']
    ]);
}
fclose($output);
exit;
?>