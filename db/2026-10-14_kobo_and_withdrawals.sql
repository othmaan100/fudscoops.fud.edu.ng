-- Kobo and withdrawals (2026-10-14). Run once on the fuded535_fudscoops database, AFTER
-- db/2026-10-14_loan_controls.sql, and BEFORE uploading the code from 2026-10-14.
--
-- 1. Money columns hold kobo: whole-number and text columns become DECIMAL (two decimal places).
--    Existing values keep their value. Repayment schedule amounts stored with many decimals
--    (e.g. 18333.333335) are rounded to kobo; the loans' totals are not affected.
-- 2. Withdrawals must leave a minimum savings balance (new setting withdrawal_min_balance, starting at the
--    minimum monthly savings). Applies to savings withdrawals and complete withdrawals.

-- 0. Backup of the repayment schedule rows that are rounded
CREATE TABLE fudscoops_loan_repayments_backup_20261014 AS SELECT * FROM fudscoops_loan_repayments;

-- One share conversion has an empty amount
UPDATE `fudscoops_shares_conversion` SET `amount_paid` = '0' WHERE TRIM(`amount_paid`) = '';

-- 1. Kobo
ALTER TABLE `fudscoops_savings`            MODIFY `savings_amount` decimal(14,2) DEFAULT NULL;
ALTER TABLE `fudscoops_member`             MODIFY `proposed_monthly_savings` decimal(14,2) DEFAULT NULL;
ALTER TABLE `fudscoops_update_savings`     MODIFY `savings_update_amount` decimal(14,2) NOT NULL,
                                           MODIFY `treasurer_approved_amount` decimal(14,2) NOT NULL DEFAULT 0;
ALTER TABLE `fudscoops_withrawals`         MODIFY `proposed_withrawal_amount` decimal(14,2) DEFAULT NULL,
                                           MODIFY `approved_withrawal_amount` decimal(14,2) NOT NULL DEFAULT 0;
ALTER TABLE `fudscoops_complete_withrawal` MODIFY `approved_amount` decimal(14,2) NOT NULL DEFAULT 0;
ALTER TABLE `fudscoops_loan`               MODIFY `loan_amount` decimal(14,2) DEFAULT NULL,
                                           MODIFY `amount_recommended` decimal(14,2) DEFAULT NULL;
ALTER TABLE `fudscoops_loan_repayments`    MODIFY `amount_paid` decimal(14,2) DEFAULT 0,
                                           MODIFY `loan_repayment_amount` decimal(14,2) DEFAULT NULL;
ALTER TABLE `fudscoops_shares`             MODIFY `amount_paid` decimal(14,2) NOT NULL,
                                           MODIFY `unit_price` decimal(14,2) DEFAULT NULL;
ALTER TABLE `fudscoops_shares_conversion`  MODIFY `amount_paid` decimal(14,2) NOT NULL;
ALTER TABLE `fudscoops_share_unit`         MODIFY `price` decimal(14,2) NOT NULL;
ALTER TABLE `fudscoops_commodity_items`    MODIFY `price_unit` decimal(14,2) NOT NULL;
ALTER TABLE `fudscoops_commodity_supply_items` MODIFY `unit_price` decimal(14,2) NOT NULL;

-- 2. Minimum savings balance left after any withdrawal (Treasurer/savings_settings.php)
INSERT IGNORE INTO `fudscoops_settings` (`setting_key`, `setting_value`, `updated_by`, `updated_at`)
SELECT 'withdrawal_min_balance', COALESCE((SELECT setting_value FROM `fudscoops_settings` WHERE setting_key = 'min_monthly_savings'), '2000'), 'system', NOW();

-- Undo: the columns can stay DECIMAL (values are unchanged). The original schedule amounts, with all their
-- decimals, are kept in fudscoops_loan_repayments_backup_20261014 (loan_repayment_amount is text there).
-- DELETE FROM fudscoops_settings WHERE setting_key = 'withdrawal_min_balance';
