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
| 3 | `db/2026-10-08_loans_shares.sql` | Adds `entry_type` and `reference_id` columns to `fudscoops_shares` (shares become a ledger; existing rows become `'purchase'`). Adds settings `loan_max_savings_multiple` (2), `guarantor_max_open_loans` (2), `share_min_balance_after_transfer` (20000). Marks two empty records deleted (`fudscoops_shares_transfer` 2, `fudscoops_shares_conversion` 1). Run after #1 and BEFORE uploading the code from 2026-10-08 (the current code is unaffected by the new columns). | Applied 2026-10-08 | Pending |
| 4 | `db/2026-10-13_loan_workflow.sql` | Loan workflow reform. Backs up `fudscoops_loan` to `fudscoops_loan_backup_20261013`. Adds loan columns `interest_rate`, `interest_amount`, `total_payable`, `treasurer_comment`, `treasurer_date`, `disbursed_amount`, `disbursement_date`, `disbursement_reference`, `disbursed_by`, `repayment_start`, `repaid_date`; adds `fudscoops_suppliers.sort_code`; adds setting `loan_interest_rate` (10). Existing `'Approve'`/`'Approved'` loans become `'Disbursed'` with no interest (total = approved amount, paid out on the loan date, deductions from that month); loans already repaid in full become `'Repaid'`; pending loans the Chairman had decided are returned to the new flow (approved → undecided; declined → `'Disapprove'`). Undo statements are in the file. Run after #3 and BEFORE uploading the code from 2026-10-13. | Applied 2026-10-13 (155 loans → Disbursed) | Pending |
| 5 | `db/2026-10-13_commodity_workflow.sql` | Commodity workflow. Backs up the applications, application items and supply items tables (`*_backup_20261013`). Status becomes free text; adds application columns for months, Treasurer and Chairman decisions, interest, total, monthly amount, supply and repayment; new table `fudscoops_commodity_repayments`; setting `commodity_max_tenor` (6). The 6 existing requests (none ever supplied) go to the Treasurer at their requested value; supply items with no stock that nobody requested get their full allocation. Undo statements are in the file. Run after #4 and BEFORE uploading the code from 2026-10-13. | Applied 2026-10-13 (6 requests → pending) | Pending |
| 6 | `db/2026-10-14_loan_limit_3x.sql` | Sets `loan_max_savings_multiple` to 3: open loans plus a new loan may not exceed 3 times the savings balance (was 2). Safe to run before or after the code. Undo statement is in the file. | Applied 2026-10-14 | Pending |
| 7 | `db/2026-10-14_loan_controls.sql` | Adds `fudscoops_loan.guarantor_comment`; new table `fudscoops_loan_overpayments`; adds `settle_from_savings`, `amount_owed`, `settled_at` to `fudscoops_complete_withrawal`; setting `soft_loan_open` = 0. No changes to existing records. Undo statements are in the file. Run after #5 and BEFORE uploading the code from 2026-10-14. | Applied 2026-10-14 | Pending |
| 8 | `db/2026-10-14_kobo_and_withdrawals.sql` | Money columns become DECIMAL(14,2) so amounts keep kobo (savings, monthly savings, savings updates, withdrawals, complete withdrawals, loans, repayments, shares, share unit, commodity prices). Old repayment schedule amounts with long decimals are rounded to kobo (backup `fudscoops_loan_repayments_backup_20261014`). Adds setting `withdrawal_min_balance` (starts at `min_monthly_savings`). Run after #7 and BEFORE uploading the code. | Applied 2026-10-14 | Pending |
| 9 | `db/2026-10-15_withdrawal_decisions.sql` | Savings withdrawals: adds `treasurer_comment`, `treasurer_decided_by`, `treasurer_decided_at`; status -1 = rejected by the Treasurer. Moves requests the Treasurer had approved but that never reached the Chairman (status 0 with an amount) to status 1. Backup `fudscoops_withrawals_backup_20261015`; undo statements in the file. Run after #8 and BEFORE uploading the code. | Applied 2026-10-15 (12 requests moved on) | Pending |
| 10 | `db/2026-10-17_withdrawal_chairman_decisions.sql` | Savings withdrawals: adds `chairman_comment`, `chairman_decided_by`, `chairman_decided_at`; `chairman_approval` -1 = rejected by the Chairman (reason shown to the member). No changes to existing records. Undo statement in the file. Run after #9 and BEFORE uploading the code. | Applied 2026-10-17 | Pending |
| 11 | `db/2026-10-17_complete_withdrawal_decisions.sql` | Complete withdrawals: `acount_number` changes from INT to text (10-digit numbers had been saved as 2147483647 and leading zeros dropped). Restores leading zeros (pads to 10 digits) and clears the 21 numbers saved as 2147483647, which the Treasurer re-enters when approving. Adds `treasurer_comment`, `treasurer_decided_by`, `treasurer_decided_at`; `chairman_approval` -1 = rejected by the Treasurer. Backup `fudscoops_complete_withrawal_backup_20261017`; undo statements in the file. Run after #10 and BEFORE uploading the code. | Applied 2026-10-17 (29 numbers fixed, 21 cleared) | Pending |
| 12 | `db/2026-10-18_complete_withdrawal_chairman.sql` | Complete withdrawals: adds `chairman_comment`, `chairman_decided_by`, `chairman_decided_at` so the Chairman can reject with a reason (`chairman_approval` -1). No changes to existing records. Undo statement in the file. Run after #11 and BEFORE uploading the code. | Applied 2026-10-18 | Pending |
| 13 | `db/2026-10-19_special_commodity.sql` | Special commodity requests: adds `fudscoops_commodity_loan_applications.request_type` ('regular' / 'special', existing rows 'regular') and the new table `fudscoops_special_commodity_items` (item, description, quantity, member's estimated price, Treasurer's price, approved quantity). No existing records change. Undo statements in the file. Run after #12 and BEFORE uploading the code. | Applied 2026-10-19 | Pending |

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

`fudscoops_loan` (one row per application; workflow from 2026-10-13):

| Column | Values |
|---|---|
| `guarantor_status` | 0 awaiting guarantor, 1 accepted, -1 declined |
| `chairman_approval` | 0 undecided, 1 authorized, -1 declined |
| `loan_status` | `'pending'` (awaiting guarantor, then Treasurer), `'Recommended'` (Treasurer fixed amount, period and interest; awaiting Chairman), `'Approve'` (Chairman authorized; awaiting payment), `'Disbursed'` (paid out; being repaid), `'Repaid'` (fully repaid), `'Disapprove'` (declined: by the Chairman if `chairman_approval` = -1, otherwise by the Treasurer). `'Approved'` (old bulk uploads) is treated as `'Disbursed'`. |
| `amount_recommended`, `interest_rate`, `interest_amount`, `total_payable` | Fixed by the Treasurer: interest = amount × rate ÷ 100 (flat); total = amount + interest |
| `disbursed_amount`, `disbursement_date`, `disbursement_reference`, `disbursed_by` | Recorded by the Treasurer when the money is paid |
| `repayment_start` | First day of the month after payment; `repaid_date` set when deductions reach `total_payable` |
| `is_action_disabled` | 1 after the Chairman decides; the Chairman's **Unlock** (before payment) returns the loan to the Treasurer |

`fudscoops_loan_repayments`: `status` 0 = monthly repayment schedule (`loan_repayment_amount` =
total ÷ months, dated `repayment_start`), 1 = deduction received (`amount_paid`, one per loan per month).

`fudscoops_shares` (from 2026-10-08; a ledger, one row per movement; holding = sum of approved rows):

| Column | Values |
|---|---|
| `entry_type` | `purchase`, `transfer_in`, `transfer_out` (negative amount), `conversion` (from savings), `adjustment` (bulk upload correction) |
| `share_status` | 0 pending, 1 approved, -1 declined |
| `secretary_endorsement`, `chairman_approval` | 0 undecided, 1 approved, -1 declined |

`fudscoops_shares_transfer` (`share_transfer_status`, `secretary_endorsement`, `chairman_approval`)
and `fudscoops_shares_conversion` (`conversion_status`, `chairman_approval`): 0 pending/undecided,
1 approved/done, -1 declined.

---

## 2026-10-19

### Treasurer savings withdrawals redesigned; withdrawal upload fixed, with empty and prefilled templates

No database changes.

**Treasurer/withdrawal_endorsement.php:**
- **Header and figures:** requests awaiting the Treasurer (and the amount asked), how many ask for more than
  allowed, and how many are with the Chairman.
- **Tabs:** Awaiting you, and With the Chairman.
- **Each request shows:**
  - the date it was asked for, or "uploaded";
  - the amount asked, with an "over the limit" flag;
  - the savings balance and the most that can be withdrawn;
  - the bank account.
- **Download as upload template:** the requests awaiting you, in the upload layout.
- **Fixes:**
  - the bank showed as a number;
  - "Total savings" ignored withdrawals already paid;
  - the old table plugin loaded from a path outside the site.

**Treasurer/uploadWithdrawals.php (part of Savings Withdrawals):**
- **Two downloads** replace the single template:
  - **Empty template:** the column headings only, for paper applications.
  - **Prefilled:** every request awaiting the Treasurer, with its Withdrawal ID, staff number, name, bank,
    account and an approved amount. That amount is what was asked, or the most that can be withdrawn if
    less. Columns after the amount (amount requested, savings balance, most allowed, date) are for
    checking and are ignored on upload.
- **Login check added:** the page had none.

**Treasurer/uploadWithdrawalCSV.php (rewritten).** Every row is now the Treasurer's approval, made with the same
method as approving one request (`MemberG1::treasurerApproveWithdrawal`).
- **With a Withdrawal ID:** the request is approved and goes to the Chairman. Before, the amount was saved
  but the status stayed 0, so uploaded requests never reached the Chairman (the problem fixed on
  2026-10-15 for single approvals). The ID must belong to the staff number in the row.
- **Without an ID (paper application):** a request is recorded and approved. A member who already has a
  request waiting is skipped, with that request's number given. Before, a new request was added anyway,
  unless one had been created in the previous 24 hours.
- **Checks on every row:**
  - the amount must leave the minimum balance (it was not checked before);
  - requests already decided are not changed (before, an approved request's amount could be overwritten);
  - the bank name is matched to the bank list;
  - account numbers that lost their leading zero in a spreadsheet are padded back to 10 digits.
- **Access:** Treasurer only (there was no login check).
- **Tested:** a prefilled file of the 28 waiting requests, plus paper and error rows, uploaded on a copy of the
  database. 27 were approved and 1 was refused (₦0 allowed); the paper application was recorded; over-limit,
  wrong-member, already-decided and duplicate rows were each refused with the reason.

### Chairman authorizations: detailed and bulk

No database changes. Every Chairman authorization can now be done in two ways:
- **Detailed:** open one request, see everything about it, and Authorize or Decline from that page.
- **Bulk:** tick requests on the list (or Select all) and authorize or decline them in one action.

Both go through one endpoint, `ajax/chairman_decisions.php` (Chairman only). It decides each request with
the same method as before, and that method's checks: savings minimum, debts, account number, stage. One
request failing does not stop the others, and the reason for each failure is listed. Declining needs a reason
the member sees. Savings updates are the exception: they have no reason field, so a reason is optional there.
Membership can only be authorized; its old "decline" only recorded a note and left the application waiting.

| Area | Detail page | Bulk on the list |
|---|---|---|
| Membership | `view_proposed_member_details.php` rebuilt. It was broken: the list links with `employee_id` but the page read `member_id`. | already there |
| Savings update | **New** `view_savings_update.php` (current and new amount, balance, recent savings); Details link on each row | already there |
| Savings withdrawal | `view_withdrawal.php` rebuilt. It showed the Treasurer's old form; it now shows the Treasurer's amount, payment details, savings left and minimum-balance check. Details link on each row | already there |
| Complete withdrawal | **New** `view_com_withdrawal_details.php` (savings, debts and guarantees, Treasurer's amount and full amount due, payment details) | **added**; requests that cannot be approved have the checkbox disabled |
| Share purchase / transfer / conversion | existing pages | lists rebuilt (`config/chairman/share_list.php`). They show only what awaits the Chairman: before, purchases not yet endorsed by the Secretary, or already authorized, were listed too. Search and checkboxes added |
| Loans | existing page | **added** on the Chairman's Loans list for loans awaiting the Chairman |
| Commodity | existing page | **added** on the Chairman's list for requests awaiting the Chairman |

| File | Change |
|---|---|
| `ajax/chairman_decisions.php` | **New** – single and bulk Chairman decisions |
| `js/fud-bulk.js` | **New** – bulk bar (select all, authorize or decline selected, one reason) and detail-page buttons |
| `config/chairman/decisions.php` | **New** – "Your decision" box for detail pages |
| `config/chairman/share_list.php` | **New** – Chairman's share lists |
| `Chairman/view_savings_update.php`, `Chairman/view_com_withdrawal_details.php` | **New** |
| `Chairman/view_proposed_member_details.php`, `Chairman/view_withdrawal.php`, `Chairman/manage_share_*.php` | Rebuilt |
| `Chairman/complete_withdrawals.php`, `Chairman/withdrawal_endorsement.php`, `Chairman/update_savings.php`, `Chairman/approved_commodity_Request.php` | Checkboxes and/or Details links |
| `config/loans/officer_loans.php` | Chairman: checkboxes on loans awaiting authorization |
| `config/classes/CommodityLoan.php` | `requestsTable()` takes `$bulk` (checkboxes on requests awaiting the Chairman) |

### Commodity Deduction: empty and prefilled templates

No database changes.

- **Treasurer/commodity_deduction.php:** two downloads replace the single template link.
  - **Empty template:** the column headings only.
  - **Prefilled for a month:** one row for each commodity request (regular or special) due a deduction that
    month and not yet recorded. Each row has the staff number, the month's last day, the amount due (the
    monthly deduction, or the balance if less), the request number, and the name, commodity and balance
    for checking.
  - **Month:** chosen on the page (default this month), which shows how many are due and the total. A
    month already uploaded drops out of the prefilled file.
- **Treasurer/uploadCommodityDeduction.php:**
  - reads an optional 4th column, Request #, so a member with two commodity loans can have a row for each;
  - a request number that does not belong to the staff number is rejected for that row;
  - with no number, the row goes to the member's oldest request being repaid, as before;
  - extra columns are ignored.
- **Tested:** a prefilled November file uploaded unchanged recorded both deductions, and downloading it
  again then gave only the heading.

### Special commodity requests: full workflow

Run `db/2026-10-19_special_commodity.sql` (#13) on the live database **before** uploading this code.

Members can ask for items the cooperative does not stock and repay them as a commodity loan. The old page could
never save: it posted to `process_commodity_loan.php`, which does not exist. Special requests now follow
the same steps as commodity requests, so they appear on the existing Treasurer, Chairman, deduction and member
pages.

1. **Request (R/special_commodity_request.php, rebuilt):**
   - the member lists up to 20 items, each with a description, quantity and estimated price;
   - they choose the months to repay and see an estimate with the interest;
   - one special request can be in progress at a time;
   - their past special requests are listed below the form.
2. **Treasurer review (Treasurer › Commodity Requests › request):**
   - the member's estimate is shown next to an editable unit price;
   - the Treasurer corrects the prices, sets each quantity (0 = not approved) and the months;
   - the flat interest is added and the request goes to the Chairman, or the Treasurer declines it with a reason.
3. **Chairman authorization:** authorize, or decline with a reason (as for commodity).
4. **Release:** the Treasurer records the date the items were released to the member (stage "Awaiting
   release" until then).
5. **Repayment:**
   - the schedule starts automatically the month after release, at the approved monthly amount;
   - deductions are recorded like any commodity deduction (single, or Treasurer › Commodity Deduction /
     CSV upload);
   - the request becomes "Fully repaid" at the total.

- **Officer list:** a "Special requests" tab, and a "special" label on these rows. The Treasurer, Chairman
  and Secretary pages accept `?stage=special` (`$stages` lists it); without it the tab showed every request.
- **Leaving members:** special requests count toward what the member owes or has in progress.

| File | Change |
|---|---|
| `db/2026-10-19_special_commodity.sql` | **New** – database changes (#13 above) |
| `R/special_commodity_request.php` | Rebuilt |
| `config/classes/CommodityLoan.php` | New `submitSpecialRequest()`, `openSpecialRequest()`, `isSpecial()`; `lines()` reads special items; `treasurerReview()` takes corrected prices; release wording, "Special requests" tab |
| `ajax/commodity_workflow.php` | New action `submit_special`; Treasurer review passes prices |

---

## 2026-10-20

### Treasurer savings and withdrawal records redesigned; duplicate pages removed

No database changes.

**Treasurer/view_savings.php (Members Savings, rebuilt):**
- **Wrong filter fixed:** the old page searched by `savings_date`, which is empty on 2,196 of 3,497 savings rows
  (monthly uploads), so most savings never appeared. It also dropped every member without an IPPIS record.
  Savings are now taken by the month they are for (month/year).
- **By month:**
  - what was saved against the monthly savings due;
  - tabs for All, Saved, Saved less than monthly and Nothing recorded, with counts;
  - each member's monthly savings, amount saved, status and savings balance;
  - search, pages of 50 and a CSV download.

  October 2026: ₦14,845,082.43 saved, with 182 members having nothing recorded.
- **Yearly summary:** the total saved and the number of members for each month.

**Treasurer/approved_withdrawals.php (Approved Withdrawals register):**
- **One page:** every withdrawal the Chairman approved, by approval date, covering savings withdrawals and
  complete withdrawals.
- **Filters and totals:** presets (this month, last month, this year, all time) or a from/to range; a type
  filter; totals; search with a running total; CSV download.
- **Payment details:** each row shows the amount paid and any debts paid from savings.
- **Missing approval date:** approvals from before 2026-10-18 did not record one (`0000-00-00`); the request
  date is used instead.

**Treasurer/treasurer_view_approved_savings.php (Savings Update):**
- Same approval and offline upload, redesigned: requests with current, requested and new amounts, the change, and
  a link to the member.
- The "Many at once" panel holds download and upload.
- Results of the last 30 days are listed.
- The Chairman's Update Savings page now labels superseded requests "Replaced", not "Rejected".

**Removed (duplicates):**
- `Treasurer/uploaded_withdrawals.php`: the same list as the "With the Chairman" tab on Treasurer Savings
  Withdrawals. A wrong request is rejected there with a reason instead of being deleted.
- `Treasurer/view_withdrawals_report.php`: approved withdrawals by date, now part of Approved Withdrawals.
- `ajax/delete_withdrawal.php`: had **no login check** and permanently deleted any withdrawal, approved
  payouts included.
- `ajax/fetch_members_savings.php`: had **no login check** and returned any member's savings.
- `ajax/fetch_withdrawals_ajax.php`: used only by the report page.

**Menu:** Treasurer › Records now has **Approved Withdrawals** instead of the three withdrawal links.

**Data note:** the 48 savings updates the Treasurer fixed on 2026-10-05 set members' monthly savings to about
₦50,0xx, whatever was requested. This looks like test data from a CSV upload; check those members'
monthly savings.

| File | Change |
|---|---|
| `Treasurer/view_savings.php` | Rebuilt |
| `Treasurer/approved_withdrawals.php` | Rebuilt as the approved withdrawals register |
| `Treasurer/treasurer_view_approved_savings.php` | Redesigned |
| `Treasurer/uploaded_withdrawals.php`, `Treasurer/view_withdrawals_report.php` | **Removed** |
| `ajax/delete_withdrawal.php`, `ajax/fetch_members_savings.php`, `ajax/fetch_withdrawals_ajax.php` | **Removed** |
| `config/classes/View.php` | Treasurer menu |
| `config/classes/SavingsG1.php` | `getChairmanApprovedSavingsUpdates()` also returns `member_id` |
| `js/custom.js` | Removed the old Members Savings form handler |
| `Chairman/update_savings.php` | "Replaced" label |

---
## 2026-10-18

### Member withdrawal history redesigned (R/view_all_withdrawals.php)

No database changes.

- **Figures:** total withdrawn, amount waiting for approval, how much the member can withdraw now (after
  the minimum balance), and the last withdrawal paid.
- **Tabs:** All, Approved, Pending and Rejected, with **Download CSV**.
- **Each request shows:**
  - its date, or "uploaded by the Treasurer" for uploaded withdrawals;
  - the amount asked for and the amount approved;
  - the bank account it was paid into;
  - its stage and date: Awaiting Treasurer, Awaiting Chairman, Approved or Rejected.
- **Rejection reasons:** a rejected request shows who rejected it and why.
- **Complete withdrawals:** listed too, with any debts paid from savings.
- **Fixes:**
  - requests rejected by the Treasurer showed as "Pending";
  - the bank showed as a number;
  - a pending request showed ₦0.00 (only the approved amount was listed);
  - with no member record the page sent the member to a missing page;
  - the table plugin was loaded from a path outside the site.

### Complete withdrawals: the Chairman sees when less than the full amount is paid

A complete withdrawal (member leaving) pays the amount the Treasurer approves. That amount can be less than the
full amount due (savings, less what they owe, less the minimum balance). The Chairman is shown the difference,
which stays in savings, and can still approve it. No database changes.

- **Treasurer (view_com_withdrawal_details.php):**
  - the amount box suggests the full amount due and can be lowered;
  - it cannot go above the full amount;
  - a lower amount shows how much stays in savings and that the Chairman will see it.
- **Chairman (complete_withdrawals.php):**
  - a request below the full amount is marked "full payout ₦…";
  - a note gives the full amount and how much would stay in savings;
  - Approve stays available. It is disabled only for real blockers: debts not being paid from savings,
    loans being guaranteed, more than the limit, or a missing account number.

| File | Change |
|---|---|
| `config/classes/LoanControls.php` | New `completePayoutShortError()` (the message; it does not block) |
| `Chairman/complete_withdrawals.php` | Shows the full payout and the difference for a lower amount |
| `Treasurer/view_com_withdrawal_details.php` | Amount suggested at the full payout; note for a lower amount |

### Chairman can set the minimum savings balance

No database changes: the amount is the existing setting `withdrawal_min_balance`.

**Chairman/withdrawal_settings.php (new; menu Settings › Minimum Savings Balance):**
- Shows the current minimum and who last changed it and when.
- Saves a new amount (0 or more, kobo allowed) after a confirmation.
- **Live preview** as the amount is typed:
  - how many members could not withdraw anything;
  - the savings held back across all members;
  - how many waiting savings and complete withdrawal requests could no longer be approved.

  At ₦3,000: 181 of 1,308 members, ₦3,574,494 held back, and 22 of 41 waiting requests.
- **Applies immediately:** every withdrawal check reads the setting, including approval of requests
  already waiting.
- **Treasurer:** can still change it on Savings Settings. The description there no longer says a
  complete withdrawal pays out everything.

| File | Change |
|---|---|
| `Chairman/withdrawal_settings.php` | **New** |
| `config/classes/View.php` | Chairman menu: Settings › Minimum Savings Balance |
| `Treasurer/savings_settings.php` | Description of the minimum balance corrected |

### Minimum balance on complete withdrawals; Chairman pages; loan deduction history for Chairman and Secretary

Run `db/2026-10-18_complete_withdrawal_chairman.sql` (#12) on the live database **before** uploading this code.

**Rule change – complete withdrawals keep the minimum balance.** A leaving member is now paid their savings
less what they owe **and** less the minimum savings balance (`withdrawal_min_balance`, now ₦3,000), which
stays in savings. This replaces the 2026-10-14 rule that paid out everything. It is applied in:
- `LoanControls::leavingObligations()` (the payout, and a new `min_balance`);
- the Treasurer's and Chairman's approval checks;
- the member's Complete Withdrawal page, which shows the amount that stays;
- the Treasurer's request page.

**Chairman/complete_withdrawals.php (redesigned):**
- **Each request shows:**
  - savings now, what the member owes (and whether it is paid from savings);
  - the amount to pay and what is left in savings;
  - the account, and the Treasurer's comment.
- **Approve** runs the same checks as before and is disabled, with the reason shown, when a request
  cannot be approved (owes and not paying from savings, guarantor, minimum balance, missing account).
- **Reject** asks for a reason, which the member sees; they can apply again. The old Reject posted to a
  placeholder address and did nothing.
- **Recent decisions:** the Chairman's decisions from the last 30 days. Who approved and when is now recorded.

**Chairman/update_savings.php (redesigned):**
- **Each request shows** current monthly savings, the new amount, the change, and the savings balance.
  Amounts below the minimum and requests replaced by a newer one are marked.
- **Approve and Reject** work one by one or in bulk, by request number (they used staff numbers before).
- **Recent decisions** for the last 30 days, with what the Treasurer did after.

**Chairman/member_list.php and member_profile.php:** the Treasurer's member list and member record, now
shared (`config/members/`).
- The Chairman sees the same figures, filters, CSV and member record.
- The Applicants tab authorizes applicants one by one or in bulk, as before.
- Links the Chairman has no page for (withdrawal and overpayment details) are hidden.
- Each loan on the member record also links to its deduction history.

**Loan deduction history (Chairman/, Secretary/ and Treasurer/ loan_deduction.php):** one shared page
(`config/loans/loan_deduction_desk.php`).
- **By month:** the Treasurer's monthly desk. The Chairman and Secretary see it read-only (no Record
  buttons, no upload).
- **Yearly history:** for each month, the loans due, the amount scheduled and recorded, a progress bar,
  and the deductions, short and missed counts. Each month opens that month.
- **Missed vs not uploaded:** a deduction counts as missed only in a month whose deduction file was
  uploaded for that loan type (the rule `LoanControls::overdueLoans()` uses). Otherwise it counts as "not
  uploaded". No file has been uploaded locally for March–October 2026, so the 155 loans show as
  "not uploaded", not missed.
- **One loan's history:** month by month, with the amount due, deducted, balance after, and status
  (Recorded / Short / Missed / Upcoming). The Treasurer also gets a link to record a deduction.
- **Download:** the month's deductions as CSV, for everyone. The Treasurer's "to record" CSV is unchanged.
- **Menus:** the Chairman's dead "Loan Deduction" link now works, and the Secretary has a new Loan
  Deductions link.

**Fixes:**
- **Savings update endpoints:** `ajax/approved_update_savings.php`, `reject_update_savings.php` and
  `bulk_approve_savings.php` had no login check; they now need the Secretary or Chairman.
  `approved_update_savings.php` used `message =>` without quotes, which is a fatal error on PHP 8.
- **Rejected savings updates:** `SavingsG1::secretaryRejectUpdateSavings()` marked **every** savings update
  the member had ever made as rejected, including approved ones. It now rejects only the pending one.
- **Rejections show who rejected:** on Treasurer pages and the member's page, a Chairman rejection of a
  complete withdrawal shows as "rejected by the Chairman" with the reason.

| File | Change |
|---|---|
| `db/2026-10-18_complete_withdrawal_chairman.sql` | **New** – database changes (#12 above) |
| `config/classes/LoanControls.php` | Complete withdrawal payout keeps the minimum balance; `min_balance` |
| `config/classes/MemberG1.php` | Complete withdrawal checks keep the minimum; Chairman approval records who; new `chairmanRejectComWithdrawal()` |
| `config/classes/SavingsG1.php` | Minimum in the "nothing to pay out" message; `secretaryRejectUpdateSavings()` fixed; new `chairmanDecideSavingsUpdate()`, `savingsUpdatesAwaitingChairman()`, `savingsUpdatesRecent()` |
| `config/classes/Dashboard.php` | Member's rejected complete withdrawal says who rejected it |
| `config/classes/View.php` | Chairman "Loan Deductions" label; Secretary Loan Deductions link |
| `config/loans/loan_deduction_desk.php` | **New** – shared loan deductions page (month, year history, one loan) |
| `config/members/member_list.php`, `config/members/member_profile.php` | **New** – shared member list and record (moved from Treasurer/) |
| `Treasurer/loan_deduction.php`, `Treasurer/member_list.php`, `Treasurer/member_profile.php` | Now load the shared pages |
| `Chairman/loan_deduction.php`, `Chairman/member_profile.php`, `Secretary/loan_deduction.php` | **New** |
| `Chairman/member_list.php`, `Chairman/complete_withdrawals.php`, `Chairman/update_savings.php` | Redesigned |
| `ajax/chairman_complete_withdrawal_decision.php`, `ajax/chairman_savings_update_decision.php` | **New** – Chairman approve or reject |
| `ajax/approved_update_savings.php`, `ajax/reject_update_savings.php`, `ajax/bulk_approve_savings.php` | Login check; PHP 8 fix |
| `R/complete_withdrawal.php`, `Treasurer/view_com_withdrawal_details.php`, `Treasurer/complete_withdrawals.php` | Minimum balance shown; Chairman rejections shown |

---
## 2026-10-17

### Treasurer complete withdrawal pages redesigned; account numbers repaired; Reject works

Run `db/2026-10-17_complete_withdrawal_decisions.sql` (#11) on the live database **before** uploading this code.

**Treasurer/complete_withdrawals.php:**
- **Login check added:** the page had none, so anyone with the link could see it.
- **Header and figures:** requests awaiting the Treasurer (and the total to pay), those that cannot be
  paid yet, those awaiting the Chairman, and those approved.
- **Tabs:** Awaiting you, Awaiting Chairman, Approved and Rejected, each with search. Before, a request
  disappeared from this page once the Treasurer set an amount.
- **Each open request shows:**
  - the savings balance, what the member owes, and the amount to pay;
  - the bank and account number, or "Account number missing";
  - a check: Clear, Pays debts from savings, Owes but not paying from savings, Guarantor or request
    open, Nothing to pay, or No account.

**Treasurer/view_com_withdrawal_details.php:**
- **Rebuilt in the style of the savings withdrawal details page:**
  - figures for savings balance, amount owed and amount to pay out;
  - notices for anything that stops payment;
  - the member's details and payslips;
  - their loans, commodity and guarantees;
  - recent savings, with a link to the member record.
- **Pay into:** shows the account the member gave on the request. The old form showed bank fields from
  the staff record, which do not exist, so they were always blank. When the account number is missing,
  the last account the member used for a savings withdrawal is shown as a hint.
- **Decision panel:** the amount to pay (up to the payout), the account number when it is missing, a
  comment, **Approve and send to Chairman**, and **Reject request**. Rejecting needs a reason the
  member sees. The amount can be changed until the Chairman decides.

**Fixes:**
- **Account numbers lost:** the column was INT, so 21 of 50 account numbers were saved as 2147483647
  and 22 lost their leading zero. The migration repairs the leading zeros and clears the lost numbers.
  The Treasurer enters the correct one when approving, and the Chairman cannot approve without it.
- **Reject approved the request:** the old form's Reject sent the amount, and the endpoint treated it as
  an approval. The old endpoint now refuses it; rejecting is done on the new page.
- **Rejected requests:** the member sees the rejection and reason on Complete Withdrawal and can apply
  again. Reports and the member record show "Rejected" instead of "Pending".
- **Roles:** only the Treasurer can set the amount (`ajax/secretary_withdrawal_endorsement.php` allowed
  any officer). The Chairman cannot approve before the Treasurer has set an amount.

| File | Change |
|---|---|
| `db/2026-10-17_complete_withdrawal_decisions.sql` | **New** – database changes (#11 above) |
| `Treasurer/complete_withdrawals.php` | Redesigned; login check |
| `Treasurer/view_com_withdrawal_details.php` | Redesigned |
| `ajax/complete_withdrawal_decision.php` | **New** – Treasurer approve or reject |
| `ajax/secretary_withdrawal_endorsement.php` | Complete withdrawal amount is Treasurer only; old Reject no longer approves |
| `config/classes/MemberG1.php` | `treasurerApproveComWithdrawal()` checks the stage, takes the account number and comment, records who decided; new `treasurerRejectComWithdrawal()` and `treasurerCompleteWithdrawals()`; `chairmanApproveComWithdrawal()` needs the amount and account number |
| `config/classes/Dashboard.php` | `memberSavingsInfo()` returns a recent rejection (`rejected_complete`); new `row()` helper |
| `R/complete_withdrawal.php` | Shows a Treasurer rejection and its reason |
| `config/classes/Report.php`, `Treasurer/member_profile.php` | Rejected complete withdrawals labelled "Rejected" |
### Treasurer member list redesigned; new member record page

No database changes.

**Treasurer/member_list.php:**
- **Header and figures:** number of members and applicants, total savings balance, shares, and loans
  outstanding (with how many members are repaying).
- **Members tab:** each approved member's monthly savings, savings balance, shares and loans owed
  (including commodity owed). The list has:
  - search, and a filter: repaying a loan, owing for commodity, shareholders, no shares, savings
    below the minimum, negative savings balance;
  - sorting by any column, and pages of 25;
  - **Download CSV** of the rows shown.
- **Applicants tab:** the same review links as before, restyled.
- **View record:** each member has a button that opens their full record.
- **Old table plugin removed:** the page no longer loads the bootstrap-table files, whose paths
  pointed outside the site.

**Treasurer/member_profile.php (new):** one member's full record, read-only.
- **Figures:** savings balance (with monthly savings and how much can be withdrawn), shares (amount and
  units), loans owed (and commodity), and how many active loans they guarantee.
- **Notices:** overdue loans, loan overpayments, requests not yet in the balance, and a negative
  savings balance.
- **Details:** personal details, the last three months' payslips, and next of kin.
- **Tabs:**
  - savings statement (newest first, with totals saved and withdrawn);
  - withdrawal requests at every stage, including complete withdrawals;
  - shares;
  - loans (total, repaid, balance, guarantor, status, decline reason);
  - loans the member guarantees;
  - commodity requests.

  Each item links to its own details page.

**Data note:** 64 members show a negative savings balance (−₦2,964,600 in total). Their approved
withdrawals are more than the savings recorded, most likely because some savings were never uploaded.
Use the "Negative savings balance" filter to review them.

| File | Change |
|---|---|
| `Treasurer/member_list.php` | Redesigned; View record button |
| `Treasurer/member_profile.php` | **New** – member record |
| `config/classes/MemberG1.php` | New `treasurerMemberDirectory()`: every approved member with savings balance, shares, loans and commodity owed in one query (checked against `LoanControls::savingsBalance`, `MemberG2::getShareHolding` and `Dashboard::memberLoans` for all 1,308 members) |

### Chairman's savings withdrawal page redesigned; Reject works and gives a reason

Run `db/2026-10-17_withdrawal_chairman_decisions.sql` (#10) on the live database **before** uploading this code.

**Chairman/withdrawal_endorsement.php:**
- **Header and figures:** requests awaiting the Chairman, total to pay out, and how many need attention.
- **Each request shows:**
  - the member, staff number and department;
  - the amount requested and its date;
  - the Treasurer's amount, flagged when it is less or more than requested, with the Treasurer's comment;
  - the savings balance now and after the withdrawal;
  - the bank account to pay into.
- **Below minimum:** a request that would take savings below the minimum balance is marked and its
  Approve button is disabled. Five of the 11 requests waiting locally are like this; they are old
  requests where the Treasurer approved more than the savings allow.
- **Approve and Reject:** each works per row or for the ticked rows, with search. Reject opens a panel
  for the reason, which is required and shown to the member. When some ticked requests fail, each
  one's reason is listed.
- **Recent decisions:** the Chairman's decisions from the last 14 days.

**Fixes:**
- **Reject did nothing:** the per-row Reject posted to `reject_withdrawal.php`, which does not exist.
- **Bulk endpoint unprotected:** `ajax/bulk_approve_withdrawal.php` had no login check, so anyone could
  approve or reject any withdrawal. It also skipped the minimum-balance check and the approval date.
  It is now Chairman only and decides each request through `MemberG1`.
- **Requests sent twice:** `js/custom.js` repeated the page's bulk approve, reject and search handlers,
  so one click sent every request twice. The copy is removed.
- **Chairman approval open to others:** `ajax/secretary_withdrawal_endorsement.php` let the Secretary and
  Treasurer give the Chairman's approval (normal and complete withdrawals). It is now Chairman only.
- **Approval date:** approval now sets `approved_date`, used by the statement and reports. It can only
  happen once, and only for requests awaiting the Chairman.
- **Member notices:** a Chairman rejection shows on the member's dashboard and on Withdraw Savings with
  its reason, like a Treasurer rejection ("rejected by the Chairman: …"). It also shows on the
  Treasurer's withdrawal details page, and the Treasurer can no longer change the request.

| File | Change |
|---|---|
| `db/2026-10-17_withdrawal_chairman_decisions.sql` | **New** – database changes (#10 above) |
| `Chairman/withdrawal_endorsement.php` | Redesigned |
| `ajax/bulk_approve_withdrawal.php` | Rewritten – Chairman only; approve or reject (with reason) one or more requests |
| `ajax/secretary_withdrawal_endorsement.php` | Chairman approval is Chairman only; records who approved |
| `config/classes/MemberG1.php` | `chairmanApproveWithdrawal()` checks the stage and sets `approved_date` and who decided; new `chairmanRejectWithdrawal()`; Chairman list has more fields, skips deleted requests, oldest first |
| `config/classes/Dashboard.php` | `recentWithdrawalRejections()` includes Chairman rejections (`reason`, `rejected_by`) |
| `R/index.php`, `R/savings/savings_withdrawal.php` | Rejection notice says who rejected and why |
| `Treasurer/view_withdrawal_details.php` | Shows "Rejected by Chairman" with the reason |
| `js/custom.js` | Removed the duplicate Chairman bulk handlers |

---
## 2026-10-15

### Savings withdrawal request page redesigned; Reject works; approved requests reach the Chairman

Run `db/2026-10-15_withdrawal_decisions.sql` (#9) on the live database **before** uploading this code.

**Treasurer/view_withdrawal_details.php:**
- **Header:** request number and stage (Awaiting Treasurer, Awaiting Chairman, Approved, Rejected).
- **Figures:** amount requested (and approved), savings balance and monthly savings, and the most that
  can be withdrawn after the minimum balance. A warning shows when the request is above that.
- **Member:** details, with the last three months' payslips, and the account to pay into.
- **Recent savings and withdrawals** from the member's statement.
- **Decision panel:**
  - the amount to approve (up to the maximum) and a comment;
  - **Approve and send to Chairman**;
  - **Reject request**, which needs a reason the member sees;
  - the amount can be changed until the Chairman decides; afterwards the decision is shown.

**Fixes:**
- **Reject did nothing:** it sent the same request as Approve, so it either failed ("Enter the approved
  amount") or, with an amount typed, approved it. Comments were never saved.
- **Approvals never reached the Chairman:** the Treasurer's approval saved the amount but left the
  status at 0, while the Chairman's list shows only status 1. 12 requests were waiting unseen; the
  migration moves them on.
- **Rejected requests:** no longer count as pending, so the member can request again. They see the
  rejection and reason on their dashboard and on Withdraw Savings.
- **No login check:** `ajax/secretary_withdrawal_endorsement.php` had none; it now requires an officer.

| File | Change |
|---|---|
| `db/2026-10-15_withdrawal_decisions.sql` | **New** – database changes (#9 above) |
| `Treasurer/view_withdrawal_details.php` | Redesigned |
| `ajax/withdrawal_decision.php` | **New** – Treasurer approve or reject |
| `config/classes/MemberG1.php` | Approval sets status 1 and records the decision; new `treasurerRejectWithdrawal()` |
| `config/classes/Dashboard.php` | Rejected requests not pending; new `recentWithdrawalRejections()` |
| `config/classes/Report.php` | Pending withdrawals exclude rejected ones |
| `R/index.php`, `R/savings/savings_withdrawal.php` | Rejection notice with the reason |
| `ajax/secretary_withdrawal_endorsement.php` | Officers only |

### Members' Savings History page; overpayment to savings made clear

- **New Savings History** (`R/savings/savings_history.php`, member menu → Savings → Savings History; the
  dashboard's Savings Balance figure also opens it):
  - savings balance, total saved (and this year) and total withdrawn;
  - a statement of every monthly saving, withdrawal and complete withdrawal (including loans or
    commodity paid from savings), newest first, with the balance after each line;
  - filters by year and by savings or withdrawals, and a CSV download;
  - a **Pending** box for amounts not yet in the balance: withdrawals awaiting approval, and overpayments
    the member chose to add to savings that the Treasurer has not yet settled.
  - The statement's balance matches the balance used everywhere else (checked for all 1,308 members).
  - Overpayments added to savings are labelled "Loan overpayment added to savings (loan #…)".
- **Loan Overpayments** (`R/overpayment.php`): after the member chooses, the page says it is waiting for the
  Treasurer to add the amount to their savings (or pay the loan, or refund), and where it will show. The
  money moves only when the Treasurer settles it on Treasurer → Loan Overpayments; this is unchanged.

| File | Change |
|---|---|
| `R/savings/savings_history.php` | **New** |
| `config/classes/Dashboard.php` | New `memberStatement()` |
| `R/overpayment.php` | Clearer waiting message |
| `R/index.php` | Savings Balance figure links to Savings History |
| `config/classes/View.php` | Member menu: Savings History |

### Treasurer can reset members' passwords; password reset secured

- **New Password Reset page** (`Treasurer/password.php`, Treasurer menu → Settings):
  - enter a staff number and **Find** to see the name, department and account type;
  - **Reset password to staff number**; the page then shows the new password to give the member;
  - a short guide (log in with the staff number, change it at once, confirm who is asking).
- **Security fixes:**
  - `ajax/reset.php` had no login check: anyone, even logged out, could reset any account's password
    (including officers') to the staff number and log in as them. It now accepts only the Secretary,
    Treasurer and Chairman.
  - Only member and applicant accounts can be reset. Officer accounts are refused on all three pages.
  - `Secretary/password.php` only redirected when the user was not the Secretary and still ran the reset;
    it now stops. Its reset uses the same safe method (`MemberG2::resetPasswordByUsername` →
    `User::resetPassword`).
- Each reset is written to the PHP error log with who did it.

| File | Change |
|---|---|
| `Treasurer/password.php` | **New** |
| `ajax/reset.php` | Officers only; lookup and reset actions |
| `config/classes/User.php` | `resetPassword()` members/applicants only; new `passwordResetLookup()` |
| `config/classes/MemberG2.php` | `resetPasswordByUsername()` uses `User::resetPassword()` |
| `Secretary/password.php` | Stops when not the Secretary; officer-account message |
| `js/custom.js` | Chairman reset page: officer-account message |
| `config/classes/View.php` | Treasurer menu: Password Reset |

### Supply items page: see and update the items already in a supply (Secretary/addCommodityItems.php)

- **Top of the page:** the supply's name, status and dates, with figures for the number of items, the
  value of all items, and what members have requested.
- **Items in this supply** (new): each item's unit price, quantity, quantity members have requested,
  quantity left, and value.
  - Change the price or quantity in place and **Save**. The quantity cannot go below what members have
    requested, and what is left moves with it.
  - Remove an item no member has requested.
  - An item listed twice is flagged.
- **Add items:** choose an item and quantity, build a list, then **Save to this supply**. An item already in
  the supply gets the extra quantity.
- **Fixes:**
  - The page had no login check, and an unknown supply number now says so.
  - It loaded a second copy of jQuery and Bootstrap 5, and table scripts from paths that do not exist.

| File | Change |
|---|---|
| `Secretary/addCommodityItems.php` | Rebuilt; items already in the supply can be updated |
| `config/classes/CommodityLoan.php` | New `supply()`, `supplyItemsList()`, `updateSupplyItem()`, `removeSupplyItem()` |
| `ajax/commodity_supply.php` | Actions `item_update`, `item_remove` |
| `css/dashboard.css` | Inline inputs and item picker |

### Adding items to a commodity supply: false error fixed; repeated items merged

Saving items on `Secretary/addCommodityItems.php` showed "Error occurred while saving items" although
the items were saved.

**Cause:** two lines in `ajax/commodity_ajax.php` read form fields this request does not send
(`application_id`, `approved_total`). The fallback sat after the type cast, so PHP printed warnings into
the reply. The reply was then not valid JSON, and the page reported a failure.

**Fixed:**
- the two lines;
- `ajax/commodity_ajax.php` no longer prints PHP warnings into its replies (they go to the error log);
- saving items (`Commodity::saveCommoditySupplyItems`) now:
  - checks the supply exists;
  - checks each item exists, with a whole quantity above 0 and a price;
  - saves all items or none;
  - adds to the quantity (and sets the new price) when an item is already in the supply, or listed
    twice, instead of creating a second line members would see;
  - reports what was added or increased;
- the page shows that message and offers to go back to Commodity Supplies.

**Data:** BATCH I 2026 (supply 5) also has an old Yam line (row 12, 800 units, created 4 Dec 2025)
saved for a supply #5 that did not exist then. It now shows alongside the Yam line added on 15 Oct 2026.

| File | Change |
|---|---|
| `ajax/commodity_ajax.php` | Two warnings fixed; warnings kept out of replies |
| `config/classes/Commodity.php` | `saveCommoditySupplyItems()` checks, merges and saves all-or-nothing |
| `Secretary/addCommodityItems.php` | Shows the server's message |

### Commodity Supplies page (Secretary/add_commodity_name.php) rebuilt

The page that opens a commodity supply to members (menu: **Commodity Supplies**, was "Add Commodity Name"):
- **Status at the top:** the supply open to members now, until when, how many items and requests it
  has, and a warning when it has no items (members then cannot request anything). Otherwise, the next
  supply.
- **Open a new supply:** name (upper case), and opening and closing **date and time**. A new supply is
  suggested to start after the last one and run two weeks, with its length shown. After saving, the
  Secretary goes straight to adding its items.
- **Checks:**
  - a name is required and must be new;
  - the supply must close after it opens, and in the future;
  - its period may not overlap another supply, because members see only one supply at a time.
- **All supplies:** status (Open now, Upcoming, Closed), dates, item and request counts, with **Items**,
  **Dates** (change the opening and closing time; overlaps are refused) and **Close now** (stops new
  requests; requests already made carry on).
- **Fixes:**
  - The page had no login check.
  - Errors other than "name exists" were never shown.
  - A closing date was saved as midnight at the start of that day, so a supply closed a day early. A
    date entered without a time now closes at 23:59:59.
  - The supply list's old Edit button saved to the wrong table (`fudscoops_commodity_name`), so dates
    could not be changed; the new Dates button replaces it.
  - Open/closed status uses the database clock, the same one the member's request page uses.

Existing supplies keep their saved times (most close at 12:00am); the Secretary can correct them with
Dates.

| File | Change |
|---|---|
| `Secretary/add_commodity_name.php` | Rebuilt |
| `ajax/commodity_supply.php` | **New** – save, change dates, close now (officers only) |
| `config/classes/CommodityLoan.php` | New `supplies()`, `saveSupply()`, `closeSupplyNow()` |
| `config/classes/View.php` | Secretary menu: Commodity Supplies |
| `css/dashboard.css` | Supply list styles |

### Commodity supply: one page opens the request period

**Add Commodity Name** (`Secretary/add_commodity_name.php`) is the page that opens a commodity supply
to members. It saves the supply's name and opening and closing dates in `fudscoops_commodity_supply`,
which the member's Commodity Request page checks. **Schedule Commodity Supply**
(`Secretary/schedule_commodity.php`) did nothing: its forms posted to `ajax/schedule.php`, which does
not exist, and its table `fudscoops_commodity_schedule` is empty and read by no page.

Removed:
- the Schedule Commodity Supply page and its menu entry;
- its two form scripts in `js/custom.js`;
- the unused `Commodity::commoditySchedule()`.

The empty `fudscoops_commodity_schedule` table is left in the database. An old copy of the page remains
in the unused `Treasurerr/` folder.

| File | Change |
|---|---|
| `Secretary/schedule_commodity.php` | **Deleted** |
| `config/classes/View.php` | Secretary menu: Schedule Commodity Supply removed |
| `js/custom.js` | Schedule form scripts removed |
| `config/classes/Commodity.php` | `commoditySchedule()` removed |

### Upload Loan Deductions page and results redesigned (Treasurer/batch_loan_deduction.php)

- **The page:**
  - a clear form (loan type from the database; a file box that shows the chosen file);
  - a "How it works" guide pointing to the Loan Deductions download of loans to record, or the template;
  - the login check is back (it was commented out, so anyone could open the page); a leftover line
    referred to an undefined object.
- **After an upload:**
  - a headline: file, loan type, rows read, amount recorded and how many rows need attention. A month
    repeated in the file is counted once.
  - figures: recorded (new months), updated (month uploaded again), now fully repaid, and not
    recorded (with overpayments flagged).
  - a row-by-row table with filters (All, Not recorded, Recorded, Updated, Repaid or overpaid):
    - file row number, member name and staff number, the loan (links to its schedule) and month;
    - amount, and whether it came from the file or the monthly instalment;
    - balance after, and the result with any notes;
    - rows not recorded are highlighted with the reason.
- **Upload button:** asks first, checks a file and loan type are chosen, shows progress and reports a
  network error.
- **Saved copy of the file:** stored under a safe name (date, time, cleaned file name). Before, it used
  the name sent by the browser.

| File | Change |
|---|---|
| `Treasurer/batch_loan_deduction.php` | Redesigned; login check |
| `Treasurer/uploadLoansDeductionExcel.php` | Results redesigned; same deduction rules; safe file copy name |
| `js/custom.js` | Upload button: checks, progress, errors |
| `css/dashboard.css` | File box, guide and results styles |

### Member dashboard: number of loans the member guarantees

A new **Loans I Guarantee** figure on the member dashboard (`R/index.php`) shows how many loans the
member actively guarantees (accepted and not yet fully repaid). Beneath it: requests waiting for their
decision, how many are overdue (the figure turns red), and the maximum allowed at a time. It opens Loans I
Guarantee (`R/guarantee.php`), which counts the same way.

| File | Change |
|---|---|
| `R/index.php` | Loans I Guarantee figure |

### Treasurer's Loan Deductions page redesigned (Treasurer/loan_deduction.php)

- **Month by month:** choose a month (arrows or a month picker; the current month by default).
- **Figures for that month:** loans due and the amount expected, deductions recorded (amount and
  loans, and how many short), loans not recorded yet with the amount outstanding, and overdue loans.
- **Tabs:** Not recorded (the default), Recorded, Short, Overdue, Fully repaid and All, plus search.
- **Each loan shows:** the monthly instalment, the deduction for the chosen month against what is due,
  repaid, balance and status, with **Record** opening the loan's schedule to record a single deduction.
- **Download … to record (CSV)** gives the loans not yet recorded for the month in the batch upload
  layout (staff number, month, amount due, plus name and loan for reference). The Treasurer corrects the
  amounts to what payroll deducted and uploads it on Batch Deductions.
- **Removed:** the extra copies of jQuery and DataTables, and their copy/Excel/PDF buttons.

| File | Change |
|---|---|
| `Treasurer/loan_deduction.php` | Redesigned |

### My Loan Deductions page redesigned (R/view_laon_deduction.php)

- **Top of the page:** amount deducted so far (and this year), the monthly deduction and what is still to
  repay, with links to My Loans and Payslips. A notice appears for an overdue loan.
- **One card per loan being repaid or repaid:**
  - total, monthly deduction, first deduction month and balance;
  - a progress bar;
  - a month-by-month table (newest first): amount due, amount deducted, the date it was recorded, and
    a status: Deducted, Short, Not recorded yet (months not uploaded yet), or Upcoming.
  - A repaid loan lists only the months that had deductions.
- **Removed:** the welcome box and buttons (they are in the menu) and a second copy of jQuery.

| File | Change |
|---|---|
| `R/view_laon_deduction.php` | Redesigned |

### Officers' Loans page redesigned (Treasurer, Chairman, Secretary)

The three `loans.php` pages share one layout, `config/loans/officer_loans.php`:
- **Top of the page:** each officer's own work first (Treasurer: applications to review, loans to pay out;
  Chairman: loans to authorize), what is still owed to the cooperative, and overdue loans.
- **Tabs by stage, with counts:** All, To review / With Treasurer, To authorize / With Chairman, To pay out,
  Repaying, Overdue, Awaiting guarantor, Fully repaid and Declined. The list now includes loans still
  waiting for the guarantor, and declined ones.
- **Search** by name, staff number or loan number.
- **Columns:** amount (approved, with the amount applied if different), total to repay, balance, a stage
  tag, and an overdue tag showing how far behind.
- **Download CSV** of the current tab. It replaces the old copy/Excel/PDF buttons, and the extra copies
  of jQuery and DataTables the page loaded are gone.
- **Links:** to overdue loans, and for the Treasurer to loan deductions and the deduction upload.

| File | Change |
|---|---|
| `config/loans/officer_loans.php` | **New** – shared loans page |
| `config/classes/MemberG3.php` | New `officerLoans()` and `officerLoanTabs()` |
| `Treasurer/loans.php`, `Chairman/loans.php`, `Secretary/loans.php` | Use the shared page |
| `css/dashboard.css` | Loans page styles |

### My Loans page redesigned (R/view_loan_approval.php)

- **Top of the page:** what the member still owes, their monthly deductions and applications in progress,
  with links to apply and to their deductions. Overdue and overpayment notices appear here too.
- **One card per loan:**
  - progress steps (Applied → Guarantor → Treasurer → Chairman → Paid out → Repaid);
  - amount applied and approved, interest, total to repay, monthly deduction and start month,
    guarantor;
  - repayment progress bar with what is repaid and the balance, and an Overdue tag when behind;
  - for a declined loan, the reason.
- **Removed:** the page's welcome box, the Payslip and My Profile buttons (both are in the menu) and
  a second copy of jQuery.

| File | Change |
|---|---|
| `config/classes/MemberG3.php` | New `myLoans()` |
| `R/view_loan_approval.php` | Redesigned |
| `css/dashboard.css` | Repayment progress bar |

### Guarantor pages redesigned; undertaking warnings fixed

- **Loans I Guarantee** (`R/guarantee.php`) shows:
  - requests waiting for the member's decision (with "Review and respond");
  - loans they are guaranteeing now, with balance and an overdue warning;
  - past requests.

  Counts at the top show how many are waiting, how many are guaranteed now (and the maximum allowed),
  and how many are overdue. The page also loaded a second copy of jQuery; that is removed.
- **Guarantor Undertaking** (`R/undertaking.php`) shows:
  - the applicant and loan (amount, amount to repay once fixed, period, materials, reason);
  - the guarantor's details;
  - what guaranteeing means;
  - the undertaking with "Accept and sign", or "Decline to guarantee" with an optional reason.

  Once decided it shows the decision. After deciding, the member returns to Loans I Guarantee.
- **Access:** a member can now open only undertakings where they are the guarantor. Before, any
  member could view any loan's details by changing `loan_id` in the address.
- **Warnings fixed:** "Undefined array key bank/acct_no" came from bank fields that do not exist in the
  staff record; they are removed. The Treasurer, Chairman and Secretary `undertaking.php` pages use the
  same layout, read-only.

| File | Change |
|---|---|
| `config/classes/MemberG3.php` | New `guaranteeRequests()` and `guarantorUndertakingPage()`; `getUndertaking()` uses it (read-only) |
| `R/guarantee.php`, `R/undertaking.php` | Redesigned |
| `js/custom.js` | Undertaking decision message; returns to Loans I Guarantee |
| `css/dashboard.css` | Guarantee and undertaking styles |

### Page requests work from every folder, locally and live

The Business Loan page showed "Network or server error. Please try again." under the amount on local
XAMPP. The scripts called the server with paths counted from the page's folder (`../../ajax/…`,
`../ajax/…`, `ajax/…`). With the portal in a sub-folder (`http://localhost/fudscoops.fud.edu.ng/`),
`../../ajax/` from `R/` pointed outside the portal (404). On the live site the browser cannot go above
the domain root, so the same path happened to work there. The guarantor **Verify** and **Submit
application** buttons, and 13 other calls, had the same problem locally.

- `js/custom.js` works out the portal's root (`fudBase`) from the address it was loaded from, and all 78
  calls use `fudBase + 'ajax/…'`. They work from any folder, locally and on live.
- Five pages one folder deep had their own `../../ajax/` paths; they now use `../ajax/`.

No database change.

| File | Change |
|---|---|
| `js/custom.js` | `fudBase`; every `ajax/` call uses it |
| `Secretary/addCommodityItems.php`, `Secretary/view_item_types.php`, `Secretary/view_items.php`, `Secretary/viewRequestDetails2.php`, `Chairman/viewRequestDetails1.php` | `../ajax/` paths |


### Browsers load the new CSS and JavaScript after an update

After the change above, the guarantor **Verify** button still showed "An unexpected error occurred": the
browser was using its cached copy of the old `js/custom.js`. The server reply was correct ("Guarantor
not found" for `SP/R/2965`; "Successfully verified!" for `SP/RA/2965`). The portal now adds the file's
last-change time to `js/custom.js`, `css/style.css`, `css/sidebar.css` and `css/dashboard.css`
(e.g. `custom.js?v=1792048982`), so browsers fetch the new file whenever it changes, including on live
after a deploy.

| File | Change |
|---|---|
| `config/classes/View.php` | `v()` version numbers on the portal's CSS and JS |

---

## 2026-10-14

### Withdrawals keep a minimum balance; complete withdrawals reduce savings; kobo; guarantor notices

Run `db/2026-10-14_kobo_and_withdrawals.sql` (#8) on the live database **before** uploading this code.

**Minimum savings balance**
- Savings withdrawals must leave at least the new setting **Minimum savings balance after a
  withdrawal** (Savings Settings; starts at the minimum monthly savings, ₦3,000 locally). Before, the
  rule was only stated on the page and never checked.
- It is checked when the member asks, when the Treasurer sets the amount and again when the Chairman
  approves. Each refusal says how much can be withdrawn.
- **Complete withdrawal (member leaving):** the minimum does not apply. The member is paid everything
  left after anything paid from savings for loans and commodity, so their savings go to zero. A member
  with less than the minimum can still leave. The member's page and the Treasurer's page show the payout.

**Complete withdrawals reduce savings**
- An approved complete withdrawal (the amount paid out, plus loans and commodity settled from
  savings) is now subtracted wherever a savings balance is worked out:
  - member dashboard, savings and withdrawal pages;
  - loan limit;
  - share conversion;
  - officer totals;
  - Summary report and member statement.
- The one complete withdrawal already approved (₦50,000) now reduces that member's balance.
- The Chairman's approval now records `date_approved`; it was never set, so the reports for a
  period left complete withdrawals out.

**Kobo**
- Savings, monthly savings, savings updates, withdrawals, loans, repayments, shares and commodity prices
  are stored with two decimal places. Before, most were whole numbers or text.
- The forms accept kobo, and the server code no longer rounds amounts to whole naira.
- 304 old repayment schedule amounts stored with long decimals (e.g. 18333.333335) are rounded to kobo
  (backup table `fudscoops_loan_repayments_backup_20261014`).
- Totals are unchanged except loan amounts, which differ by 2 kobo across all loans.

**Guarantors of overdue loans** are told on their member dashboard: "The loan you guaranteed for … is
overdue: ₦… behind."

**Officers** keep separate office and member logins, so their own loans and notices appear when they
log in as a member; no change.

**Also fixed**
- The Chairman's Approve button for savings withdrawals approved even when Cancel was clicked.
- Withdrawal approvals now show why they were refused.
- The withdrawal details pages show the applicant's surname.

| File | Change |
|---|---|
| `db/2026-10-14_kobo_and_withdrawals.sql` | **New** – database changes (#8 above) |
| `config/classes/LoanControls.php` | Minimum balance, savings balance, most that can be withdrawn; payout for leaving members; overdue loans by guarantor |
| `config/classes/MemberG1.php` | Withdrawal request/approvals check the minimum; complete withdrawals in balances; date approved; kobo; surname fix |
| `config/classes/MemberG2.php`, `config/classes/MemberG3.php`, `config/classes/Dashboard.php`, `config/classes/Report.php` | Complete withdrawals subtracted from savings; kobo |
| `config/classes/SavingsG1.php` | `parseAmount` keeps kobo; complete withdrawal request checks the payout |
| `R/index.php` | Guarantor notice for overdue loans |
| `R/savings/savings_withdrawal.php`, `R/complete_withdrawal.php`, `R/savings/update_savings.php` | Minimum balance shown and applied; kobo |
| `Treasurer/savings_settings.php` | Minimum balance setting; kobo |
| `Treasurer/view_proposed_member_details.php`, `Treasurer/treasurer_view_approved_savings.php` | Kobo |
| `Treasurer/uploadSharesExcel.php`, `Secretary/uploadSharesExcel.php` | Kobo |
| `Chairman/withdrawal_endorsement.php`, `js/custom.js` | Approve confirms properly; refusal reasons shown |

### Loan controls: overdue loans, overpayments, decline reasons, leaving members

Run `db/2026-10-14_loan_controls.sql` (#7) on the live database **before** uploading this code.
All rules are in `config/classes/LoanControls.php`. Notices appear on dashboards only.

**Overdue loans**
- A month counts once the Treasurer has recorded deductions for it (any loan of that type has a
  deduction that month). Months not uploaded, and future months, never count, so old loans are not
  flagged for March–September 2026 just because those months were never uploaded.
- A running loan is overdue when it has repaid less than the instalments due for those months.
  Months with no deduction, or less than the instalment, are listed as missed or short.
- Shown to the member (dashboard notice) and to the Treasurer, Chairman and Secretary: dashboard count
  and a new **Overdue Loans** page in each menu. The loan details page shows the arrears.

**Over-repayments**
- A deduction that takes a loan past what it owes is recorded and flagged, not refused:
  - Single entry and the batch upload say "OVERPAID by ₦…".
  - A deduction uploaded for a loan that is already fully repaid is recorded on that loan as an
    overpayment (before, it was skipped).
- The member gets a dashboard notice and chooses on the new **Loan Overpayments** page: add it to
  savings, pay one of their other loans, or a refund to a bank account (10-digit account number).
- The Treasurer settles it on **Loan Overpayments**. Settling:
  - adds a savings record for this month, or
  - adds the amount to the chosen loan's deduction for this month, or
  - records the refund's transfer reference.
- If a month is re-uploaded so the excess changes, an unsettled overpayment is updated or removed.

**Decline reasons**
- The Treasurer and Chairman must give a reason when declining a loan. The guarantor may give one
  (optional).
- The member sees the reason under the loan's status in View Loan Approval.

**Leaving members (complete withdrawal)**
- A request is refused while the member:
  - is guarantor for someone else's loan that is not fully repaid;
  - has a loan or commodity request still being decided or awaiting payment or supply.
- Loans and commodity being repaid must be repaid first. Alternatively, the member ticks "Use my
  savings to pay what I owe, and pay me the rest".
- The Treasurer's details page shows what is owed, the member's choice, and the amount to pay out.
- The Chairman's approval checks everything again. If the member chose to settle, the loans and
  commodity are paid from savings at approval. The Chairman sees the reason when approval is refused.
- **Fixes:**
  - The Approve button's "Are you sure?" no longer approves when Cancel is clicked.
  - The request now uses the logged-in member's staff number, not one sent by the browser.

**Other**
- **Payslips:** the loan details page shows the applicant's payslips for the last three months, as on
  the proposed member page. The complete-withdrawal, withdrawal and membership-decision pages now use
  the same links (their old links pointed to no month). Officers' `slip.php` now requires an officer login.
- **Soft Loans closed** (new setting "Soft loans open", Savings Settings). The Soft Loan page says so
  and applications are refused.
- **Early payoff:** the full interest is charged. The Treasurer records the whole balance; the
  repayment schedule shows the amount.
- **Older page fixes:**
  - The guarantor undertaking now shows the date the guarantor decided (it always showed today).
  - The complete-withdrawal details show the applicant's surname.

| File | Change |
|---|---|
| `db/2026-10-14_loan_controls.sql` | **New** – database changes (#7 above) |
| `config/classes/LoanControls.php` | **New** – overdue loans, overpayments, leaving checks |
| `config/classes/MemberG3.php` | Decline reasons, guarantor reason, overpayment flag on every deduction, soft-loan switch, payslips and notices on loan details, early-payoff note |
| `config/classes/MemberG1.php` | Chairman approval checks and settles; Treasurer details show what is owed; payslip links |
| `config/classes/SavingsG1.php` | Complete withdrawal request checks obligations and records the settle choice |
| `config/classes/CommodityLoan.php` | `creditRequest()` for settling commodity from savings |
| `config/classes/Dashboard.php` | Overdue loans (Treasurer, Chairman, Secretary); overpayments to settle (Treasurer) |
| `config/classes/View.php` | Menus: Overdue Loans (3 officers), Loan Overpayments (Treasurer, member) |
| `config/loans/loan_application_form.php` | Soft loans closed notice |
| `ajax/overpayment.php` | **New** – member's choice, Treasurer's settlement |
| `ajax/complete_withdrawal.php` | Logged-in member only; settle choice |
| `ajax/save_repayment.php`, `ajax/undertaking.php` | Overpaid message; guarantor's reason |
| `R/overpayment.php`, `Treasurer/overpayments.php` | **New** |
| `Treasurer/overdue_loans.php`, `Chairman/overdue_loans.php`, `Secretary/overdue_loans.php` | **New** |
| `R/index.php`, `R/complete_withdrawal.php` | Overdue and overpayment notices; obligations and settle option |
| `Treasurer/uploadLoansDeductionExcel.php` | Overpayments flagged; deductions after full repayment recorded |
| `Treasurer/savings_settings.php` | Soft loans open setting |
| `Treasurer/slip.php`, `Chairman/slip.php`, `Secretary/slip.php` | Officer login required |
| `Chairman/complete_withdrawals.php`, `js/custom.js` | Approve confirms properly and shows refusals |

### Loan limit raised to 3 times savings

A member's open loans plus a new loan may now be up to **3 times** their savings balance (was 2).
The limit is still the `loan_max_savings_multiple` setting in Savings Settings; the code's default is now 3 as well.
The loan application page, the live amount check and the Treasurer's review all use it.

| File | Change |
|---|---|
| `db/2026-10-14_loan_limit_3x.sql` | **New** – setting changed to 3 (#6 above) |
| `config/classes/MemberG3.php` | Default limit 3 |
| `Treasurer/savings_settings.php` | Default shown 3 |

---

## 2026-10-13

### Member savings pages redesigned

Update Savings, Withdraw Savings, Target Savings Request and Complete Withdrawal now match the loan
and commodity forms. Each page has:
- the member's savings balance and current monthly savings at the top;
- a notice when a request is already awaiting approval (the form is hidden for withdrawals);
- numbered sections and a confirm panel;
- "what happens next".

No database change.

Fixed at the same time:
- **Target Savings:** the server replies in JSON but the page only accepted `1`, so every saved
  application showed "Error! Contact system admin" (one member's application was saved twice).
  The page now reads the reply. Its undertaking text described a bank withdrawal; it now describes
  the savings plan. The months fields enforce the minimum for the member's grade.
- **Update Savings and Complete Withdrawal:** the pages had no message area, so success and error
  messages never appeared. Update Savings said "Withdrawal request submitted" on success.
- **Withdraw Savings:** any refusal was reported as "You already have a pending withdrawal".
  The page now shows the server's reason. The amount is limited to the balance, and the minimum
  monthly savings shown comes from Savings Settings (it was fixed at ₦2,000).
- **Account numbers:** both withdrawal forms accept exactly 10 digits and keep leading zeros.
- **Access check:** the pages now stop when the user is not a logged-in member (before, they only
  redirected and carried on running).

| File | Change |
|---|---|
| `R/savings/update_savings.php`, `R/savings/savings_withdrawal.php`, `R/savings/target_savings_request.php`, `R/complete_withdrawal.php` | Redesigned |
| `config/classes/Dashboard.php` | New `memberSavingsInfo()` (monthly savings and pending requests) |
| `js/custom.js` | New `fudMessage()`; messages and replies for the four forms |
| `css/dashboard.css` | Summary total line uses a class (`.fud-total`) |
| `config/loans/loan_application_form.php`, `R/commodity_request.php` | Use `.fud-total` |

### Member menu links work when the portal is in a sub-folder

The member menu links (`/R/...`) start from the web root. On local XAMPP the portal is at
`http://localhost/fudscoops.fud.edu.ng/`, so every member menu item (e.g. Soft Loan Request, View Loan
Approval, View Loan Deduction) gave "Not Found". `View::appUrl()` now adds the portal's folder to these
links. The live site, at the domain root, is unaffected.

| File | Change |
|---|---|
| `config/classes/View.php` | New `appUrl()`; used for sidebar links and group links |

### Commodity workflow rebuilt (same steps as loans)

Run `db/2026-10-13_commodity_workflow.sql` (#5) on the live database **before** uploading this code.

New flow:

1. **Member requests** (Commodity Request) while a supply is open: chooses items and quantities,
   and how many months to repay (1 to `commodity_max_tenor`, default 6, set in Savings Settings).
   The server checks:
   - the supply is open;
   - one request per member per supply (a declined request can be made again);
   - the per-member item limits (senior SP / junior);
   - the stock left.

   It works out the value itself and reserves the stock.
2. **Treasurer reviews:** fixes the quantities (up to what was requested) and months, then sends the
   request to the Chairman, or declines it with a reason. The flat loan interest rate (`loan_interest_rate`,
   10%) is added once. Reduced or declined quantities go back to the supply's stock. The Treasurer
   can revise the request until the Chairman decides.
3. **Chairman** authorizes or declines (with a reason). Before supply, the Chairman can return an
   authorized request to the Treasurer.
4. **Treasurer records the supply** (date and note). Deductions start the following month.
5. **Deductions:**
   - The Treasurer records a month on the request's page, or uploads a CSV on the new
     Commodity Deduction page.
   - Re-recording a month replaces it, and a deduction may not exceed the balance.
   - The request becomes **Fully repaid** automatically.

Member pages: My Commodity Requests (stage, total, monthly, balance), request details, and
View Commodity Deduction (balance, monthly deduction and deductions per request; the page was missing).
The Secretary sees requests read-only. The officer dashboards count commodity requests to review,
authorize and supply.

Also fixed:
- **No role checks:** any logged-in user could approve, reject or disburse through `ajax/commodity_ajax.php`. It now:
  - refuses the old request, decision and disbursement actions ("page has changed");
  - allows the supply set-up actions to officers only.
- **Supply items had no stock:** they were saved with `remaining_quantity` 0, so members could not request them (the December 2025 supply). New items start with their full allocation, and the migration repairs items nobody had requested.

| File | Change |
|---|---|
| `db/2026-10-13_commodity_workflow.sql` | **New** – database changes (#5 above) |
| `config/classes/CommodityLoan.php` | **New** – request, review, decision, supply, deductions, lists, shared details page |
| `ajax/commodity_workflow.php` | **New** – role-checked actions for the workflow |
| `ajax/commodity_ajax.php` | Role checks; old workflow actions retired |
| `config/classes/Commodity.php` | Supply items saved with their stock; application items query fixed |
| `R/commodity_request.php` | Rebuilt request form |
| `R/view_commodity_request.php`, `R/viewRequestDetails.php` | Member list and details |
| `R/commodity_deduction.php` | **New** – member's commodity deductions |
| `Treasurer/view_commodity_request.php`, `Treasurer/viewRequestDetails.php` | Review, supply, single deductions |
| `Treasurer/commodity_deduction.php`, `Treasurer/uploadCommodityDeduction.php`, `Treasurer/Commodity_repayment_template.csv` | **New** – deductions list and CSV upload |
| `Chairman/approved_commodity_Request.php`, `Chairman/viewRequestDetails.php` | Authorize, decline, return |
| `Secretary/view_commodity_request.php`, `Secretary/viewRequestDetails.php` | View only |
| `config/classes/View.php` | Treasurer menu: Commodity Deduction; Chairman menu: Commodity Requests |
| `config/classes/Dashboard.php` | Commodity tasks for Treasurer, Chairman and Secretary |
| `Treasurer/savings_settings.php` | Commodity repayment period setting |
| `css/dashboard.css` | Stage tabs and commodity page styles |

### Business Loan and Soft Loan application pages redesigned

No database change.

**Layout**
- Both pages now use one shared form, `config/loans/loan_application_form.php`.
- At the top, the member sees their savings balance, how much they can borrow, the interest rate and the longest repayment period.
- The form has four numbered sections: your details, the loan, supplier, guarantor.
- Repayment periods are shown as buttons.
- A summary panel works out the interest, total and monthly deduction as the member types.

**When the form is hidden**
- Instead of the form, a member who already has a loan of that type sees its status and balance.
- A Soft Loan applicant without target savings gets a link to start target savings.

**Corrected text**
- The conditions now match the rules: 2× savings balance (was "three times your share capital") and flat interest from Savings Settings.
- The "[An Interest Free Thrift and Loan Society]" tagline was removed from these pages.

**Bug fixes**
- Members could not submit the form. The submit check read a misspelled variable (`isloanDeserving`) that was never set, so it always said "You don't have enough funds".
- The live amount check (`ajax/getTotal_share.php`) used 3× share capital. It now uses the same limit as the server: savings multiple, less loans still owed, and one open loan per type.
- The account number accepts exactly 10 digits. It was a number field, so leading zeros were dropped and the 10-digit limit was ignored.

**Commodity**
- `Commodity::getApplicationItems` had a leftover test value (`application_id = 5`). The query failed, and every commodity request detail page (member, Secretary, Treasurer, Chairman) showed no items, so requests could not be approved or disbursed. It now uses the application being viewed.

| File | Change |
|---|---|
| `config/loans/loan_application_form.php` | **New** – shared loan application page |
| `R/business_loan.php`, `R/soft_loan.php` | Now load the shared page |
| `config/classes/MemberG3.php` | New `loanApplicationContext()` (figures for the page) |
| `ajax/getTotal_share.php` | Live amount check uses the real loan limit |
| `js/custom.js` | Submit fix, summary panel, loan type sent with the amount check |
| `css/dashboard.css` | Loan application styles |
| `config/classes/Commodity.php` | Application items query fixed |

### Loan workflow reform (application to repayment)

Run `db/2026-10-13_loan_workflow.sql` (#4) on the live database **before** uploading this code.

New flow:

1. **Member applies.** Each application is a new record. Supplier phone number and sort code are
   now saved (they were lost before).
2. **Guarantor accepts or declines.**
3. **Treasurer reviews** (loan details page). The Treasurer fixes the amount and the number of
   months, then recommends or declines. The amount may not exceed the amount applied for or the
   member's loan limit (times savings balance, less what they still owe). Flat interest
   (`loan_interest_rate`, default 10%, set in Savings Settings) is added once; a live preview shows
   interest, total and the monthly deduction. The Treasurer can revise until the Chairman decides.
4. **Chairman authorizes or declines** the Treasurer's figures. **Unlock** (before payment)
   returns the loan to the Treasurer.
5. **Treasurer records the payment** (date and bank reference). Deductions start the following
   month, and the schedule (total ÷ months) is created at this point.
6. **Deductions** are recorded one month at a time, individually or by batch upload. A shortfall
   adds extra months to the schedule. When the deductions reach the total, the loan becomes
   **Fully repaid** automatically. The member and the guarantor are told on their dashboards
   ("You are released as guarantor"), and the member may apply for that loan type again.

Other changes:

- Treasurer and Chairman dashboards show "Loan applications to review", "Loans to authorize" and
  "Loans to pay out".
- Loan lists, the member's loan page, the deduction list and the reports show interest, total to
  repay, repaid and balance. Reports use the payment date and the total including interest.
- **Batch deduction upload:**
  - The CSV has an optional third column with the amount actually deducted (blank = the full
    instalment, commas allowed). Re-uploading a month updates it instead of adding another.
  - Rows for members without a loan of that type being repaid are skipped (they were counted as
    inserted before).
  - Impossible dates (e.g. 13/40/2026) and unknown staff numbers are now rejected.
- Both loan upload scripts are restricted to the Treasurer.

| File | Change |
|---|---|
| `db/2026-10-13_loan_workflow.sql` | **New** – database changes (#4 above) |
| `config/classes/MemberG3.php` | Treasurer review, Chairman decision, payment, interest, loan limit, auto repaid, shared loan details page, lists, supplier data |
| `ajax/process_loan_decision.php` | Actions: recommend / decline (Treasurer), authorize / decline / unlock (Chairman), disburse (Treasurer) |
| `ajax/save_repayment.php` | Says when the loan is now fully repaid |
| `ajax/getLoanRepaymentAmount.php` | Preview with flat interest; officers only |
| `Treasurer/uploadLoansDeductionExcel.php` | Amount column, update same month, auto repaid, strict date/staff checks, Treasurer only |
| `Treasurer/Loan_repayment_template.csv` | Third column: amount deducted |
| `Treasurer/batch_loan_deduction.php` | Upload instructions |
| `Treasurer/uploadLoansExcel.php` | Uploaded loans recorded as paid out (no interest); Treasurer only |
| `Treasurer/savings_settings.php` | Loan interest rate setting |
| `config/classes/Dashboard.php` | New loan tasks; totals include interest; recently repaid notices |
| `R/index.php` | Fully repaid / released as guarantor notices; balance column |
| `config/classes/Report.php` | Paid-out loans, interest and total columns |
| `config/classes/MemberG1.php` | Cumulative loan uses the new statuses |

### Database connection works on both the live server and local XAMPP

- The deploy workflow uploaded the local `config/classes/DB.php` (login `root`, no password) to
  the live server, so the live site could not connect and login stopped at "Please wait...".
- `config/classes/DB.php` now tries database logins in order and uses the first that connects:
  `config/db_config.php` if present, then the live login (`fuded535_hrms`), then local XAMPP
  (`root`). The same file can be deployed. Connection errors are written to the error log instead
  of being shown on the page.
- If the live login is different, create `config/db_config.php` on the server only:
  `<?php return ['host' => 'localhost', 'dbname' => '...', 'username' => '...', 'password' => '...'];`
  It is in `.gitignore` and excluded from the deploy workflow.

Files: `config/classes/DB.php`, `.gitignore`, `.github/workflows/deploy.yml`. Database: none.
(Afterwards the user set `DB.php` back to the live login only.)

### Treasurer login redirect

- After login, treasurers were sent to `/Treasurer /` (the old live folder with a trailing space),
  which no longer exists since the deploy uploads the repository's `Treasurer/` folder: 404.
  The redirect and two other links in `js/custom.js` now point to `Treasurer/`.
- The old `Treasurer ` folder on the server is no longer used and can be deleted once the new
  `Treasurer/` folder is confirmed working.

Files: `js/custom.js`. Database: none.

## 2026-10-08

### Dashboards redesigned

All five dashboards share a new look (`css/dashboard.css`, matching the sidebar): a welcome
banner (greeting in Nigerian time, name, role, date, shortcut buttons), stat cards with icons,
and section cards.

- **Member (`R/index.php`):** monthly savings, savings balance, shares (amount and units), loans
  outstanding, total withdrawn; notices for guarantor requests and share transfers to accept;
  "My Pending Requests" (share purchase, conversion, savings change, withdrawals awaiting
  approval); "My Loans" with each loan's stage and balance; recent withdrawals with bank names.
  Fixed: "Total Unit of Shares" showed the naira amount as units (e.g. "250,000 Units").
  The login check now runs before any data is read.
- **Applicant (`UR/index.php`):** progress tracker (Applied → Treasurer fixes savings → Chairman
  authorizes → Member) with an explanation of the current step, the proposed or fixed monthly
  savings, date applied and the latest decision.
- **Chairman, Treasurer, Secretary-General:** one shared page
  (`config/dashboards/officer_dashboard.php`) with **Needs Your Attention**: counts of items
  waiting at that officer's step, each linking to the list (same conditions as the lists);
  personal notices (guarantor requests, share transfers); **Cooperative Overview** (members,
  savings balance, share capital, loans outstanding, latest deduction month). The Secretary's
  previous seven cards always showed 0; the Chairman's and Treasurer's dashboards had only a heading.
- Accounts without a staff record (e.g. `chairman`) are greeted by role instead of an error message.

Files:
- New: `config/classes/Dashboard.php` (dashboard figures, read-only), `config/dashboards/officer_dashboard.php`, `css/dashboard.css`
- Changed: `R/index.php`, `UR/index.php`, `Chairman/index.php`, `Treasurer/index.php`, `Secretary/index.php`,
  `config/classes/View.php` (loads `css/dashboard.css`)
- Database: none

### Generate Report fixes (found during the loan and shares review)

- Loans approved through the system are saved with `loan_status = 'Approve'`; bulk-uploaded
  loans with `'Approved'`. Reports counted only `'Approved'`, so they would have missed loans
  approved by the Treasurer in the system. Both are now counted.
- `fudscoops_shares.unit_price` holds the purchase amount, not the unit price, so share
  purchase "Units" always showed 1. Units now use the current unit price (₦3,000).
- Monthly loan repayments saved by the Treasurer's approval contain thousands separators
  (e.g. "18,333.33"); they are now read correctly.
- Withdrawals report: new **Type** column (Cash / Converted to shares). Share conversions are
  recorded as savings withdrawals with `withdrawal_type_id = 2`.

Files: `config/classes/Report.php`. Database: none.

### Shares module

- **Several purchases per member.** `fudscoops_shares` is now a ledger: every purchase is a new
  record; a member's holding is the total of approved records. A purchase no longer edits the
  member's existing record, and new amounts are not approved until the Treasurer confirms them.
- **Approval order enforced:** Secretary endorses → Chairman approves → Treasurer confirms. Each
  step is refused until the previous one is done.
- **Treasurer Decline now rejects** the purchase (`share_status = -1`). Secretary and Chairman
  declines are also saved as -1 and close the purchase.
- **Transfers to a member with no shares no longer lose the shares.** An approved transfer adds a
  `transfer_out` entry (negative) for the sender and a `transfer_in` entry for the receiver, in one
  database transaction; if anything fails, nothing changes. The amount comes from the transfer
  being approved (previously the sender's oldest transfer).
- **Declined transfers and conversions** are saved as -1 (previously 0, the same as pending).
- **Conversions** (savings → shares) are new records each time; on approval a `conversion` share
  entry and a savings withdrawal (type 2) are recorded in one transaction.
- **Share Increase** now saves a new share purchase (it is the purchase page with its own heading).
  It previously submitted a savings-to-shares conversion.
- **Eligibility (standard practice):** only approved members; amounts in whole units of the unit
  price (₦3,000), at least one unit; amount paid must equal the share amount; teller number and
  bank required; a teller number cannot be used twice; one purchase awaiting approval at a time.
  Transfers: receiver must be an approved member and not the sender, no more than the sender's
  holding (less transfers awaiting approval), at least ₦20,000 must remain (setting), one pending
  transfer at a time; only the receiver can accept. Conversions: at most the savings balance less
  the minimum monthly savings.
- **Pages:** purchase and conversion pages show the member's shares, open blank (no longer
  pre-filled with the old record) and are no longer disabled for good after the first approval.
  The member's **View Share** shows each entry and the balance; **View Share Holders** shows one
  line per member with the total.
- **Bulk share upload** sets a member's total by adding an `adjustment` entry for the difference
  (it previously overwrote the member's record).
- The purchase slip prints the logged-in member's latest purchase.
- The unused `ajax/share_transfer.php` (moved shares with no approval) now refuses.
- The receiver's **Accept Shares** button now updates after accepting (a PHP string was swallowing
  the JavaScript `${transferId}`).

### Loans module

- **Each application creates a new loan record**; an existing loan is never overwritten. A member
  can hold several loans over time.
- **Soft Loans are saved as Soft Loans** (the form now says which loan type it is). Each form only
  offers repayment periods within the type's maximum (Business 3/6/7, Soft 3/6/9 months).
- **Eligibility checks:** approved member; one open (pending or not fully repaid) loan per type;
  open loans plus the new loan ≤ 2 × savings balance (setting); repayment period within the
  type's maximum; Soft Loans only for members on target savings (as the page already required).
- **Guarantor checks enforced on the server:** guarantor must exist, be an approved member, not be
  the applicant, not be junior staff (JP), and not already guarantee 2 open loans (setting). The
  **Verify** button shows the same result. The guarantor can now also **Decline**; only the
  guarantor named on the loan can decide.
- **Workflow:** guarantor → Chairman → Treasurer, each enforced. The **Secretary can only view
  loans**: decision, tenor change and unlock requests from the Secretary are refused.
- **Declines are distinct:** Chairman decline is saved as -1 (previously 0, the same as
  undecided); guarantor decline as -1. Lists show the stage in words (Awaiting guarantor /
  Awaiting Chairman / Awaiting Treasurer / Approved / Declined by …) and the loan type.
- **Treasurer buttons no longer damage repayment records:** approving creates or updates only the
  repayment schedule row (status 0); declining removes only the schedule row; deductions received
  (status 1) are never changed or deleted. A decided loan can only be changed after the Chairman
  unlocks it.
- The monthly repayment is saved as a plain number (no thousands separator). The 6% formula is
  unchanged (see Known issues).
- **Repayments:** recording a deduction is refused for loans that are not approved, and no longer
  turns the schedule row into a payment.
- **Bulk uploads:** the loan upload updates the member's open loan of the same type (or adds a new
  loan) and updates its schedule row instead of adding another; the deduction upload posts to the
  member's open approved loan of that type.
- The member dashboard's loan total counts approved loans only.

### Safari and messages

- All officer decision forms (loans, shares, transfers, conversions, withdrawals, membership)
  record the clicked button instead of relying on keyboard focus, which Safari does not set.
- Officer and member forms show the reason when a request is refused instead of
  "Contact system admin".

### Logins

- Every loan and share endpoint now checks the role: members for applications, purchases,
  transfers, conversions and guarantor decisions (using the logged-in staff number, not the one in
  the form); Chairman, Treasurer or Secretary for their own steps.

### Settings

- **Savings Settings** (Treasurer) has a new **Loan and Share Rules** section: loan limit
  (× savings balance), loans one member may guarantee, minimum shares kept after a transfer.

### Files changed on 2026-10-08

| File | Change |
|---|---|
| `db/2026-10-08_loans_shares.sql` | **New** – database changes (#3 above) |
| `config/classes/MemberG2.php` | Share ledger: purchases, approvals, transfers, conversions, views, eligibility helpers |
| `config/classes/MemberG3.php` | Loans: new record per application, eligibility, guarantor rules, decisions, repayments, stage labels |
| `config/classes/User.php` | Share helpers read the ledger total; new `getLatestSharePurchase()` |
| `config/classes/MemberG1.php` | Dashboard loan total: approved loans only |
| `config/classes/Report.php` | Report fixes (morning); share entry types in Summary, Statement and Shares reports |
| `js/custom.js` | Safari button fix, server messages, guarantor decline, loan/share form messages |
| `ajax/business_loan.php` | Loan type from form, logged-in member, eligibility messages |
| `ajax/verify_guarantor.php` | Same guarantor rules as the application |
| `ajax/undertaking.php` | Guarantor accept/decline, logged-in guarantor only |
| `ajax/guarantor_decision.php` | Logged-in guarantor only (not used by the pages) |
| `ajax/process_loan_decision.php` | Role checks; Secretary refused; repayment saved as a plain number |
| `ajax/updateLoanTenor.php` | Chairman/Treasurer only, pending loans only, within maximum tenor |
| `ajax/unlock_loan_decision.php` | Chairman only |
| `ajax/save_repayment.php` | Treasurer only |
| `ajax/getLoanRepaymentAmount.php` | Officers only |
| `ajax/share_purchase.php` | Logged-in member; new record per purchase |
| `ajax/share_pending_transfer.php` | Logged-in member |
| `ajax/share_pending_conversion.php` | Logged-in member |
| `ajax/accept_transfer_share.php` | Logged-in receiver only |
| `ajax/share_transfer.php` | Disabled (direct transfer without approval) |
| `ajax/check_worth.php`, `ajax/verifyBuyer.php` | Logged-in member only |
| `ajax/process_share_decision.php` | Role checks per step |
| `ajax/process_share_transfer_decision.php` | Role checks per step |
| `ajax/process_share_conversion_decision.php` | Role checks; removed broken Secretary branch |
| `R/share_purchase.php` | Blank form, shows shares and pending purchase, whole units |
| `R/share_increase.php` | Now the purchase page with "SHARE INCREASE" heading |
| `R/share_conversion.php` | Blank form, shows shares and savings, not disabled after the first approval |
| `R/print.php` | Purchase slip: logged-in member's latest purchase |
| `R/business_loan.php`, `R/soft_loan.php` | Loan type field; repayment periods within the type's maximum |
| `Treasurer/savings_settings.php` | Loan and Share Rules section |
| `Treasurer/uploadLoansExcel.php` | Updates the open loan of the same type or adds a new one; no duplicate schedule rows |
| `Treasurer/uploadLoansDeductionExcel.php` | Deductions go to the open approved loan of the type |
| `Treasurer/uploadSharesExcel.php`, `Secretary/uploadSharesExcel.php` | Adjustment entry instead of overwriting |

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

### Loans
- Bulk-uploaded loans (`Treasurer/uploadLoansExcel.php`) are recorded as already paid out with
  no interest; the 10% applies only to loans that go through the Treasurer's review.
- Many existing loans have two schedule rows from earlier re-uploads (304 rows for 155 loans);
  the latest is used.

### Commodity
- Special Commodity Request posts to `R/process_commodity_loan.php`, which does not exist; nothing is saved.
  (Waiting to hear who prices special items.)
- Commodity counts toward neither the loan limit nor the Generate Report pages yet.
- Table names in the code (`fudscoops_commodity_Items`, `fudscoops_CommodityBatches`) differ in case
  from the local copy; check the live database's names.
- `fudscoops_commodity_Items.quantity` (overall stock) is not reduced when items are supplied; the
  supply's own stock (`fudscoops_commodity_supply_items.remaining_quantity`) is what limits requests.

### Shares
- 564 of 685 holdings recorded before 2026-10-08 are not whole units of ₦3,000 (one is ₦85).
  New purchases, transfers and conversions must be whole units.

### Other

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
- The old staff reports (`Chairman/report.php`, `Secretary/report.php`) are from the HR system and
  are no longer linked from the menus.
- Menu links to pages not present in this copy: Chairman **Loan Deduction**
  (`Chairman/loan_deduction.php`) and Secretary **Schedule** (`Secretary/fee.php`).
- Passwords are stored as unsalted MD5.
- Several officer-level logins look like test accounts and should be reviewed or removed:
  Secretary level (2) `q`, `jj`, `jj2`, `jj3`, `ssss`, `DD`, `Mahboob`, `SP/R/2075`; Treasurer level
  (3) `ict` (local database, 2026-10-08).
