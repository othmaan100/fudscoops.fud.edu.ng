<?php
    // Commodity Deduction (Treasurer): requests being repaid, and the monthly CSV upload
    // (Treasurer/uploadCommodityDeduction.php). Single deductions are recorded on each request's page.
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    require_once('../config/classes/CommodityLoan.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../auth/') || !User::isBur('../auth/logout.php')) {
        exit();
    }

    // Requests due a deduction in $month (YYYY-MM): being repaid, deductions started, nothing recorded for the month yet
    $month = isset($_GET['month']) && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $_GET['month']) ? $_GET['month'] : date('Y-m');
    $con = (new DB())->getConnection();
    $stmt = $con->prepare("SELECT a.application_id, a.monthly_amount, a.repayment_start, e.sp_no, e.fname, e.lname, e.oname, " . CommodityLoan::NAME_SQL . ",
                                  COALESCE(a.total_payable, a.total_amount) - (SELECT COALESCE(SUM(r.amount_paid), 0) FROM fudscoops_commodity_repayments r
                                                                               WHERE r.application_id = a.application_id) AS balance
                           FROM fudscoops_commodity_loan_applications a
                           LEFT JOIN fudscoops_commodity_supply s ON s.commodity_name_id = a.commodity_supply_id
                           JOIN fudscoops_member m ON m.member_id = a.member_id
                           JOIN employee e ON e.employee_id = m.employee_id
                           WHERE a.status = 'supplied' AND (a.repayment_start IS NULL OR a.repayment_start <= :first)
                             AND NOT EXISTS (SELECT 1 FROM fudscoops_commodity_repayments r2 WHERE r2.application_id = a.application_id AND r2.repayment_month = :first2)
                           ORDER BY e.sp_no, a.application_id");
    $stmt->execute([':first' => $month . '-01', ':first2' => $month . '-01']);
    $due = array_values(array_filter($stmt->fetchAll(PDO::FETCH_ASSOC), function ($r) { return $r['balance'] > 0.005; }));
    $dueTotal = 0;
    foreach ($due as &$d) {
        $d['amount'] = round(min((float) $d['monthly_amount'], (float) $d['balance']), 2);
        $dueTotal += $d['amount'];
    }
    unset($d);

    // Downloads: ?export=template (empty) or ?export=due&month=YYYY-MM (one row per request due, with the amount)
    if (isset($_GET['export']) && in_array($_GET['export'], ['template', 'due'], true)) {
        $heading = ['Staff Number', 'Repayment Month (mm/dd/yyyy)', 'Amount Deducted (blank = full monthly deduction)', 'Request # (optional)', 'Name', 'Commodity', 'Balance before'];
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . ($_GET['export'] === 'template' ? 'commodity_deduction_template.csv' : 'commodity_deductions_due_' . $month . '.csv') . '"');
        $out = fopen('php://output', 'w');
        fputcsv($out, $heading);
        if ($_GET['export'] === 'due') {
            $date = date('m/t/Y', strtotime($month . '-01'));
            foreach ($due as $d) {
                fputcsv($out, [$d['sp_no'], $date, number_format($d['amount'], 2, '.', ''), $d['application_id'],
                               preg_replace('/\s+/', ' ', trim($d['fname'] . ' ' . $d['oname'] . ' ' . $d['lname'])), $d['commodity_name'],
                               number_format($d['balance'], 2, '.', '')]);
            }
        }
        fclose($out);
        exit();
    }
    $h = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
    echo $view->subHeader('../');
?>
    <?php echo $view->subPartialNav('../'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->treasurerSideNav(); ?>
      <div class="main-panel">
        <div class="content-wrapper fud-dash">

          <div class="fud-hero">
            <div class="fud-hero-date">Commodity</div>
            <h2><i class="mdi mdi-cart-outline"></i> Commodity Deduction</h2>
            <p>Record the monthly deductions for supplied commodity. A request is marked fully repaid when the deductions reach its total.</p>
          </div>

          <div class="fud-section">
            <div class="fud-section-head"><h5><i class="mdi mdi-cloud-upload"></i> Upload a month's deductions</h5></div>
            <div class="fud-section-body">
              <div class="row mb-3">
                <div class="col-md-6 mb-2">
                  <div class="border rounded p-3 h-100">
                    <div class="font-weight-bold mb-1"><i class="mdi mdi-file-outline"></i> Empty template</div>
                    <p class="small text-muted mb-2">Just the column headings, to fill in yourself.</p>
                    <a class="btn btn-sm btn-outline-primary" href="?export=template"><i class="mdi mdi-download"></i> Download empty template</a>
                  </div>
                </div>
                <div class="col-md-6 mb-2">
                  <div class="border rounded p-3 h-100">
                    <div class="font-weight-bold mb-1"><i class="mdi mdi-file-document"></i> Prefilled for a month</div>
                    <p class="small text-muted mb-2">Every member with commodity due in the month and not yet recorded, with the amount due
                      (the monthly deduction, or the balance if less).</p>
                    <form class="form-inline" method="get">
                      <input type="hidden" name="export" value="due">
                      <input type="month" name="month" class="form-control form-control-sm mr-2 mb-1" value="<?php echo $h($month); ?>" id="dueMonth">
                      <button class="btn btn-sm btn-primary mb-1"<?php echo $due ? '' : ' disabled'; ?>><i class="mdi mdi-download"></i> Download prefilled (<?php echo count($due); ?>)</button>
                    </form>
                    <div class="small text-muted mt-1"><?php echo count($due); ?> request<?php echo count($due) === 1 ? '' : 's'; ?> due in <?php echo date('F Y', strtotime($month . '-01')); ?>,
                      ₦<?php echo number_format($dueTotal, 2); ?> in all.</div>
                  </div>
                </div>
              </div>
              <ul class="small text-muted pl-3">
                <li>CSV columns: Staff Number, Repayment Month (mm/dd/yyyy), Amount Deducted. Leave the amount blank for the full monthly deduction.</li>
                <li>Request # is optional: when given, the row goes to that request; otherwise to the member's oldest commodity being repaid.
                  The Name, Commodity and Balance columns are for checking and are ignored.</li>
                <li>Uploading a month again replaces that month.</li>
              </ul>
              <form id="commodityUpload" enctype="multipart/form-data" class="form-inline">
                <input type="file" name="uploadFile" accept=".csv" class="form-control mr-2 mb-2" required>
                <button type="submit" class="btn btn-primary mb-2"><i class="mdi mdi-upload"></i> Upload</button>
              </form>
              <div id="uploadResult" class="mt-3"></div>
            </div>
          </div>

          <div class="fud-section">
            <div class="fud-section-head"><h5>Being repaid</h5><a href="view_commodity_request.php">All requests</a></div>
            <div class="fud-section-body">
              <?php echo CommodityLoan::requestsTable('supplied', false); ?>
            </div>
          </div>

        </div>
        <?php echo $view->subFooter('../'); ?>

<script>
// Choosing another month reloads the page so the count and total are for that month
document.getElementById('dueMonth').addEventListener('change', function () {
    if (/^\d{4}-\d{2}$/.test(this.value)) { location.href = '?month=' + this.value; }
});
document.getElementById('commodityUpload').addEventListener('submit', function (e) {
    e.preventDefault();
    if (!confirm('Upload these deductions?')) { return; }
    var result = document.getElementById('uploadResult');
    result.innerHTML = '<div class="alert alert-primary">Uploading, please wait...</div>';
    fetch('uploadCommodityDeduction.php', { method: 'POST', body: new FormData(this), credentials: 'same-origin' })
        .then(function (r) { return r.text(); })
        .then(function (html) { result.innerHTML = html; })
        .catch(function () { result.innerHTML = '<div class="alert alert-danger">Network error. Please try again.</div>'; });
});
</script>
