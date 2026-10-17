-- Chairman decisions on savings withdrawals (2026-10-17). Run once on fuded535_fudscoops, AFTER
-- db/2026-10-15_withdrawal_decisions.sql, and BEFORE uploading the code from 2026-10-17.
--
-- fudscoops_withrawals.chairman_approval from now on:
--    0  awaiting the Chairman (once status = 1)
--    1  approved by the Chairman (approved_date is set to the time of approval)
--   -1  rejected by the Chairman (reason in chairman_comment, shown to the member)

ALTER TABLE `fudscoops_withrawals`
  ADD COLUMN `chairman_comment` text DEFAULT NULL COMMENT 'Chairman reason when rejected (shown to the member)',
  ADD COLUMN `chairman_decided_by` varchar(45) DEFAULT NULL,
  ADD COLUMN `chairman_decided_at` datetime DEFAULT NULL;

-- To undo:
-- ALTER TABLE fudscoops_withrawals DROP COLUMN chairman_comment, DROP COLUMN chairman_decided_by, DROP COLUMN chairman_decided_at;
