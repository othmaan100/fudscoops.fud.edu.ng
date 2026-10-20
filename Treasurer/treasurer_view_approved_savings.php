<?php
    // Monthly savings update requests the Chairman approved, waiting for the Treasurer to fix the new amount
    // (ajax/treasurer_approve_savings_update.php), one by one or offline (download_savings_updates.php, then upload the CSV).
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    require_once('../config/classes/SavingsG1.php');
    $view = new View();
    $user = new User();
    $savings = new SavingsG1();

    if (!User::is_authenticated('../auth/') || !User::isBur('../auth/logout.php')) {
        exit();
    }

    // Upload of the approved list (offline processing). Post/Redirect/Get so a page refresh does not re-submit.
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['approved_list'])) {
        $file = $_FILES['approved_list'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
            $summary = ['approved' => 0, 'skipped' => 0, 'errors' => ['No file uploaded or the upload failed.']];
        } elseif ($ext !== 'csv') {
            $summary = ['approved' => 0, 'skipped' => 0, 'errors' => ['Only CSV files are allowed. In Excel use File > Save As > CSV (Comma delimited).']];
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $summary = ['approved' => 0, 'skipped' => 0, 'errors' => ['File is too large (maximum 2MB).']];
        } else {
            $summary = $savings->processTreasurerSavingsUpload($file['tmp_name']);
        }

        $_SESSION['savings_upload_summary'] = $summary;
        header('location: treasurer_view_approved_savings.php');
        exit();
    }

    $uploadSummary = isset($_SESSION['savings_upload_summary']) ? $_SESSION['savings_upload_summary'] : null;
    unset($_SESSION['savings_upload_summary']);

    $h = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
    $money = function ($v) { return ((float) $v < 0 ? '−' : '') . '₦' . number_format(abs((float) $v), 2); };
    $requests = $savings->getChairmanApprovedSavingsUpdates();
    $recent = array_values(array_filter($savings->savingsUpdatesRecent(), function ($d) { return $d['is_secretary_approved'] == 1 && $d['is_treasurer_approved'] != 0; }));
    $minimum = SavingsG1::getMinMonthlySavings();
    $change = 0;
    foreach ($requests as $r) {
        $change += (float) $r['savings_update_amount'] - (float) $r['proposed_monthly_savings'];
    }

    echo $view->subHeader('../');
?>
    <?php echo $view->subPartialNav('../'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->treasurerSideNav(); ?>
      <div class="main-panel">
        <div class="content-wrapper fud-dash">

          <div class="fud-hero">
            <div class="fud-hero-date">Savings</div>
            <h2><i class="mdi mdi-calendar-edit"></i> Monthly savings updates</h2>
            <p>Requests the Chairman has approved. Fix the new monthly amount for each member, one by one below or for many at once
              with the download and upload. Approving changes the member's monthly savings straight away.</p>
            <div class="fud-hero-actions">
              <a href="savings_settings.php"><i class="mdi mdi-cog"></i> Minimum <?php echo $money($minimum); ?> a month</a>
              <a href="view_savings.php"><i class="mdi mdi-wallet"></i> Members savings</a>
            </div>
          </div>

          <?php if ($uploadSummary) { ?>
            <div class="fud-notice <?php echo empty($uploadSummary['errors']) ? 'success' : ''; ?>"><i class="mdi mdi-<?php echo empty($uploadSummary['errors']) ? 'check-circle' : 'alert-circle'; ?>"></i>
              <span><strong>Upload result:</strong> <?php echo (int) $uploadSummary['approved']; ?> approved,
                <?php echo (int) $uploadSummary['skipped']; ?> left pending (blank amount), <?php echo count($uploadSummary['errors']); ?> not processed.
                <?php if (!empty($uploadSummary['errors'])) { ?>
                  <ul class="mb-0 mt-2 pl-3"><?php foreach ($uploadSummary['errors'] as $error) { ?><li><?php echo $h($error); ?></li><?php } ?></ul>
                <?php } ?></span></div>
          <?php } ?>

          <div class="fud-stats">
            <div class="fud-stat"><div class="fud-stat-icon fud-amber"><i class="mdi mdi-clock-outline"></i></div>
              <div><div class="fud-stat-label">Awaiting you</div><div class="fud-stat-value" id="pendingCount"><?php echo count($requests); ?></div>
                <div class="fud-stat-sub">approved by the Chairman</div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-blue"><i class="mdi mdi-trending-up"></i></div>
              <div><div class="fud-stat-label">Change in monthly savings</div><div class="fud-stat-value"><?php echo ($change > 0 ? '+' : '') . $money($change); ?></div>
                <div class="fud-stat-sub">if approved as requested</div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-green"><i class="mdi mdi-check-all"></i></div>
              <div><div class="fud-stat-label">Fixed in the last 30 days</div><div class="fud-stat-value"><?php echo count(array_filter($recent, function ($d) { return $d['is_treasurer_approved'] == 1; })); ?></div>
                <div class="fud-stat-sub">see the list below</div></div></div>
          </div>

          <div class="row">
            <div class="col-lg-8">
              <div class="fud-section">
                <div class="fud-section-head"><h5><i class="mdi mdi-format-list-checks"></i> Requests</h5></div>
                <div class="fud-section-body">
                  <div class="msg"></div>
                  <?php if (!$requests) { ?>
                    <p class="fud-empty"><i class="mdi mdi-check-circle-outline"></i> No savings update requests are waiting. They appear here after the Chairman approves them.</p>
                  <?php } else { ?>
                    <input class="form-control fud-search" type="search" id="searchSavingsUpdate" placeholder="Search name, staff number, department…">
                    <div class="table-responsive">
                      <table class="table fud-table mb-0">
                        <thead><tr><th>Member</th><th class="num">Current</th><th class="num">Requested</th><th style="min-width:180px">New monthly amount</th><th></th></tr></thead>
                        <tbody id="searchSavingsUpdateBody">
                        <?php foreach ($requests as $r) {
                            $name = preg_replace('/\s+/', ' ', trim($r['fname'] . ' ' . $r['oname'] . ' ' . $r['lname']));
                            $diff = (float) $r['savings_update_amount'] - (float) $r['proposed_monthly_savings']; ?>
                          <tr>
                            <td style="white-space:normal;min-width:170px">
                              <a href="member_profile.php?member_id=<?php echo (int) $r['member_id']; ?>" class="font-weight-bold text-dark"><?php echo $h($name); ?></a>
                              <div class="small text-muted"><?php echo $h($r['sp_no']); ?><?php echo $r['dept'] ? ' · ' . $h($r['dept']) : ''; ?></div>
                              <div class="small text-muted">requested <?php echo date('d M Y', strtotime($r['update_proposed_date'])); ?>
                                <?php if ($r['earlier_requests'] > 0) { ?> · <span title="Earlier pending requests from this member are closed when this one is approved">replaces <?php echo (int) $r['earlier_requests']; ?> earlier</span><?php } ?></div>
                            </td>
                            <td class="num text-nowrap"><?php echo $money($r['proposed_monthly_savings']); ?></td>
                            <td class="num text-nowrap"><strong><?php echo $money($r['savings_update_amount']); ?></strong>
                              <div class="small <?php echo $diff >= 0 ? 'text-success' : 'text-danger'; ?>"><?php echo ($diff > 0 ? '+' : '') . $money($diff); ?></div></td>
                            <td>
                              <div class="input-group input-group-sm">
                                <div class="input-group-prepend"><span class="input-group-text">₦</span></div>
                                <input type="number" class="form-control approved-amount" min="<?php echo $minimum; ?>" step="0.01"
                                       value="<?php echo $h($r['savings_update_amount']); ?>" data-requested="<?php echo $h($r['savings_update_amount']); ?>">
                              </div>
                            </td>
                            <td class="text-right"><button type="button" class="btn btn-sm btn-success text-nowrap approve-savings-update"
                                data-update-id="<?php echo (int) $r['id']; ?>" data-name="<?php echo $h($name . ' (' . $r['sp_no'] . ')'); ?>"><i class="mdi mdi-check"></i> Approve</button></td>
                          </tr>
                        <?php } ?>
                        </tbody>
                      </table>
                    </div>
                  <?php } ?>
                </div>
              </div>
            </div>

            <div class="col-lg-4">
              <div class="fud-section">
                <div class="fud-section-head"><h5><i class="mdi mdi-file-excel"></i> Many at once</h5></div>
                <div class="fud-section-body">
                  <ol class="small pl-3">
                    <li>Download the list. <em>Approved Amount</em> is filled with what each member asked for.</li>
                    <li>Change an amount where a different one is approved, or clear it to leave the request pending. Do not change <em>Request ID</em> or <em>Staff Number</em>.</li>
                    <li>Save as CSV and upload it here.</li>
                  </ol>
                  <a href="download_savings_updates.php" class="btn btn-outline-primary btn-block mb-3<?php echo $requests ? '' : ' disabled'; ?>"><i class="mdi mdi-download"></i> Download list (CSV)</a>
                  <form method="POST" enctype="multipart/form-data" onsubmit="return confirm('Approve every request in this file with the amounts entered?');">
                    <label class="fud-file mb-2" for="approvedList"><i class="mdi mdi-file-delimited"></i>
                      <span id="approvedListName">Choose the .csv file</span>
                      <input type="file" id="approvedList" name="approved_list" accept=".csv" required></label>
                    <button type="submit" class="btn btn-success btn-block"><i class="mdi mdi-upload"></i> Upload approved list</button>
                  </form>
                  <p class="small text-muted mt-2 mb-0">Where a member applied more than once, only the latest request is listed; approving it closes the earlier ones.</p>
                </div>
              </div>
            </div>
          </div>

          <?php if ($recent) { ?>
            <div class="fud-section">
              <div class="fud-section-head"><h5><i class="mdi mdi-history"></i> Decided in the last 30 days</h5></div>
              <div class="fud-section-body">
                <div class="table-responsive">
                  <table class="table fud-table mb-0">
                    <thead><tr><th>Chairman approved</th><th>Member</th><th class="num">Requested</th><th>Result</th></tr></thead>
                    <tbody>
                    <?php foreach ($recent as $d) { ?>
                      <tr><td class="text-nowrap"><?php echo date('d M Y', strtotime($d['approved_at'])); ?></td>
                        <td><?php echo $h(trim($d['fname'] . ' ' . $d['lname'])); ?> <span class="small text-muted">(<?php echo $h($d['sp_no']); ?>)</span></td>
                        <td class="num"><?php echo $money($d['savings_update_amount']); ?></td>
                        <td><?php echo $d['is_treasurer_approved'] == 1 ? '<span class="fud-pill ok">Fixed at ' . $money($d['treasurer_approved_amount']) . '</span>'
                            : '<span class="fud-pill wait" title="The member made a newer request, which was approved instead">Replaced</span>'; ?></td></tr>
                    <?php } ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          <?php } ?>

        </div>
        <?php echo $view->subFooter('../'); ?>

<script>
(function () {
    var file = document.getElementById('approvedList');
    file.addEventListener('change', function () { document.getElementById('approvedListName').textContent = file.files.length ? file.files[0].name : 'Choose the .csv file'; });
    var body = document.getElementById('searchSavingsUpdateBody');
    if (!body) { return; }
    var msg = document.querySelector('.msg');
    document.getElementById('searchSavingsUpdate').addEventListener('input', function () {
        var q = this.value.toLowerCase();
        body.querySelectorAll('tr').forEach(function (r) { r.style.display = r.textContent.toLowerCase().indexOf(q) !== -1 ? '' : 'none'; });
    });
    body.addEventListener('click', function (e) {
        var button = e.target.closest('.approve-savings-update');
        if (!button) { return; }
        var row = button.closest('tr'), input = row.querySelector('.approved-amount');
        var amount = parseFloat(input.value), requested = parseFloat(input.getAttribute('data-requested')), minimum = parseFloat(input.getAttribute('min'));
        var fmt = function (v) { return '₦' + v.toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); };
        if (isNaN(amount) || amount < minimum) { alert('Enter an amount of at least ' + fmt(minimum) + '.'); input.focus(); return; }
        var question = 'Approve ' + fmt(amount) + ' as the new monthly savings for ' + button.getAttribute('data-name') + '?';
        if (Math.abs(amount - requested) > 0.005) { question = 'The member asked for ' + fmt(requested) + '.\n' + question; }
        if (!confirm(question)) { return; }
        button.disabled = true;
        var data = new FormData();
        data.append('update_id', button.getAttribute('data-update-id'));
        data.append('amount', amount);
        fetch('../ajax/treasurer_approve_savings_update.php', { method: 'POST', body: data, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                msg.innerHTML = '<div class="alert alert-' + (res.success ? 'success' : 'danger') + '"></div>';
                msg.firstChild.textContent = res.message;
                if (res.success) {
                    row.parentNode.removeChild(row);
                    document.getElementById('pendingCount').textContent = body.querySelectorAll('tr').length;
                } else { button.disabled = false; }
            })
            .catch(function () { msg.innerHTML = '<div class="alert alert-danger">Network or server error. Please try again.</div>'; button.disabled = false; });
    });
})();
</script>
