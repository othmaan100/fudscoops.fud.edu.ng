-- Commodity workflow (2026-10-13). Run once on the fuded535_fudscoops database, AFTER
-- db/2026-10-13_loan_workflow.sql (it uses the loan_interest_rate setting), and BEFORE uploading the
-- commodity code from 2026-10-13.
--
-- New workflow, the same as loans:
--   member requests items during an open supply and chooses the months to repay (stock is reserved)
--   -> Treasurer fixes the quantities and months (flat loan_interest_rate added once)
--   -> Chairman authorizes -> Treasurer records that the items were supplied
--   -> monthly deductions from the month after supply -> marked fully repaid automatically.
--
-- fudscoops_commodity_loan_applications.status from now on:
--   'pending'      awaiting the Treasurer
--   'recommended'  Treasurer fixed quantities, months and interest; awaiting the Chairman
--   'authorized'   Chairman authorized; awaiting supply
--   'supplied'     items handed over; being repaid
--   'repaid'       fully repaid
--   'declined'     declined (chairman_approval -1 = by the Chairman, otherwise by the Treasurer)

-- 0. Backups (keep until you are happy with the result)
CREATE TABLE fudscoops_commodity_applications_backup_20261013 AS SELECT * FROM fudscoops_commodity_loan_applications;
CREATE TABLE fudscoops_commodity_application_items_backup_20261013 AS SELECT * FROM fudscoops_commodity_loan_application_items;
CREATE TABLE fudscoops_commodity_supply_items_backup_20261013 AS SELECT * FROM fudscoops_commodity_supply_items;

-- 1. Status becomes free text (the old list had no value for supplied, repaid or declined)
ALTER TABLE `fudscoops_commodity_loan_applications`
  MODIFY `status` varchar(20) NOT NULL DEFAULT 'pending';

-- 2. New application columns
ALTER TABLE `fudscoops_commodity_loan_applications`
  ADD COLUMN `tenor` int(11) DEFAULT NULL COMMENT 'Months to repay',
  ADD COLUMN `requested_amount` decimal(12,2) DEFAULT NULL COMMENT 'Value of the items as requested',
  ADD COLUMN `treasurer_comment` text DEFAULT NULL,
  ADD COLUMN `treasurer_date` date DEFAULT NULL,
  ADD COLUMN `reviewed_by` varchar(45) DEFAULT NULL,
  ADD COLUMN `chairman_approval` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0 undecided, 1 authorized, -1 declined',
  ADD COLUMN `chairman_comment` text DEFAULT NULL,
  ADD COLUMN `chairman_date` date DEFAULT NULL,
  ADD COLUMN `interest_rate` decimal(5,2) NOT NULL DEFAULT 0,
  ADD COLUMN `interest_amount` decimal(12,2) NOT NULL DEFAULT 0,
  ADD COLUMN `total_payable` decimal(12,2) DEFAULT NULL COMMENT 'total_amount + interest',
  ADD COLUMN `monthly_amount` decimal(12,2) DEFAULT NULL,
  ADD COLUMN `supplied_by` varchar(45) DEFAULT NULL,
  ADD COLUMN `supply_note` varchar(255) DEFAULT NULL,
  ADD COLUMN `repayment_start` date DEFAULT NULL COMMENT 'Month of the first deduction',
  ADD COLUMN `repaid_date` date DEFAULT NULL;

-- 3. Monthly deductions (one row per request per month; re-recording a month updates it)
CREATE TABLE IF NOT EXISTS `fudscoops_commodity_repayments` (
  `repayment_id` int(11) NOT NULL AUTO_INCREMENT,
  `application_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `repayment_month` date NOT NULL COMMENT 'First day of the month',
  `amount_paid` decimal(12,2) NOT NULL,
  `recorded_by` varchar(45) DEFAULT NULL,
  `recorded_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`repayment_id`),
  UNIQUE KEY `uq_commodity_repayment_month` (`application_id`, `repayment_month`),
  KEY `idx_commodity_repayment_member` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Longest repayment period for commodity (the Treasurer can change it on Treasurer/savings_settings.php)
INSERT IGNORE INTO `fudscoops_settings` (`setting_key`, `setting_value`, `updated_by`, `updated_at`)
VALUES ('commodity_max_tenor', '6', 'system', NOW());

-- 5. Requests decided under the old flow (Secretary approved; nothing was ever supplied) go to the
--    Treasurer: value back to the requested items. The Secretary's quantities are kept in
--    quantity_approved but are no longer used.
UPDATE `fudscoops_commodity_loan_application_items` SET `subtotal` = `quantity_requested` * `unit_price`;
UPDATE `fudscoops_commodity_loan_applications` a
SET a.`requested_amount` = (SELECT COALESCE(SUM(i.`quantity_requested` * i.`unit_price`), 0)
                            FROM `fudscoops_commodity_loan_application_items` i WHERE i.`application_id` = a.`application_id`),
    a.`total_amount` = a.`requested_amount`,
    a.`status` = 'pending'
WHERE a.`status` IN ('pending', 'approved', '');
UPDATE `fudscoops_commodity_loan_applications` SET `status` = 'declined' WHERE `status` = 'rejected';

-- 6. Supply items added without stock: adding items never set remaining_quantity, so it stayed 0 and
--    members could not request them. Items nobody has requested get their full allocation.
UPDATE `fudscoops_commodity_supply_items` si
SET si.`remaining_quantity` = si.`allocated_quantity`
WHERE si.`remaining_quantity` = 0
  AND NOT EXISTS (SELECT 1 FROM `fudscoops_commodity_loan_application_items` i
                  WHERE i.`commodity_supply_item_id` = si.`commodity_supply_item_id`);

-- To undo the data changes (the new columns and table can stay):
-- UPDATE fudscoops_commodity_loan_applications a JOIN fudscoops_commodity_applications_backup_20261013 b
--   ON b.application_id = a.application_id SET a.status = b.status, a.total_amount = b.total_amount;
-- UPDATE fudscoops_commodity_loan_application_items a JOIN fudscoops_commodity_application_items_backup_20261013 b
--   ON b.application_item_id = a.application_item_id SET a.subtotal = b.subtotal;
-- UPDATE fudscoops_commodity_supply_items a JOIN fudscoops_commodity_supply_items_backup_20261013 b
--   ON b.commodity_supply_item_id = a.commodity_supply_item_id SET a.remaining_quantity = b.remaining_quantity;
