<?php
// Commodity supplies (Secretary/add_commodity_name.php): open a supply, change its dates, close it now.
// Officers only (2 Secretary, 3 Treasurer, 4 Chairman). Replies JSON {ok, message, id}.
require_once '../config/classes/DB.php';
require_once '../config/classes/CommodityLoan.php';

header('Content-Type: application/json');
if (!isset($_SESSION['access_level']) || !in_array((int) $_SESSION['access_level'], [2, 3, 4], true)) {
    echo json_encode(['ok' => false, 'message' => 'Please log in again.']);
    exit();
}
$action = isset($_POST['action']) ? $_POST['action'] : '';
$id = isset($_POST['supply_id']) ? (int) $_POST['supply_id'] : 0;

if ($action === 'save') {
    $result = CommodityLoan::saveSupply($id, isset($_POST['name']) ? $_POST['name'] : '',
                                        isset($_POST['opens']) ? $_POST['opens'] : '', isset($_POST['closes']) ? $_POST['closes'] : '');
    if ($result['ok']) {
        $result['message'] = $id ? 'Dates saved.' : 'Supply saved. Next, add the items members can request.';
    }
    echo json_encode($result);
} elseif ($action === 'close') {
    echo json_encode(CommodityLoan::closeSupplyNow($id)
        ? ['ok' => true, 'message' => 'The supply is closed to new requests.']
        : ['ok' => false, 'message' => 'Only a supply that is open now can be closed.']);
} elseif ($action === 'item_update' || $action === 'item_remove') {
    // An item already in a supply (Secretary/addCommodityItems.php)
    $rowId = isset($_POST['row_id']) ? (int) $_POST['row_id'] : 0;
    $result = $action === 'item_update'
        ? CommodityLoan::updateSupplyItem($rowId, isset($_POST['price']) ? $_POST['price'] : '', isset($_POST['allocated']) ? $_POST['allocated'] : '')
        : CommodityLoan::removeSupplyItem($rowId);
    echo json_encode($result === 1
        ? ['ok' => true, 'message' => $action === 'item_update' ? 'Item updated.' : 'Item removed from the supply.']
        : ['ok' => false, 'message' => $result]);
} else {
    echo json_encode(['ok' => false, 'message' => 'This page has changed. Please reload it and try again.']);
}
