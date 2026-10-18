<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once('DB.php');
require_once('Settings.php');

/*
 * Commodity requests (workflow from 2026-10-13, the same as loans; db/2026-10-13_commodity_workflow.sql)
 *   1. Member requests items during an open supply and chooses the months to repay. The quantities are
 *      reserved from the supply's remaining stock.                                  status 'pending'
 *   2. Treasurer fixes the quantities (no more than requested) and months; the flat loan interest rate
 *      (Savings Settings) is added once.                                            status 'recommended'
 *      or declines (reserved stock is released).                                    status 'declined'
 *   3. Chairman authorizes ('authorized', chairman_approval 1) or declines ('declined', -1).
 *      Before supply, the Chairman can return an authorized request to the Treasurer.
 *   4. Treasurer records that the items were supplied. Deductions start the following month.
 *                                                                                   status 'supplied'
 *   5. Monthly deductions (one per month, single or CSV upload). When they reach the total the request
 *      becomes 'repaid'.
 * Supply setup (commodity names, items, schedules) stays in Commodity.php.
 *
 * Special requests (request_type 'special', db/2026-10-19_special_commodity.sql): items the cooperative does not
 * stock. The member lists each item with an estimated unit price (R/special_commodity_request.php); there is no
 * supply and no stock (commodity_supply_id 0, items in fudscoops_special_commodity_items). The Treasurer corrects
 * the prices and approves some or all items, then the same steps follow: Chairman, release (recorded as 'supplied';
 * deductions start the next month) and monthly repayment.
 */
class CommodityLoan
{
    const ITEMS = 'fudscoops_commodity_Items'; // capital I, as on the live server

    private static function con()
    {
        $con = (new DB())->getConnection();
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $con;
    }

    private static function h($v)
    {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    }

    private static function money($v)
    {
        return '₦' . number_format((float) $v, 2);
    }

    public static function interestRate()
    {
        return (float) Settings::get('loan_interest_rate', 10);
    }

    public static function maxTenor()
    {
        $t = (int) Settings::get('commodity_max_tenor', 6);
        return $t > 0 ? $t : 6;
    }

    public static function stageLabel($app)
    {
        switch (isset($app['status']) ? $app['status'] : '') {
            case 'pending':     return 'Awaiting Treasurer';
            case 'recommended': return 'Awaiting Chairman';
            case 'authorized':  return self::isSpecial($app) ? 'Awaiting release' : 'Awaiting supply';
            case 'supplied':
            case 'completed':   return 'Repaying';
            case 'repaid':      return 'Fully repaid';
            case 'declined':
            case 'rejected':    return (isset($app['chairman_approval']) && $app['chairman_approval'] == -1) ? 'Declined by Chairman' : 'Declined by Treasurer';
        }
        return ucfirst((string) $app['status']);
    }

    public static function isSpecial($app)
    {
        return isset($app['request_type']) && $app['request_type'] === 'special';
    }

    // Name shown for a request: its supply, or "Special request"
    const NAME_SQL = "COALESCE(s.commodity_name, IF(a.request_type = 'special', 'Special request', NULL)) AS commodity_name";

    public static function stagePill($app)
    {
        $label = self::stageLabel($app);
        $class = strpos($label, 'Declined') === 0 ? 'no' : (in_array($label, ['Repaying', 'Fully repaid'], true) ? 'ok' : 'wait');
        return '<span class="fud-pill ' . $class . '">' . self::h($label) . '</span>';
    }

    // ---------------------------------------------------------------- supply and request

    // ---------------------------------------------------------------- supplies (Secretary/add_commodity_name.php)

    // All supplies, newest first, with item and request counts and a status: open, upcoming or closed
    public static function supplies()
    {
        // Status from the database clock, the same one openSupply() and the member's page use
        $rows = self::con()->query("SELECT s.*,
                                           CASE WHEN s.opening_date > NOW() THEN 'upcoming' WHEN s.closing_date >= NOW() THEN 'open' ELSE 'closed' END AS state,
                                           (SELECT COUNT(*) FROM fudscoops_commodity_supply_items i WHERE i.commodity_supply_id = s.commodity_name_id) AS items,
                                           (SELECT COUNT(*) FROM fudscoops_commodity_loan_applications a WHERE a.commodity_supply_id = s.commodity_name_id) AS requests
                                    FROM fudscoops_commodity_supply s WHERE COALESCE(s.is_deleated, 0) = 0
                                    ORDER BY s.opening_date DESC, s.commodity_name_id DESC")->fetchAll(PDO::FETCH_ASSOC);
        return $rows;
    }

    // Creates ($id = 0) or changes a supply. $opens/$closes: 'YYYY-MM-DDTHH:MM' (datetime-local) or 'YYYY-MM-DD'.
    // Returns ['ok' => true, 'id' => …] or ['ok' => false, 'message' => …]
    public static function saveSupply($id, $name, $opens, $closes)
    {
        $con = self::con();
        $name = strtoupper(trim(preg_replace('/\s+/', ' ', strip_tags((string) $name))));
        $toTime = function ($v, $endOfDay) {
            $v = str_replace('T', ' ', trim((string) $v));
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) {
                $v .= $endOfDay ? ' 23:59:59' : ' 00:00:00';
            } elseif (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $v)) {
                $v .= $endOfDay ? ':59' : ':00';
            }
            $d = DateTime::createFromFormat('Y-m-d H:i:s', $v);
            return $d && $d->format('Y-m-d H:i:s') === $v ? $v : null;
        };
        $open = $toTime($opens, false);
        $close = $toTime($closes, true);
        if ($id == 0 && $name === '') {
            return ['ok' => false, 'message' => 'Enter a name for the supply, e.g. NOVEMBER 2026 SUPPLY.'];
        }
        if (!$open || !$close) {
            return ['ok' => false, 'message' => 'Enter the opening and closing date and time.'];
        }
        if ($close <= $open) {
            return ['ok' => false, 'message' => 'The closing time must be after the opening time.'];
        }
        if ($close <= $con->query("SELECT NOW()")->fetchColumn()) {
            return ['ok' => false, 'message' => 'The closing time has already passed.'];
        }
        if ($id == 0) {
            $stmt = $con->prepare("SELECT COUNT(*) FROM fudscoops_commodity_supply WHERE commodity_name = :n AND COALESCE(is_deleated, 0) = 0");
            $stmt->execute([':n' => $name]);
            if ($stmt->fetchColumn() > 0) {
                return ['ok' => false, 'message' => 'A supply called ' . $name . ' already exists.'];
            }
        }
        // Members see one supply at a time, so periods may not overlap
        $stmt = $con->prepare("SELECT commodity_name, opening_date, closing_date FROM fudscoops_commodity_supply
                               WHERE commodity_name_id <> :id AND COALESCE(is_deleated, 0) = 0 AND opening_date < :close AND closing_date > :open LIMIT 1");
        $stmt->execute([':id' => (int) $id, ':open' => $open, ':close' => $close]);
        if ($clash = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return ['ok' => false, 'message' => 'This period overlaps ' . $clash['commodity_name'] . ' ('
                  . date('d M Y H:i', strtotime($clash['opening_date'])) . ' – ' . date('d M Y H:i', strtotime($clash['closing_date']))
                  . '). Members can only see one supply at a time; choose other dates or change that supply first.'];
        }
        if ($id == 0) {
            $con->prepare("INSERT INTO fudscoops_commodity_supply (commodity_name, opening_date, closing_date) VALUES (:n, :o, :c)")
                ->execute([':n' => $name, ':o' => $open, ':c' => $close]);
            return ['ok' => true, 'id' => (int) $con->lastInsertId()];
        }
        $stmt = $con->prepare("UPDATE fudscoops_commodity_supply SET opening_date = :o, closing_date = :c WHERE commodity_name_id = :id");
        $stmt->execute([':o' => $open, ':c' => $close, ':id' => (int) $id]);
        return ['ok' => true, 'id' => (int) $id];
    }

    // One supply (with status), or null
    public static function supply($supplyId)
    {
        foreach (self::supplies() as $s) {
            if ((int) $s['commodity_name_id'] === (int) $supplyId) {
                return $s;
            }
        }
        return null;
    }

    // Items already in a supply: allocated, requested (reserved by members' requests) and left
    public static function supplyItemsList($supplyId)
    {
        $stmt = self::con()->prepare("SELECT si.commodity_supply_item_id, si.item_id, si.unit_price, si.allocated_quantity, si.remaining_quantity,
                                             ci.commodity_item, ci.commodity_description,
                                             (SELECT COUNT(DISTINCT li.application_id) FROM fudscoops_commodity_loan_application_items li
                                               WHERE li.commodity_supply_item_id = si.commodity_supply_item_id) AS request_count
                                      FROM fudscoops_commodity_supply_items si
                                      LEFT JOIN " . self::ITEMS . " ci ON ci.commodity_item_id = si.item_id
                                      WHERE si.commodity_supply_id = :s ORDER BY ci.commodity_item, si.commodity_supply_item_id");
        $stmt->execute([':s' => (int) $supplyId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $r['requested'] = max(0, (float) $r['allocated_quantity'] - (float) $r['remaining_quantity']);
        }
        return $rows;
    }

    // Changes an item's price and total quantity in a supply. The quantity cannot go below what members have
    // already requested; what is left moves by the same amount. Returns 1 or a message.
    public static function updateSupplyItem($rowId, $price, $allocated)
    {
        $con = self::con();
        $stmt = $con->prepare("SELECT allocated_quantity, remaining_quantity FROM fudscoops_commodity_supply_items WHERE commodity_supply_item_id = :id");
        $stmt->execute([':id' => (int) $rowId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return 'Item not found. Reload the page.';
        }
        $price = round((float) preg_replace('/[^0-9.]/', '', (string) $price), 2);
        $allocated = trim((string) $allocated);
        if ($price <= 0) {
            return 'Enter a unit price above 0.';
        }
        if (!ctype_digit($allocated) || (int) $allocated < 1) {
            return 'Enter a whole quantity of at least 1.';
        }
        $requested = (float) $row['allocated_quantity'] - (float) $row['remaining_quantity'];
        if ((int) $allocated < $requested) {
            return 'Members have already requested ' . (int) $requested . '; the quantity cannot be less than that.';
        }
        $con->prepare("UPDATE fudscoops_commodity_supply_items SET unit_price = :p, allocated_quantity = :a, remaining_quantity = :r
                       WHERE commodity_supply_item_id = :id")
            ->execute([':p' => $price, ':a' => (int) $allocated, ':r' => (int) $allocated - $requested, ':id' => (int) $rowId]);
        return 1;
    }

    // Removes an item from a supply, only if no member has requested it. Returns 1 or a message.
    public static function removeSupplyItem($rowId)
    {
        $con = self::con();
        $stmt = $con->prepare("SELECT COUNT(*) FROM fudscoops_commodity_loan_application_items WHERE commodity_supply_item_id = :id");
        $stmt->execute([':id' => (int) $rowId]);
        if ($stmt->fetchColumn() > 0) {
            return 'Members have requested this item, so it cannot be removed. Reduce its quantity instead.';
        }
        $stmt = $con->prepare("DELETE FROM fudscoops_commodity_supply_items WHERE commodity_supply_item_id = :id");
        $stmt->execute([':id' => (int) $rowId]);
        return $stmt->rowCount() ? 1 : 'Item not found. Reload the page.';
    }

    // Stops new requests now (requests already made carry on through the Treasurer and Chairman)
    public static function closeSupplyNow($id)
    {
        $stmt = self::con()->prepare("UPDATE fudscoops_commodity_supply SET closing_date = NOW() - INTERVAL 1 SECOND
                                      WHERE commodity_name_id = :id AND closing_date > NOW() AND opening_date <= NOW()");
        $stmt->execute([':id' => (int) $id]);
        return $stmt->rowCount() > 0;
    }

    // The supply open for requests now (latest closing date first), or null
    public static function openSupply()
    {
        $stmt = self::con()->query("SELECT * FROM fudscoops_commodity_supply
                                    WHERE opening_date <= NOW() AND closing_date >= NOW() AND COALESCE(is_deleated, 0) = 0
                                    ORDER BY closing_date DESC LIMIT 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // Most a member may take of an item: senior staff (SP) and junior staff limits on the item; null = no limit
    private static function memberMax($item, $spNo)
    {
        $raw = stripos((string) $spNo, 'SP') === 0 ? $item['max_senior_quantity'] : $item['max_junior_quantity'];
        $max = (int) $raw;
        return $max > 0 ? $max : null;
    }

    public static function supplyItems($supplyId, $spNo)
    {
        $stmt = self::con()->prepare("SELECT si.commodity_supply_item_id, si.unit_price, si.remaining_quantity, ci.commodity_item,
                                             ci.commodity_description, ci.max_senior_quantity, ci.max_junior_quantity
                                      FROM fudscoops_commodity_supply_items si
                                      JOIN " . self::ITEMS . " ci ON ci.commodity_item_id = si.item_id
                                      WHERE si.commodity_supply_id = :s ORDER BY ci.commodity_item");
        $stmt->execute([':s' => $supplyId]);
        $items = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $item) {
            $max = self::memberMax($item, $spNo);
            $item['max_for_member'] = (int) ($max === null ? $item['remaining_quantity'] : min($max, $item['remaining_quantity']));
            $items[] = $item;
        }
        return $items;
    }

    // The member's request in this supply that is not declined, or null
    public static function memberRequestInSupply($memberId, $supplyId)
    {
        $stmt = self::con()->prepare("SELECT * FROM fudscoops_commodity_loan_applications
                                      WHERE member_id = :m AND commodity_supply_id = :s AND status NOT IN ('declined', 'rejected')
                                      ORDER BY application_id DESC LIMIT 1");
        $stmt->execute([':m' => $memberId, ':s' => $supplyId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // $quantities: [commodity_supply_item_id => quantity]. Returns 1 or a message.
    public static function submitRequest($memberId, $spNo, $supplyId, $quantities, $tenor)
    {
        $con = self::con();
        $stmt = $con->prepare("SELECT treasurer_approval, chairman_approval FROM fudscoops_member WHERE member_id = :m AND is_deleted = 0");
        $stmt->execute([':m' => $memberId]);
        $member = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$member || $member['treasurer_approval'] != 1 || $member['chairman_approval'] != 1) {
            return 'Only approved members can request commodity.';
        }
        $supply = self::openSupply();
        if (!$supply || (int) $supply['commodity_name_id'] !== (int) $supplyId) {
            return 'This commodity supply is closed. Reload the page to see the current supply.';
        }
        if (self::memberRequestInSupply($memberId, $supplyId)) {
            return 'You already have a request for this supply.';
        }
        $max = self::maxTenor();
        if (!ctype_digit((string) $tenor) || (int) $tenor < 1 || (int) $tenor > $max) {
            return 'Choose a repayment period of 1 to ' . $max . ' months.';
        }
        $wanted = [];
        foreach ((array) $quantities as $itemId => $qty) {
            $qty = trim((string) $qty);
            if ($qty === '' || (ctype_digit($qty) && (int) $qty === 0)) {
                continue;
            }
            if (!ctype_digit($qty)) {
                return 'Enter whole numbers for the quantities.';
            }
            $wanted[(int) $itemId] = (int) $qty;
        }
        if (!$wanted) {
            return 'Choose at least one item.';
        }

        try {
            $con->beginTransaction();
            $lines = [];
            $total = 0;
            $item = $con->prepare("SELECT si.*, ci.commodity_item, ci.max_senior_quantity, ci.max_junior_quantity
                                   FROM fudscoops_commodity_supply_items si JOIN " . self::ITEMS . " ci ON ci.commodity_item_id = si.item_id
                                   WHERE si.commodity_supply_item_id = :id AND si.commodity_supply_id = :s FOR UPDATE");
            foreach ($wanted as $itemId => $qty) {
                $item->execute([':id' => $itemId, ':s' => $supplyId]);
                $row = $item->fetch(PDO::FETCH_ASSOC);
                if (!$row) {
                    $con->rollBack();
                    return 'One of the items is not part of this supply. Reload the page and try again.';
                }
                $limit = self::memberMax($row, $spNo);
                if ($limit !== null && $qty > $limit) {
                    $con->rollBack();
                    return 'You can request at most ' . $limit . ' of ' . $row['commodity_item'] . '.';
                }
                if ($qty > $row['remaining_quantity']) {
                    $con->rollBack();
                    return 'Only ' . (int) $row['remaining_quantity'] . ' of ' . $row['commodity_item'] . ' is left.';
                }
                $lines[] = [$itemId, $qty, (float) $row['unit_price']];
                $total += $qty * (float) $row['unit_price'];
            }

            $con->prepare("INSERT INTO fudscoops_commodity_loan_applications
                               (member_id, commodity_supply_id, total_amount, requested_amount, tenor, application_date, status)
                           VALUES (:m, :s, :t, :t2, :tenor, NOW(), 'pending')")
                ->execute([':m' => $memberId, ':s' => $supplyId, ':t' => $total, ':t2' => $total, ':tenor' => (int) $tenor]);
            $appId = (int) $con->lastInsertId();
            $insert = $con->prepare("INSERT INTO fudscoops_commodity_loan_application_items
                                         (application_id, commodity_supply_item_id, quantity_requested, quantity_approved, disbursed_quantity, unit_price, subtotal)
                                     VALUES (:a, :i, :q, 0, 0, :p, :sub)");
            $reserve = $con->prepare("UPDATE fudscoops_commodity_supply_items SET remaining_quantity = remaining_quantity - :q
                                      WHERE commodity_supply_item_id = :i");
            foreach ($lines as $line) {
                list($itemId, $qty, $price) = $line;
                $insert->execute([':a' => $appId, ':i' => $itemId, ':q' => $qty, ':p' => $price, ':sub' => $qty * $price]);
                $reserve->execute([':q' => $qty, ':i' => $itemId]);
            }
            $con->commit();
            return 1;
        } catch (Exception $e) {
            if ($con->inTransaction()) {
                $con->rollBack();
            }
            error_log('Commodity request: ' . $e->getMessage());
            return 'Could not save the request. Please try again.';
        }
    }

    // ---------------------------------------------------------------- special requests

    const MAX_SPECIAL_ITEMS = 20;

    // The member's special request still in progress (not yet released or declined), or null
    public static function openSpecialRequest($memberId)
    {
        $stmt = self::con()->prepare("SELECT * FROM fudscoops_commodity_loan_applications
                                      WHERE member_id = :m AND request_type = 'special' AND status IN ('pending', 'recommended', 'authorized')
                                      ORDER BY application_id DESC LIMIT 1");
        $stmt->execute([':m' => $memberId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // $items: [['name' => , 'desc' => , 'qty' => , 'price' => (estimated unit price)], ...]. Returns 1 or a message.
    public static function submitSpecialRequest($memberId, $items, $tenor)
    {
        $con = self::con();
        $stmt = $con->prepare("SELECT treasurer_approval, chairman_approval FROM fudscoops_member WHERE member_id = :m AND is_deleted = 0");
        $stmt->execute([':m' => $memberId]);
        $member = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$member || $member['treasurer_approval'] != 1 || $member['chairman_approval'] != 1) {
            return 'Only approved members can make a special commodity request.';
        }
        if (self::openSpecialRequest($memberId)) {
            return 'You already have a special request in progress. Wait until it is released or declined.';
        }
        $max = self::maxTenor();
        if (!ctype_digit((string) $tenor) || (int) $tenor < 1 || (int) $tenor > $max) {
            return 'Choose a repayment period of 1 to ' . $max . ' months.';
        }
        $lines = [];
        foreach ((array) $items as $i => $item) {
            $name = trim(strip_tags(isset($item['name']) ? (string) $item['name'] : ''));
            $desc = trim(strip_tags(isset($item['desc']) ? (string) $item['desc'] : ''));
            $qty = trim(isset($item['qty']) ? (string) $item['qty'] : '');
            $price = preg_replace('/[^0-9.]/', '', isset($item['price']) ? (string) $item['price'] : '');
            $n = $i + 1;
            if ($name === '') {
                return "Item $n: enter the item name.";
            }
            if (!ctype_digit($qty) || (int) $qty < 1) {
                return "$name: enter a whole-number quantity of at least 1.";
            }
            if ($price === '' || !is_numeric($price) || (float) $price <= 0) {
                return "$name: enter the estimated price of one item.";
            }
            $lines[] = [mb_substr($name, 0, 100), mb_substr($desc, 0, 255), (int) $qty, round((float) $price, 2)];
        }
        if (!$lines) {
            return 'Add at least one item.';
        }
        if (count($lines) > self::MAX_SPECIAL_ITEMS) {
            return 'Request at most ' . self::MAX_SPECIAL_ITEMS . ' items at a time.';
        }
        $total = 0;
        foreach ($lines as $l) {
            $total += $l[2] * $l[3];
        }

        try {
            $con->beginTransaction();
            $con->prepare("INSERT INTO fudscoops_commodity_loan_applications
                               (member_id, commodity_supply_id, request_type, total_amount, requested_amount, tenor, application_date, status)
                           VALUES (:m, 0, 'special', :t, :t2, :tenor, NOW(), 'pending')")
                ->execute([':m' => $memberId, ':t' => $total, ':t2' => $total, ':tenor' => (int) $tenor]);
            $appId = (int) $con->lastInsertId();
            $insert = $con->prepare("INSERT INTO fudscoops_special_commodity_items
                                         (application_id, item_name, description, quantity_requested, estimated_price, unit_price, quantity_approved, subtotal)
                                     VALUES (:a, :n, :d, :q, :p, :p2, 0, :sub)");
            foreach ($lines as $l) {
                $insert->execute([':a' => $appId, ':n' => $l[0], ':d' => $l[1], ':q' => $l[2], ':p' => $l[3], ':p2' => $l[3], ':sub' => $l[2] * $l[3]]);
            }
            $con->commit();
            return 1;
        } catch (Exception $e) {
            if ($con->inTransaction()) {
                $con->rollBack();
            }
            error_log('Special commodity request: ' . $e->getMessage());
            return 'Could not save the request. Please try again.';
        }
    }

    // ---------------------------------------------------------------- details

    public static function application($appId)
    {
        $stmt = self::con()->prepare("SELECT a.*, " . self::NAME_SQL . ", e.sp_no, e.title, e.fname, e.lname, e.oname, e.phone_no,
                                             (SELECT COALESCE(SUM(r.amount_paid), 0) FROM fudscoops_commodity_repayments r
                                               WHERE r.application_id = a.application_id) AS repaid
                                      FROM fudscoops_commodity_loan_applications a
                                      LEFT JOIN fudscoops_commodity_supply s ON s.commodity_name_id = a.commodity_supply_id
                                      LEFT JOIN fudscoops_member m ON m.member_id = a.member_id
                                      LEFT JOIN employee e ON e.employee_id = m.employee_id
                                      WHERE a.application_id = :id");
        $stmt->execute([':id' => $appId]);
        $app = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($app) {
            $app['payable'] = self::payable($app);
            $app['balance'] = max(0, $app['payable'] - $app['repaid']);
        }
        return $app ?: null;
    }

    private static function payable($app)
    {
        return (float) ($app['total_payable'] !== null ? $app['total_payable'] : $app['total_amount']);
    }

    public static function lines($appId)
    {
        $type = self::con()->prepare("SELECT request_type FROM fudscoops_commodity_loan_applications WHERE application_id = :id");
        $type->execute([':id' => $appId]);
        if ($type->fetchColumn() === 'special') {
            // Same keys as a supply line; no supply item, so no stock (commodity_supply_item_id null)
            $stmt = self::con()->prepare("SELECT special_item_id AS application_item_id, application_id, NULL AS commodity_supply_item_id,
                                                 quantity_requested, quantity_approved, unit_price, estimated_price, subtotal,
                                                 item_name AS commodity_item, COALESCE(description, '') AS commodity_description, 0 AS remaining_quantity
                                          FROM fudscoops_special_commodity_items WHERE application_id = :id ORDER BY special_item_id");
            $stmt->execute([':id' => $appId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        $stmt = self::con()->prepare("SELECT li.*, ci.commodity_item, ci.commodity_description, si.remaining_quantity
                                      FROM fudscoops_commodity_loan_application_items li
                                      JOIN fudscoops_commodity_supply_items si ON si.commodity_supply_item_id = li.commodity_supply_item_id
                                      JOIN " . self::ITEMS . " ci ON ci.commodity_item_id = si.item_id
                                      WHERE li.application_id = :id ORDER BY ci.commodity_item");
        $stmt->execute([':id' => $appId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Quantity currently reserved for a line: the Treasurer's quantity once reviewed, otherwise the request
    private static function reserved($app, $line)
    {
        return (int) (empty($app['treasurer_date']) ? $line['quantity_requested'] : $line['quantity_approved']);
    }

    private static function changeStock($con, $supplyItemId, $delta)
    {
        if ($supplyItemId === null) { // special request item: not from stock
            return true;
        }
        if ($delta < 0) { // take more from stock
            $stmt = $con->prepare("UPDATE fudscoops_commodity_supply_items SET remaining_quantity = remaining_quantity - :q
                                   WHERE commodity_supply_item_id = :i AND remaining_quantity >= :q2");
            $stmt->execute([':q' => -$delta, ':q2' => -$delta, ':i' => $supplyItemId]);
            return $stmt->rowCount() > 0;
        }
        if ($delta > 0) { // give back, never above the allocation
            $con->prepare("UPDATE fudscoops_commodity_supply_items SET remaining_quantity = LEAST(allocated_quantity, remaining_quantity + :q)
                           WHERE commodity_supply_item_id = :i")->execute([':q' => $delta, ':i' => $supplyItemId]);
        }
        return true;
    }

    private static function lockApp($con, $appId)
    {
        $stmt = $con->prepare("SELECT * FROM fudscoops_commodity_loan_applications WHERE application_id = :id FOR UPDATE");
        $stmt->execute([':id' => $appId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ---------------------------------------------------------------- Treasurer and Chairman

    // $quantities: [application_item_id => quantity]. $prices: [application_item_id => unit price], special requests
    // only (the Treasurer corrects the member's estimates); supply items keep the supply price.
    public static function treasurerReview($appId, $quantities, $tenor, $comment, $recommend, $by, $prices = [])
    {
        $con = self::con();
        try {
            $con->beginTransaction();
            $app = self::lockApp($con, $appId);
            if (!$app) {
                $con->rollBack();
                return 'Request not found.';
            }
            if (!in_array($app['status'], ['pending', 'recommended'], true) || $app['chairman_approval'] != 0) {
                $con->rollBack();
                return 'This request has already been decided. Reload the page.';
            }
            $lines = self::lines($appId);
            $comment = trim((string) $comment);

            if (!$recommend) {
                if ($comment === '') {
                    $con->rollBack();
                    return 'Give the reason for declining; the member will see it.';
                }
                foreach ($lines as $line) {
                    self::changeStock($con, $line['commodity_supply_item_id'], self::reserved($app, $line));
                }
                $con->prepare("UPDATE fudscoops_commodity_loan_applications SET status = 'declined', treasurer_comment = :c,
                                      treasurer_date = CURDATE(), reviewed_by = :by, updated_at = NOW() WHERE application_id = :id")
                    ->execute([':c' => $comment, ':by' => $by, ':id' => $appId]);
                $con->commit();
                return 1;
            }

            $max = self::maxTenor();
            if (!ctype_digit((string) $tenor) || (int) $tenor < 1 || (int) $tenor > $max) {
                $con->rollBack();
                return 'Choose a repayment period of 1 to ' . $max . ' months.';
            }
            $total = 0;
            $special = self::isSpecial($app);
            $update = $special
                ? $con->prepare("UPDATE fudscoops_special_commodity_items SET quantity_approved = :q, unit_price = :p, subtotal = :s
                                 WHERE special_item_id = :id AND application_id = :a")
                : $con->prepare("UPDATE fudscoops_commodity_loan_application_items SET quantity_approved = :q, subtotal = :s
                                 WHERE application_item_id = :id");
            foreach ($lines as $line) {
                $key = $line['application_item_id'];
                $qty = isset($quantities[$key]) ? trim((string) $quantities[$key]) : '';
                if (!ctype_digit($qty) || (int) $qty > $line['quantity_requested']) {
                    $con->rollBack();
                    return 'Enter a quantity from 0 to ' . $line['quantity_requested'] . ' for ' . $line['commodity_item'] . '.';
                }
                $qty = (int) $qty;
                if ($special) {
                    $price = preg_replace('/[^0-9.]/', '', isset($prices[$key]) ? (string) $prices[$key] : (string) $line['unit_price']);
                    if ($price === '' || !is_numeric($price) || ((float) $price <= 0 && $qty > 0)) {
                        $con->rollBack();
                        return 'Enter the unit price of ' . $line['commodity_item'] . '.';
                    }
                    $price = round((float) $price, 2);
                    $update->execute([':q' => $qty, ':p' => $price, ':s' => $qty * $price, ':id' => $key, ':a' => $appId]);
                    $total += $qty * $price;
                    continue;
                }
                if (!self::changeStock($con, $line['commodity_supply_item_id'], self::reserved($app, $line) - $qty)) {
                    $con->rollBack();
                    return 'Not enough ' . $line['commodity_item'] . ' left in this supply for ' . $qty . '.';
                }
                $update->execute([':q' => $qty, ':s' => $qty * $line['unit_price'], ':id' => $key]);
                $total += $qty * $line['unit_price'];
            }
            if ($total <= 0) {
                $con->rollBack();
                return 'Approve at least one item, or decline the request.';
            }
            $rate = self::interestRate();
            $interest = round($total * $rate / 100, 2);
            $payable = $total + $interest;
            $con->prepare("UPDATE fudscoops_commodity_loan_applications
                           SET status = 'recommended', total_amount = :t, tenor = :tenor, interest_rate = :r, interest_amount = :i,
                               total_payable = :p, monthly_amount = :m, treasurer_comment = :c, treasurer_date = CURDATE(),
                               reviewed_by = :by, updated_at = NOW()
                           WHERE application_id = :id")
                ->execute([':t' => $total, ':tenor' => (int) $tenor, ':r' => $rate, ':i' => $interest, ':p' => $payable,
                           ':m' => round($payable / (int) $tenor, 2), ':c' => $comment, ':by' => $by, ':id' => $appId]);
            $con->commit();
            return 1;
        } catch (Exception $e) {
            if ($con->inTransaction()) {
                $con->rollBack();
            }
            error_log('Commodity review: ' . $e->getMessage());
            return 'Could not save the decision. Please try again.';
        }
    }

    public static function chairmanDecision($appId, $authorize, $comment)
    {
        $con = self::con();
        try {
            $con->beginTransaction();
            $app = self::lockApp($con, $appId);
            if (!$app || $app['status'] !== 'recommended' || $app['chairman_approval'] != 0) {
                $con->rollBack();
                return ($app && $app['status'] === 'pending') ? 'The Treasurer has not fixed this request yet.' : 'This request has already been decided. Reload the page.';
            }
            $comment = trim((string) $comment);
            if ($authorize) {
                $con->prepare("UPDATE fudscoops_commodity_loan_applications SET status = 'authorized', chairman_approval = 1,
                                      chairman_comment = :c, chairman_date = CURDATE(), updated_at = NOW() WHERE application_id = :id")
                    ->execute([':c' => $comment, ':id' => $appId]);
            } else {
                if ($comment === '') {
                    $con->rollBack();
                    return 'Give the reason for declining; the member will see it.';
                }
                foreach (self::lines($appId) as $line) {
                    self::changeStock($con, $line['commodity_supply_item_id'], self::reserved($app, $line));
                }
                $con->prepare("UPDATE fudscoops_commodity_loan_applications SET status = 'declined', chairman_approval = -1,
                                      chairman_comment = :c, chairman_date = CURDATE(), updated_at = NOW() WHERE application_id = :id")
                    ->execute([':c' => $comment, ':id' => $appId]);
            }
            $con->commit();
            return 1;
        } catch (Exception $e) {
            if ($con->inTransaction()) {
                $con->rollBack();
            }
            error_log('Commodity Chairman decision: ' . $e->getMessage());
            return 'Could not save the decision. Please try again.';
        }
    }

    // Before supply, the Chairman can send an authorized request back to the Treasurer
    public static function chairmanReturn($appId)
    {
        $stmt = self::con()->prepare("UPDATE fudscoops_commodity_loan_applications SET status = 'pending', chairman_approval = 0,
                                             chairman_date = NULL, updated_at = NOW()
                                      WHERE application_id = :id AND status = 'authorized'");
        $stmt->execute([':id' => $appId]);
        return $stmt->rowCount() ? 1 : 'Only authorized requests that have not been supplied can be returned.';
    }

    public static function recordSupply($appId, $date, $note, $by)
    {
        $d = DateTime::createFromFormat('Y-m-d', (string) $date);
        if (!$d || $d->format('Y-m-d') !== $date) {
            return 'Enter the date the items were supplied.';
        }
        if ($date > date('Y-m-d')) {
            return 'The supply date cannot be in the future.';
        }
        $start = (new DateTime($date))->modify('first day of next month')->format('Y-m-d');
        $con = self::con();
        $stmt = $con->prepare("UPDATE fudscoops_commodity_loan_applications
                               SET status = 'supplied', disbursement_date = :d, supplied_by = :by, supply_note = :n,
                                   repayment_start = :start, updated_at = NOW()
                               WHERE application_id = :id AND status = 'authorized' AND chairman_approval = 1");
        $stmt->execute([':d' => $date, ':by' => $by, ':n' => trim((string) $note), ':start' => $start, ':id' => $appId]);
        if (!$stmt->rowCount()) {
            return 'Only requests the Chairman has authorized can be recorded as supplied or released.';
        }
        $con->prepare("UPDATE fudscoops_commodity_loan_application_items SET disbursed_quantity = quantity_approved WHERE application_id = :id")
            ->execute([':id' => $appId]);
        return 1;
    }

    // One deduction per request per month; recording a month again replaces it. Returns 1 or a message.
    public static function saveRepayment($appId, $date, $amount, $by)
    {
        $app = self::application($appId);
        if (!$app || $app['status'] !== 'supplied') {
            return ($app && $app['status'] === 'repaid') ? 'This request is already fully repaid.' : 'Deductions can only be recorded after the items are supplied.';
        }
        $ts = strtotime((string) $date);
        if (!$ts) {
            return 'Enter the month of the deduction.';
        }
        $month = date('Y-m-01', $ts);
        if ($app['repayment_start'] && $month < $app['repayment_start']) {
            return 'Deductions start in ' . date('F Y', strtotime($app['repayment_start'])) . '.';
        }
        $amount = round((float) preg_replace('/[^0-9.]/', '', (string) $amount), 2);
        if ($amount <= 0) {
            return 'Enter the amount deducted.';
        }
        $con = self::con();
        $stmt = $con->prepare("SELECT COALESCE(SUM(amount_paid), 0) FROM fudscoops_commodity_repayments WHERE application_id = :id AND repayment_month <> :m");
        $stmt->execute([':id' => $appId, ':m' => $month]);
        $others = (float) $stmt->fetchColumn();
        if ($others + $amount > $app['payable'] + 0.005) {
            return 'That is more than the balance of ' . self::money(max(0, $app['payable'] - $others)) . '.';
        }
        $con->prepare("INSERT INTO fudscoops_commodity_repayments (application_id, member_id, repayment_month, amount_paid, recorded_by)
                       VALUES (:id, :m, :month, :a, :by)
                       ON DUPLICATE KEY UPDATE amount_paid = VALUES(amount_paid), recorded_by = VALUES(recorded_by), recorded_at = NOW()")
            ->execute([':id' => $appId, ':m' => $app['member_id'], ':month' => $month, ':a' => $amount, ':by' => $by]);
        if ($others + $amount >= $app['payable'] - 0.005) {
            $con->prepare("UPDATE fudscoops_commodity_loan_applications SET status = 'repaid', repaid_date = CURDATE(), updated_at = NOW()
                           WHERE application_id = :id")->execute([':id' => $appId]);
        }
        return 1;
    }

    // Adds a payment to the current month's deduction (settling from savings when a member leaves)
    public static function creditRequest($appId, $amount, $by)
    {
        $app = self::application($appId);
        if (!$app || !in_array($app['status'], ['supplied', 'completed'], true)) {
            return 'The commodity request is no longer being repaid.';
        }
        $con = self::con();
        $con->prepare("INSERT INTO fudscoops_commodity_repayments (application_id, member_id, repayment_month, amount_paid, recorded_by)
                       VALUES (:id, :m, :month, :a, :by)
                       ON DUPLICATE KEY UPDATE amount_paid = amount_paid + VALUES(amount_paid), recorded_by = VALUES(recorded_by), recorded_at = NOW()")
            ->execute([':id' => $appId, ':m' => $app['member_id'], ':month' => date('Y-m-01'), ':a' => round($amount, 2), ':by' => $by]);
        if ($app['repaid'] + $amount >= $app['payable'] - 0.005) {
            $con->prepare("UPDATE fudscoops_commodity_loan_applications SET status = 'repaid', repaid_date = CURDATE(), updated_at = NOW()
                           WHERE application_id = :id")->execute([':id' => $appId]);
        }
        return 1;
    }

    public static function repayments($appId)
    {
        $stmt = self::con()->prepare("SELECT * FROM fudscoops_commodity_repayments WHERE application_id = :id ORDER BY repayment_month");
        $stmt->execute([':id' => $appId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Oldest request being repaid by this staff member (batch deduction upload)
    public static function openRequestForStaff($spNo)
    {
        $stmt = self::con()->prepare("SELECT a.application_id FROM fudscoops_commodity_loan_applications a
                                      JOIN fudscoops_member m ON m.member_id = a.member_id
                                      JOIN employee e ON e.employee_id = m.employee_id
                                      WHERE e.sp_no = :sp AND a.status = 'supplied' ORDER BY a.application_id LIMIT 1");
        $stmt->execute([':sp' => $spNo]);
        return (int) $stmt->fetchColumn();
    }

    // ---------------------------------------------------------------- lists

    // Officer list. $stage: '' (all), a status, or 'special'. $bulk: a checkbox on each request awaiting the Chairman
    // (Chairman's list; js/fud-bulk.js authorizes the ticked ones through ajax/chairman_decisions.php).
    public static function requestsTable($stage = '', $tabs = true, $bulk = false)
    {
        $sql = "SELECT a.*, " . self::NAME_SQL . ", e.sp_no, e.title, e.fname, e.lname, e.oname,
                       (SELECT COALESCE(SUM(r.amount_paid), 0) FROM fudscoops_commodity_repayments r WHERE r.application_id = a.application_id) AS repaid
                FROM fudscoops_commodity_loan_applications a
                LEFT JOIN fudscoops_commodity_supply s ON s.commodity_name_id = a.commodity_supply_id
                LEFT JOIN fudscoops_member m ON m.member_id = a.member_id
                LEFT JOIN employee e ON e.employee_id = m.employee_id";
        $params = [];
        if ($stage === 'special') {
            $sql .= " WHERE a.request_type = 'special'";
        } elseif ($stage !== '') {
            $sql .= " WHERE a.status = :st";
            $params[':st'] = $stage;
        }
        $stmt = self::con()->prepare($sql . " ORDER BY FIELD(a.status, 'pending', 'recommended', 'authorized', 'supplied') = 0, a.application_id DESC");
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $counts = [];
        foreach (self::con()->query("SELECT status, COUNT(*) n FROM fudscoops_commodity_loan_applications GROUP BY status")->fetchAll(PDO::FETCH_ASSOC) as $c) {
            $counts[$c['status']] = (int) $c['n'];
        }
        $specials = (int) self::con()->query("SELECT COUNT(*) FROM fudscoops_commodity_loan_applications WHERE request_type = 'special'")->fetchColumn();
        $tabList = ['' => 'All', 'pending' => 'Awaiting Treasurer', 'recommended' => 'Awaiting Chairman', 'authorized' => 'Awaiting supply',
                 'supplied' => 'Repaying', 'repaid' => 'Fully repaid', 'declined' => 'Declined', 'special' => 'Special requests'];
        $html = $tabs ? '<div class="fud-tabs">' : '';
        foreach ($tabs ? $tabList : [] as $key => $label) {
            $n = $key === '' ? array_sum($counts) : ($key === 'special' ? $specials : (isset($counts[$key]) ? $counts[$key] : 0));
            $html .= '<a class="' . ($stage === $key ? 'active' : '') . '" href="?stage=' . $key . '">' . $label . ' <span>' . $n . '</span></a>';
        }
        $html .= $tabs ? '</div>' : '';

        if (!$rows) {
            return $html . '<p class="fud-empty">No commodity requests here.</p>';
        }
        $bulk = $bulk && count(array_filter($rows, function ($r) { return $r['status'] === 'recommended'; })) > 0;
        $html .= '<div class="table-responsive"' . ($bulk ? ' data-bulk="commodity"' : '') . '><table class="table fud-table"><thead><tr>'
                . ($bulk ? '<th style="width:28px"><input type="checkbox" class="bulk-all" title="Select all awaiting you"></th>' : '') . '<th>#</th><th>Member</th><th>Supply</th><th>Requested</th>
                  <th class="num">Items (₦)</th><th class="num">To repay (₦)</th><th class="num">Balance (₦)</th><th>Stage</th><th></th></tr></thead><tbody>';
        foreach ($rows as $r) {
            $payable = self::payable($r);
            $repaying = in_array($r['status'], ['supplied', 'repaid'], true);
            $check = '';
            if ($bulk) {
                $check = '<td>' . ($r['status'] === 'recommended' ? '<input type="checkbox" class="bulk-check" value="' . (int) $r['application_id'] . '" data-label="'
                       . self::h(trim($r['fname'] . ' ' . $r['lname']) . ' (' . $r['sp_no'] . ') – ' . $r['commodity_name'] . ' #' . $r['application_id'] . ', ' . self::money($payable)) . '">' : '') . '</td>';
            }
            $html .= '<tr>' . $check . '<td>' . (int) $r['application_id'] . '</td>
                <td>' . self::h(trim($r['fname'] . ' ' . $r['lname'])) . '<div class="text-muted small">' . self::h($r['sp_no']) . '</div></td>
                <td>' . self::h($r['commodity_name']) . (self::isSpecial($r) ? ' <span class="fud-pill wait">special</span>' : '') . '</td>
                <td>' . date('d/m/Y', strtotime($r['application_date'])) . '</td>
                <td class="num">' . number_format($r['total_amount'], 2) . '</td>
                <td class="num">' . ($r['total_payable'] !== null ? number_format($payable, 2) : '–') . '</td>
                <td class="num">' . ($repaying ? number_format(max(0, $payable - $r['repaid']), 2) : '–') . '</td>
                <td>' . self::stagePill($r) . '</td>
                <td><a class="btn btn-sm btn-outline-primary" href="viewRequestDetails.php?applicationId=' . (int) $r['application_id'] . '">Open</a></td></tr>';
        }
        return $html . '</tbody></table></div>';
    }

    public static function memberRequests($memberId)
    {
        $stmt = self::con()->prepare("SELECT a.*, " . self::NAME_SQL . ",
                                             (SELECT COALESCE(SUM(r.amount_paid), 0) FROM fudscoops_commodity_repayments r WHERE r.application_id = a.application_id) AS repaid
                                      FROM fudscoops_commodity_loan_applications a
                                      LEFT JOIN fudscoops_commodity_supply s ON s.commodity_name_id = a.commodity_supply_id
                                      WHERE a.member_id = :m ORDER BY a.application_id DESC");
        $stmt->execute([':m' => $memberId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $r['payable'] = self::payable($r);
            $r['balance'] = max(0, $r['payable'] - $r['repaid']);
        }
        return $rows;
    }

    // ---------------------------------------------------------------- details page

    // $role: 'treasurer', 'chairman', 'secretary' or 'member' (members see their own requests only)
    public static function detailsPage($appId, $role, $memberId = 0)
    {
        $app = $appId ? self::application($appId) : null;
        if (!$app || ($role === 'member' && (int) $app['member_id'] !== (int) $memberId)) {
            echo '<div class="fud-notice"><i class="mdi mdi-alert-circle"></i><span>Request not found.</span>'
               . '<a href="view_commodity_request.php">Back to the list</a></div>';
            return;
        }
        $lines = self::lines($appId);
        $repayments = self::repayments($appId);
        $h = function ($v) { return self::h($v); };
        $money = function ($v) { return self::money($v); };
        $status = $app['status'];
        $reviewed = !empty($app['treasurer_date']) && $status !== 'pending';
        $rate = $app['total_payable'] !== null ? (float) $app['interest_rate'] : self::interestRate();
        $tenor = (int) ($app['tenor'] ?: self::maxTenor());
        $treasurerEdits = $role === 'treasurer' && in_array($status, ['pending', 'recommended'], true) && $app['chairman_approval'] == 0;
        $special = self::isSpecial($app);
        $supplied = $special ? 'Released' : 'Supplied';

        // progress
        $steps = ['Requested', 'Treasurer', 'Chairman', $supplied, 'Repaid'];
        $reached = ['pending' => 1, 'recommended' => 2, 'authorized' => 3, 'supplied' => 4, 'completed' => 4, 'repaid' => 5];
        $done = isset($reached[$status]) ? $reached[$status] : 0;
        $back = $role === 'chairman' ? 'approved_commodity_Request.php' : 'view_commodity_request.php';
        ?>
        <div class="fud-dash fud-commodity">
          <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div>
              <h3 class="mb-1"><?php echo $special ? 'Special commodity request' : 'Commodity request'; ?> #<?php echo (int) $app['application_id']; ?> <?php echo self::stagePill($app); ?></h3>
              <div class="text-muted"><?php echo $h($app['commodity_name']); ?> · requested <?php echo date('d/m/Y', strtotime($app['application_date'])); ?></div>
            </div>
            <a href="<?php echo $back; ?>" class="btn btn-light btn-sm"><i class="mdi mdi-arrow-left"></i> All requests</a>
          </div>

          <?php if ($status !== 'declined' && $status !== 'rejected') { ?>
          <div class="fud-section"><div class="fud-section-body">
            <div class="fud-steps">
              <?php foreach ($steps as $i => $s) {
                  $class = $i < $done ? 'done' : ($i === $done ? 'current' : ''); ?>
                <div class="fud-step <?php echo $class; ?>"><div class="fud-step-dot"><i class="mdi <?php echo $class === 'done' ? 'mdi-check' : 'mdi-circle-small'; ?>"></i></div><?php echo $s; ?></div>
              <?php } ?>
            </div>
          </div></div>
          <?php } ?>

          <div class="fud-stats">
            <div class="fud-stat"><div class="fud-stat-icon fud-blue"><i class="mdi mdi-cart-outline"></i></div>
              <div><div class="fud-stat-label"><?php echo $reviewed ? 'Items approved' : 'Items requested'; ?></div>
                <div class="fud-stat-value"><?php echo $money($app['total_amount']); ?></div>
                <?php if ($app['requested_amount'] !== null && (float) $app['requested_amount'] != (float) $app['total_amount']) { ?>
                  <div class="fud-stat-sub">requested <?php echo $money($app['requested_amount']); ?></div><?php } ?></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-amber"><i class="mdi mdi-percent"></i></div>
              <div><div class="fud-stat-label">Interest (<?php echo rtrim(rtrim(number_format($rate, 2), '0'), '.'); ?>% flat)</div>
                <div class="fud-stat-value"><?php echo $app['total_payable'] !== null ? $money($app['interest_amount']) : '–'; ?></div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-purple"><i class="mdi mdi-calendar-clock"></i></div>
              <div><div class="fud-stat-label">Monthly deduction</div>
                <div class="fud-stat-value"><?php echo $app['monthly_amount'] !== null ? $money($app['monthly_amount']) : '–'; ?></div>
                <div class="fud-stat-sub"><?php echo $tenor; ?> month(s)<?php echo $app['repayment_start'] ? ' from ' . date('M Y', strtotime($app['repayment_start'])) : ''; ?></div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-green"><i class="mdi mdi-cash-multiple"></i></div>
              <div><div class="fud-stat-label">Repaid / balance</div>
                <div class="fud-stat-value"><?php echo $money($app['repaid']); ?></div>
                <div class="fud-stat-sub">balance <?php echo $app['total_payable'] !== null ? $money($app['balance']) : '–'; ?></div></div></div>
          </div>

          <div class="row">
            <div class="col-lg-8">
              <div class="fud-section">
                <div class="fud-section-head"><h5>Items</h5>
                  <small class="text-muted"><?php echo $h(trim($app['title'] . ' ' . $app['fname'] . ' ' . $app['lname'] . ' ' . $app['oname'])); ?> · <?php echo $h($app['sp_no']); ?></small></div>
                <div class="fud-section-body">
                  <form id="commodityReview">
                  <div class="table-responsive">
                    <table class="table fud-table">
                      <thead><tr><th>Item</th><?php if ($special) { ?><th class="num">Member's estimate</th><?php } ?><th class="num">Unit price</th><th class="num">Requested</th>
                        <th class="num"><?php echo $treasurerEdits ? 'Approve' : 'Approved'; ?></th><th class="num">Amount</th></tr></thead>
                      <tbody>
                      <?php foreach ($lines as $line) {
                          $approved = $reviewed ? (int) $line['quantity_approved'] : (int) $line['quantity_requested'];
                          $extra = (int) $line['remaining_quantity']; ?>
                        <tr>
                          <td><?php echo $h($line['commodity_item']); ?><?php if (strcasecmp(trim($line['commodity_description']), trim($line['commodity_item'])) !== 0) { ?><div class="text-muted small"><?php echo $h($line['commodity_description']); ?></div><?php } ?></td>
                          <?php if ($special) { ?><td class="num text-muted"><?php echo number_format($line['estimated_price'], 2); ?></td><?php } ?>
                          <td class="num"><?php if ($special && $treasurerEdits) { ?>
                              <input type="text" inputmode="decimal" class="form-control form-control-sm fud-price" style="width:120px;margin-left:auto;text-align:right"
                                     name="price[<?php echo (int) $line['application_item_id']; ?>]" value="<?php echo number_format($line['unit_price'], 2, '.', ''); ?>">
                            <?php } else { echo number_format($line['unit_price'], 2);
                                if ($special && $reviewed && abs($line['unit_price'] - $line['estimated_price']) > 0.005) { ?><div class="small text-muted">corrected</div><?php }
                            } ?></td>
                          <td class="num"><?php echo (int) $line['quantity_requested']; ?></td>
                          <td class="num">
                            <?php if ($treasurerEdits) { ?>
                              <input type="number" class="form-control form-control-sm fud-qty" name="qty[<?php echo (int) $line['application_item_id']; ?>]"
                                     min="0" max="<?php echo (int) $line['quantity_requested']; ?>" value="<?php echo $approved; ?>" data-price="<?php echo (float) $line['unit_price']; ?>">
                            <?php } else { echo $reviewed ? $approved : '–'; } ?>
                          </td>
                          <td class="num fud-line-total"><?php echo number_format(($reviewed || $treasurerEdits ? $approved : $line['quantity_requested']) * $line['unit_price'], 2); ?></td>
                        </tr>
                      <?php } ?>
                      </tbody>
                    </table>
                  </div>

                  <?php if ($treasurerEdits) { ?>
                    <div class="form-row mt-3">
                      <div class="form-group col-md-4">
                        <label for="tenor">Repay over</label>
                        <select class="form-control" id="tenor" name="tenor">
                          <?php for ($m = 1; $m <= self::maxTenor(); $m++) { ?>
                            <option value="<?php echo $m; ?>" <?php echo $m === $tenor ? 'selected' : ''; ?>><?php echo $m; ?> month<?php echo $m > 1 ? 's' : ''; ?></option>
                          <?php } ?>
                        </select>
                        <small class="text-muted"><?php echo $app['tenor'] ? 'The member asked for ' . (int) $app['tenor'] . '.' : 'Older request: the member did not choose a period.'; ?></small>
                      </div>
                      <div class="form-group col-md-8">
                        <label for="comment">Comment <small class="text-muted">(required when declining)</small></label>
                        <textarea class="form-control" id="comment" name="comment" rows="2"><?php echo $h($app['treasurer_comment']); ?></textarea>
                      </div>
                    </div>
                    <?php if ($special) { ?><p class="small text-muted mb-2">These items are not in stock: correct each unit price to what the cooperative will pay, and set the quantity to 0 for any item you do not approve.</p><?php } ?>
                    <div class="fud-notice info" id="reviewPreview" data-rate="<?php echo self::interestRate(); ?>"></div>
                    <button type="button" class="btn btn-primary" data-action="treasurer_recommend"><i class="mdi mdi-check"></i> Send to the Chairman</button>
                    <button type="button" class="btn btn-outline-danger" data-action="treasurer_decline"><i class="mdi mdi-close"></i> Decline</button>
                  <?php } ?>
                  </form>
                </div>
              </div>

              <?php if (in_array($status, ['supplied', 'repaid', 'completed'], true)) { ?>
              <div class="fud-section">
                <div class="fud-section-head"><h5>Deductions</h5></div>
                <div class="fud-section-body">
                  <?php if (!$repayments) { ?><p class="fud-empty">No deductions recorded yet.</p><?php } else { ?>
                  <table class="table fud-table"><thead><tr><th>Month</th><th class="num">Amount (₦)</th><th>Recorded</th></tr></thead><tbody>
                    <?php foreach ($repayments as $r) { ?>
                      <tr><td><?php echo date('F Y', strtotime($r['repayment_month'])); ?></td><td class="num"><?php echo number_format($r['amount_paid'], 2); ?></td>
                        <td class="text-muted small"><?php echo date('d/m/Y', strtotime($r['recorded_at'])) . ($role !== 'member' ? ' · ' . $h($r['recorded_by']) : ''); ?></td></tr>
                    <?php } ?>
                  </tbody></table>
                  <?php } ?>
                  <?php if ($role === 'treasurer' && $status === 'supplied') {
                      $next = $app['repayment_start'] ?: date('Y-m-01');
                      if ($repayments) {
                          $next = date('Y-m-01', strtotime(end($repayments)['repayment_month'] . ' +1 month'));
                      } ?>
                    <form id="commodityRepayment" class="form-row align-items-end mt-3">
                      <div class="form-group col-md-4"><label for="repMonth">Month</label>
                        <input type="month" class="form-control" id="repMonth" name="month" value="<?php echo substr($next, 0, 7); ?>" required></div>
                      <div class="form-group col-md-4"><label for="repAmount">Amount deducted (₦)</label>
                        <input type="text" inputmode="decimal" class="form-control" id="repAmount" name="amount"
                               value="<?php echo number_format(min($app['monthly_amount'], $app['balance']), 2, '.', ''); ?>" required></div>
                      <div class="form-group col-md-4"><button type="button" class="btn btn-primary btn-block" data-action="save_repayment">Record deduction</button></div>
                    </form>
                  <?php } ?>
                </div>
              </div>
              <?php } ?>
            </div>

            <div class="col-lg-4">
              <?php if ($role === 'chairman' && $status === 'recommended' && $app['chairman_approval'] == 0) { ?>
                <div class="fud-section"><div class="fud-section-head"><h5>Your decision</h5></div><div class="fud-section-body">
                  <form id="chairmanForm">
                    <div class="form-group"><label for="ccomment">Comment <small class="text-muted">(required when declining)</small></label>
                      <textarea class="form-control" id="ccomment" name="comment" rows="2"></textarea></div>
                    <button type="button" class="btn btn-success btn-block" data-action="chairman_authorize"><i class="mdi mdi-check"></i> Authorize</button>
                    <button type="button" class="btn btn-outline-danger btn-block" data-action="chairman_decline"><i class="mdi mdi-close"></i> Decline</button>
                  </form>
                </div></div>
              <?php } elseif ($role === 'chairman' && $status === 'authorized') { ?>
                <div class="fud-section"><div class="fud-section-body">
                  <p class="mb-2">Authorized; waiting for the Treasurer to <?php echo $special ? 'release the items' : 'record the supply'; ?>.</p>
                  <button type="button" class="btn btn-outline-secondary btn-block" data-action="chairman_return"><i class="mdi mdi-undo"></i> Return to the Treasurer</button>
                </div></div>
              <?php } elseif ($role === 'treasurer' && $status === 'authorized') { ?>
                <div class="fud-section"><div class="fud-section-head"><h5><?php echo $special ? 'Release items' : 'Record supply'; ?></h5></div><div class="fud-section-body">
                  <form id="supplyForm">
                    <div class="form-group"><label for="supplyDate">Date <?php echo strtolower($supplied); ?></label>
                      <input type="date" class="form-control" id="supplyDate" name="date" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" required></div>
                    <div class="form-group"><label for="supplyNote">Note <small class="text-muted">(optional)</small></label>
                      <input type="text" class="form-control" id="supplyNote" name="note" maxlength="255" placeholder="e.g. collected at the store"></div>
                    <button type="button" class="btn btn-primary btn-block" data-action="record_supply"><i class="mdi mdi-truck"></i> Items <?php echo strtolower($supplied); ?></button>
                    <small class="text-muted d-block mt-2">Deductions start the month after this date.</small>
                  </form>
                </div></div>
              <?php } elseif ($role === 'member' && in_array($status, ['pending', 'recommended', 'authorized'], true)) { ?>
                <div class="fud-notice info"><i class="mdi mdi-information-outline"></i><span>Your request is with the
                  <?php echo $status === 'pending' ? 'Treasurer' : ($status === 'recommended' ? 'Chairman' : ($special ? 'Treasurer to release the items' : 'store for supply')); ?>.</span></div>
              <?php } ?>

              <div class="fud-section"><div class="fud-section-head"><h5>Decisions</h5></div><div class="fud-section-body">
                <dl class="fud-sum fud-decisions">
                  <dt>Treasurer</dt><dd><?php echo $app['treasurer_date'] ? date('d/m/Y', strtotime($app['treasurer_date'])) : '–'; ?></dd>
                  <?php if ($app['treasurer_comment'] !== null && $app['treasurer_comment'] !== '') { ?><dd class="fud-wide">"<?php echo $h($app['treasurer_comment']); ?>"</dd><?php } ?>
                  <dt>Chairman</dt><dd><?php echo $app['chairman_date'] ? date('d/m/Y', strtotime($app['chairman_date'])) . ($app['chairman_approval'] == 1 ? ' · authorized' : ($app['chairman_approval'] == -1 ? ' · declined' : '')) : '–'; ?></dd>
                  <?php if ($app['chairman_comment'] !== null && $app['chairman_comment'] !== '') { ?><dd class="fud-wide">"<?php echo $h($app['chairman_comment']); ?>"</dd><?php } ?>
                  <dt><?php echo $supplied; ?></dt><dd><?php echo ($app['disbursement_date'] && substr($app['disbursement_date'], 0, 4) !== '0000') ? date('d/m/Y', strtotime($app['disbursement_date'])) : '–'; ?></dd>
                  <?php if ($app['supply_note']) { ?><dd class="fud-wide"><?php echo $h($app['supply_note']); ?></dd><?php } ?>
                  <?php if ($app['repaid_date']) { ?><dt>Fully repaid</dt><dd><?php echo date('d/m/Y', strtotime($app['repaid_date'])); ?></dd><?php } ?>
                </dl>
              </div></div>
            </div>
          </div>
        </div>

        <script>
        (function () {
            var appId = <?php echo (int) $app['application_id']; ?>;
            var preview = document.getElementById('reviewPreview');
            function money(v) { return '₦' + v.toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
            function updatePreview() {
                if (!preview) { return; }
                var total = 0;
                document.querySelectorAll('.fud-qty').forEach(function (input) {
                    var priceInput = input.closest('tr').querySelector('.fud-price');
                    var price = priceInput ? (parseFloat(priceInput.value.replace(/[^0-9.]/g, '')) || 0) : parseFloat(input.dataset.price);
                    var line = (parseInt(input.value, 10) || 0) * price;
                    input.closest('tr').querySelector('.fud-line-total').textContent = line.toLocaleString('en-NG', { minimumFractionDigits: 2 });
                    total += line;
                });
                var months = parseInt(document.getElementById('tenor').value, 10) || 1;
                var interest = Math.round(total * parseFloat(preview.dataset.rate)) / 100;
                preview.innerHTML = '<i class="mdi mdi-calculator"></i><span>Items ' + money(total) + ' + interest ' + money(interest)
                    + ' = <strong>' + money(total + interest) + '</strong>: ' + money(Math.round((total + interest) / months * 100) / 100)
                    + ' a month for ' + months + ' month(s).</span>';
            }
            document.querySelectorAll('.fud-qty, .fud-price, #tenor').forEach(function (el) { el.addEventListener('input', updatePreview); el.addEventListener('change', updatePreview); });
            updatePreview();

            var confirmText = {
                treasurer_recommend: 'Send these quantities to the Chairman?', treasurer_decline: 'Decline this request? The reserved items go back to the supply.',
                chairman_authorize: 'Authorize this request?', chairman_decline: 'Decline this request?',
                chairman_return: 'Return this request to the Treasurer?', record_supply: <?php echo json_encode($special ? 'Record that the items were released to the member? Deductions start the following month.' : 'Record that the items were supplied?'); ?>,
                save_repayment: 'Record this deduction?'
            };
            document.querySelectorAll('.fud-commodity [data-action]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var action = btn.dataset.action;
                    if (!confirm(confirmText[action])) { return; }
                    var form = btn.closest('form');
                    var data = form ? new FormData(form) : new FormData();
                    data.append('action', action);
                    data.append('application_id', appId);
                    btn.disabled = true;
                    fetch('../ajax/commodity_workflow.php', { method: 'POST', body: data, credentials: 'same-origin' })
                        .then(function (r) { return r.text(); })
                        .then(function (reply) {
                            reply = reply.trim();
                            if (reply === '1') { location.reload(); return; }
                            alert(reply || 'Could not save. Please try again.');
                            btn.disabled = false;
                        })
                        .catch(function () { alert('Network error. Please try again.'); btn.disabled = false; });
                });
            });
        })();
        </script>
        <?php
    }
}
