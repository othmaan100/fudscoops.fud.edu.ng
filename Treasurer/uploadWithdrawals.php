<?php
    // Upload savings withdrawals (part of Savings Withdrawals): the Treasurer's decision for many requests at once, for
    // members who applied online or on paper. Processed by uploadWithdrawalCSV.php.
    //   ?export=template  empty template
    //   ?export=applied   prefilled: every request awaiting the Treasurer (withdrawal_endorsement.php), with the amount
    //                     that can be paid (the request, or less if the minimum savings balance would not stay)
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    require_once('../config/classes/LoanControls.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../auth/') || !User::isBur('../auth/logout.php')) {
        exit();
    }
    $con = (new DB())->getConnection();
    $applied = $con->query("SELECT w.withrawals_id, w.member_id, w.proposed_withrawal_amount, w.proposed_date, w.bank_id, w.acount_number, w.account_name,
                                   b.name AS bank_name, e.sp_no, e.fname, e.lname, e.oname
                            FROM fudscoops_withrawals w
                            JOIN fudscoops_member m ON m.member_id = w.member_id
                            JOIN employee e ON e.employee_id = m.employee_id
                            LEFT JOIN banks b ON b.id = w.bank_id
                            WHERE w.status = 0 AND w.chairman_approval = 0 AND COALESCE(w.withrawals_is_deleted, 0) = 0
                            ORDER BY w.withrawals_id")->fetchAll(PDO::FETCH_ASSOC);

    if (isset($_GET['export']) && in_array($_GET['export'], ['template', 'applied'], true)) {
        // Columns 1-7 are read by uploadWithdrawalCSV.php; the rest are for checking and are ignored
        $heading = ['Withdrawal ID', 'Staff Number', 'Full Name', 'Bank', 'Account Number', 'Account Name', 'Approved Withdrawal Amount'];
        if ($_GET['export'] === 'applied') {
            $heading = array_merge($heading, ['Amount Requested', 'Savings Balance', 'Most That Can Be Withdrawn', 'Date Requested']);
        }
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . ($_GET['export'] === 'template' ? 'withdrawals_upload_template.csv' : 'withdrawals_applied_' . date('Y-m-d') . '.csv') . '"');
        $out = fopen('php://output', 'w');
        fputcsv($out, $heading);
        if ($_GET['export'] === 'applied') {
            foreach ($applied as $a) {
                $balance = LoanControls::savingsBalance($a['member_id']);
                $max = LoanControls::maxWithdrawal($a['member_id']);
                // Account numbers as text so spreadsheets keep leading zeros
                fputcsv($out, [$a['withrawals_id'], $a['sp_no'], preg_replace('/\s+/', ' ', trim($a['fname'] . ' ' . $a['lname'] . ' ' . $a['oname'])),
                               $a['bank_name'] ?: $a['bank_id'], $a['acount_number'], $a['account_name'],
                               number_format(min((float) $a['proposed_withrawal_amount'], $max), 2, '.', ''),
                               number_format((float) $a['proposed_withrawal_amount'], 2, '.', ''), number_format($balance, 2, '.', ''),
                               number_format($max, 2, '.', ''), $a['proposed_date'] ? date('d/m/Y', strtotime($a['proposed_date'])) : '']);
            }
        }
        fclose($out);
        exit();
    }
    echo $view->subHeader('../');
?>
    <?php echo $view->subPartialNav('../'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->treasurerSideNav(); ?>
      <div class="main-panel">
        <div class="content-wrapper fud-dash">

          <div class="fud-hero">
            <div class="fud-hero-date">Savings withdrawals</div>
            <h2><i class="mdi mdi-cloud-upload"></i> Upload withdrawals</h2>
            <p>Approve many withdrawals at once, for members who applied online or on paper. Each row you upload is approved for
              its amount and goes to the Chairman, just like approving it on its own page.</p>
            <div class="fud-hero-actions">
              <a href="withdrawal_endorsement.php"><i class="mdi mdi-arrow-left"></i> Savings withdrawals</a>
            </div>
          </div>

          <div class="fud-section">
            <div class="fud-section-head"><h5><span class="fud-num">1</span> Get a template</h5></div>
            <div class="fud-section-body">
              <div class="row">
                <div class="col-md-6 mb-2">
                  <div class="border rounded p-3 h-100">
                    <div class="font-weight-bold mb-1"><i class="mdi mdi-file-outline"></i> Empty template</div>
                    <p class="small text-muted mb-2">The column headings only. Use it for paper applications: leave Withdrawal ID blank and fill in the member's staff number,
                      bank, account and the amount to pay.</p>
                    <a class="btn btn-sm btn-outline-primary" href="?export=template"><i class="mdi mdi-download"></i> Download empty template</a>
                  </div>
                </div>
                <div class="col-md-6 mb-2">
                  <div class="border rounded p-3 h-100">
                    <div class="font-weight-bold mb-1"><i class="mdi mdi-file-document"></i> Prefilled with requests awaiting you</div>
                    <p class="small text-muted mb-2">Every request in Savings Withdrawals that is waiting for you, with its Withdrawal ID and payment details. The approved amount
                      is filled in as the amount asked, or the most that can be withdrawn if that is less. Change it, or delete a row you are not approving.</p>
                    <a class="btn btn-sm btn-primary<?php echo $applied ? '' : ' disabled'; ?>" href="?export=applied"><i class="mdi mdi-download"></i> Download prefilled (<?php echo count($applied); ?>)</a>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="fud-section">
            <div class="fud-section-head"><h5><span class="fud-num">2</span> Upload the file</h5></div>
            <div class="fud-section-body">
              <ul class="small text-muted pl-3">
                <li><strong>With a Withdrawal ID</strong> (online requests): the request is approved for the amount and sent to the Chairman.</li>
                <li><strong>Without one</strong> (paper applications): a new request is recorded for the staff number and sent to the Chairman. Staff number,
                  account number and account name are needed. A member who already has a request waiting is skipped; put that request's ID in the row instead.</li>
                <li>Every row is checked like a single approval: the amount must leave at least <?php echo '₦' . number_format(LoanControls::withdrawalMinBalance(), 2); ?> in savings,
                  and requests already decided are not changed. Columns after Approved Withdrawal Amount are ignored.</li>
                <li>Save the file as <strong>.csv</strong>, at most 500KB.</li>
              </ul>
              <form id="wdUpload" class="form-inline" enctype="multipart/form-data">
                <input type="file" id="uploadFile" name="uploadFile" accept=".csv" class="form-control mr-2 mb-2" required>
                <button type="submit" class="btn btn-primary mb-2"><i class="mdi mdi-upload"></i> Upload</button>
              </form>
              <div id="uploadResult" class="mt-3"></div>
            </div>
          </div>

        </div>
        <?php echo $view->subFooter('../'); ?>
<script>
document.getElementById('wdUpload').addEventListener('submit', function (e) {
    e.preventDefault();
    if (!confirm('Upload these withdrawals? Each valid row is approved and sent to the Chairman.')) { return; }
    var result = document.getElementById('uploadResult');
    result.innerHTML = '<div class="alert alert-primary">Uploading, please wait...</div>';
    fetch('uploadWithdrawalCSV.php', { method: 'POST', body: new FormData(this), credentials: 'same-origin' })
        .then(function (r) { return r.text(); })
        .then(function (html) { result.innerHTML = html; })
        .catch(function () { result.innerHTML = '<div class="alert alert-danger">Network error. Please try again.</div>'; });
});
</script>
