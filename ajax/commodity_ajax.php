<?php
require_once '../config/classes/DB.php';
    require_once '../config/classes/MemberG1.php';
    require_once '../config/classes/MemberG2.php';
    require_once '../config/classes/User.php';
    require_once '../config/classes/Commodity.php';
    
    $db = new DB();
    $member = new MemberG1();
    $member2 = new MemberG2();
    $user = new User();


// Create Commodity instance 
$commodity = new Commodity($db);

// Initialize response
$response = ['success' => false, 'message' => ''];

try {
    // Get action from request
    $action = $_POST['action'] ?? '';
    $supply_id = $_POST['supply_id'] ?? '';
    $itesms_request = $_POST['data'] ?? '';
    
    $application_id = (int)$_POST['application_id'] ?? '';
    $decision = $_POST['decision'] ?? '';
    $approved_quantities = $_POST['approved_quantities'] ?? [];
    $approved_total = (float)$_POST['approved_total']  ?? '';
    $admin_comments = trim($_POST['admin_comments'] ?? '');
    
    $disbursed_quantities = isset($_POST['disbursed_quantities']) && is_array($_POST['disbursed_quantities']) ? $_POST['disbursed_quantities'] : [];
    $disbursed_total = isset($_POST['disbursed_total']) ? (float)$_POST['disbursed_total'] : 0.0;
   

   
   
   
    
     $staff_info =  $user->getStaffInfoArray($_SESSION['username']);
     $employ_id =  $user->getEmployeeId($staff_info['sp_no']);
     $user_id =  $member->getMemberId($employ_id );
     
     
     

    // Process different actions
    switch ($action) {
       
         // ========== COMMODITY NAME ACTIONS ==========
         
        case 'create_commodity_name':
            $comm_name = $_POST['comm_name'] ?? '';
            $start_date = $_POST['start_date'] ?? '';
            $end_date = $_POST['end_date'] ?? '';
        
            if (empty($comm_name)) {
                $response['message'] = 'Commodity name is required';
                echo json_encode($response); // Return the response as JSON
                exit; // Stop further execution
            } else {
                $result = $commodity->createCommodityName($comm_name, $start_date, $end_date) ;
        
                if ($result['success']) {
                    // Success response
                    $response = ['success' => true, 'message' => 'Commodity Name created successfully'];
                } else {
                    // Handle only the case where commodity name exists
                    if ($result['message'] == 'commodity_name_exists') {
                        $response = ['success' => false, 'message' => 'Commodity Name already exists'];
                    } else {
                        // No message is returned for other failure cases
                        $response = ['success' => false];
                    }
                }
            }
        
            echo json_encode($response); // Ensure the response is in JSON format
            exit; // Stop further execution
       break;
       
       case 'get_commodity_name':
            $db = new DB();
            $con = $db->getConnection();
            $id = $_POST['commodity_name_id'] ?? '';
        
            if (empty($id)) {
                $response = ['success' => false, 'message' => 'Missing commodity ID.'];
            } else {
                $stmt = $con->prepare("SELECT * FROM fudscoops_commodity_name WHERE commodity_name_id = :id LIMIT 1");
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    $data = $stmt->fetch(PDO::FETCH_ASSOC);
                    $response = ['success' => true, 'data' => $data];
                } else {
                    $response = ['success' => false, 'message' => 'Commodity not found.'];
                }
            }
       break;
       
        case 'update_commodity_name':
            $id = $_POST['commodity_name_id'] ?? '';
            $name = $_POST['commodity_name'] ?? '';
        
            if (empty($id) || empty($name)) {
                $response = ['success' => false, 'message' => 'Missing fields.'];
            } else {
                $success = $commodity->updateCommodityName($id, $name);
                if ($success) {
                    $response = ['success' => true];
                } else {
                    $response = ['success' => false, 'message' => 'Update failed or name already exists.'];
                }
            }
        break;



        
         // ========== ITEM TYPE ACTIONS ==========
        
        case 'create_item_type':
            $type_name = $_POST['type_name'] ?? '';
            //$commodity_name_id = $_POST['commodity_name_id'] ?? '';
            $description = $_POST['description'] ?? '';
            
            
          $response=  $commodity->createItemType($type_name, $description);
             
            
            break;

        case 'update_item_type':
            $type_id = $_POST['type_id'] ?? 0;
            $type_name = $_POST['type_name'] ?? '';
            $description = $_POST['description'] ?? '';
            
            if (empty($type_name)) {
                $response['message'] = 'Type name is required';
            } elseif ($commodity->updateItemType($type_id, $type_name, $description)) {
                $response = ['success' => true, 'message' => 'Item type updated successfully'];
            } else {
                $response['message'] = 'Failed to update item type';
            }
            break;

        case 'delete_item_type':
            $type_id = $_POST['type_id'] ?? 0;
            $result = $commodity->deleteItemType($type_id);
            
            if (strpos($result, 'successfully') !== false) {
                $response = ['success' => true, 'message' => $result];
            } else {
                $response['message'] = $result;
            }
            break;

        case 'get_item_type':
            $type_id = $_POST['type_id'] ?? 0;
            $type = $commodity->getItemType($type_id);
            
            if ($type) {
                $response = ['success' => true, 'data' => $type];
            } else {
                $response['message'] = 'Item type not found';
            }
            break;
            
        case 'get_item_type_by_commodity':
            $commodity_id = intval($_POST['commodity_name_id'] ?? 0);
            
            if ($commodity_id > 0) {
                $types = $commodity->getItemTypesByCommodity($commodity_id); // Call method
                if (!empty($types)) {
                    $response = ['success' => true, 'data' => $types];
                } else {
                    $response = ['success' => false, 'message' => 'No types found'];
                }
            } else {
                $response = ['success' => false, 'message' => 'Invalid commodity ID'];
            }
        
        break;

        case 'get_types':
            $types = $commodity->getAllItemTypes();
            $response = ['success' => true, 'data' => $types];
            break;
            

        case 'create_item':
            
           // $commodity_name_id = intval($_POST['commodity_name_id'] ?? 0);
            $type_id = intval($_POST['type_id'] ?? 0);
            $item = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $quantity = $_POST['quantity'] ?? '';
            $unit = $_POST['unit'] ?? '';
            $max_senior_quantity = $_POST['max_senior_quantity'] ?? '';
            $max_junior_quantity = $_POST['max_junior_quantity'] ?? '';
            $is_active = $_POST['status'] ?? '';
        
            if (trim($item) === '' ||  $type_id <= 0 || trim($quantity) === '' || trim($unit) === ''  | trim($max_senior_quantity) === '' | trim($max_junior_quantity) === '' || $is_active === '') {
                $response = ['success' => false, 'message' => 'All fields are required.'];
            } else {
               // $result = $commodity->createItem($commodity_name_id, $type_id, $item, $description, $quantity, $unit, $max_senior_quantity, $max_junior_quantity, $is_active);
                 $result = $commodity->createItem($type_id, $item, $description, $quantity, $unit, $max_senior_quantity, $max_junior_quantity, $is_active);
              
                
                if (is_array($result)) {
                    $response = $result;
                } elseif ($result === true) {
                    $response = ['success' => true, 'message' => 'Item created successfully.'];
                } else {
                    $response = ['success' => false, 'message' => 'Failed to create item.'];
                }
            }
        break;




       case 'update_item':
            $id = $_POST['commodity_item_id'] ?? 0;
            $data = [
                'commodity_item' => $_POST['commodity_item'] ?? '',
                'type_id' => $_POST['type_id'] ?? 0,
                'commodity_description' => $_POST['commodity_description'] ?? '',
                'quantity' => $_POST['quantity'] ?? 0,
                'price_unit' => $_POST['price_unit'] ?? 0.00,
                'max_senior_quantity' => $_POST['max_senior_quantity'] ?? 0,
                'max_junior_quantity' => $_POST['max_junior_quantity'] ?? 0,
            ];
        
            $result = $commodity->updateItem($id, $data); // Implement this method
        
            if ($result) {
                $response= ['success' => true, 'message' => 'Item updated successfully.'];
               
            } else {
                $response= ['success' => false, 'message' => 'Failed to update item.'];
                
            }
            break;

        case 'delete_item':
            $item_id = $_POST['item_id'] ?? 0;
            $result = $commodity->deleteItem($item_id, $user_id);
            
            if (strpos($result, 'successfully') !== false) {
                $response = ['success' => true, 'message' => $result];
            } else {
                $response['message'] = $result;
            }
            break;

        case 'get_item':
            $item_id = $_POST['item_id'] ?? 0;
            $item = $commodity->getItem($item_id);
            
            if ($item) {
                $response = ['success' => true, 'data' => $item];
            } else {
                $response['message'] = 'Item not found';
            }
            break;

        case 'get_items':
            $items = $commodity->getAllItems();
            $response = ['success' => true, 'data' => $items];
            break;
        case 'get_supply_items':
            $items = $commodity->getAllSupplyItems($supply_id);
            $response = ['success' => true, 'data' => $items];
            break; 
            
            
            case 'add_quantity':
            $itemId = $_POST['commodity_item_id'] ?? 0;
            $addQty = (int)($_POST['add_quantity'] ?? 0);
        
            if ($itemId <= 0 || $addQty <= 0) {
                $response= ['success' => false, 'message' => 'Invalid item or quantity.'];
                exit;
            }
        
            $result = $commodity->addQuantity($itemId, $addQty);
        
            if ($result !== false) {
                 $response= [
                    'success' => true,
                    'message' => 'Quantity added successfully!',
                    'new_quantity' => $result // returns the updated total quantity
                ];
            } else {
                 $response= ['success' => false, 'message' => 'Failed to update quantity.'];
            }
            break;
            
         case 'add_items_request':
            // $items = $commodity->processCommodityLoanApplication($user_id, $itesms_request['commodity_supply_id'], $selected_items, $quantities, $itesms_request['total_amount']);
            // $response = $items; 
            // Parse the serialized form data
                $serialized_data = $_POST['data'];
                parse_str($serialized_data, $form_data);
                
                // Extract data from parsed form
                $commodity_supply_id = $form_data['commodity_supply_id'] ?? '';
                $selected_items = $form_data['selected_items'] ?? [];
                $quantities = $form_data['quantities'] ?? [];
                $total_amount = $form_data['total_amount'] ?? 0;
                
                // Validation
                if (empty($commodity_supply_id)) {
                    $response['message'] = 'Invalid commodity supply ID';
                    exit;
                }
                
                if (empty($selected_items)) {
                     $response['message'] =  'No items selected';
                    exit;
                }
                
                if (empty($user_id)) {
                     $response['message'] =  'Member information not found';
                    exit;
                }
        
            
                
                // Process the application
                $result = $commodity->processCommodityLoanApplication(
                    $user_id, // This is actually member_id
                    $commodity_supply_id, 
                    $selected_items, 
                    $quantities, 
                    $total_amount
                );
                
               $response = $result;
            break;
            
            case 'process_counter_offer':
                if (!$application_id) {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Invalid application ID'
                        ]);
                        exit;
                    }
            // Validate approved quantities
                if (($decision == 'approved' || $decision == 'counter_offered') && empty($approved_quantities)) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'No quantities specified for approval'
                    ]);
                    exit;
                }
                
                    if ($decision == 'rejected' && empty($admin_comments)) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Comments are required for rejection'
                    ]);
                    exit;
                }
            $result = $commodity->processCounterOffer(
                            $application_id,
                            $decision,
                            $approved_quantities,
                            $approved_total,
                            $admin_comments
                        ); 
            $response = $result;
           // $response = ['success' => true, 'data' => $result]; 
            
            break; 

        // ========== INVENTORY ACTIONS ==========
        case 'update_inventory':
            $item_id = $_POST['item_id'] ?? 0;
            $quantity = $_POST['quantity'] ?? 0;
            $min_per_member = $_POST['min_per_member'] ?? 0;
            $max_per_member = $_POST['max_per_member'] ?? '';
            
            if ($commodity->updateInventory($item_id, $quantity, $min_per_member, $max_per_member, $user_id)) {
                $response = ['success' => true, 'message' => 'Inventory updated successfully'];
            } else {
                $response['message'] = 'Failed to update inventory';
            }
            break;
            
        case 'process_disbursement':
                if (!$application_id) {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Invalid application ID'
                        ]);
                        exit;
                    }
           
                
                
             if ($application_id > 0 && !empty($disbursed_quantities)) {
            $result = Commodity::processDisbursement($application_id, $disbursed_quantities, $disbursed_total, $admin_comments);
                echo json_encode($result);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid data provided.']);
            }
            $response = $result;
            break; 
            
             // ==================== BATCH OPERATIONS ====================
         case 'create_batch':
            // Validate required fields
            if (empty($_POST['batch_name']) || empty($_POST['opening_date']) || empty($_POST['closing_date'])) {
                $response['message'] = 'Batch name and dates are required';
                break;
            }
            
            // Validate dates
            if (strtotime($_POST['opening_date']) > strtotime($_POST['closing_date'])) {
                $response['message'] = 'Closing date must be after opening date';
                break;
            }
            
            $result = $commodity->createBatch(
                $_POST['batch_name'],
                $_POST['description'] ?? '',
                $_POST['opening_date'],
                $_POST['closing_date'],
                $_POST['max_members'] ?? '',
                $_POST['max_loan_amount'] ?? '',
                $user_id
            );
            
            $response = $result;
            break;
            
        case 'get_batches':
            $active_only = isset($_POST['active_only']) ? (bool)$_POST['active_only'] : false;
            $result = $commodity->getBatches($active_only);
            $response = $result;
            break;
            
        case 'get_batch':
            if (empty($_POST['batch_id'])) {
                $response['message'] = 'Batch ID is required';
                break;
            }
            
            $result = $commodity->getBatch($_POST['batch_id']);
            $response = $result;
            break;
            
        case 'update_batch':
            // Validate required fields
            if (empty($_POST['batch_id']) || empty($_POST['batch_name']) || 
                empty($_POST['opening_date']) || empty($_POST['closing_date'])) {
                $response['message'] = 'All required fields must be filled';
                break;
            }
            
            // Validate dates
            if (strtotime($_POST['opening_date']) > strtotime($_POST['closing_date'])) {
                $response['message'] = 'Closing date must be after opening date';
                break;
            }
            
            $result = $commodity->updateBatch(
                $_POST['batch_id'],
                $_POST['batch_name'],
                $_POST['description'] ?? '',
                $_POST['opening_date'],
                $_POST['closing_date'],
                $_POST['max_members'] ?? '',
                $_POST['max_loan_amount'] ?? '',
                isset($_POST['is_active']) ? 1 : 0
            );
            
            $response = $result;
            break;
            
        case 'update_batch_status':
            if (empty($_POST['batch_id'])) {
                $response['message'] = 'Batch ID is required';
                break;
            }
            
            $result = $commodity->updateBatchStatus(
                $_POST['batch_id'],
                isset($_POST['is_active']) ? 1 : 0
            );
            
            $response = $result;
            break;
            
        case 'delete_batch':
            if (empty($_POST['batch_id'])) {
                $response['message'] = 'Batch ID is required';
                break;
            }
            
            $result = $commodity->deleteBatch($_POST['batch_id']);
            $response = $result;
            break;
            
        // ==================== BATCH ITEM OPERATIONS ====================
        case 'add_commodity_supply_items':
            if (empty($_POST['commodity_name_id']) || empty($_POST['items']) ) {
                $response['message'] = 'Batch ID and Items  are required';
                break;
            }
            
             $result = $commodity->saveCommoditySupplyItems(
                 intval($_POST['commodity_name_id']),
                 $_POST['items']
             );
            
            
            $response = $result;
            break;
            
        case 'get_batch_items':
            if (empty($_POST['batch_id'])) {
                $response['message'] = 'Batch ID is required';
                break;
            }
            
            $result = $commodity->getBatchItems($_POST['batch_id']);
            $response = $result;
            break;
            
        case 'update_batch_item':
            if (empty($_POST['batch_item_id']) || empty($_POST['quantity'])) {
                $response['message'] = 'Batch Item ID and Quantity are required';
                break;
            }
            
            $result = $commodity->updateBatchItem(
                $_POST['batch_item_id'],
                $_POST['quantity'],
                $user_id
            );
            
            $response = $result;
            break;
            
        case 'remove_batch_item':
            if (empty($_POST['batch_item_id'])) {
                $response['message'] = 'Batch Item ID is required';
                break;
            }
            
            $result = $commodity->removeBatchItem($_POST['batch_item_id']);
            $response = $result;
            break;


        default:
            $response['message'] = 'Invalid action requested';
            break;
    }
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>