<?php
// Staff list upload (Treasurer/upload_staff.php). POST uploadFile (CSV) and mode=check | import.
//   check:  reports what would happen to every row; nothing is saved.
//   import: saves the rows that pass (config/classes/StaffUpload.php) and keeps a copy of the file.
// Replies JSON {ok, message, mode, summary, rows: [...]}.
require_once('../config/classes/DB.php');
require_once('../config/classes/User.php');
require_once('../config/classes/StaffUpload.php');

header('Content-Type: application/json');
if (!isset($_SESSION['access_level'], $_SESSION['username']) || (int) $_SESSION['access_level'] !== 3) {
    echo json_encode(['ok' => false, 'message' => 'Only the Treasurer can upload the staff list. Please log in again.']);
    exit();
}
$mode = isset($_POST['mode']) && $_POST['mode'] === 'import' ? 'import' : 'check';
$file = isset($_FILES['uploadFile']) ? $_FILES['uploadFile'] : null;
if (!$file || $file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
    echo json_encode(['ok' => false, 'message' => 'Choose the CSV file first.']);
    exit();
}
if (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'csv') {
    echo json_encode(['ok' => false, 'message' => 'Only .csv files can be uploaded. In Excel use File › Save As › CSV (Comma delimited).']);
    exit();
}
if ($file['size'] > 2 * 1024 * 1024) {
    echo json_encode(['ok' => false, 'message' => 'The file is larger than 2MB. Split it into smaller files.']);
    exit();
}

$read = StaffUpload::readCsv($file['tmp_name']);
if ($read['error']) {
    echo json_encode(['ok' => false, 'message' => $read['error']]);
    exit();
}
$plan = StaffUpload::plan($read['rows']);
$summary = StaffUpload::summary($plan);
if ($mode === 'import') {
    $plan = StaffUpload::import($plan);
    $summary['saved'] = count(array_filter($plan, function ($p) { return $p['saved'] && $p['action'] === 'create'; }));
    $summary['codes_saved'] = count(array_filter($plan, function ($p) { return $p['saved'] && $p['action'] === 'existing'; }));
    $dir = '../resources/uploads/';
    if (is_dir($dir) && is_writable($dir)) {
        @copy($file['tmp_name'], $dir . date('Ymd_His') . '_staff_' . preg_replace('/[^A-Za-z0-9._-]/', '_', basename($file['name'])));
    }
    $message = $summary['saved'] . ' staff added to the staff list' . ($summary['codes_saved'] ? '; GIFMIS code added for ' . $summary['codes_saved'] . ' existing staff' : '') . '.';
} else {
    $message = $summary['create'] ? $summary['create'] . ' new staff ready to add.' : 'No new staff to add.';
    if ($summary['codes_added']) {
        $message .= ' ' . $summary['codes_added'] . ' existing staff will get their GIFMIS code.';
    }
}
$rows = array_map(function ($p) {
    return ['line' => $p['line'], 'staff' => $p['staff'] ?: $p['input']['staff'], 'name' => $p['name']['full'], 'file_name' => $p['input']['name'],
            'existing_name' => $p['existing_name'], 'code' => $p['code'], 'doa' => $p['doa'], 'action' => $p['action'], 'add_code' => $p['add_code'],
            'login' => $p['login'], 'errors' => $p['errors'], 'warnings' => $p['warnings'], 'input' => $p['input'],
            'saved' => isset($p['saved']) ? $p['saved'] : null, 'result' => isset($p['result']) ? $p['result'] : null];
}, $plan);
echo json_encode(['ok' => true, 'mode' => $mode, 'message' => $message, 'summary' => $summary, 'rows' => $rows]);
