<?php
require_once '../config/classes/DB.php';
require_once '../config/classes/MemberG1.php';
require_once '../config/classes/User.php';
require_once '../config/classes/Commodity.php';

header('Content-Type: application/json');

$commodityManager = new Commodity();
$response = ['success' => false, 'message' => ''];

try {
    $action = $_REQUEST['action'] ?? '';
    $supply_id = $_POST['supply_id'] ?? '';
    
    switch ($action) {
        case 'get_supplies':
            $response = $commodityManager->getCommodityNames();
            break;
            
        case 'get_batches':
            if (!isset($_GET['supply_id'])) {
                throw new Exception('Supply ID required');
            }
            $response = $commodityManager->getCommodityBatches($_GET['supply_id']);
            break;
            
        case 'get_commodities':
            $response = $commodityManager->getActiveCommodities();
            break;
            
        case 'get_current_price':
            if (!isset($_GET['commodity_id'])) {
                throw new Exception('Commodity ID required');
            }
            $response = $commodityManager->getCurrentPrice($_GET['commodity_id']);
            break;
            
        case 'get_batch_items':
            if (!isset($_GET['batch_id'])) {
                throw new Exception('Batch ID required');
            }
            $response = $commodityManager->getBatchItems($_GET['batch_id']);
            break;
            
        case 'add_batch':
            $required = ['supply_id', 'name', 'opening_date', 'closing_date'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Missing required field: $field");
                }
            }
            
            $data = [
                'supply_id' => $_POST['supply_id'],
                'name' => $_POST['name'],
                'opening_date' => $_POST['opening_date'],
                'closing_date' => $_POST['closing_date'],
                'status' => $_POST['status'] ?? 'pending',
                'max_members' => $_POST['max_members'] ?? null,
                'max_loan_amount' => $_POST['max_loan_amount'] ?? null
            ];
            
            $batchId = $commodityManager->addBatch($data);
            $response = [
                'success' => true,
                'message' => 'Batch added successfully',
                'batch_id' => $batchId
            ];
            break;
            
        case 'add_batch_item':
            $required = ['batch_id', 'commodity_id', 'quantity', 'min_per_member', 'max_per_member'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Missing required field: $field");
                }
            }
            
            $data = [
                'batch_id' => $_POST['batch_id'],
                'commodity_id' => $_POST['commodity_id'],
                'quantity' => $_POST['quantity'],
                'min_per_member' => $_POST['min_per_member'],
                'max_per_member' => $_POST['max_per_member'],
                'price_id' => $commodityManager->getCurrentPriceId($_POST['commodity_id'])
            ];
            
            $itemId = $commodityManager->addBatchItem($data);
            $response = [
                'success' => true,
                'message' => 'Item added to batch successfully',
                'batch_item_id' => $itemId
            ];
            break;
            
        case 'remove_batch_item':
            if (!isset($_POST['batch_item_id'])) {
                throw new Exception('Batch item ID required');
            }
            
            $success = $commodityManager->removeBatchItem($_POST['batch_item_id']);
            $response = [
                'success' => $success,
                'message' => $success ? 'Item removed successfully' : 'Failed to remove item'
            ];
            break;
         case 'get_supply_items':
            $items = $commodityManager->getAllSupplyItems($supply_id);
            $response = [
                'success' => true, 
                'data' => $items
                ];
            break;
            
        default:
            $response['message'] = 'Invalid action';
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);