-- Opening savings balances (2026-10-23). Run once on fuded535_fudscoops, AFTER db/2026-10-18_complete_withdrawal_chairman.sql,
-- and BEFORE uploading the code from 2026-10-23.
--
-- fudscoops_savings.entry_type:
--   'monthly'  a month's savings deduction (uploadDeposit.php; at most one per member per month)
--   'credit'   a loan overpayment the member chose to add to savings
--   'opening'  savings a member had before joining the portal ("Consolidated Savings" / opening balance),
--              recorded by the members upload (Treasurer/upload.php); month/year = the month the balance is as at
--
-- Why: the members upload saved the opening balance as that month's savings row, so the month's real deduction was
-- then refused by the savings upload ("Already exists"), or, if uploaded first, overwritten by the opening balance.
-- Balances do not change: every row still counts towards savings.

CREATE TABLE fudscoops_savings_backup_20261023 AS SELECT * FROM fudscoops_savings;

ALTER TABLE `fudscoops_savings`
  ADD COLUMN `entry_type` varchar(10) NOT NULL DEFAULT 'monthly' COMMENT 'monthly | opening' AFTER `savings_amount`;

-- The members upload of 4 August 2026 (1,300 rows, the only rows dated that day) recorded opening balances
-- as "August 2026" savings. Mark them as opening balances.
UPDATE `fudscoops_savings` SET `entry_type` = 'opening' WHERE DATE(`savings_date`) = '2026-08-04';

-- Loan overpayments the Treasurer added to savings (LoanControls::settleOverpayment) are credits, not a month's deduction
-- (type 'credit'), so they no longer block that month's savings upload.
UPDATE `fudscoops_savings` s
  JOIN `fudscoops_loan_overpayments` o ON o.member_id = s.member_idmember AND o.status = 'settled' AND o.member_choice = 'savings'
   AND DATE(o.settled_at) = s.savings_date AND ROUND(o.amount, 2) = s.savings_amount
SET s.entry_type = 'credit';

-- To undo:
-- ALTER TABLE fudscoops_savings DROP COLUMN entry_type;
