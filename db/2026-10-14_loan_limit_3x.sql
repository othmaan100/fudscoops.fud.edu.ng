-- Loan limit raised to 3 times the savings balance (2026-10-14). Run once on fuded535_fudscoops.
-- A member's open loans plus a new loan may not exceed this many times their savings balance
-- (MemberG3::loanEligibilityError and MemberG3::loanLimit). The Treasurer can still change it on
-- Treasurer/savings_settings.php. Safe to run before or after uploading the code.
UPDATE `fudscoops_settings`
SET `setting_value` = '3', `updated_by` = 'system', `updated_at` = NOW()
WHERE `setting_key` = 'loan_max_savings_multiple';

-- If the setting was never created (db/2026-10-08_loans_shares.sql not run), create it
INSERT IGNORE INTO `fudscoops_settings` (`setting_key`, `setting_value`, `updated_by`, `updated_at`)
VALUES ('loan_max_savings_multiple', '3', 'system', NOW());

-- To undo: UPDATE fudscoops_settings SET setting_value = '2' WHERE setting_key = 'loan_max_savings_multiple';
