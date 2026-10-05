<?php
if(!isset($_SESSION)) 
    { 
        session_start(); 
    } 
    
require_once('User.php');


class Commodity {
    private $db;
    
      public function __construct() {
        $db = new DB();
        $this->conn = $db->getConnection();
    }
    
    
    // ========== COMMODITY NAME METHOD ==========================
    
    
   public function createCommodityName($comm_name, $opening_date, $closing_date) {
        $db = new DB();
        $con = $db->getConnection();
    
        // Sanitize input
        $comm_name = htmlspecialchars(strip_tags($comm_name));
    
        // Check if name exists
        $q = "SELECT 1 FROM fudscoops_commodity_supply WHERE commodity_name = :comm_name LIMIT 1";
        $stmt = $con->prepare($q);
        $stmt->bindParam(':comm_name', $comm_name);
        $stmt->execute();
    
        if ($stmt->rowCount() > 0) {
            // Return specific message if name exists
            return ['success' => false, 'message' => 'commodity_name_exists'];
        }
    
        // Insert
        $insert = "INSERT INTO fudscoops_commodity_supply (commodity_name,opening_date,closing_date) VALUES (:comm_name,:opening_date,:closing_date)";
        $stmt2 = $con->prepare($insert);
        $stmt2->bindParam(':comm_name', $comm_name);
         $stmt2->bindParam(':opening_date', $opening_date);
          $stmt2->bindParam(':closing_date', $closing_date);
    
        if ($stmt2->execute()) {
            return ['success' => true];
        }
    
        // Return failure message for insert failure
        return ['success' => false, 'message' => 'commodity_creation_failed'];
    }


    
    public static function getCommodityNameTable() {
        $db = new DB();
        $con = $db->getConnection();
        try {
            // Get all item types from database
            $query = "SELECT * FROM fudscoops_commodity_name ORDER BY created_at DESC";
            $stmt = $con->prepare($query);
            $stmt->execute();
            $comm_name = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            // Start building HTML table
            $html = '<div class="table-responsive">
                    <table class="table  table-bordered">
                        <thead >
                            <tr>
                                <th>S/N</th>
                                <th>Commodity Supply Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>';
    
            // Populate table rows
            $sn=0;
            foreach ($comm_name as $name) {
                $sn++;
                $html .= '<tr>
                            <td>' . $sn . '</td>
                            <td>' . htmlspecialchars($name['commodity_name']) . '</td>
                            <td>
                                <button class="btn btn-sm btn-success edit-commodity-name" 
                                        data-id=' . $name['commodity_name_id'] . '
                                        data-name="' . htmlspecialchars($name['commodity_name']) . '">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-danger delete-commodity-name" 
                                        data-id=' . $name['commodity_name_id'] . '>
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>';
            }
    
            // Close table
            $html .= '</tbody></table></div>';
    
            return $html;
    
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }
    
        public static function getCommodityNameSupplyTable() {
        $db = new DB();
        $con = $db->getConnection();
        try {
            // Get all item types from database
            $query = "SELECT * FROM fudscoops_commodity_supply ORDER BY created_at DESC";
            $stmt = $con->prepare($query);
            $stmt->execute();
            $comm_name = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            // Start building HTML table
            $html = '<div class="table-responsive">
                    <table class="table  table-bordered">
                        <thead >
                            <tr>
                                <th>S/N</th>
                                <th>Commodity Supply Name</th>
                                <th>Opening Date</th>
                                <th>Closing Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>';
    
            // Populate table rows
            $sn=0;
            foreach ($comm_name as $name) {
                $sn++;
                $html .= '<tr>
                            <td>' . $sn . '</td>
                            <td>' . htmlspecialchars($name['commodity_name']) . '</td>
                            <td>' . htmlspecialchars($name['opening_date']) . '</td>
                            <td>' . htmlspecialchars($name['closing_date']) . '</td>
                            <td>
                                <button class="btn btn-sm btn-success edit-commodity-name" 
                                        data-id=' . $name['commodity_name_id'] . '
                                        data-name="' . htmlspecialchars($name['commodity_name']) . '">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <a href="addCommodityItems.php?commodity_name_id=' . $name['commodity_name_id'] . '" class="btn btn-sm btn-outline-primary view-items" >
                                        <i class="bi bi-box-seam"></i> Add/View Items 
                                    </a>
                            </td>
                        </tr>';
            }
    
            // Close table
            $html .= '</tbody></table></div>';
    
            return $html;
    
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }
    
    public function updateCommodityName($id, $name) 
    {
        $db = new DB();
        $con = $db->getConnection();
    
        $name = htmlspecialchars(strip_tags($name));
    
        // Check for duplicates (ignore current ID)
        $q = "SELECT * FROM fudscoops_commodity_name 
              WHERE commodity_name = :name AND commodity_name_id != :id LIMIT 1";
        $stmt = $con->prepare($q);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    
        if ($stmt->rowCount() > 0) {
            return false; // Name already exists for another record
        }
    
        // Proceed to update
        $update = "UPDATE fudscoops_commodity_name 
                   SET commodity_name = :name 
                   WHERE commodity_name_id = :id";
        $stmt2 = $con->prepare($update);
        $stmt2->bindParam(':name', $name);
        $stmt2->bindParam(':id', $id);
    
        return $stmt2->execute();
    }


    
    public function deleteCommodityName($commodity_name_id) {
        // Check if name is used by any items
        $check_query = "SELECT COUNT(*) as count FROM fudscoops_commodity_Items WHERE commodity_name_id = ?";
        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->execute([$type_id]);
        $result = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            return "Name is in use by items and cannot be deleted";
        }
        
        $query = "DELETE FROM fudscoops_commodity_name WHERE commodity_name_id = ?";
        $stmt = $this->conn->prepare($query);
        
        if($stmt->execute([$type_id])) {
            return "Name deleted successfully";
        }
        return "Failed to delete Name";

    }
    
     public function getCommodityNames() {
        $db = new DB();
        $con = $db->getConnection();
        
        $query = "SELECT * FROM fudscoops_commodity_name ORDER BY created_at DESC";
        $stmt = $con->prepare($query);
        $stmt->execute();
        $comm_name = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        
        return [
            'success' => true,
            'data' => $comm_name
        ];
    }
    
    
       public function getCommoditySupplyDropdown() {
                $db = new DB();
                $con = $db->getConnection();
                
                $query = "SELECT commodity_name_id, commodity_name FROM fudscoops_commodity_supply ORDER BY created_at DESC";
                $stmt = $con->prepare($query);
                $stmt->execute();
                $comm_name = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
                $dropdownOptions = "";
                foreach ($comm_name as $item) {
                    $dropdownOptions .= "<option value='{$item['commodity_name_id']}'>{$item['commodity_name']}</option>";
                }
            
                return $dropdownOptions;
    }
    
           public function getCommodityNamesDropdown() {
                $db = new DB();
                $con = $db->getConnection();
                
                $query = "SELECT commodity_name_id, commodity_name FROM fudscoops_commodity_name ORDER BY created_at DESC";
                $stmt = $con->prepare($query);
                $stmt->execute();
                $comm_name = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
                $dropdownOptions = "";
                foreach ($comm_name as $item) {
                    $dropdownOptions .= "<option value='{$item['commodity_name_id']}'>{$item['commodity_name']}</option>";
                }
            
                return $dropdownOptions;
    }
    
      public static function getMemberCommodityNameSupplyRequests($member_id) {
        $db = new DB();
        $con = $db->getConnection();
        try {
            // Get all item types from database
          $query = "SELECT employee.sp_no, employee.title,employee.fname,employee.lname,employee.oname, fudscoops_commodity_loan_applications.application_id,
                    fudscoops_commodity_supply.commodity_name, fudscoops_commodity_loan_applications.application_date, fudscoops_commodity_loan_applications.total_amount,
                    fudscoops_commodity_loan_applications.status
                    FROM `fudscoops_commodity_loan_applications` 
                    INNER JOIN fudscoops_member 
                        ON fudscoops_commodity_loan_applications.member_id = fudscoops_member.member_id 
                    INNER JOIN employee 
                        ON fudscoops_member.employee_id = employee.employee_id
                     INNER JOIN fudscoops_commodity_supply 
                        ON fudscoops_commodity_supply.commodity_name_id = fudscoops_commodity_loan_applications.commodity_supply_id 
                         where fudscoops_commodity_loan_applications.member_id=:member_id
                     ORDER BY fudscoops_commodity_loan_applications.created_at DESC
                     
                     ";
            $stmt = $con->prepare($query);
            $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
            $stmt->execute();
            $comm_name = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            // Start building HTML table 	
            $html = '<div class="table-responsive">
                    <table class="table  table-bordered">
                        <thead >
                            <tr>
                                <th>S/N</th>
                                <th>Staff Name</th>
                                <th>Staff ID</th>
                                <th>Application </th>
                                 <th>Supply Schedule </th>
                                  <th>Application Date </th>
                                   <th>Total Amount </th>
                                    <th>Status </th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>';
    
            // Populate table rows
            $sn=0;
            foreach ($comm_name as $name) {  	 	
                $sn++;
                $html .= '<tr>
                            <td>' . $sn . '</td>
                            <td>' . htmlspecialchars($name['title']) . ' ' . htmlspecialchars($name['fname']) . ' ' . htmlspecialchars($name['lname']) . ' ' . htmlspecialchars($name['oname']) . '</td>
                            <td>' . htmlspecialchars($name['sp_no']) . '</td>
                            <td>' . htmlspecialchars($name['application_id']) . '</td>
                             <td>' . htmlspecialchars($name['commodity_name']) . '</td>
                              <td>' . htmlspecialchars($name['application_date']) . '</td>
                               <td> ₦' . htmlspecialchars(number_format($name['total_amount'],2)) . '</td>
                                <td>' . htmlspecialchars($name['status']) . '</td>
                            <td>
                                
                                <a href="viewRequestDetails.php?applicationId=' . $name['application_id'] . '" class="btn btn-sm btn-outline-primary view-items" >
                                        <i class="bi bi-box-seam"></i> View Request Details 
                                    </a>
                            </td>
                        </tr>';
            }
    
            // Close table
            $html .= '</tbody></table></div>';
    
            return $html;
    
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }
    
    
public static function getCommodityNameSupplyRequests() {
    $db = new DB();
    $con = $db->getConnection();
    try {
        // Get all item types from database
        $query = "SELECT 
                    employee.sp_no, 
                    employee.title,
                    employee.fname,
                    employee.lname,
                    employee.oname, 
                    fudscoops_commodity_loan_applications.application_id,
                    fudscoops_commodity_supply.commodity_name, 
                    fudscoops_commodity_loan_applications.application_date, 
                    fudscoops_commodity_loan_applications.total_amount,
                    fudscoops_commodity_loan_applications.status
                FROM `fudscoops_commodity_loan_applications` 
                INNER JOIN fudscoops_member 
                    ON fudscoops_commodity_loan_applications.member_id = fudscoops_member.member_id 
                INNER JOIN employee 
                    ON fudscoops_member.employee_id = employee.employee_id
                INNER JOIN fudscoops_commodity_supply 
                    ON fudscoops_commodity_supply.commodity_name_id = fudscoops_commodity_loan_applications.commodity_supply_id 
                ORDER BY fudscoops_commodity_loan_applications.created_at DESC";
        
        $stmt = $con->prepare($query);
        $stmt->execute();
        $comm_name = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Start building HTML table with DataTable integration
        $html = '<div class="table-responsive">
                <table id="commodityRequestsTable" class="table table-striped table-bordered table-hover" style="width:100%">
                    <thead class="table-dark">
                        <tr>
                            <th>S/N</th>
                            <th>Staff Name</th>
                            <th>Staff ID</th>
                            <th>Application ID</th>
                            <th>Supply Schedule</th>
                            <th>Application Date</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';

        // Populate table rows
        $sn = 0;
        foreach ($comm_name as $name) {
            $sn++;
            
            // Format staff name
            $staffName = trim(
                htmlspecialchars($name['title']) . ' ' . 
                htmlspecialchars($name['fname']) . ' ' . 
                htmlspecialchars($name['lname']) . ' ' . 
                htmlspecialchars($name['oname'])
            );
            
            // Format status with badge
            $statusBadge = self::getStatusBadge($name['status']);
            
            // Format amount
            $formattedAmount = '₦' . number_format($name['total_amount'], 2);
            
            // Format date
            $formattedDate = date('M d, Y', strtotime($name['application_date']));
            
            $html .= '<tr>
                        <td>' . $sn . '</td>
                        <td>' . $staffName . '</td>
                        <td>' . htmlspecialchars($name['sp_no']) . '</td>
                        <td>' . htmlspecialchars($name['application_id']) . '</td>
                        <td>' . htmlspecialchars($name['commodity_name']) . '</td>
                        <td data-order="' . strtotime($name['application_date']) . '">' . $formattedDate . '</td>
                        <td data-order="' . $name['total_amount'] . '">' . $formattedAmount . '</td>
                        <td>' . $statusBadge . '</td>
                        <td>
                            <a href="viewRequestDetails.php?batch=' . urlencode($name['application_id']) . '" 
                               class="btn btn-sm btn-primary view-items" 
                               title="View Request Details">
                                <i class="bi bi-box-seam"></i> View Details
                            </a>
                        </td>
                    </tr>';
        }

        // Close table
        $html .= '</tbody>
                  <tfoot class="table-light">
                      <tr>
                          <th>S/N</th>
                          <th>Staff Name</th>
                          <th>Staff ID</th>
                          <th>Application ID</th>
                          <th>Supply Schedule</th>
                          <th>Application Date</th>
                          <th>Total Amount</th>
                          <th>Status</th>
                          <th>Actions</th>
                      </tr>
                  </tfoot>
              </table>
          </div>';

        // Add DataTable initialization script
        $html .= '
        <script>
        $(document).ready(function() {
            $("#commodityRequestsTable").DataTable({
                responsive: true,
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                order: [[5, "desc"]], // Sort by Application Date descending
                dom: "Bfrtip",
                buttons: [
                    {
                        extend: "copy",
                        className: "btn btn-sm btn-secondary",
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7] // Exclude Actions column
                        }
                    },
                    {
                        extend: "csv",
                        className: "btn btn-sm btn-success",
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7]
                        }
                    },
                    {
                        extend: "excel",
                        className: "btn btn-sm btn-success",
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7]
                        }
                    },
                    {
                        extend: "pdf",
                        className: "btn btn-sm btn-danger",
                        orientation: "landscape",
                        pageSize: "A4",
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7]
                        },
                        customize: function(doc) {
                            doc.content[1].table.widths = ["5%", "20%", "10%", "10%", "15%", "12%", "12%", "10%"];
                            doc.styles.tableHeader.fillColor = "#343a40";
                            doc.styles.tableHeader.color = "white";
                        }
                    },
                    {
                        extend: "print",
                        className: "btn btn-sm btn-info",
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7]
                        },
                        customize: function(win) {
                            $(win.document.body).css("font-size", "10pt");
                            $(win.document.body).find("table")
                                .addClass("compact")
                                .css("font-size", "inherit");
                        }
                    }
                ],
                columnDefs: [
                    { orderable: false, targets: [8] }, // Disable sorting on Actions column
                    { className: "text-center", targets: [0, 2, 7, 8] },
                    { className: "text-end", targets: [6] }
                ],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search requests...",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ requests",
                    infoEmpty: "No requests available",
                    infoFiltered: "(filtered from _MAX_ total requests)",
                    zeroRecords: "No matching requests found",
                    emptyTable: "No commodity requests available"
                },
                drawCallback: function() {
                    // Add tooltips to buttons
                    $("[title]").tooltip();
                }
            });
        });
        </script>';

        return $html;

    } catch (PDOException $e) {
        return '<div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <strong>Database Error:</strong> ' . htmlspecialchars($e->getMessage()) . '
                </div>';
    }
}

// Helper function to generate status badges
private static function getStatusBadge($status) {
    $status = strtolower(trim($status));
    $badgeClass = '';
    $icon = '';
    
    switch($status) {
        case 'pending':
            $badgeClass = 'bg-warning text-dark';
            $icon = '<i class="bi bi-clock-history"></i>';
            break;
        case 'approved':
            $badgeClass = 'bg-success';
            $icon = '<i class="bi bi-check-circle"></i>';
            break;
        case 'rejected':
        case 'declined':
            $badgeClass = 'bg-danger';
            $icon = '<i class="bi bi-x-circle"></i>';
            break;
        case 'processing':
            $badgeClass = 'bg-info';
            $icon = '<i class="bi bi-arrow-repeat"></i>';
            break;
        case 'completed':
            $badgeClass = 'bg-primary';
            $icon = '<i class="bi bi-check-all"></i>';
            break;
        default:
            $badgeClass = 'bg-secondary';
            $icon = '<i class="bi bi-question-circle"></i>';
    }
    
    return '<span class="badge ' . $badgeClass . '">' . $icon . ' ' . ucfirst($status) . '</span>';
}
    
    public static function getApprovedCommodityNameSupplyRequests() {
        $db = new DB();
        $con = $db->getConnection();
        try {
            // Get all item types from database
          $query = "SELECT employee.sp_no, employee.title,employee.fname,employee.lname,employee.oname, fudscoops_commodity_loan_applications.application_id,
                    fudscoops_commodity_supply.commodity_name, fudscoops_commodity_loan_applications.application_date, fudscoops_commodity_loan_applications.total_amount,
                    fudscoops_commodity_loan_applications.status
                    FROM `fudscoops_commodity_loan_applications` 
                    INNER JOIN fudscoops_member 
                        ON fudscoops_commodity_loan_applications.member_id = fudscoops_member.member_id 
                    INNER JOIN employee 
                        ON fudscoops_member.employee_id = employee.employee_id
                     INNER JOIN fudscoops_commodity_supply 
                        ON fudscoops_commodity_supply.commodity_name_id = fudscoops_commodity_loan_applications.application_id
                    WHERE fudscoops_commodity_loan_applications.status='approved'
                     ORDER BY fudscoops_commodity_loan_applications.created_at DESC";
            $stmt = $con->prepare($query);
            $stmt->execute();
            $comm_name = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            // Start building HTML table 	
            $html = '<div class="table-responsive">
                    <table class="table  table-bordered">
                        <thead >
                            <tr>
                                <th>S/N</th>
                                <th>Staff Name</th>
                                <th>Staff ID</th>
                                <th>Application </th>
                                 <th>Supply Schedule </th>
                                  <th>Application Date </th>
                                   <th>Total Amount </th>
                                    <th>Status </th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>';
    
            // Populate table rows
            $sn=0;
            foreach ($comm_name as $name) {  	 	
                $sn++;
                $html .= '<tr>
                            <td>' . $sn . '</td>
                            <td>' . htmlspecialchars($name['title']) . ' ' . htmlspecialchars($name['fname']) . ' ' . htmlspecialchars($name['lname']) . ' ' . htmlspecialchars($name['oname']) . '</td>
                            <td>' . htmlspecialchars($name['sp_no']) . '</td>
                            <td>' . htmlspecialchars($name['application_id']) . '</td>
                             <td>' . htmlspecialchars($name['commodity_name']) . '</td>
                              <td>' . htmlspecialchars($name['application_date']) . '</td>
                               <td> ₦' . htmlspecialchars(number_format($name['total_amount'],2)) . '</td>
                                <td>' . htmlspecialchars($name['status']) . '</td>
                            <td>
                                
                                <a href="viewRequestDetails.php?application_id=' . $name['application_id'] . '" class="btn btn-sm btn-outline-primary view-items" >
                                        <i class="bi bi-box-seam"></i> View Request Details 
                                    </a>
                            </td>
                        </tr>';
            }
    
            // Close table
            $html .= '</tbody></table></div>';
    
            return $html;
    
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }
     
    
   public static function getApplicationDetails($application_id, $memberId = "") {
    $db = new DB();
    $con = $db->getConnection();

    try {
        // Base query
        $query = "SELECT 
                    app.application_id,
                    app.total_amount,
                    app.status,
                    app.application_date,
                    app.remarks,
                    app.created_at,
                    emp.sp_no,
                    emp.title,
                    emp.fname,
                    emp.lname,
                    emp.oname,
                    emp.employee_id,
                    cs.commodity_name,
                    cs.commodity_name_id
                  FROM fudscoops_commodity_loan_applications app
                  INNER JOIN fudscoops_member mem ON app.member_id = mem.member_id
                  INNER JOIN employee emp ON mem.employee_id = emp.employee_id
                  LEFT JOIN fudscoops_commodity_supply cs ON cs.commodity_name_id = app.commodity_supply_id
                  WHERE app.application_id = :application_id";

        // Add memberId condition if provided
        if (!empty($memberId)) {
            $query .= " AND app.member_id = :member_id";
        }

        $stmt = $con->prepare($query);
        $stmt->bindParam(':application_id', $application_id, PDO::PARAM_INT);

        if (!empty($memberId)) {
            $stmt->bindParam(':member_id', $memberId, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Error getting application details: " . $e->getMessage());
        return false;
    }
}


public static function getApplicationItems($application_id) {
    $db = new DB();
    $con = $db->getConnection();
    try {
                        $query = "SELECT 
                                        ai.application_id,
                                        cli.commodity_supply_item_id,
                                        cli.quantity_requested,
                                        cli.unit_price,
                                        cli.subtotal,
                                        cli.quantity_approved,
                                        ci.commodity_item,
                                        ci.commodity_description,
                                        ci.quantity AS stock_quantity,
                                        csi.remaining_quantity,
                                        csi.item_id,
                                        cs.commodity_name,
                                        cs.commodity_name_id
                                    FROM
                                        fudscoops_commodity_loan_applications AS ai
                                    INNER JOIN
                                        fudscoops_commodity_loan_application_items AS cli
                                        ON ai.application_id = cli.application_id
                                    INNER JOIN
                                        fudscoops_commodity_supply_items AS csi
                                        ON cli.commodity_supply_item_id = csi.commodity_supply_item_id
                                    INNER JOIN
                                        fudscoops_commodity_Items AS ci
                                        ON csi.item_id = ci.commodity_item_id
                                    INNER JOIN
                                        fudscoops_commodity_supply AS cs
                                        ON csi.commodity_supply_id = cs.commodity_name_id
                                    WHERE
                                        ai.application_id = 5
                                        AND ai.commodity_supply_id = cs.commodity_name_id  -- ✅ Enforce supply consistency
                                    ORDER BY ci.commodity_item";

               
        
        $stmt = $con->prepare($query);
        $stmt->bindParam(':application_id', $application_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("Error getting application items: " . $e->getMessage());
        return [];
    }
}


    /**
 * Process counter offer decision
 */
public static function processCounterOffer($application_id, $decision, $approved_quantities, $approved_total, $admin_comments) {
    $db = new DB();
    $con = $db->getConnection();
    
    try {
        $con->beginTransaction();
        
        // Update main application
        $updateAppQuery = "UPDATE fudscoops_commodity_loan_applications 
                          SET status = :status,
                              remarks = :admin_comments,
                              updated_at = NOW()
                          WHERE application_id = :application_id";
        
        $stmt = $con->prepare($updateAppQuery);
        $stmt->bindParam(':status', $decision);
        $stmt->bindParam(':admin_comments', $admin_comments);
        $stmt->bindParam(':application_id', $application_id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Update individual items with approved quantities
        foreach ($approved_quantities as $commodity_supply_item_id => $approved_qty) {
            // Get the original item details
            $getItemQuery = "SELECT unit_price FROM fudscoops_commodity_loan_application_items 
                           WHERE application_id = :application_id AND commodity_supply_item_id = :commodity_supply_item_id";
            $itemStmt = $con->prepare($getItemQuery);
            $itemStmt->bindParam(':application_id', $application_id, PDO::PARAM_INT);
            $itemStmt->bindParam(':commodity_supply_item_id', $commodity_supply_item_id, PDO::PARAM_INT);
            $itemStmt->execute();
            $itemData = $itemStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($itemData) {
                $approved_subtotal = $approved_qty * $itemData['unit_price'];
                
                $updateItemQuery = "UPDATE fudscoops_commodity_loan_application_items 
                                  SET quantity_approved = :quantity_approved,
                                      subtotal = :subtotal
                                  WHERE application_id = :application_id AND commodity_supply_item_id = :commodity_supply_item_id";
                
                $itemUpdateStmt = $con->prepare($updateItemQuery);
                $itemUpdateStmt->bindParam(':quantity_approved', $approved_qty);
                $itemUpdateStmt->bindParam(':subtotal', $approved_subtotal);
                $itemUpdateStmt->bindParam(':application_id', $application_id, PDO::PARAM_INT);
                $itemUpdateStmt->bindParam(':commodity_supply_item_id', $commodity_supply_item_id, PDO::PARAM_INT);
                $itemUpdateStmt->execute();
            }
        }
        
        // Update the total amount in the main application table
        $updateTotalQuery = "UPDATE fudscoops_commodity_loan_applications 
                           SET total_amount = :approved_total 
                           WHERE application_id = :application_id";
        $totalStmt = $con->prepare($updateTotalQuery);
        $totalStmt->bindParam(':approved_total', $approved_total);
        $totalStmt->bindParam(':application_id', $application_id, PDO::PARAM_INT);
        $totalStmt->execute();
        
        $con->commit(); 
        
        return [
            'success' => true,
            'message' => ucfirst(str_replace('_', ' ', $decision)) . ' successfully processed!'
        ];
        
    } catch (PDOException $e) {
        $con->rollBack();
        error_log("Error processing counter offer: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Database error occurred while processing the request.'
        ];
    }
}

public static function processDisbursement($application_id, $disbursed_quantities, $disbursed_total, $admin_comments) {
        $db = new DB();
        $con = $db->getConnection();
        
        try {
            $con->beginTransaction();
            
            // Step 1: Update the main application status to 'Disbursed'
            $updateAppQuery = "UPDATE fudscoops_commodity_loan_applications 
                               SET status = 'disbursed',
                                   remarks = CONCAT(IFNULL(remarks, ''), '\nDisbursement Note: ', :admin_comments),
                                   total_amount = :disbursed_total,
                                   disbursement_date = NOW(),
                                   updated_at = NOW()
                               WHERE application_id = :application_id AND status = 'approved'";
            
            $appStmt = $con->prepare($updateAppQuery);
            $appStmt->bindParam(':admin_comments', $admin_comments);
            $appStmt->bindParam(':disbursed_total', $disbursed_total);
            $appStmt->bindParam(':application_id', $application_id, PDO::PARAM_INT);
            $appStmt->execute();

            if ($appStmt->rowCount() == 0) {
                 throw new Exception("Application not found or not in 'approved' state.");
            }

            // Step 2: Loop through each item to update application items and deduct from inventory
            foreach ($disbursed_quantities as $commodity_supply_item_id => $disbursed_qty) {
                $disbursed_qty = (int)$disbursed_qty;
                if ($disbursed_qty < 0) continue; // Skip negative values

                // Get item details needed for inventory update
                $itemDetailsQuery = "SELECT 
                                         si.commodity_supply_item_id, 
                                         li.quantity_approved,
                                         ci.quantity as stock_quantity
                                     FROM fudscoops_commodity_loan_application_items li
                                     JOIN fudscoops_commodity_supply_items si ON li.commodity_supply_item_id = si.commodity_supply_item_id
                                     JOIN fudscoops_commodity_Items ci ON si.commodity_supply_item_id = ci.commodity_item_id
                                     WHERE li.application_id = :application_id 
                                     AND li.commodity_supply_item_id = :commodity_supply_item_id";

                $itemStmt = $con->prepare($itemDetailsQuery);
                $itemStmt->bindParam(':application_id', $application_id, PDO::PARAM_INT);
                $itemStmt->bindParam(':commodity_supply_item_id', $commodity_supply_item_id, PDO::PARAM_INT);
                $itemStmt->execute();
                $itemData = $itemStmt->fetch(PDO::FETCH_ASSOC);

                if (!$itemData) {
                    throw new Exception("Invalid item ID #{$commodity_supply_item_id} found in the application.");
                }

                // Security check: cannot disburse more than approved or more than is in stock
                if ($disbursed_qty > $itemData['quantity_approved'] || $disbursed_qty > $itemData['stock_quantity']) {
                    throw new Exception("Attempted to disburse item #{$itemData['commodity_item_id']} with quantity {$disbursed_qty}, which exceeds approved ({$itemData['quantity_approved']}) or stock ({$itemData['stock_quantity']}) limits.");
                }

                // A) Update the disbursed quantity in the application items table
                $updateItemQuery = "UPDATE fudscoops_commodity_loan_application_items
                                    SET disbursed_quantity = :quantity_disbursed
                                    WHERE application_id = :application_id AND commodity_supply_item_id = :commodity_supply_item_id";
                
                $itemUpdateStmt = $con->prepare($updateItemQuery);
                $itemUpdateStmt->bindParam(':quantity_disbursed', $disbursed_qty, PDO::PARAM_INT);
                $itemUpdateStmt->bindParam(':application_id', $application_id, PDO::PARAM_INT);
                $itemUpdateStmt->bindParam(':commodity_supply_item_id', $commodity_supply_item_id, PDO::PARAM_INT);
                $itemUpdateStmt->execute();

                // B) CRITICAL: Deduct the quantity from the main inventory table
                if ($disbursed_qty > 0) {
                    $inventoryUpdateQuery = "UPDATE fudscoops_commodity_Items
                                             SET quantity = quantity - :disbursed_qty
                                             WHERE commodity_item_id  = :inventory_item_id";
                    $invStmt = $con->prepare($inventoryUpdateQuery);
                    $invStmt->bindParam(':disbursed_qty', $disbursed_qty, PDO::PARAM_INT);
                    $invStmt->bindParam(':inventory_item_id', $itemData['commodity_item_id'], PDO::PARAM_INT);
                    $invStmt->execute();
                }
            }
            
            $con->commit();
            
            return [
                'success' => true,
                'message' => 'Disbursement successfully recorded and inventory updated.'
            ];
            
        } catch (Exception $e) {
            $con->rollBack();
            error_log("Error processing disbursement: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ];
        }
    }

    

   public function getCommodityBatches($supplyId) {
            $db = new DB();
            $con = $db->getConnection();
            
            $query = "SELECT * FROM fudscoops_CommodityBatches 
                      WHERE commodity_name_id = ? 
                      ORDER BY opening_date DESC";
            
            $stmt = $con->prepare($query);
            $stmt->execute([$supplyId]); // Pass the parameter here
            
            $comm_name = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
            return [
                'success' => true,
                'data' => $comm_name
            ];
}


        public function checkSchedule()
        {
            $db = new DB();
            $con = $db->getConnection();
        
            $today = date('Y-m-d H:i:s'); // Include time component
        
            $q = "SELECT COUNT(*) as count FROM fudscoops_commodity_supply 
                  WHERE opening_date <= :today AND closing_date >= :today";
        
            $stm = $con->prepare($q);
            $stm->bindParam(':today', $today);
            $stm->execute();
            
            $result = $stm->fetch(PDO::FETCH_ASSOC);
            
            return ($result['count'] > 0);
        }


         public  function getSchedulesTimeOut() {
            $r_v = '';
        
            $db = new DB();
            $con = $db->getConnection();
        
            // Corrected SQL query (removed stray quote and added ORDER BY for clarity)
            $q = "SELECT closing_date FROM fudscoops_commodity_supply ORDER BY closing_date DESC LIMIT 1";
        
            $stm = $con->prepare($q);
            $stm->execute();
        
            if ($stm->rowCount() > 0) {
                $row = $stm->fetch(PDO::FETCH_ASSOC);
                $r_v = $row['closing_date'];
            } else {
                $r_v = 0;
            }
        
            return $r_v;
        }
        
        
         public  function getSchedulesTimeOutId() {
            $r_v = '';
        
            $db = new DB();
            $con = $db->getConnection();
        
            // Corrected SQL query (removed stray quote and added ORDER BY for clarity)
            $q = "SELECT commodity_name_id FROM fudscoops_commodity_supply ORDER BY closing_date DESC LIMIT 1";
        
            $stm = $con->prepare($q);
            $stm->execute();
        
            if ($stm->rowCount() > 0) {
                $row = $stm->fetch(PDO::FETCH_ASSOC);
                $r_v = $row['commodity_name_id'];
            } else {
                $r_v = 0;
            }
        
            return $r_v;
        }




   
    // ==================== ITEM TYPE METHODS ====================
    
  public function createItemType($type_name, $description) {
        $db = new DB();
        $con = $db->getConnection();
    
        // Sanitize inputs
        $type_name = htmlspecialchars(strip_tags($type_name));
        $description = htmlspecialchars(strip_tags($description));
    
        try {
            // Check if type_name already exists
            $checkQuery = "SELECT COUNT(*) FROM CommodityTypes WHERE type_name = :type_name";
            $checkStmt = $con->prepare($checkQuery);
            $checkStmt->bindParam(':type_name', $type_name);
            $checkStmt->execute();
    
            if ($checkStmt->fetchColumn() > 0) {
                return ['success' => false, 'message' => 'Item type already exists.'];
            }
    
            // Insert new item type
            $insertQuery = "INSERT INTO CommodityTypes (type_name, description) 
                            VALUES (:type_name, :description)";
            $insertStmt = $con->prepare($insertQuery);
            $insertStmt->bindParam(':type_name', $type_name);
            $insertStmt->bindParam(':description', $description);
    
            if ($insertStmt->execute()) {
                return ['success' => true, 'message' => $type_name. ' Item type created successfully.'];
            } else {
                return ['success' => false, 'message' => 'Failed to create item type.'];
            }
    
        } catch (PDOException $e) {
            error_log("Error creating item type: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred.'];
        }
}



    public static function getItemTypesTable() {
        $db = new DB();
        $con = $db->getConnection();
        try {
            // Get all item types from database
            $query = "SELECT * FROM CommodityTypes ORDER BY type_name";
            $stmt = $con->prepare($query);
            $stmt->execute();
            $types = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            // Start building HTML table
            $html = '<div class="table-responsive">
                    <table class="table  table-bordered">
                        <thead >
                            <tr>
                                <th>S/N</th>
                                <th>Type Name</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>';
    
            // Populate table rows
            $sn=0;
            foreach ($types as $type) {
                $sn++;
                $html .= '<tr>
                            <td>' . $sn . '</td>
                            <td>' . htmlspecialchars($type['type_name']) . '</td>
                            <td>' . htmlspecialchars($type['description'] ?? '') . '</td>
                            <td>
                                <button class="btn btn-sm btn-success edit-item-type" 
                                        data-id="' . $type['type_id'] . '"
                                        data-name="' . htmlspecialchars($type['type_name']) . '"
                                        data-description="' . htmlspecialchars($type['description'] ?? '') . '">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                             
                            </td>
                        </tr>';
            }
    
            // Close table
            $html .= '</tbody></table></div>';
    
            return $html;
    
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    public function getItemType($type_id) {
        $query = "SELECT * FROM CommodityTypes WHERE type_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$type_id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getItemTypesByCommodity($commodity_id) {
        $db = new DB();
        $con = $db->getConnection();
    
        $q = "SELECT type_id AS id, type_name AS name 
              FROM CommodityTypes 
              WHERE commodity_name_id = :commodity_id 
              ORDER BY created_at DESC";
    
        $stm = $con->prepare($q);
        $stm->bindParam(':commodity_id', $commodity_id, PDO::PARAM_INT);
        $stm->execute();
    
        $result = $stm->fetchAll(PDO::FETCH_ASSOC);
        $con = null;
    
        return $result ?: [];
    }

    

    public function getAllItemTypes() {
        $query = "SELECT * FROM CommodityTypes ORDER BY type_name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateItemType($type_id, $type_name, $description) {
        $query = "UPDATE CommodityTypes SET 
                 type_name = :type_name,
                 description = :description
                 WHERE type_id = :type_id";
        
        $stmt = $this->conn->prepare($query);
        
        $type_name = htmlspecialchars(strip_tags($type_name));
        $description = htmlspecialchars(strip_tags($description));
        $type_id = htmlspecialchars(strip_tags($type_id));
        
        $stmt->bindParam(':type_name', $type_name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':type_id', $type_id);
        
        return $stmt->execute();
    }

    public function deleteItemType($type_id) {
        // Check if type is used by any items
        $check_query = "SELECT COUNT(*) as count FROM fudscoops_commodity_Items WHERE type_id = ?";
        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->execute([$type_id]);
        $result = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            return "Type is in use by items and cannot be deleted";
        }
        
        $query = "DELETE FROM CommodityTypes WHERE type_id = ?";
        $stmt = $this->conn->prepare($query);
        
        if($stmt->execute([$type_id])) {
            return "Type deleted successfully";
        }
        return "Failed to delete type";
    }

    // ==================== ITEM METHODS ====================
    
    public function getItemTypesDropdown() 
    {
            $db = new DB();
            $conn = $db->getConnection();
        try {
            $query = "SELECT type_id, type_name FROM CommodityTypes WHERE 1 ORDER BY type_name";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $types = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
             $options = '';
            
            foreach ($types as $type) {
                $options .= sprintf(
                    '<option value="%d">%s</option>',
                    htmlspecialchars($type['type_id'], ENT_QUOTES),
                    htmlspecialchars($type['type_name'], ENT_QUOTES)
                );
            }
            
            return $options; 
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage(),
                'options' => '<option value="">Error loading types</option>'
            ];
        }
   }

    
    public function itemExists($type_id, $item) 
    {
        $db = new DB();
        $con = $db->getConnection();
    
        $q = "SELECT COUNT(*) FROM fudscoops_commodity_Items 
              WHERE type_id = :type_id 
                AND LOWER(commodity_item) = LOWER(:item)";
        
        $stm = $con->prepare($q);
        $stm->execute([
            ':type_id' => $type_id,
            ':item' => $item
        ]);
    
        return $stm->fetchColumn() > 0;
    }

    
    public function getAllowedQuantityForMember($sp_no, $max_senior_quantity, $max_junior_quantity) 
    {
        if (str_starts_with($sp_no, 'SP/R')) {
            return $max_senior;
        } elseif (str_starts_with($sp_no, 'JP/R')) {
            return $max_junior;
        } else {
            return '0'; // Not eligible
        }
    }

    
    public function createItem( $type_id, $item, $description, $quantity, $unit, $max_senior_quantity, $max_junior_quantity, $is_active) 
    {
        $db = new DB();
        $con = $db->getConnection();
    
        // Sanitize inputs
        $item = htmlspecialchars(strip_tags($item));
       // $commodity_name_id = htmlspecialchars(strip_tags($commodity_name_id));
        $type_id = htmlspecialchars(strip_tags($type_id));
        $description = htmlspecialchars(strip_tags($description));
        $quantity = htmlspecialchars(strip_tags($quantity));
        $unit = htmlspecialchars(strip_tags($unit));
        $is_active = htmlspecialchars(strip_tags($is_active));
        $max_senior_quantity = htmlspecialchars(strip_tags($max_senior_quantity));
        $max_junior_quantity = htmlspecialchars(strip_tags($max_junior_quantity));
    
        // ✅ Check for duplicates (case-insensitive)
        if ($this->itemExists($type_id, $item)) {
            return ['success' => false, 'message' => 'Item already exists.'];
        }
    
        $query = "INSERT INTO fudscoops_commodity_Items 
                    ( type_id, commodity_item, commodity_description, quantity, price_unit, max_senior_quantity, max_junior_quantity, is_active)
                  VALUES 
                    ( :type_id, :item, :description, :quantity, :unit, :max_senior_quantity, :max_junior_quantity, :is_active)";
        
        $stmt = $con->prepare($query);
    
       if (!$stmt) {
            error_log("Prepare failed: " . $this->conn->error);
            return false;
            }
    
        // Bind parameters
        
      //  $stmt->bindParam(':comm_id', $commodity_name_id);
        $stmt->bindParam(':type_id', $type_id);
        $stmt->bindParam(':item', $item);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':unit', $unit);
        $stmt->bindParam(':max_senior_quantity', $max_senior_quantity);
        $stmt->bindParam(':max_junior_quantity', $max_junior_quantity);
        $stmt->bindParam(':is_active', $is_active);
    
        if (!$stmt->execute()) {
                print_r($stmt->errorInfo()); // See error in browser or logs
                return false;
            }
            
            return true;
    }
    
    
    public function commoditySchedule($start,$stop,$batch,$commodity_name_id,$level,$for)
    {

        $db = new DB();
        $con = $db->getConnection();
        $start = date('Y-m-d', strtotime($start));
        $stop = date('Y-m-d', strtotime($stop));
        
       
            $query = "INSERT INTO fudscoops_commodity_schedule (start_date,stop_date,commodity_name_id,batch,schedule_level,schedule_for)
                 VALUES(:start,:stop,:comm_name,:batch,:level,:for)";
            $stm2 = $con->prepare($query);
            $stm2->bindParam(":start", $start, PDO::PARAM_STR);
            $stm2->bindParam(":stop", $stop, PDO::PARAM_STR);
            $stm2->bindParam(":comm_name", $session, PDO::PARAM_INT);
            $stm2->bindParam(":batch", $type, PDO::PARAM_INT);
            $stm2->bindParam(":level", $level, PDO::PARAM_STR);
            $stm2->bindParam(":for", $for, PDO::PARAM_STR);
            $stm2->execute();
            $count = $stm2->rowCount();
            if ($count>0) {
                return 1;
            }else {
                return -1;
            }                            
    }

    
    public function getItem($item_id) {
        $query = "SELECT i.*, t.type_name 
                 FROM CommodityItems i
                 JOIN CommodityTypes t ON i.type_id = t.type_id
                 WHERE i.item_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$item_id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllItems() {
        $query = "SELECT i.*, t.type_name 
                 FROM CommodityItems i
                 JOIN CommodityTypes t ON i.type_id = t.type_id
                 ORDER BY i.name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
public static function getItemsTable() {
    $db = new DB();
    $con = $db->getConnection();
    try {
        // Fetch items JOINED with their types
        $query = "
            SELECT 
                ci.commodity_item_id,
                ci.type_id,
                ci.commodity_item,
                ci.commodity_description,
                ci.quantity,
                ci.price_unit,
                ci.max_senior_quantity,
                ci.max_junior_quantity,
                ct.type_name
            FROM fudscoops_commodity_Items ci
            LEFT JOIN CommodityTypes ct ON ci.type_id = ct.type_id
            ORDER BY ct.type_name, ci.commodity_item
        ";

        $stmt = $con->prepare($query);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Start building HTML table
        $html = '<div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Item Name</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Available Qty</th>
                            <th>Price/Unit</th>
                            <th>Max Senior Qty</th>
                            <th>Max Junior Qty</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';

        // Populate table rows
        $sn = 0;
        foreach ($items as $item) {
            $sn++;
            $html .= '<tr>
                        <td>' . $sn . '</td>
                        <td>' . htmlspecialchars($item['commodity_item'] ?? '') . '</td>
                        <td>' . htmlspecialchars($item['type_name'] ?? 'Uncategorized') . '</td>
                        <td>' . htmlspecialchars($item['commodity_description'] ?? '') . '</td>
                        <td class="quantity-td" data-item-id="' . $item['commodity_item_id'] . '">' . htmlspecialchars($item['quantity'] ?? '0') . '</td>
                        <td>' . htmlspecialchars($item['price_unit'] ?? '0.00') . '</td>
                        <td>' . htmlspecialchars($item['max_senior_quantity'] ?? '0') . '</td>
                        <td>' . htmlspecialchars($item['max_junior_quantity'] ?? '0') . '</td>
                        <td>
                            <button class="btn btn-sm btn-success edit-item"
                                    data-id="' . $item['commodity_item_id'] . '"
                                    data-type-id="' . $item['type_id'] . '"
                                    data-item-name="' . htmlspecialchars($item['commodity_item'] ?? '') . '"
                                    data-description="' . htmlspecialchars($item['commodity_description'] ?? '') . '"
                                    data-quantity="' . htmlspecialchars($item['quantity'] ?? '0') . '"
                                    data-price-unit="' . htmlspecialchars($item['price_unit'] ?? '0.00') . '"
                                    data-max-senior="' . htmlspecialchars($item['max_senior_quantity'] ?? '0') . '"
                                    data-max-junior="' . htmlspecialchars($item['max_junior_quantity'] ?? '0') . '">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-sm btn-info add-quantity"
                                    data-id="' . $item['commodity_item_id'] . '"
                                    data-item-name="' . htmlspecialchars($item['commodity_item'] ?? '') . '">
                                <i class="fas fa-plus"></i> Add Qty
                            </button>
                        </td>
                    </tr>';
        }

        // Close table
        $html .= '</tbody></table></div>';

        return $html;

    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ];
    }
}

    public function addQuantity($itemId, $quantityToAdd) {
            $db = new DB();
            $con = $db->getConnection();
        
            // Update quantity: current + added
            $sql = "UPDATE fudscoops_commodity_Items 
                    SET quantity = quantity + :add_qty 
                    WHERE commodity_item_id = :id";
        
            $stmt = $con->prepare($sql);
            $success = $stmt->execute([
                ':add_qty' => $quantityToAdd,
                ':id' => $itemId
            ]);
        
            if ($success) {
                // Fetch and return new quantity
                $stmt = $con->prepare("SELECT quantity FROM fudscoops_commodity_Items WHERE commodity_item_id = :id");
                $stmt->execute([':id' => $itemId]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return $row ? (int)$row['quantity'] : false;
            }
        
            return false;
        }
    
      public function getAllSupplyItems($supply_id) {
            $query = "SELECT * FROM fudscoops_commodity_supply 
                      INNER JOIN fudscoops_commodity_supply_items 
                      ON fudscoops_commodity_supply.commodity_name_id = fudscoops_commodity_supply_items.commodity_supply_id 
                      INNER JOIN fudscoops_commodity_Items on fudscoops_commodity_Items.commodity_item_id=fudscoops_commodity_supply_items.item_id
                      WHERE fudscoops_commodity_supply.commodity_name_id = :supply_id";
        
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':supply_id', $supply_id, PDO::PARAM_INT);
            $stmt->execute();
        
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    
   public function getAllItemsDropdown($selectedProductId = null) {
        $query = "SELECT i.*, t.type_name 
                 FROM fudscoops_commodity_Items  i
                 JOIN CommodityTypes t ON i.type_id = t.type_id
                 ORDER BY i.commodity_item";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $options = '';
        
        foreach ($items as $item) {
            $selected = ($selectedProductId == $item['commodity_item_id']) ? ' selected' : '';
            
            $options .= '<option value="' . htmlspecialchars($item['commodity_item_id'], ENT_QUOTES) . '"' . 
                       $selected . 
                       ' data-unit-price="' . htmlspecialchars($item['price_unit'], ENT_QUOTES) . '"' .
                       ' data-item-qty="' . htmlspecialchars($item['quantity'], ENT_QUOTES) . '"' .
                       ' data-max-senior-quantity="' . htmlspecialchars($item['max_senior_quantity'], ENT_QUOTES) . '"' .
                       ' data-max-junior-quantity="' . htmlspecialchars($item['max_junior_quantity'], ENT_QUOTES) . '">' .
                       htmlspecialchars($item['commodity_item'], ENT_QUOTES) . 
                       ' (' . htmlspecialchars($item['quantity'], ENT_QUOTES) . ' in stock)' .
                       '</option>';
        }
        
        return $options;
}

  
    
// Corrected getCommoditySupplyItems method
public static function getCommoditySupplyItems($commodity_supply_id) {
    $db = new DB();
    $con = $db->getConnection();
    
    $r = '';
    $sn = 0;
    
    try {
        // Fixed the JOIN conditions - the issue was in the ON clauses
        $query = "SELECT 
                    fcs.*,
                    fcsi.*,
                    fci.commodity_item,
                    fci.commodity_item_id
                  FROM fudscoops_commodity_supply fcs
                  INNER JOIN fudscoops_commodity_supply_items fcsi 
                    ON fcs.commodity_name_id = fcsi.commodity_supply_id 
                  INNER JOIN fudscoops_commodity_Items fci 
                    ON fci.commodity_item_id = fcsi.item_id
                  WHERE fcs.commodity_name_id = :supply_id
                  ORDER BY fcsi.created_at DESC";
                  
        $stm = $con->prepare($query);
        $stm->bindParam(":supply_id", $commodity_supply_id, PDO::PARAM_INT);
        $stm->execute();
        
        if ($stm->rowCount() > 0) {
            while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
                $sn++;
                $itemId = $row['commodity_supply_item_id'];
                $itemName = htmlspecialchars($row['commodity_item']);
                $unitPrice = $row['unit_price'];
                $remainingQty = $row['remaining_quantity'];
                
                // Format unit price for display
                $displayPrice = number_format($unitPrice, 2);
                
                $r .= '
                <tr class="item-row" data-item-id="'.$itemId.'" data-price="'.$unitPrice.'">
                    <td>'.$sn.'</td>
                    <td>
                        <input type="checkbox" class="form-check-input item-checkbox" 
                               data-item-id="'.$itemId.'" 
                               data-item-name="'.$itemName.'" 
                               data-price="'.$unitPrice.'"
                               name="selected_items[]" 
                               value="'.$itemId.'">
                    </td>
                    <td>'.$itemName.'</td>
                    <td class="unit-price">'.$displayPrice.'</td>
                   
                    <td>
                        <input type="number" class="form-control quantity-input" 
                               data-item-id="'.$itemId.'" 
                               name="quantities['.$itemId.']"
                               min="0" max="'.$remainingQty.'" 
                               value="0" disabled>
                    </td>
                    <td class="subtotal">0.00</td>
                </tr>';
            }
        } else {
            $r = '<tr><td colspan="7" class="text-center text-danger">No items found for this commodity supply</td></tr>';
        }
        
    } catch (PDOException $e) {
        error_log("Error fetching commodity supply items: " . $e->getMessage());
        $r = '<tr><td colspan="7" class="text-center text-danger">Error loading items. Please try again.</td></tr>';
    }
    
    return $r;
}

    
            // Corrected getCommoditySupplyItems method
        public static function getCommoditySupplyItems3($commodity_supply_id) {
            $db = new DB();
            $con = $db->getConnection();
            
            $r = '';
            $sn = 0;
            
            try {
                // $query = "SELECT csi.*, ci.commodity_item, ci.commodity_item_id, cs.commodity_name_id
                //           FROM fudscoops_commodity_supply cs
                //           INNER JOIN fudscoops_commodity_supply_items csi 
                //               ON cs.commodity_name_id = csi.commodity_supply_id 
                //           INNER JOIN fudscoops_commodity_Items ci 
                //               ON ci.commodity_item_id = csi.item_id
                //           WHERE cs.commodity_name_id = :supply_id
                //           AND csi.remaining_quantity > 0
                //           ORDER BY csi.created_at DESC";
                
                 $query = "SELECT * FROM fudscoops_commodity_supply 
                          INNER JOIN fudscoops_commodity_supply_items 
                          ON fudscoops_commodity_supply.commodity_name_id = fudscoops_commodity_supply_items.commodity_supply_id 
                          INNER JOIN fudscoops_commodity_Items 
                          ON fudscoops_commodity_Items.commodity_item_id = fudscoops_commodity_supply_items.item_id
                          WHERE fudscoops_commodity_supply.commodity_name_id = :supply_id
                          ORDER BY fudscoops_commodity_supply_items.created_at DESC";
                          
                
                $stm = $con->prepare($query);
                $stm->bindParam(":supply_id", $commodity_supply_id, PDO::PARAM_INT);
                $stm->execute();
                
                if ($stm->rowCount() > 0) {
                    while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
                        $sn++;
                        $itemId = $row['commodity_supply_item_id'];
                        $itemName = htmlspecialchars($row['commodity_item']);
                        //$unitPrice = number_format($row['unit_price'], 2, '.', '');
                        $unitPrice = $row['unit_price'];
                        $remainingQty = $row['remaining_quantity'];
                        
                        $r .= '
                        <tr class="item-row" data-item-id="'.$itemId.'" data-price="'.$unitPrice.'">
                            <td>'.$sn.'</td>
                            <td>
                                <input type="checkbox" class="form-check-input item-checkbox" 
                                       data-item-id="'.$itemId.'" 
                                       data-item-name="'.$itemName.'" 
                                       data-price="'.$unitPrice.'"
                                       name="selected_items[]" 
                                       value="'.$itemId.'">
                            </td>
                            <td>'.$itemName.'</td>
                            <td class="unit-price">'.$row['unit_price'].'</td>
                            <td>'.$remainingQty.'</td>
                            <td>
                                <input type="number" class="form-control quantity-input" 
                                       data-item-id="'.$itemId.'" 
                                       name="quantities['.$itemId.']"
                                       min="0" max="'.$remainingQty.'" 
                                       value="0" disabled>
                            </td>
                            <td class="subtotal">0.00</td>
                        </tr>';
                    }
                } else {
                    $r = '<tr><td colspan="7" class="text-center text-danger">No items found for this commodity supply</td></tr>';
                }
                
            } catch (PDOException $e) {
                error_log("Error fetching commodity supply items: " . $e->getMessage());
                $r = '<tr><td colspan="7" class="text-center text-danger">Error loading items</td></tr>';
            }
            
            return $r;
        }
        
        // Additional method to process the form submission
        public static function processCommodityLoanApplication($member_id, $commodity_supply_id, $selected_items, $quantities, $total_amount) {
            $db = new DB();
            $con = $db->getConnection();
            
            try {
                $con->beginTransaction();
                
                // Insert main application record
                $app_query = "INSERT INTO fudscoops_commodity_loan_applications 
                              (member_id, commodity_supply_id, total_amount, application_date, status) 
                              VALUES (:member_id, :supply_id, :total_amount, NOW(), 'pending')"; 
                
                $app_stmt = $con->prepare($app_query);
                $app_stmt->bindParam(':member_id', $member_id);
                $app_stmt->bindParam(':supply_id', $commodity_supply_id);
                $app_stmt->bindParam(':total_amount', $total_amount);
                $app_stmt->execute();
                
                $application_id = $con->lastInsertId();
                
                // Insert application items
                $item_query = "INSERT INTO fudscoops_commodity_loan_application_items 
                               (application_id, commodity_supply_item_id, quantity_requested, unit_price, subtotal) 
                               VALUES (:app_id, :item_id, :quantity, :unit_price, :subtotal)";
                
                $item_stmt = $con->prepare($item_query);
                
                // Update remaining quantities
                $update_query = "UPDATE fudscoops_commodity_supply_items 
                                 SET remaining_quantity = remaining_quantity - :quantity 
                                 WHERE commodity_supply_item_id = :item_id AND remaining_quantity >= :quantity";
                
                $update_stmt = $con->prepare($update_query);
                
                foreach ($selected_items as $item_id) {
                    if (isset($quantities[$item_id]) && $quantities[$item_id] > 0) {
                        // Get item details
                        $item_details_query = "SELECT unit_price FROM fudscoops_commodity_supply_items 
                                               WHERE commodity_supply_item_id = :item_id";
                        $details_stmt = $con->prepare($item_details_query);
                        $details_stmt->bindParam(':item_id', $item_id);
                        $details_stmt->execute();
                        $item_details = $details_stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($item_details) {
                            $unit_price = $item_details['unit_price'];
                            $quantity = $quantities[$item_id];
                            $subtotal = $unit_price * $quantity;
                            
                            // Insert application item
                            $item_stmt->bindParam(':app_id', $application_id);
                            $item_stmt->bindParam(':item_id', $item_id);
                            $item_stmt->bindParam(':quantity', $quantity);
                            $item_stmt->bindParam(':unit_price', $unit_price);
                            $item_stmt->bindParam(':subtotal', $subtotal);
                            $item_stmt->execute();
                            
                            // Update remaining quantity
                            $update_stmt->bindParam(':quantity', $quantity);
                            $update_stmt->bindParam(':item_id', $item_id);
                            $update_stmt->execute();
                            
                            if ($update_stmt->rowCount() == 0) {
                                throw new Exception("Insufficient quantity available for item ID: " . $item_id);
                            }
                        }
                    }
                }
                
                $con->commit();
                return array('success' => true, 'application_id' => $application_id, 'message' => 'Application submitted successfully');
                
            } catch (Exception $e) {
                $con->rollback();
                error_log("Error processing commodity loan application: " . $e->getMessage());
                return array('success' => false, 'message' => $e->getMessage());
            }
        }
        
        // Method to check if member has pending applications
        public static function hasPendingApplication($member_id, $commodity_supply_id) {
            $db = new DB();
            $con = $db->getConnection();
            
            $query = "SELECT COUNT(*) as count FROM fudscoops_commodity_loan_applications 
                      WHERE member_id = :member_id 
                      AND commodity_supply_id = :supply_id 
                      AND status IN ('pending', 'approved')";
            
            $stmt = $con->prepare($query);
            $stmt->bindParam(':member_id', $member_id);
            $stmt->bindParam(':supply_id', $commodity_supply_id);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        }
        
        // Method to get member's application history
        public static function getMemberApplications($member_id, $limit = 10) {
            $db = new DB();
            $con = $db->getConnection();
            
            $query = "SELECT cla.*, cs.commodity_name, cs.opening_date, cs.closing_date 
                      FROM fudscoops_commodity_loan_applications cla
                      INNER JOIN fudscoops_commodity_supply cs ON cla.commodity_supply_id = cs.commodity_name_id
                      WHERE cla.member_id = :member_id 
                      ORDER BY cla.application_date DESC 
                      LIMIT :limit";
            
            $stmt = $con->prepare($query);
            $stmt->bindParam(':member_id', $member_id);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

    
    
    public static function getCommoditySupplyItems1($commodity_supply_id) {
        
            $db = new DB();
            $con = $db->getConnection();
          
            $r = '';
            $sn = 0;
            
            try {
                // Query to fetch items from commodity_supply_items table
               $query = "SELECT * FROM fudscoops_commodity_supply 
                          INNER JOIN fudscoops_commodity_supply_items 
                          ON fudscoops_commodity_supply.commodity_name_id = fudscoops_commodity_supply_items.commodity_supply_id 
                          INNER JOIN fudscoops_commodity_Items 
                          ON fudscoops_commodity_Items.commodity_item_id = fudscoops_commodity_supply_items.item_id
                          WHERE fudscoops_commodity_supply.commodity_name_id = :supply_id
                          ORDER BY fudscoops_commodity_supply_items.created_at DESC";
               
                $stm = $con->prepare($query);
                $stm->bindParam(":supply_id", $commodity_supply_id, PDO::PARAM_INT);
                $stm->execute();
                
                if ($stm->rowCount() > 0) {
                    while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
                        $sn++;
                        $r .= '
                        <tr class="'.$row['commodity_supply_item_id'].'">
                            <td>'.$sn.'</td>
                            <td>'.$row['commodity_item'].'</td>
                            <td>'.$row['commodity_item'].'</td>
                            <td>'.number_format($row['unit_price'], 2).'</td>
                            <td> <input type="text" id="qty_request" name="qty_request"></td>
                        </tr>
                        ';
                    }
                } else {
                    $r = '<tr><td colspan="7" class="text-center text-danger">No items found for this commodity supply</td></tr>';
                }
                
            } catch (PDOException $e) {
                error_log("Error fetching commodity supply items: " . $e->getMessage());
                $r = '<tr><td colspan="7" class="text-center text-danger">Error loading items: ' . $e->getMessage() . '</td></tr>';
            }
            
            // $con = null; // Close connection if needed
            return $r;
        }

    public static function getCommoditySupplyItemsPreview($commodity_supply_id)
    {
            $db = new DB();
            $con = $db->getConnection();
            
            $r = ''; 
            $sn = 0;
            
            try {
                $query = "SELECT csi.*, ci.item_name, ci.commodity_item
                          FROM fudscoops_commodity_supply_items csi
                          INNER JOIN fudscoops_commodity_Items ci ON csi.item_id = ci.commodity_item_id
                          WHERE csi.commodity_supply_id = :supply_id
                          ORDER BY csi.created_at DESC";
                
                $stm = $con->prepare($query);
                $stm->bindParam(":supply_id", $commodity_supply_id, PDO::PARAM_INT);
                $stm->execute();
                
                if ($stm->rowCount() > 0) {
                    while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
                        $sn++;
                        $r .= '
                        <tr>
                            <td>'.$sn.'</td>
                            <td>'.$row['commodity_item'].'</td>
                            <td>'.$row['item_name'].'</td>
                            <td>'.number_format($row['unit_price'], 2).'</td>
                            <td>'.$row['allocated_quantity'].'</td>
                            <td>'.$row['remaining_quantity'].'</td>
                        </tr>
                        ';
                    }
                } else {
                    $r = '<tr><td colspan="6" class="text-center">No items available</td></tr>';
                }
                
            } catch (PDOException $e) {
                $r = '<tr><td colspan="6" class="text-center">Error loading data</td></tr>';
            }
            
            return $r;
    }
    

    public function updateItem($id, $data) {
            $db = new DB();
            $con = $db->getConnection();
        
            $sql = "UPDATE fudscoops_commodity_Items SET 
                        commodity_item = :commodity_item,
                        type_id = :type_id,
                        commodity_description = :commodity_description,
                        quantity = :quantity,
                        price_unit = :price_unit,
                        max_senior_quantity = :max_senior_quantity,
                        max_junior_quantity = :max_junior_quantity
                    WHERE commodity_item_id = :id";
        
            $stmt = $con->prepare($sql);
            return $stmt->execute([
                ':commodity_item' => $data['commodity_item'],
                ':type_id' => $data['type_id'],
                ':commodity_description' => $data['commodity_description'],
                ':quantity' => $data['quantity'],
                ':price_unit' => $data['price_unit'],
                ':max_senior_quantity' => $data['max_senior_quantity'],
                ':max_junior_quantity' => $data['max_junior_quantity'],
                ':id' => $id
            ]);
}
    public function deleteItem($item_id, $user_id) {
        // Check if item is used in any loans
        $check_query = "SELECT COUNT(*) as count FROM CommodityLoanItems WHERE item_id = ?";
        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->execute([$item_id]);
        $result = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            // Soft delete
            $query = "UPDATE CommodityItems SET is_active = 0, updated_by = ?, updated_at = NOW() WHERE item_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$user_id, $item_id]);
            return "Item deactivated (used in existing loans)";
        }
        
        // Hard delete
        $query = "DELETE FROM CommodityItems WHERE item_id = ?";
        $stmt = $this->conn->prepare($query);
        
        if($stmt->execute([$item_id])) {
            return "Item deleted successfully";
        }
        return "Failed to delete item";
    }

    // ==================== INVENTORY METHODS ====================
    
    public function updateInventory($item_id, $quantity, $min_per_member, $max_per_member, $updated_by) {
        $query = "UPDATE CommodityInventory SET 
                 total_quantity = :total_quantity,
                 current_quantity = :current_quantity,
                 min_per_member = :min_per_member,
                 max_per_member = :max_per_member,
                 updated_at = NOW(),
                 updated_by = :updated_by
                 WHERE item_id = :item_id";
        
        $stmt = $this->conn->prepare($query);
        
        $quantity = htmlspecialchars(strip_tags($quantity));
        $min_per_member = htmlspecialchars(strip_tags($min_per_member));
        $max_per_member = htmlspecialchars(strip_tags($max_per_member));
        $updated_by = htmlspecialchars(strip_tags($updated_by));
        $item_id = htmlspecialchars(strip_tags($item_id));
        
        $stmt->bindParam(':total_quantity', $quantity);
        $stmt->bindParam(':current_quantity', $quantity);
        $stmt->bindParam(':min_per_member', $min_per_member);
        $stmt->bindParam(':max_per_member', $max_per_member);
        $stmt->bindParam(':updated_by', $updated_by);
        $stmt->bindParam(':item_id', $item_id);
        
        return $stmt->execute();
    }
    
    
 
    
  

    // ==================== BATCH MANAGEMENT FUNCTIONS ====================

    /**
     * Create a new commodity batch 
     */
      
function saveCommoditySupplyItems($commodity_name_id, $items) {
    $items = json_decode($items);
    $commodity_name_id = $commodity_name_id; // Assuming batch_id is passed in the request

    foreach ($items as $item) {
        $stmt = $this->conn->prepare("INSERT INTO fudscoops_commodity_supply_items (commodity_supply_id, item_id, unit_price, allocated_quantity) 
                               VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $commodity_name_id,
            $item->itemId,
            $item->unitPrice,
            $item->quantity
        ]);
    }

     return [
        'success' => true,
        'status' => 'success',
        "message" => "Items saved successfully",
        "commodity_supply_id" => $commodity_name_id,
        "items" => $items
    ];
}

    
     
    // ==================== BATCH METHODS ====================
    
    public function createBatch($batch_name, $description, $opening_date, $closing_date, $max_members, $max_loan_amount, $created_by) {
        try {
            // Validate dates
            if (strtotime($opening_date) > strtotime($closing_date)) {
                return ['success' => false, 'message' => 'Closing date must be after opening date'];
            }
            
            $query = "INSERT INTO CommodityBatches SET 
                     batch_name = :batch_name,
                     description = :description,
                     opening_date = :opening_date,
                     closing_date = :closing_date,
                     max_members_per_batch = :max_members,
                     max_loan_amount = :max_loan_amount,
                     created_by = :created_by";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindValue(':batch_name', htmlspecialchars(strip_tags($batch_name)));
            $stmt->bindValue(':description', htmlspecialchars(strip_tags($description)));
            $stmt->bindValue(':opening_date', $opening_date);
            $stmt->bindValue(':closing_date', $closing_date);
            $stmt->bindValue(':max_members', $max_members ? (int)$max_members : null, PDO::PARAM_INT);
            $stmt->bindValue(':max_loan_amount', $max_loan_amount ? (float)$max_loan_amount : null);
            $stmt->bindValue(':created_by', (int)$created_by, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Batch created successfully',
                    'batch_id' => $this->conn->lastInsertId()
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to create batch'];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }
    
    public function getBatchesTable($include_inactive = false) {
    try {
        // Get batches from database
        $query = "SELECT * FROM CommodityBatches";
        if (!$include_inactive) {
            $query .= " WHERE is_active = 1";
        }
        $query .= " ORDER BY opening_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $batches = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Start building HTML table
        $html = '<div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>Batch ID</th>
                            <th>Batch Name</th>
                            <th>Opening Date</th>
                            <th>Closing Date</th>
                            <th>Max Members</th>
                            <th>Max Loan</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';

        // Populate table rows
        foreach ($batches as $batch) {
            $status = $batch['is_active'] ? 
                '<span class="badge bg-success">Active</span>' : 
                '<span class="badge bg-secondary">Inactive</span>';
            
            $html .= '<tr>
                        <td>' . $batch['batch_id'] . '</td>
                        <td>' . htmlspecialchars($batch['batch_name']) . '</td>
                        <td>' . $batch['opening_date'] . '</td>
                        <td>' . $batch['closing_date'] . '</td>
                        <td>' . ($batch['max_members_per_batch'] ?? 'Unlimited') . '</td>
                        <td>' . ($batch['max_loan_amount'] ? number_format($batch['max_loan_amount'], 2) : 'No limit') . '</td>
                        <td>' . $status . '</td>
                        <td>
                            <button class="btn btn-sm btn-primary edit-batch" 
                                    data-batch-id="' . $batch['batch_id'] . '"
                                    data-batch-name="' . htmlspecialchars($batch['batch_name']) . '"
                                    data-description="' . htmlspecialchars($batch['description'] ?? '') . '"
                                    data-opening-date="' . $batch['opening_date'] . '"
                                    data-closing-date="' . $batch['closing_date'] . '"
                                    data-max-members="' . $batch['max_members_per_batch'] . '"
                                    data-max-loan="' . $batch['max_loan_amount'] . '"
                                    data-is-active="' . $batch['is_active'] . '">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-sm btn-danger delete-batch" 
                                    data-batch-id="' . $batch['batch_id'] . '">
                                <i class="fas fa-trash-alt"></i> Delete
                            </button>
                        </td>
                    </tr>';
        }

        // Close table
        $html .= '</tbody></table></div>';

        return $html; 

    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage(),
            'html' => '<div class="alert alert-danger">Error loading batches</div>'
        ];
    }
}

    public function getBatches($active_only = false) {
        try {
            $query = "SELECT * FROM CommodityBatches";
            if ($active_only) {
                $query .= " WHERE is_active = 1 AND closing_date >= CURDATE()";
            }
            $query .= " ORDER BY opening_date DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return [
                'success' => true,
                'batches' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    public function updateBatchStatus($batch_id, $is_active) {
        try {
            $query = "UPDATE CommodityBatches SET 
                     is_active = :is_active
                     WHERE batch_id = :batch_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':is_active', (bool)$is_active, PDO::PARAM_BOOL);
            $stmt->bindValue(':batch_id', (int)$batch_id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Batch status updated'
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to update batch status'];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

   

}

?>