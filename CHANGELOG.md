# FUDSCOOPS Change Log

Record of changes to the FUDSCOOPS portal. Every change that touches the database has a
migration file in `db/` and is listed under **Database changes**.

Paths are relative to the project root. The treasurer pages live in `Treasurer/` locally and in
the `Treasurer ` folder (with a trailing space) on the live server.

---

## Database changes

Run these on the live `fuded535_fudscoops` database in this order. Each file says what it does
at the top.

| # | File | What it does | Local | Live |
|---|------|--------------|-------|------|
| 1 | `db/2026-10-06_fudscoops_settings.sql` | New table `fudscoops_settings` (key/value settings). Seeds `min_monthly_savings` = 2000. | Applied 2026-10-06 | Pending |
| 2 | `db/2026-10-06_member_approval_split.sql` | One-time fix for members with `chairman_approval = 1` and `treasurer_approval = 0`. Bulk-imported members (no chairman decision record) are set to `treasurer_approval = 1`. Applicants approved through the old flow are set back to `chairman_approval = 0` so they go through the new flow. Backs up the affected rows to `fudscoops_member_approval_backup_20261006` first; undo statement is in the file. Run after uploading the code from 2026-10-06. | Applied 2026-10-06 (220 members fixed, 1 applicant reset) | Pending |

### Meaning of the approval columns (as of 2026-10-06)

`fudscoops_member`:

| `treasurer_approval` | `chairman_approval` | Meaning |
|---|---|---|
| 0 | 0 | Applied; awaiting the Treasurer |
| 1 | 0 | Treasurer has fixed the monthly savings; awaiting the Chairman |
| 1 | 1 | Full member (can log in to `R/`) |

`fudscoops_update_savings` (savings update requests):

| `is_secretary_approved` | `is_treasurer_approved` | Meaning |
|---|---|---|
| 0 | 0 | Requested by the member; awaiting the Chairman/Secretary |
| 1 | 0 | Approved by the Chairman; awaiting the Treasurer |
| 1 | 1 | Approved by the Treasurer; `treasurer_approved_amount` applied to the member's monthly savings |
| 1 | -1 | Superseded: the member made a newer request, which was approved instead |
| -1 | any | Rejected |

---

## 2026-10-06

### Generate Report

- New **Generate Report** page for the Chairman, Treasurer and Secretary-General (menu item
  **Generate Report**; the Secretary's old **Report** link now opens it). Nine reports:
  - **Cooperative Summary**: members, pending applications, savings deposited/withdrawn,
    complete withdrawals, savings balance, share capital, loans granted/repaid/outstanding,
    commodity; savings by month and loans by type.
  - **Member Statement** (by staff number): savings, withdrawals, complete withdrawal, shares,
    loans with balances, commodity applications.
  - **Savings Deposits**: by month and by member.
  - **Withdrawals**: approved savings withdrawals and complete withdrawals, with pending counts.
  - **Loans**: loans granted in the period and all loans with a balance (monthly repayment,
    repaid, outstanding).
  - **Loan Repayments**: deductions received.
  - **Shares**: purchases in the period and holdings per member.
  - **Commodity**: applications by status.
  - **Membership**: new members and pending applications.
- Every report has a date range (the Member Statement has an "up to" date), and can be viewed
  on screen, downloaded for Excel (CSV) or as a PDF (A4 landscape with the cooperative heading and
  page numbers), or printed.
- How figures are calculated (also noted on the reports):
  - Savings balance = savings deposited − savings withdrawals approved by the Chairman, the same
    as members' dashboards. Complete withdrawals are listed separately and are not deducted
    (this is how the system already calculates balances).
  - Savings are counted by deduction month (`fudscoops_savings.month`/`year`).
  - Loan repayments are deductions uploaded by the Treasurer (`fudscoops_loan_repayments`
    `status = 1`, `amount_paid`). Rows with `status = 0` are the repayment schedule created when a
    loan is uploaded. No deductions had been uploaded as of 2026-10-06, so all loans show their
    full amount as outstanding.
  - Records marked deleted (`*_is_deleted = 1`) are excluded.

Files:
- New: `config/classes/Report.php` (report data), `config/reports/reports_page.php` (page, CSV,
  PDF), `Chairman/reports.php`, `Treasurer/reports.php`, `Secretary/reports.php`
- Changed: `config/classes/View.php` (menu links)
- Database: none (read-only)

### Sidebar menus redesigned

- New look for all role menus (Chairman, Secretary-General, Treasurer, Member, Applicant):
  a profile card at the top (staff number and role), section headings, distinct icons in rounded
  tiles, rounded hover/active states with an accent bar on the current page, and a cleaner
  sub-menu. Works in the collapsed (icon-only) sidebar and on mobile.
- Menus are now described as data and rendered by one helper (`View::renderSidebar()`), so every
  menu has the same, complete markup. The Secretary and Applicant menus were missing a closing
  `</ul>`.
- Grouping: Chairman and Treasurer approvals split into **Members & Savings** and **Shares**;
  Treasurer uploads grouped under **Uploads**; member **Complete Withdrawal** moved into
  **Savings**.
- Label fixes ("Registrations", "Withdrawals", "Disbursement", etc.).
- Removed the duplicate **Sales** entries (they opened the membership list, which is already in
  the menu). **Generate Report** (no page yet) is shown greyed out with a "Soon" badge.
- Added **Savings Settings** to the Treasurer menu.
- All other links are unchanged (same pages, same addresses).
- Added `View::ICTSideNav()`. Six pages called it but it did not exist, so they crashed
  (`Chairman/report.php`, `Chairman/upload.php`, `Secretary/report.php`, which the Secretary menu
  links to as **Report**, and three in `Treasurerr/`). It shows the logged-in user's menu.

Files:
- New: `css/sidebar.css`
- Changed: `config/classes/View.php` (sidebar functions; `header()` and `subHeader()` load
  `css/sidebar.css`)
- Database: none

**Before uploading `View.php`:** compare it with the live copy. If the live `View.php` has menu
entries or functions that this copy does not (for example its own `ICTSideNav()`), they need to be
merged in first, or they will be lost.

### Membership registration: Treasurer reviews, then Chairman authorizes

- **New flow:** applicant registers → Treasurer reviews the applicant's details and fixes the
  monthly savings → Chairman authorizes. (Previously the Chairman approved first.)
- **Treasurer:** the Membership Applicants tab lists every applicant not yet fully approved, with
  a status. **Review & Approve** opens the new details page (staff, payroll, bank, next of kin,
  last three payslips), where the Treasurer approves the proposed amount or enters a different
  one. The amount can be changed until the Chairman authorizes it.
- **Chairman:** the applicants tab lists only applicants the Treasurer has approved, with the
  fixed amount. No details page. The Chairman approves one at a time (**Approve** on the row) or
  several at once (checkboxes, select-all, **Approve Selected**). The list is no longer
  paginated, so select-all covers every applicant.
- The Chairman cannot authorize an applicant until the Treasurer has fixed the amount.
- Only the Treasurer can fix amounts and only the Chairman can authorize (previously any
  logged-in user could call these endpoints).
- The Chairman's Approve/Reject form on the old details page now reads the clicked button
  instead of keyboard focus. Browsers that do not focus clicked buttons (e.g. Safari) were
  recording every decision as "Registration Rejected".
- Bulk member upload (`Treasurer/uploadMembersExcel.php`, `uploadMembersExcel2.php`) now sets
  `treasurer_approval = 1`. Imported members were previously unable to log in as members.

Files:
- New: `Treasurer/view_proposed_member_details.php`, `ajax/chairman_approve_memberships.php`
- Changed: `Treasurer/member_list.php`, `Chairman/member_list.php`, `config/classes/MemberG1.php`
  (`getApplicantDetails`, `treasurerApproveMembership`, `getTreasurerApplicants` replaces
  `getCharimanApprovedApplicants`, `getMembershipApplicants`, `secretaryApproveRegisteration`,
  `getSecretaryMembershipDecision`), `ajax/process_member_approval.php`,
  `ajax/secretary_member_endorse.php`, `js/custom.js`, `Treasurer/uploadMembersExcel.php`,
  `Treasurer/uploadMembersExcel2.php`
- Database: migration 2

### Membership registration form

- Re-submitting the registration form no longer shows an error after saving. A re-submitted
  application goes back to the Treasurer.
- Proposed monthly savings is required and must be at least the minimum, checked on the form and
  on the server.
- The applicant's staff number is taken from their login, not from the form (an applicant could
  previously register someone else).

Files: `UR/register.php`, `ajax/register_member.php`, `config/classes/MemberG1.php`
(`registerMember`), `js/custom.js`

### Minimum monthly savings setting

- New Treasurer page **Savings Settings** (`Treasurer/savings_settings.php`) to view and change
  the minimum monthly savings (default ₦2,000). Records who changed it and when. Linked from the
  Membership Applicants tab and the Savings Update page.
- The minimum is enforced on: membership registration, the Treasurer's approved amount for
  applicants, members' and applicants' savings update requests, and the Treasurer's savings
  update approvals.

Files:
- New: `config/classes/Settings.php`, `Treasurer/savings_settings.php`
- Changed: `config/classes/SavingsG1.php` (`getMinMonthlySavings`, `updateSavingsAmount`),
  `UR/update_savings.php`, `R/savings/update_savings.php`, `js/custom.js`
- Database: migration 1

### Fixes

- `config/classes/DB.php`: `get_magic_quotes_gpc()` is only called if it exists. It was removed
  in PHP 8, which crashed every page on the local XAMPP (PHP 8.2). No effect on the live server
  (PHP 7). **Do not upload the local `DB.php`**: it holds local database credentials. Copy only
  this one-line change if needed.
- `config/classes/MemberG1.php`: `secretaryApproveRegisteration` used `bindParam` with a function
  result, a fatal error on PHP 8.

---

## 2026-10-05

### Treasurer: savings update requests

- New Treasurer page **Savings Update** (`Treasurer/treasurer_view_approved_savings.php`, already
  linked in the Treasurer menu) listing savings update requests approved by the Chairman.
  - **Download List (CSV)** with an Approved Amount column pre-filled with the requested amount,
    for offline processing; **Upload Approved List** applies the edited file and reports what was
    approved, left pending or refused. Rows are matched by Request ID and checked against the
    staff number.
  - Or approve each request on the page, with the requested amount or a different one.
  - Approval sets the member's monthly savings to the approved amount.
  - Where a member applied more than once, only the latest request is shown; approving it marks
    earlier pending requests as superseded (`is_treasurer_approved = -1`).
- The older pages (`treasurer_view_approved_savings2.php`, `download_approved_savings_csv.php`,
  `upload_approved_savings_csv.php`, `ajax/save_treasurer_savings_amount.php`) are unchanged and
  still work. `SavingsG1::updateTreasurerApprovedAmount()` is kept as it was for them; the new
  pages use `approveSavingsUpdateRequest()`.

Files:
- New: `Treasurer/treasurer_view_approved_savings.php`, `Treasurer/download_savings_updates.php`,
  `ajax/treasurer_approve_savings_update.php`
- Changed: `config/classes/SavingsG1.php` (`getChairmanApprovedSavingsUpdates`,
  `approveSavingsUpdateRequest`, `processTreasurerSavingsUpload`, `parseAmount`)
- Database: none

---

## Local development environment (not deployed)

- 2026-10-05: created MySQL user `fuded535_hrms` on the local XAMPP with access to
  `fuded535_fudscoops` only. No longer needed since the local `DB.php` uses `root`.
- `Treasurerr/` is an old local copy of the treasurer folder and can be deleted.

---

## Known issues not yet addressed

- Chairman rejection only records the decision; the applicant stays in the Chairman's list.
  (The Chairman's list no longer has a Reject option; the old details page
  `Chairman/view_proposed_member_details.php` still has one but is no longer linked.)
- A pending applicant changing their amount on `UR/update_savings.php` files a savings update
  request instead of changing the application.
- `ajax/save_treasurer_savings_amount.php`, `ajax/approved_update_savings.php` and
  `ajax/reject_update_savings.php` do not check who is logged in. `Secretary/update_savings.php`
  and `Secretary/member_list.php` have their login check commented out.
- `Treasurer/slip.php` (payslip download) does not check who is logged in.
- Some pages show another role's menu (e.g. 5 Treasurer pages call the Chairman's `subSideNav()`,
  3 `UR/` pages call the member menu), so some of their menu links lead to missing pages.
- Two membership records point to staff records that do not exist (`fudscoops_member.member_id`
  1077 → employee 2468, and 1310 → employee 0). They appear in the Membership report as
  "Staff record missing".
- Complete withdrawals are not deducted from members' savings balances anywhere in the system.
- The old staff reports (`Chairman/report.php`, `Secretary/report.php`) are from the HR system and
  are no longer linked from the menus.
- Menu links to pages not present in this copy: Chairman **Loan Deduction**
  (`Chairman/loan_deduction.php`) and Secretary **Schedule** (`Secretary/fee.php`).
- Passwords are stored as unsalted MD5.
