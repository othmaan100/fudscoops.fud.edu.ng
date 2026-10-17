-- Complete withdrawals: account numbers and Treasurer decisions (2026-10-17). Run once on fuded535_fudscoops,
-- AFTER db/2026-10-17_withdrawal_chairman_decisions.sql, and BEFORE uploading the code from 2026-10-17.
--
-- 1. acount_number was INT(11): 10-digit account numbers above 2147483647 were saved as 2147483647, and
--    numbers starting with 0 lost their leading zeros. The column becomes text.
--    - numbers shorter than 10 digits get their leading zeros back (checked against the same members'
--      savings withdrawal accounts, which were stored as text);
--    - 2147483647 is not a real account number: it is cleared (NULL) and the Treasurer enters the correct
--      number when approving (Treasurer/view_com_withdrawal_details.php).
-- 2. The Treasurer can reject a request: chairman_approval = -1 with the reason in treasurer_comment
--    (shown to the member). Pending lists already only show chairman_approval = 0.

CREATE TABLE fudscoops_complete_withrawal_backup_20261017 AS SELECT * FROM fudscoops_complete_withrawal;

ALTER TABLE `fudscoops_complete_withrawal`
  MODIFY `acount_number` varchar(15) DEFAULT NULL,
  ADD COLUMN `treasurer_comment` text DEFAULT NULL COMMENT 'Treasurer note; the reason when rejected (shown to the member)',
  ADD COLUMN `treasurer_decided_by` varchar(45) DEFAULT NULL,
  ADD COLUMN `treasurer_decided_at` datetime DEFAULT NULL;

UPDATE `fudscoops_complete_withrawal` SET `acount_number` = NULL WHERE `acount_number` = '2147483647';
UPDATE `fudscoops_complete_withrawal` SET `acount_number` = LPAD(`acount_number`, 10, '0')
WHERE `acount_number` REGEXP '^[0-9]{1,9}$';

-- To undo:
-- UPDATE fudscoops_complete_withrawal c JOIN fudscoops_complete_withrawal_backup_20261017 b ON b.comp_withdrwal_id = c.comp_withdrwal_id
--   SET c.acount_number = b.acount_number, c.chairman_approval = b.chairman_approval;
-- ALTER TABLE fudscoops_complete_withrawal DROP COLUMN treasurer_comment, DROP COLUMN treasurer_decided_by, DROP COLUMN treasurer_decided_at;
