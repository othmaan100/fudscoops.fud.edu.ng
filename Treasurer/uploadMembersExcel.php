<?php
// Members upload (Treasurer/upload.php). POST uploadFile (CSV) and mode=check | import.
//   check:  reads the file and reports what would happen to every row; nothing is saved.
//   import: saves the rows that pass the check (config/classes/MemberUpload.php) and keeps a copy of the file.
// Replies JSON {ok, message, mode, summary, rows: [...]}.
require_once('../config/classes/DB.php');
require_once('../config/classes/User.php');
require_once('../config/classes/MemberUpload.php');

header('Content-Type: application/json');
if (!isset($_SESSION['access_level'], $_SESSION['username']) || (int) $_SESSION['access_level'] !== 3) {
    echo json_encode(['ok' => false, 'message' => 'Only the Treasurer can upload members. Please log in again.']);
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
if ($file['size'] > 1024 * 1024) {
    echo json_encode(['ok' => false, 'message' => 'The file is larger than 1MB. Split it into smaller files.']);
    exit();
}

$read = MemberUpload::readCsv($file['tmp_name']);
if ($read['error']) {
    echo json_encode(['ok' => false, 'message' => $read['error']]);
    exit();
}
$plan = MemberUpload::plan($read['rows']);
$summary = MemberUpload::summary($plan);

if ($mode === 'import') {
    $plan = MemberUpload::import($plan, $_SESSION['username']);
    $summary['saved'] = count(array_filter($plan, function ($p) { return $p['saved']; }));
    // Keep a copy of what was imported
    $dir = '../resources/uploads/';
    if (is_dir($dir) && is_writable($dir)) {
        @copy($file['tmp_name'], $dir . date('Ymd_His') . '_members_' . preg_replace('/[^A-Za-z0-9._-]/', '_', basename($file['name'])));
    }
    $message = $summary['saved'] . ' member' . ($summary['saved'] === 1 ? '' : 's') . ' saved.';
} else {
    $ready = $summary['create'] + $summary['approve'];
    $message = $ready ? $ready . ' row' . ($ready === 1 ? ' is' : 's are') . ' ready to import.' : 'No row can be imported. Correct the file and check it again.';
}

$rows = array_map(function ($p) {
    return ['line' => $p['line'], 'staff' => $p['staff'], 'name' => $p['name'], 'file_name' => $p['file_name'], 'dept' => $p['dept'],
            'action' => $p['action'], 'monthly' => $p['monthly'], 'opening' => $p['add_opening'] ? $p['opening'] : 0, 'opening_in' => $p['opening_in'], 'as_at' => $p['as_at'],
            'errors' => $p['errors'], 'warnings' => $p['warnings'], 'saved' => isset($p['saved']) ? $p['saved'] : null,
            'result' => isset($p['result']) ? $p['result'] : null];
}, $plan);
echo json_encode(['ok' => true, 'mode' => $mode, 'message' => $message, 'summary' => $summary, 'rows' => $rows,
                  'old_template' => !in_array('as_at', $read['columns'], true) && in_array('opening', $read['columns'], true)]);
