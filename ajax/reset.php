<?php
// Password reset for members and applicants (Treasurer/password.php, Chairman/password.php).
// Officers only (2 Secretary, 3 Treasurer, 4 Chairman). The password becomes the staff number; officer accounts
// cannot be reset here (User::resetPassword).
//   action=lookup  -> JSON {ok, name, dept, role, can_reset} or {ok:false, message}
//   action=reset   -> JSON {ok, message}
//   no action      -> 1 reset, 0 not found, -2 officer account, -1 error (older pages)
require_once '../config/classes/DB.php';
require_once '../config/classes/User.php';
$db = new DB();
$user = new User();

$action = isset($_POST['action']) ? $_POST['action'] : '';
if (!isset($_SESSION['access_level']) || !in_array((int) $_SESSION['access_level'], [2, 3, 4], true)) {
    echo $action === '' ? 'Please log in again.' : json_encode(['ok' => false, 'message' => 'Please log in again.']);
    exit();
}
$username = strtoupper(trim($db->cleanData(isset($_POST['username']) ? $_POST['username'] : '')));

if ($action === 'lookup') {
    header('Content-Type: application/json');
    $info = $username === '' ? null : $user->passwordResetLookup($username);
    echo json_encode($info ? ['ok' => true] + $info : ['ok' => false, 'message' => 'No login was found for ' . $username . '. Check the staff number.']);
} elseif ($action === 'reset') {
    header('Content-Type: application/json');
    $result = $user->resetPassword($username);
    if ($result === 1) {
        error_log('Password reset: ' . $username . ' by ' . $_SESSION['username']);
    }
    $messages = [1 => 'Password reset. ' . $username . ' can now log in with their staff number as the password, and should change it.',
                 0 => 'No login was found for ' . $username . '.',
                 -2 => 'This is an officer account; it cannot be reset here.',
                 -1 => 'The password could not be reset. Please try again.'];
    echo json_encode(['ok' => $result === 1, 'message' => $messages[$result]]);
} else {
    echo $user->resetPassword($username);
}
