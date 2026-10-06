-- One-time fix for members stuck with chairman_approval = 1 and treasurer_approval = 0.
-- Run once on the fuded535_fudscoops database, AFTER uploading the new code.
--
-- 1. Members created by the treasurer's bulk member upload (Treasurer/uploadMembersExcel.php)
--    never got treasurer_approval, so they cannot log in as members. They have no chairman
--    decision record. Mark them treasurer-approved.
-- 2. Applicants the chairman approved through the old flow (they have a 'Registration Approved'
--    decision record) go back through the new flow: the treasurer fixes the amount, then the
--    chairman re-authorizes it.

-- Backup of the affected rows (keep until you are happy with the result)
CREATE TABLE fudscoops_member_approval_backup_20261006 AS
SELECT member_id, employee_id, chairman_approval, treasurer_approval, is_active
FROM fudscoops_member
WHERE chairman_approval = 1 AND treasurer_approval = 0;

-- Preview: how many rows each step will change
SELECT
  SUM(d.employee_id IS NULL)     AS bulk_imported_to_mark_treasurer_approved,
  SUM(d.employee_id IS NOT NULL) AS applicants_to_reauthorize
FROM fudscoops_member m
LEFT JOIN (SELECT DISTINCT employee_id FROM fudscoops_membership_decision
           WHERE decision = 'Registration Approved') d ON d.employee_id = m.employee_id
WHERE m.chairman_approval = 1 AND m.treasurer_approval = 0;

-- Step 2 first (applicants back to the treasurer, then the chairman)
UPDATE fudscoops_member m
SET m.chairman_approval = 0
WHERE m.chairman_approval = 1 AND m.treasurer_approval = 0
  AND EXISTS (SELECT 1 FROM fudscoops_membership_decision d
              WHERE d.employee_id = m.employee_id AND d.decision = 'Registration Approved');

-- Step 1 (bulk-imported members become full members)
UPDATE fudscoops_member m
SET m.treasurer_approval = 1
WHERE m.chairman_approval = 1 AND m.treasurer_approval = 0;

-- To undo:
-- UPDATE fudscoops_member m
-- JOIN fudscoops_member_approval_backup_20261006 b ON b.member_id = m.member_id
-- SET m.chairman_approval = b.chairman_approval, m.treasurer_approval = b.treasurer_approval;
