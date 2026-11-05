<?php
    // Upload staff into the staff list (employee table). Members can only be uploaded (upload.php) for staff in this list.
    // The work is done by uploadStaff.php (config/classes/StaffUpload.php); nothing is saved by the check.
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../auth/') || !User::isBur('../auth/logout.php')) {
        exit();
    }
    $con = (new DB())->getConnection();
    $staffCount = (int) $con->query("SELECT COUNT(*) FROM employee")->fetchColumn();
    $nonMembers = (int) $con->query("SELECT COUNT(*) FROM employee e LEFT JOIN fudscoops_member m ON m.employee_id = e.employee_id WHERE m.member_id IS NULL")->fetchColumn();
    echo $view->subHeader('../');
?>
    <?php echo $view->subPartialNav('../'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->treasurerSideNav(); ?>
      <div class="main-panel">
        <div class="content-wrapper fud-dash fud-apply">

          <div class="fud-hero">
            <div class="fud-hero-date">Uploads</div>
            <h2><i class="mdi mdi-account-card-details"></i> Upload staff list</h2>
            <p>Add new staff of the university to the staff list. A staff member must be on this list before they can be
              uploaded as a member or apply for membership. Check the file first: nothing is saved until you import it.</p>
            <div class="fud-hero-actions">
              <a href="../Staff_uploads.csv?v=<?php echo @filemtime(__DIR__ . '/../Staff_uploads.csv'); ?>" download="Staff_uploads.csv"><i class="mdi mdi-download"></i> Download template</a>
              <a href="upload.php"><i class="mdi mdi-account-multiple-plus"></i> Upload members</a>
            </div>
          </div>

          <div class="fud-stats">
            <div class="fud-stat"><div class="fud-stat-icon fud-blue"><i class="mdi mdi-account-multiple"></i></div>
              <div><div class="fud-stat-label">Staff on the list</div><div class="fud-stat-value"><?php echo number_format($staffCount); ?></div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-amber"><i class="mdi mdi-account-outline"></i></div>
              <div><div class="fud-stat-label">Not yet members</div><div class="fud-stat-value"><?php echo number_format($nonMembers); ?></div></div></div>
          </div>

          <div class="row">
            <div class="col-lg-6">
              <div class="fud-section">
                <div class="fud-section-head"><h5><span class="fud-num">1</span> The file</h5></div>
                <div class="fud-section-body">
                  <p class="small">Use the HR list as it is (for example <em>S/N, STAFF ID, GIFMIS CODE, FULL NAME, date-apptm</em>), or the template. Columns are found by their headings.</p>
                  <table class="table fud-table mb-3">
                    <thead><tr><th>Column</th><th>What to enter</th></tr></thead>
                    <tbody>
                      <tr><td class="text-nowrap"><strong>Staff ID</strong></td><td>Required. SPRN3237 or SP/RN/3237 (saved as SP/RN/3237; JPR3322 as JP/R/3322).</td></tr>
                      <tr><td class="text-nowrap"><strong>Full Name</strong></td><td>Required. Titles such as Dr, Prof, Mr are recognised.</td></tr>
                      <tr><td class="text-nowrap"><strong>GIFMIS Code</strong></td><td>Optional. The IPPIS / GIFMIS number, e.g. TI345381.</td></tr>
                      <tr><td><strong>Date of appointment</strong></td><td>Optional. 01-Nov-23, 01/11/2023 or 2023-11-01.</td></tr>
                      <tr><td><strong>Department, Cadre, Gender, Phone, Email</strong></td><td>Optional.</td></tr>
                    </tbody>
                  </table>
                  <ul class="fud-howto mb-0">
                    <li>Staff already on the list are not changed (a missing GIFMIS code is added).</li>
                    <li>A staff ID on the list under a different name, or a GIFMIS code that belongs to another staff member, is shown as a problem and not saved.</li>
                    <li>New staff get a login: the staff ID, with the staff ID as the first password.</li>
                  </ul>
                </div>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="fud-section">
                <div class="fud-section-head"><h5><span class="fud-num">2</span> Check and import</h5></div>
                <div class="fud-section-body">
                  <form id="staffUpload">
                    <label class="fud-file mb-3" for="uploadFile">
                      <i class="mdi mdi-file-delimited"></i>
                      <span id="uploadFileName">Choose the .csv file (up to 2MB)</span>
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
                <a href="#" data-f="create">New staff</a>
                <a href="#" data-f="existing">Already on the list</a>
                <a href="#" data-f="error">Problems</a>
              </div>
            </div>
            <div class="fud-section-body">
              <input type="search" id="upSearch" class="form-control fud-search" placeholder="Search staff ID or name…">
              <div class="table-responsive">
                <table class="table fud-table mb-0">
                  <thead><tr><th>Line</th><th>Staff</th><th>GIFMIS code</th><th>Appointed</th><th>Result</th></tr></thead>
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
    var form = document.getElementById('staffUpload'), input = document.getElementById('uploadFile');
    var checkBtn = document.getElementById('checkFile'), importBtn = document.getElementById('importFile');
    var msg = document.querySelector('.msg'), last = null, checkedFile = null, filterKey = 'all';
    var esc = function (s) { var d = document.createElement('div'); d.textContent = s == null ? '' : String(s); return d.innerHTML; };

    input.addEventListener('change', function () {
        document.getElementById('uploadFileName').textContent = input.files.length ? input.files[0].name : 'Choose the .csv file (up to 2MB)';
        importBtn.disabled = true;
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
        fetch('uploadStaff.php', { method: 'POST', body: data, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                checkBtn.disabled = false;
                if (!res.ok) { alertBox(false, res.message); return; }
                last = res;
                var s = res.summary, ready = s.create + s.codes_added;
                alertBox(mode === 'import' ? (s.saved + s.codes_saved) > 0 : ready > 0, res.message);
                if (mode === 'check') { checkedFile = input.files[0]; importBtn.disabled = !ready; importBtn.innerHTML = '<i class="mdi mdi-database-import"></i> Import ' + s.create + ' new staff'; }
                render(res);
            })
            .catch(function () { checkBtn.disabled = false; alertBox(false, 'Network or server error. Please try again.'); });
    }
    form.addEventListener('submit', function (e) { e.preventDefault(); send('check'); });
    importBtn.addEventListener('click', function () {
        if (!last || checkedFile !== input.files[0]) { alertBox(false, 'Check the file before importing.'); return; }
        var s = last.summary;
        if (!confirm('Add ' + s.create + ' new staff to the staff list' + (s.codes_added ? ' and add the GIFMIS code for ' + s.codes_added + ' existing staff' : '') + '?\n\n'
            + s.logins + ' login(s) will be created (first password = staff ID).\nRows with problems are not saved.')) { return; }
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
        box.innerHTML = stat('mdi-account-plus', 'fud-green', imported ? 'Added' : 'New staff', imported ? s.saved : s.create, s.logins + ' login(s)')
            + stat('mdi-account-check', 'fud-purple', 'Already on the list', s.existing, s.codes_added ? s.codes_added + ' get a GIFMIS code' : 'not changed')
            + stat('mdi-alert-circle-outline', s.error ? 'fud-rose' : 'fud-green', 'Problems', s.error, s.error ? 'not saved' : 'none');
        document.getElementById('upTitle').textContent = (imported ? 'Import result: ' : 'Check result: ') + s.rows + ' row(s)';
        var html = '';
        res.rows.forEach(function (r) {
            var pill, notes = '';
            if (imported && r.saved) { pill = '<span class="fud-pill ok">' + (r.action === 'create' ? 'Added' : 'Code added') + '</span>'; }
            else if (r.action === 'error') { pill = '<span class="fud-pill no">Problem</span>'; }
            else if (r.action === 'existing') { pill = '<span class="fud-pill wait">Already on the list</span>' + (r.add_code && !imported ? ' <span class="fud-pill ok">add code</span>' : ''); }
            else if (imported) { pill = '<span class="fud-pill no">Not saved</span>'; }
            else { pill = '<span class="fud-pill ok">New staff</span>'; }
            (r.errors || []).forEach(function (e) { notes += '<div class="small text-danger">' + esc(e) + '</div>'; });
            (r.warnings || []).forEach(function (w) { notes += '<div class="small text-muted">' + esc(w) + '</div>'; });
            if (imported && r.result && !r.saved && r.action === 'create') { notes += '<div class="small text-danger">' + esc(r.result) + '</div>'; }
            var who = r.action === 'existing' && r.existing_name ? r.existing_name : r.name;
            html += '<tr data-f="' + r.action + '"' + (r.action === 'error' ? ' class="fud-row-problem"' : '') + '><td>' + r.line + '</td>'
                + '<td style="white-space:normal;min-width:200px"><strong>' + esc(who || r.file_name || '–') + '</strong><div class="small text-muted">' + esc(r.staff) + '</div></td>'
                + '<td class="small">' + esc(r.code || '–') + '</td><td class="small text-nowrap">' + esc(r.doa || '–') + '</td>'
                + '<td style="white-space:normal;min-width:240px">' + pill + notes + '</td></tr>';
        });
        document.getElementById('upRows').innerHTML = html;
        document.getElementById('upResults').style.display = '';
        document.getElementById('downloadProblems').style.display = s.error ? '' : 'none';
        filter('all');
    }
    function filter(f) {
        filterKey = f;
        var q = document.getElementById('upSearch').value.toLowerCase();
        document.querySelectorAll('#upFilter a').forEach(function (a) { a.classList.toggle('active', a.getAttribute('data-f') === f); });
        document.querySelectorAll('#upRows tr').forEach(function (tr) {
            tr.style.display = (f === 'all' || tr.getAttribute('data-f') === f) && tr.textContent.toLowerCase().indexOf(q) !== -1 ? '' : 'none';
        });
    }
    document.querySelectorAll('#upFilter a').forEach(function (a) { a.addEventListener('click', function (e) { e.preventDefault(); filter(a.getAttribute('data-f')); }); });
    document.getElementById('upSearch').addEventListener('input', function () { filter(filterKey); });
    document.getElementById('downloadProblems').addEventListener('click', function () {
        if (!last) { return; }
        var q = function (v) { v = v == null ? '' : String(v); return /[",\r\n]/.test(v) ? '"' + v.replace(/"/g, '""') + '"' : v; };
        var lines = ['STAFF ID,GIFMIS CODE,FULL NAME,DATE OF APPOINTMENT,DEPARTMENT,CADRE,GENDER,PHONE,EMAIL,PROBLEM'];
        last.rows.filter(function (r) { return r.action === 'error'; }).forEach(function (r) {
            var i = r.input;
            lines.push([i.staff, i.code, i.name, i.doa, i.dept, i.cadre, i.gender, i.phone, i.email, r.errors.join(' ')].map(q).join(','));
        });
        var a = document.createElement('a');
        a.href = URL.createObjectURL(new Blob(['﻿' + lines.join('\r\n')], { type: 'text/csv' }));
        a.download = 'staff_upload_problems.csv';
        document.body.appendChild(a); a.click(); a.remove();
    });
})();
</script>
