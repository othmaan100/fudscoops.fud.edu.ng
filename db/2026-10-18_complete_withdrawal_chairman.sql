-- Chairman decisions on complete withdrawals (2026-10-18). Run once on fuded535_fudscoops, AFTER
-- db/2026-10-17_complete_withdrawal_decisions.sql, and BEFORE uploading the code from 2026-10-18.
--
-- fudscoops_complete_withrawal.chairman_approval: 0 pending, 1 approved, -1 rejected
-- (by the Treasurer: reason in treasurer_comment; by the Chairman: reason in chairman_comment).

ALTER TABLE `fudscoops_complete_withrawal`
  ADD COLUMN `chairman_comment` text DEFAULT NULL COMMENT 'Chairman reason when rejected (shown to the member)',
  ADD COLUMN `chairman_decided_by` varchar(45) DEFAULT NULL,
  ADD COLUMN `chairman_decided_at` datetime DEFAULT NULL;

-- To undo:
-- ALTER TABLE fudscoops_complete_withrawal DROP COLUMN chairman_comment, DROP COLUMN chairman_decided_by, DROP COLUMN chairman_decided_at;
