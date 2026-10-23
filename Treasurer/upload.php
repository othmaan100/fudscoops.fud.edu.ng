<?php
    // Upload new members (Treasurer): fill the template (../Members_uploads.csv), check the file, then import it.
    // The work is done by uploadMembersExcel.php (config/classes/MemberUpload.php); nothing is saved by the check.
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    require_once('../config/classes/SavingsG1.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../auth/') || !User::isBur('../auth/logout.php')) {
        exit();
    }
    $minimum = SavingsG1::getMinMonthlySavings();
    $lastMonth = date('m/Y', strtotime('first day of last month'));
    echo $view->subHeader('../');
?>
    <?php echo $view->subPartialNav('../'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->treasurerSideNav(); ?>
      <div class="main-panel">
        <div class="content-wrapper fud-dash fud-apply">

          <div class="fud-hero">
            <div class="fud-hero-date">Uploads</div>
            <h2><i class="mdi mdi-account-multiple-plus"></i> Upload new members</h2>
            <p>Add many staff as members at once, with their monthly savings and any savings they had before the portal.
              Check the file first: nothing is saved until you import it.</p>
            <div class="fud-hero-actions">
              <a href="../Members_uploads.csv?v=<?php echo @filemtime(__DIR__ . '/../Members_uploads.csv'); ?>" download="Members_uploads.csv"><i class="mdi mdi-download"></i> Download template</a>
              <a href="member_list.php"><i class="mdi mdi-account-group"></i> Members</a>
              <a href="uploadDeposit.php"><i class="mdi mdi-cash-multiple"></i> Upload monthly savings</a>
            </div>
          </div>

          <div class="row">
            <div class="col-lg-6">
              <div class="fud-section">
                <div class="fud-section-head"><h5><span class="fud-num">1</span> Fill the template</h5></div>
                <div class="fud-section-body">
                  <table class="table fud-table mb-3">
                    <thead><tr><th>Column</th><th>What to enter</th></tr></thead>
                    <tbody>
                      <tr><td class="text-nowrap"><strong>Staff Number</strong></td><td>Required. As on the staff record, e.g. SP/RA/1234 or JP/R/208.</td></tr>
                      <tr><td><strong>Name</strong></td><td>Optional. If given, it must match the staff record, which catches a mistyped staff number.</td></tr>
                      <tr><td class="text-nowrap"><strong>Monthly Savings</strong></td><td>Required. At least ₦<?php echo number_format($minimum, 2); ?>. Kobo allowed; no ₦ sign needed.</td></tr>
                      <tr><td><strong>Opening Savings Balance</strong></td><td>Optional. Savings the member already had before the portal. Recorded once as an opening balance, separate from monthly deductions.</td></tr>
                      <tr><td><strong>Balance As At</strong></td><td>Optional. The month that balance is up to, as MM/YYYY. Blank means last month (<?php echo $lastMonth; ?>).</td></tr>
                    </tbody>
                  </table>
                  <p class="small text-muted mb-1">Example:</p>
                  <div class="table-responsive">
                    <table class="table fud-table small mb-3" style="background:#f8f9fc">
                      <thead><tr><th>Staff Number</th><th>Name</th><th>Monthly Savings</th><th>Opening Savings Balance</th><th>Balance As At (MM/YYYY)</th></tr></thead>
                      <tbody><tr><td>SP/RA/1234</td><td>Aisha Bello</td><td>5000</td><td>120000</td><td><?php echo $lastMonth; ?></td></tr>
                        <tr><td>JP/R/208</td><td></td><td>3000</td><td></td><td></td></tr></tbody>
                    </table>
                  </div>
                  <ul class="fud-howto mb-0">
                    <li>Keep the heading row. Save as <strong>CSV (Comma delimited)</strong> in Excel.</li>
                    <li>Staff already members are left as they are; change their monthly savings through Savings Update.</li>
                    <li>Applicants still waiting for approval are approved by the upload.</li>
                  </ul>
                </div>
              </div>
            </div>

            <div class="col-lg-6">
              <div class="fud-section">
                <div class="fud-section-head"><h5><span class="fud-num">2</span> Check and import</h5></div>
                <div class="fud-section-body">
                  <form id="membersUpload">
                    <label class="fud-file mb-3" for="uploadFile">
                      <i class="mdi mdi-file-delimited"></i>
                      <span id="uploadFileName">Choose the completed .csv file (up to 1MB)</span>
                      <input type="file" id="uploadFile" name="uploadFile" accept=".csv" required>
                    </label>
                    <button type="submit" class="btn btn-primary btn-lg" id="checkFile"><i class="mdi mdi-magnify"></i> Check file</button>
                    <button type="button" class="btn btn-success btn-lg" id="importFile" disabled><i class="mdi mdi-database-import"></i> Import</button>
                  </form>
                  <div class="msg mt-3"></div>
                  <div class="fud-stats mt-2" id="upSummary" style="display:none"></div>
                </div>
              </div>
            </div>
          </div>

          <div class="fud-section fud-upload-result" id="upResults" style="display:none">
            <div class="fud-section-head d-flex flex-wrap justify-content-between align-items-center">
              <h5><i class="mdi mdi-format-list-checks"></i> <span id="upTitle">Rows</span></h5>
              <div class="fud-tabs mb-0" id="upFilter">
                <a href="#" data-f="all" class="active">All</a>
                <a href="#" data-f="ok">Ready / saved</a>
                <a href="#" data-f="existing">Already members</a>
                <a href="#" data-f="error">Problems</a>
              </div>
            </div>
            <div class="fud-section-body">
              <div class="table-responsive">
                <table class="table fud-table mb-0">
                  <thead><tr><th>Line</th><th>Staff</th><th class="num">Monthly</th><th class="num">Opening balance</th><th>Result</th></tr></thead>
                  <tbody id="upRows"></tbody>
                </table>
              </div>
              <button type="button" class="btn btn-sm btn-outline-secondary mt-3" id="downloadProblems" style="display:none"><i class="mdi mdi-download"></i> Download the rows with problems (CSV)</button>
            </div>
          </div>

        </div>
        <?php echo $view->subFooter('../'); ?>

<script>
(function () {
    var form = document.getElementById('membersUpload'), input = document.getElementById('uploadFile');
    var checkBtn = document.getElementById('checkFile'), importBtn = document.getElementById('importFile');
    var msg = document.querySelector('.msg'), last = null, checkedFile = null;
    var esc = function (s) { var d = document.createElement('div'); d.textContent = s == null ? '' : String(s); return d.innerHTML; };
    var fmt = function (v) { return '₦' + Number(v || 0).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); };
    var monthName = function (ym) { if (!ym) { return ''; } var p = ym.split('-'); return new Date(p[0], p[1] - 1, 1).toLocaleString('en-GB', { month: 'short', year: 'numeric' }); };

    input.addEventListener('change', function () {
        document.getElementById('uploadFileName').textContent = input.files.length ? input.files[0].name : 'Choose the completed .csv file (up to 1MB)';
        importBtn.disabled = true;   // a new file must be checked first
        checkedFile = null;
    });

    function alertBox(ok, text) { msg.innerHTML = '<div class="alert alert-' + (ok ? 'success' : 'danger') + ' mb-0"></div>'; msg.firstChild.textContent = text; }

    function send(mode) {
        if (!input.files.length) { alertBox(false, 'Choose the CSV file first.'); return; }
        var data = new FormData();
        data.append('uploadFile', input.files[0]);
        data.append('mode', mode);
        checkBtn.disabled = true; importBtn.disabled = true;
        msg.innerHTML = '<div class="alert alert-info mb-0"><i class="mdi mdi-loading mdi-spin"></i> ' + (mode === 'import' ? 'Importing…' : 'Checking the file…') + '</div>';
        fetch('uploadMembersExcel.php', { method: 'POST', body: data, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                checkBtn.disabled = false;
                if (!res.ok) { alertBox(false, res.message); return; }
                last = res;
                var s = res.summary, ready = s.create + s.approve;
                alertBox(mode === 'import' ? s.saved > 0 : ready > 0, res.message + (res.old_template ? ' (Old template: Consolidated Savings is read as the opening balance, as at last month.)' : ''));
                if (mode === 'check') { checkedFile = input.files[0]; importBtn.disabled = !ready; importBtn.innerHTML = '<i class="mdi mdi-database-import"></i> Import ' + ready + ' row' + (ready === 1 ? '' : 's'); }
                render(res);
            })
            .catch(function () { checkBtn.disabled = false; alertBox(false, 'Network or server error. Please try again.'); });
    }
    form.addEventListener('submit', function (e) { e.preventDefault(); send('check'); });
    importBtn.addEventListener('click', function () {
        if (!last || checkedFile !== input.files[0]) { alertBox(false, 'Check the file before importing.'); return; }
        var s = last.summary;
        if (!confirm('Import ' + (s.create + s.approve) + ' member(s)?\n\n' + s.create + ' new member(s), ' + s.approve + ' applicant(s) approved, '
            + s.openings + ' opening balance(s) totalling ' + fmt(s.opening_total) + '.\nRows with problems and existing members are not changed.')) { return; }
        send('import');
    });

    function stat(icon, color, label, value, sub) {
        return '<div class="fud-stat"><div class="fud-stat-icon ' + color + '"><i class="mdi ' + icon + '"></i></div><div><div class="fud-stat-label">' + label
            + '</div><div class="fud-stat-value">' + value + '</div>' + (sub ? '<div class="fud-stat-sub">' + sub + '</div>' : '') + '</div></div>';
    }
    function render(res) {
        var s = res.summary, imported = res.mode === 'import';
        var box = document.getElementById('upSummary');
        box.style.display = '';
        box.innerHTML = stat('mdi-account-plus', 'fud-green', imported ? 'Saved' : 'New members', imported ? s.saved : s.create, imported ? 'of ' + (s.create + s.approve) + ' ready' : (s.approve ? '+ ' + s.approve + ' applicant(s) approved' : ''))
            + stat('mdi-cash-plus', 'fud-blue', 'Opening balances', s.openings, fmt(s.opening_total))
            + stat('mdi-account-check', 'fud-purple', 'Already members', s.existing, 'not changed')
            + stat('mdi-alert-circle-outline', s.error ? 'fud-rose' : 'fud-green', 'Problems', s.error, s.error ? 'fix and upload again' : 'none');
        document.getElementById('upTitle').textContent = (imported ? 'Import result: ' : 'Check result: ') + s.rows + ' row(s)';
        var html = '';
        res.rows.forEach(function (r) {
            var cls = r.action === 'error' ? 'error' : (r.action === 'existing' ? 'existing' : 'ok');
            var pill, notes = '';
            if (imported && r.saved) { pill = '<span class="fud-pill ok">Saved</span>'; }
            else if (r.action === 'error') { pill = '<span class="fud-pill no">Problem</span>'; }
            else if (r.action === 'existing') { pill = '<span class="fud-pill wait">Already a member</span>'; }
            else if (imported) { pill = '<span class="fud-pill no">Not saved</span>'; }
            else { pill = '<span class="fud-pill ok">' + (r.action === 'create' ? 'New member' : 'Approve applicant') + '</span>'; }
            (r.errors || []).forEach(function (e) { notes += '<div class="small text-danger">' + esc(e) + '</div>'; });
            (r.warnings || []).forEach(function (w) { notes += '<div class="small text-muted">' + esc(w) + '</div>'; });
            if (imported && r.result && !r.saved && r.action !== 'error' && r.action !== 'existing') { notes += '<div class="small text-danger">' + esc(r.result) + '</div>'; }
            html += '<tr data-f="' + cls + '"' + (cls === 'error' ? ' class="fud-row-problem"' : '') + '><td>' + r.line + '</td>'
                + '<td style="white-space:normal;min-width:180px"><strong>' + esc(r.name || r.file_name || '–') + '</strong><div class="small text-muted">' + esc(r.staff) + (r.dept && r.dept !== '0' ? ' · ' + esc(r.dept) : '') + '</div></td>'
                + '<td class="num">' + (r.monthly != null ? fmt(r.monthly) : '–') + '</td>'
                + '<td class="num">' + (r.opening > 0 ? fmt(r.opening) + '<div class="small text-muted">as at ' + esc(monthName(r.as_at)) + '</div>' : '–') + '</td>'
                + '<td style="white-space:normal;min-width:220px">' + pill + notes + '</td></tr>';
        });
        document.getElementById('upRows').innerHTML = html;
        document.getElementById('upResults').style.display = '';
        document.getElementById('downloadProblems').style.display = s.error ? '' : 'none';
        filter('all');
    }
    function filter(f) {
        document.querySelectorAll('#upFilter a').forEach(function (a) { a.classList.toggle('active', a.getAttribute('data-f') === f); });
        document.querySelectorAll('#upRows tr').forEach(function (tr) { tr.style.display = f === 'all' || tr.getAttribute('data-f') === f ? '' : 'none'; });
    }
    document.querySelectorAll('#upFilter a').forEach(function (a) { a.addEventListener('click', function (e) { e.preventDefault(); filter(a.getAttribute('data-f')); }); });
    // The rows with problems, in the template layout with the reason added, to correct and upload again
    document.getElementById('downloadProblems').addEventListener('click', function () {
        if (!last) { return; }
        var q = function (v) { v = v == null ? '' : String(v); return /[",\r\n]/.test(v) ? '"' + v.replace(/"/g, '""') + '"' : v; };
        var lines = ['Staff Number,Name,Monthly Savings,Opening Savings Balance,Balance As At (MM/YYYY),Problem'];
        last.rows.filter(function (r) { return r.action === 'error'; }).forEach(function (r) {
            var asAt = r.as_at ? r.as_at.split('-')[1] + '/' + r.as_at.split('-')[0] : '';
            lines.push([r.staff, r.file_name, r.monthly == null ? '' : r.monthly, r.opening_in, asAt, r.errors.join(' ')].map(q).join(','));
        });
        var a = document.createElement('a');
        a.href = URL.createObjectURL(new Blob(['﻿' + lines.join('\r\n')], { type: 'text/csv' }));
        a.download = 'members_upload_problems.csv';
        document.body.appendChild(a); a.click(); a.remove();
    });
})();
</script>
