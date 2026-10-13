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
            <div class="fud-section-head"><h5><i class="mdi mdi-cloud-upload"></i> Upload a month's deductions</h5>
              <a href="Commodity_repayment_template.csv" download>Download template</a></div>
            <div class="fud-section-body">
              <ul class="small text-muted pl-3">
                <li>CSV columns: Staff Number, Repayment Month (mm/dd/yyyy), Amount Deducted. Leave the amount blank for the full monthly deduction.</li>
                <li>Each row goes to the member's oldest commodity request that is being repaid. Uploading a month again replaces that month.</li>
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
