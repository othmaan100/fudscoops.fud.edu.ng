-- Loans and shares fixes (2026-10-08). Run once on the fuded535_fudscoops database,
-- AFTER db/2026-10-06_fudscoops_settings.sql (it uses the fudscoops_settings table)
-- and BEFORE uploading the code from 2026-10-08 (the current code is unaffected by the new columns).

-- 1. Shares become a ledger: one row per movement, holdings = SUM(amount_paid) of approved rows.
--    entry_type: 'purchase'      member bought shares (Secretary -> Chairman -> Treasurer approval)
--                'transfer_in'   received from another member (positive amount)
--                'transfer_out'  given to another member (negative amount)
--                'conversion'    savings converted to shares
--                'adjustment'    bulk share upload correcting a member's total to the uploaded figure
--    reference_id: fudscoops_shares_transfer.transfer_id or fudscoops_shares_conversion.conversion_id
--    share_status: 0 pending, 1 approved, -1 declined.
--    Existing rows (purchases and bulk uploads) become 'purchase'.
ALTER TABLE `fudscoops_shares`
  ADD COLUMN `entry_type` varchar(20) NOT NULL DEFAULT 'purchase' AFTER `member_id`,
  ADD COLUMN `reference_id` int(11) DEFAULT NULL AFTER `entry_type`;

-- 2. Eligibility settings (the Treasurer can change them on Treasurer/savings_settings.php)
INSERT IGNORE INTO `fudscoops_settings` (`setting_key`, `setting_value`, `updated_by`, `updated_at`) VALUES
  ('loan_max_savings_multiple', '2', 'system', NOW()),     -- open loans + new loan <= 2 x savings balance
  ('guarantor_max_open_loans', '2', 'system', NOW()),      -- loans one member may guarantee at a time
  ('share_min_balance_after_transfer', '20000', 'system', NOW()); -- existing rule for share transfers

-- 3. Remove two empty records created on 2026-03-03 (no shares, no amount, no sender)
UPDATE `fudscoops_shares_transfer` SET `share_transfer_is_deleted` = 1
WHERE `shares_id` = 0 AND `worth` = 0 AND `transfer_employee_id` = 0 AND `share_transfer_status` = 0;

UPDATE `fudscoops_shares_conversion` SET `share_conversion_is_deleted` = 1
WHERE (`amount_paid` IS NULL OR `amount_paid` = '' OR `amount_paid` = '0') AND `conversion_status` = 0;

-- Status values used from 2026-10-08 (no existing rows need converting):
--   fudscoops_loan.guarantor_status      0 pending, 1 accepted, -1 declined by guarantor
--   fudscoops_loan.chairman_approval     0 undecided, 1 approved, -1 declined
--   fudscoops_loan.loan_status           'pending', 'Approve' (Treasurer approved; bulk uploads use 'Approved'), 'Disapprove'
--   fudscoops_shares.secretary_endorsement / chairman_approval   0 undecided, 1 approved, -1 declined
--   fudscoops_shares_transfer.share_transfer_status / chairman_approval / secretary_endorsement   0, 1, -1
--   fudscoops_shares_conversion.conversion_status / chairman_approval   0, 1, -1
