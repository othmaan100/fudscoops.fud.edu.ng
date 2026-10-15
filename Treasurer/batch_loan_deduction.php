<?php
    // Upload a month's loan deductions (CSV). Processed by uploadLoansDeductionExcel.php (js/custom.js #upload_loans_deduction).
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    require_once('../config/classes/DB.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../auth/') || !User::isBur('../auth/logout.php')) {
        exit();
    }
    $types = (new DB())->getConnection()->query("SELECT loan_type_id, loan_type_name FROM fudscoops_loan_type WHERE is_deleted = 0 ORDER BY loan_type_id")->fetchAll(PDO::FETCH_KEY_PAIR);
    $h = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
    echo $view->subHeader('../');
?>
    <?php echo $view->subPartialNav('../'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->treasurerSideNav(); ?>
      <div class="main-panel">
        <div class="content-wrapper fud-dash fud-apply">

          <div class="fud-hero">
            <div class="fud-hero-date">Loans</div>
            <h2><i class="mdi mdi-cloud-upload"></i> Upload Loan Deductions</h2>
            <p>Record a whole month of salary deductions at once from a CSV file.</p>
            <div class="fud-hero-actions">
              <a href="loan_deduction.php"><i class="mdi mdi-file-document-box"></i> Loan Deductions (list to record)</a>
              <a href="Loan_repayment_template.csv" download><i class="mdi mdi-download"></i> Blank template</a>
            </div>
          </div>

          <div class="row">
            <div class="col-lg-7">
              <div class="fud-section">
                <div class="fud-section-head"><h5><i class="mdi mdi-file-upload"></i> Upload a file</h5></div>
                <div class="fud-section-body">
                  <form id="loanDeductionUpload" enctype="multipart/form-data" onsubmit="return false;">
                    <div class="form-group">
                      <label for="loanId">Loan type</label>
                      <select class="form-control" name="loanId" id="loanId" required>
                        <option value="">Select the loan type</option>
                        <?php foreach ($types as $id => $name) { ?>
                          <option value="<?php echo (int) $id; ?>" <?php echo count($types) === 1 ? 'selected' : ''; ?>><?php echo $h($name); ?></option>
                        <?php } ?>
                      </select>
                    </div>
                    <div class="form-group">
                      <label for="uploadFile">CSV file</label>
                      <label class="fud-file" for="uploadFile">
                        <i class="mdi mdi-file-delimited"></i>
                        <span id="uploadFileName">Choose a .csv file (up to 500KB)</span>
                        <input type="file" id="uploadFile" name="uploadFile" accept=".csv" required>
                      </label>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg" name="upload_loans_deduction" id="upload_loans_deduction">
                      <i class="mdi mdi-cloud-upload"></i> Upload deductions</button>
                  </form>
                </div>
              </div>
            </div>
            <div class="col-lg-5">
              <div class="fud-section">
                <div class="fud-section-head"><h5><i class="mdi mdi-information-outline"></i> How it works</h5></div>
                <div class="fud-section-body">
                  <ol class="fud-howto">
                    <li>On <a href="loan_deduction.php">Loan Deductions</a>, choose the month and download the loans <strong>to record</strong>,
                      or start from the <a href="Loan_repayment_template.csv" download>blank template</a>.</li>
                    <li>Columns: <strong>Staff Number</strong>, <strong>Repayment Month</strong> (mm/dd/yyyy) and the
                      <strong>Amount Deducted</strong>. Leave the amount blank to record the full monthly instalment.</li>
                    <li>Correct each amount to what payroll actually deducted, save as <strong>CSV</strong> and upload it here.</li>
                  </ol>
                  <ul class="fud-checks mb-0">
                    <li>Each row goes to the member's loan of the chosen type that is being repaid.</li>
                    <li>Uploading a month again replaces that month; nothing is counted twice.</li>
                    <li>Loans are marked fully repaid automatically; overpayments are flagged for the member.</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <div id="msg"></div>

        </div>
        <?php echo $view->subFooter('../'); ?>

<script>
document.getElementById('uploadFile').addEventListener('change', function () {
    document.getElementById('uploadFileName').textContent = this.files.length ? this.files[0].name : 'Choose a .csv file (up to 500KB)';
});
</script>
