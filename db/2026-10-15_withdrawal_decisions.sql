-- Savings withdrawal decisions (2026-10-15). Run once on fuded535_fudscoops, AFTER
-- db/2026-10-14_kobo_and_withdrawals.sql, and BEFORE uploading the code from 2026-10-15.
--
-- fudscoops_withrawals.status from now on:
--    0  awaiting the Treasurer
--    1  Treasurer approved an amount; awaiting the Chairman (chairman_approval 0) / approved (chairman_approval 1)
--   -1  rejected by the Treasurer (reason in treasurer_comment)
--
-- Fix: the Treasurer's approval saved the amount but left status at 0, while the Chairman's list only shows
-- status 1, so requests the Treasurer approved never reached the Chairman. They are moved on here.

CREATE TABLE fudscoops_withrawals_backup_20261015 AS SELECT * FROM fudscoops_withrawals;

ALTER TABLE `fudscoops_withrawals`
  ADD COLUMN `treasurer_comment` text DEFAULT NULL COMMENT 'Treasurer note; the reason when rejected (shown to the member)',
  ADD COLUMN `treasurer_decided_by` varchar(45) DEFAULT NULL,
  ADD COLUMN `treasurer_decided_at` datetime DEFAULT NULL;

-- Requests the Treasurer approved that were stuck before the Chairman
UPDATE `fudscoops_withrawals`
SET `status` = 1
WHERE `status` = 0 AND `chairman_approval` = 0 AND `approved_withrawal_amount` > 0 AND COALESCE(`withrawals_is_deleted`, 0) = 0;

-- To undo:
-- UPDATE fudscoops_withrawals w JOIN fudscoops_withrawals_backup_20261015 b ON b.withrawals_id = w.withrawals_id SET w.status = b.status;
-- ALTER TABLE fudscoops_withrawals DROP COLUMN treasurer_comment, DROP COLUMN treasurer_decided_by, DROP COLUMN treasurer_decided_at;
