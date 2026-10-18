-- Special commodity requests (2026-10-19). Run once on fuded535_fudscoops, AFTER
-- db/2026-10-18_complete_withdrawal_chairman.sql, and BEFORE uploading the code from 2026-10-19.
--
-- A member asks for items the cooperative does not stock (R/special_commodity_request.php), with an estimated
-- price for each. They follow the commodity workflow (config/classes/CommodityLoan.php):
--   pending (Treasurer corrects prices, approves some or all items) -> recommended -> authorized (Chairman)
--   -> supplied (Treasurer releases the items; deductions start the next month) -> repaid.
-- The request is a row in fudscoops_commodity_loan_applications with request_type 'special' and
-- commodity_supply_id 0; its items are in fudscoops_special_commodity_items (no supply stock is used).
-- No existing records change.

ALTER TABLE `fudscoops_commodity_loan_applications`
  ADD COLUMN `request_type` varchar(10) NOT NULL DEFAULT 'regular' COMMENT 'regular = from a supply; special = items not stocked';

CREATE TABLE `fudscoops_special_commodity_items` (
  `special_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `application_id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `quantity_requested` int(11) NOT NULL,
  `estimated_price` decimal(14,2) NOT NULL COMMENT 'Unit price the member estimated',
  `unit_price` decimal(14,2) NOT NULL COMMENT 'Unit price (corrected by the Treasurer)',
  `quantity_approved` int(11) NOT NULL DEFAULT 0,
  `subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`special_item_id`),
  KEY `idx_special_items_application` (`application_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- To undo:
-- DROP TABLE fudscoops_special_commodity_items;
-- ALTER TABLE fudscoops_commodity_loan_applications DROP COLUMN request_type;
