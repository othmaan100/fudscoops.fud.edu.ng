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

## 2026-10-13

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
- `fudscoops_loan_repayments.amount_paid` holds whole naira only (kobo are dropped).

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
- Complete withdrawals are not deducted from members' savings balances anywhere in the system.
- The old staff reports (`Chairman/report.php`, `Secretary/report.php`) are from the HR system and
  are no longer linked from the menus.
- Menu links to pages not present in this copy: Chairman **Loan Deduction**
  (`Chairman/loan_deduction.php`) and Secretary **Schedule** (`Secretary/fee.php`).
- Passwords are stored as unsalted MD5.
- Several officer-level logins look like test accounts and should be reviewed or removed:
  Secretary level (2) `q`, `jj`, `jj2`, `jj3`, `ssss`, `DD`, `Mahboob`, `SP/R/2075`; Treasurer level
  (3) `ict` (local database, 2026-10-08).
