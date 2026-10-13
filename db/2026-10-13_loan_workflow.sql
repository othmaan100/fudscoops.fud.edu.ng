-- Loan workflow reform (2026-10-13). Run once on the fuded535_fudscoops database, AFTER
-- db/2026-10-06_fudscoops_settings.sql and db/2026-10-08_loans_shares.sql, and BEFORE uploading the
-- code from 2026-10-13. The current code is unaffected by the new columns.
--
-- New workflow: member applies -> guarantor accepts -> Treasurer fixes the amount (flat interest added)
-- -> Chairman authorizes that amount -> Treasurer records the disbursement -> monthly deductions from
-- the month after disbursement -> loan marked fully repaid automatically.
--
-- fudscoops_loan.loan_status from now on:
--   'pending'      applied; awaiting guarantor (guarantor_status 0) or Treasurer (guarantor_status 1)
--   'Recommended'  Treasurer fixed amount, interest and period; awaiting the Chairman
--   'Approve'      Chairman authorized; awaiting disbursement
--   'Disbursed'    paid out; being repaid
--   'Repaid'       fully repaid
--   'Disapprove'   declined (guarantor_status -1 / chairman_approval -1 show who declined)

-- 0. Backup of the loans before this change (keep until you are happy with the result)
CREATE TABLE fudscoops_loan_backup_20261013 AS SELECT * FROM fudscoops_loan;

-- 1. New loan columns
ALTER TABLE `fudscoops_loan`
  ADD COLUMN `interest_rate` decimal(5,2) NOT NULL DEFAULT 0 COMMENT 'Flat % added to the approved amount',
  ADD COLUMN `interest_amount` decimal(12,2) NOT NULL DEFAULT 0,
  ADD COLUMN `total_payable` decimal(12,2) DEFAULT NULL COMMENT 'Approved amount + interest',
  ADD COLUMN `treasurer_comment` text DEFAULT NULL,
  ADD COLUMN `treasurer_date` date DEFAULT NULL,
  ADD COLUMN `disbursed_amount` decimal(12,2) DEFAULT NULL,
  ADD COLUMN `disbursement_date` date DEFAULT NULL,
  ADD COLUMN `disbursement_reference` varchar(100) DEFAULT NULL,
  ADD COLUMN `disbursed_by` varchar(45) DEFAULT NULL,
  ADD COLUMN `repayment_start` date DEFAULT NULL COMMENT 'Month of the first deduction',
  ADD COLUMN `repaid_date` date DEFAULT NULL;

-- 2. Supplier sort code (the loan form asked for it but it was not stored)
ALTER TABLE `fudscoops_suppliers` ADD COLUMN `sort_code` varchar(20) DEFAULT NULL AFTER `account_number`;

-- 3. Interest rate setting (flat %, the Treasurer can change it on Treasurer/savings_settings.php)
INSERT IGNORE INTO `fudscoops_settings` (`setting_key`, `setting_value`, `updated_by`, `updated_at`)
VALUES ('loan_interest_rate', '10', 'system', NOW());

-- 4. Existing approved loans are running loans: mark them disbursed with no interest and keep their
--    schedule (deductions counted from the month of the loan date, as before)
UPDATE `fudscoops_loan`
SET `total_payable` = COALESCE(NULLIF(`amount_recommended`, 0), `loan_amount`),
    `disbursed_amount` = COALESCE(NULLIF(`amount_recommended`, 0), `loan_amount`),
    `disbursement_date` = STR_TO_DATE(LEFT(`loan_date`, 10), '%Y-%m-%d'),
    `repayment_start` = DATE_FORMAT(STR_TO_DATE(LEFT(`loan_date`, 10), '%Y-%m-%d'), '%Y-%m-01'),
    `loan_status` = 'Disbursed'
WHERE `loan_status` IN ('Approve', 'Approved');

-- 5. Applications caught mid-way in the old order (Chairman approved before the Treasurer) go back
--    through the new order: Treasurer fixes the amount, then the Chairman authorizes it
UPDATE `fudscoops_loan` SET `chairman_approval` = 0 WHERE `loan_status` = 'pending' AND `chairman_approval` = 1;
-- ... and applications the Chairman declined become declined loans
UPDATE `fudscoops_loan` SET `loan_status` = 'Disapprove' WHERE `loan_status` = 'pending' AND `chairman_approval` = -1;

-- 6. Loans already fully repaid
UPDATE `fudscoops_loan` l
SET l.`loan_status` = 'Repaid', l.`repaid_date` = CURDATE()
WHERE l.`loan_status` = 'Disbursed'
  AND l.`total_payable` <= (SELECT COALESCE(SUM(r.`amount_paid`), 0) FROM `fudscoops_loan_repayments` r
                            WHERE r.`loan_id` = l.`loan_id` AND r.`status` = 1 AND COALESCE(r.`is_deleted`, 0) = 0);

-- To undo the data changes (columns can stay):
-- UPDATE fudscoops_loan l JOIN fudscoops_loan_backup_20261013 b ON b.loan_id = l.loan_id
-- SET l.loan_status = b.loan_status, l.chairman_approval = b.chairman_approval;
