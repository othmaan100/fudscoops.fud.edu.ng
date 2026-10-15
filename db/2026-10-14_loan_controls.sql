-- Loan controls (2026-10-14). Run once on the fuded535_fudscoops database, AFTER
-- db/2026-10-13_loan_workflow.sql and db/2026-10-13_commodity_workflow.sql, and BEFORE uploading the
-- code from 2026-10-14. Adds no data changes to existing records.
--
-- 1. Guarantors may give a reason when they decline (optional)
-- 2. Over-repayments: when deductions go past what a loan owes, the excess is recorded here. The member
--    chooses what to do with it (add to savings, pay another loan, refund to an account) and the
--    Treasurer settles it.
-- 3. Complete withdrawals: a member who owes loans or commodity may choose to settle them from savings;
--    the settlement is posted when the Chairman approves.
-- 4. Soft loans closed for now (the Treasurer can open them on Treasurer/savings_settings.php)

ALTER TABLE `fudscoops_loan`
  ADD COLUMN `guarantor_comment` text DEFAULT NULL COMMENT 'Reason given by the guarantor when declining (optional)';

CREATE TABLE IF NOT EXISTS `fudscoops_loan_overpayments` (
  `overpayment_id` int(11) NOT NULL AUTO_INCREMENT,
  `loan_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `status` varchar(10) NOT NULL DEFAULT 'open' COMMENT 'open = awaiting the member, chosen = awaiting the Treasurer, settled',
  `member_choice` varchar(10) DEFAULT NULL COMMENT 'savings, loan or refund',
  `target_loan_id` int(11) DEFAULT NULL COMMENT 'Loan to pay when member_choice = loan',
  `bank_name` varchar(100) DEFAULT NULL,
  `account_number` varchar(20) DEFAULT NULL,
  `account_name` varchar(150) DEFAULT NULL,
  `chosen_at` datetime DEFAULT NULL,
  `settled_by` varchar(45) DEFAULT NULL,
  `settled_at` datetime DEFAULT NULL,
  `settle_note` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`overpayment_id`),
  KEY `idx_overpayment_loan` (`loan_id`),
  KEY `idx_overpayment_member` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `fudscoops_complete_withrawal`
  ADD COLUMN `settle_from_savings` tinyint(4) NOT NULL DEFAULT 0 COMMENT '1 = pay loans and commodity owed from savings, pay out the rest',
  ADD COLUMN `amount_owed` decimal(12,2) NOT NULL DEFAULT 0 COMMENT 'Loans and commodity owed when requested (updated when settled)',
  ADD COLUMN `settled_at` datetime DEFAULT NULL COMMENT 'When the amount owed was paid from savings';

INSERT IGNORE INTO `fudscoops_settings` (`setting_key`, `setting_value`, `updated_by`, `updated_at`)
VALUES ('soft_loan_open', '0', 'system', NOW());

-- To undo:
-- ALTER TABLE fudscoops_loan DROP COLUMN guarantor_comment;
-- DROP TABLE fudscoops_loan_overpayments;
-- ALTER TABLE fudscoops_complete_withrawal DROP COLUMN settle_from_savings, DROP COLUMN amount_owed, DROP COLUMN settled_at;
-- DELETE FROM fudscoops_settings WHERE setting_key = 'soft_loan_open';
