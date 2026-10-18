<?php
// Commodity request workflow (config/classes/CommodityLoan.php). Replies 1 on success, otherwise a message.
require_once '../config/classes/DB.php';
require_once '../config/classes/User.php';
require_once '../config/classes/CommodityLoan.php';
$user = new User();

if (!isset($_SESSION['access_level'], $_SESSION['username'])) {
    echo 'Your session has expired. Please log in again.';
    exit();
}
$level = (int) $_SESSION['access_level'];
$action = isset($_POST['action']) ? $_POST['action'] : '';
$appId = isset($_POST['application_id']) ? (int) $_POST['application_id'] : 0;
$comment = isset($_POST['comment']) ? $_POST['comment'] : '';

// Who may do what: 1 member, 3 Treasurer, 4 Chairman
$roles = [
    'submit_request'      => 1,
    'submit_special'      => 1,
    'treasurer_recommend' => 3,
    'treasurer_decline'   => 3,
    'record_supply'       => 3,
    'save_repayment'      => 3,
    'chairman_authorize'  => 4,
    'chairman_decline'    => 4,
    'chairman_return'     => 4,
];
if (!isset($roles[$action])) {
    echo 'This page has changed. Please reload it and try again.';
    exit();
}
if ($roles[$action] !== $level) {
    echo 'You are not allowed to do this.';
    exit();
}

switch ($action) {
    case 'submit_request':
        $memberId = $user->getMemberId($user->getEmployeeId($_SESSION['username']));
        echo CommodityLoan::submitRequest($memberId, $_SESSION['username'], isset($_POST['supply_id']) ? (int) $_POST['supply_id'] : 0,
                                          isset($_POST['qty']) && is_array($_POST['qty']) ? $_POST['qty'] : [],
                                          isset($_POST['tenor']) ? $_POST['tenor'] : '');
        break;
    case 'submit_special':
        // items_json: [{name, desc, qty, price}, ...]
        $memberId = $user->getMemberId($user->getEmployeeId($_SESSION['username']));
        $items = json_decode(isset($_POST['items_json']) ? (string) $_POST['items_json'] : '[]', true);
        echo CommodityLoan::submitSpecialRequest($memberId, is_array($items) ? $items : [], isset($_POST['tenor']) ? $_POST['tenor'] : '');
        break;
    case 'treasurer_recommend':
    case 'treasurer_decline':
        echo CommodityLoan::treasurerReview($appId, isset($_POST['qty']) && is_array($_POST['qty']) ? $_POST['qty'] : [],
                                            isset($_POST['tenor']) ? $_POST['tenor'] : '', $comment,
                                            $action === 'treasurer_recommend', $_SESSION['username'],
                                            isset($_POST['price']) && is_array($_POST['price']) ? $_POST['price'] : []);
        break;
    case 'record_supply':
        echo CommodityLoan::recordSupply($appId, isset($_POST['date']) ? $_POST['date'] : '', isset($_POST['note']) ? $_POST['note'] : '', $_SESSION['username']);
        break;
    case 'save_repayment':
        $month = isset($_POST['month']) ? $_POST['month'] . '-01' : '';
        echo CommodityLoan::saveRepayment($appId, $month, isset($_POST['amount']) ? $_POST['amount'] : '', $_SESSION['username']);
        break;
    case 'chairman_authorize':
    case 'chairman_decline':
        echo CommodityLoan::chairmanDecision($appId, $action === 'chairman_authorize', $comment);
        break;
    case 'chairman_return':
        echo CommodityLoan::chairmanReturn($appId);
        break;
}
