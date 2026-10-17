<?php
    // Treasurer's member list: approved members with their savings, shares and loans (each opens member_profile.php),
    // and membership applicants (view_proposed_member_details.php fixes their monthly savings).
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    require_once('../config/classes/MemberG1.php');
    require_once('../config/classes/SavingsG1.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../auth/') || !User::isBur('../auth/logout.php')) {
        exit();
    }
    $members = new MemberG1();
    $h = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
    $money = function ($v) { return ((float) $v < 0 ? '−' : '') . '₦' . number_format(abs((float) $v), 2); };

    $registered = $members->treasurerMemberDirectory();
    $applicants = $members->getTreasurerApplicants();   // not yet fully approved: the Treasurer fixes the amount first
    $minMonthlySavings = SavingsG1::getMinMonthlySavings();
    $awaitingTreasurer = count(array_filter($applicants, function ($m) { return $m['treasurer_approval'] != 1; }));
    $totals = ['savings' => 0, 'shares' => 0, 'loans' => 0, 'borrowers' => 0, 'commodity' => 0];
    foreach ($registered as $m) {
        $totals['savings'] += $m['savings_balance'];
        $totals['shares'] += $m['shares'];
        $totals['loans'] += $m['loans_outstanding'];
        $totals['borrowers'] += $m['open_loans'] > 0 ? 1 : 0;
        $totals['commodity'] += $m['commodity_outstanding'];
    }
    $tab = isset($_GET['tab']) && $_GET['tab'] === 'applicants' ? 'applicants' : 'members';

    echo $view->subHeader('../');
?>
    <?php echo $view->subPartialNav('../'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->treasurerSideNav(); ?>
      <div class="main-panel">
        <div class="content-wrapper fud-dash">

          <div class="fud-hero">
            <div class="fud-hero-date">Members</div>
            <h2><i class="mdi mdi-account-group"></i> Members</h2>
            <p>Every approved member with their savings, shares and loans. Open a member to see their full record.
              Membership applications wait under Applicants until you fix their monthly savings.</p>
          </div>

          <div class="fud-stats">
            <div class="fud-stat"><div class="fud-stat-icon fud-blue"><i class="mdi mdi-account-multiple"></i></div>
              <div><div class="fud-stat-label">Members</div><div class="fud-stat-value"><?php echo number_format(count($registered)); ?></div>
                <div class="fud-stat-sub"><?php echo count($applicants); ?> applicant<?php echo count($applicants) === 1 ? '' : 's'; ?></div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-green"><i class="mdi mdi-safe"></i></div>
              <div><div class="fud-stat-label">Savings balance</div><div class="fud-stat-value"><?php echo $money($totals['savings']); ?></div>
                <div class="fud-stat-sub">all members</div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-purple"><i class="mdi mdi-chart-pie"></i></div>
              <div><div class="fud-stat-label">Shares</div><div class="fud-stat-value"><?php echo $money($totals['shares']); ?></div>
                <div class="fud-stat-sub">approved purchases</div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-amber"><i class="mdi mdi-cash-multiple"></i></div>
              <div><div class="fud-stat-label">Loans outstanding</div><div class="fud-stat-value"><?php echo $money($totals['loans']); ?></div>
                <div class="fud-stat-sub"><?php echo number_format($totals['borrowers']); ?> member<?php echo $totals['borrowers'] == 1 ? '' : 's'; ?> repaying</div></div></div>
          </div>

          <div class="fud-tabs" id="memberTabs">
            <a href="?tab=members" data-tab="members" class="<?php echo $tab === 'members' ? 'active' : ''; ?>">Members <span><?php echo number_format(count($registered)); ?></span></a>
            <a href="?tab=applicants" data-tab="applicants" class="<?php echo $tab === 'applicants' ? 'active' : ''; ?>">Applicants <span><?php echo count($applicants); ?></span>
              <?php if ($awaitingTreasurer) { ?><span class="badge badge-danger ml-1"><?php echo $awaitingTreasurer; ?> to review</span><?php } ?></a>
          </div>

          <!-- Members -->
          <div class="fud-section" data-panel="members"<?php echo $tab === 'members' ? '' : ' style="display:none"'; ?>>
            <div class="fud-section-head d-flex flex-wrap justify-content-between align-items-center">
              <h5><i class="mdi mdi-account-check"></i> Approved members</h5>
              <button type="button" class="btn btn-sm btn-light" id="exportMembers"><i class="mdi mdi-download"></i> Download CSV</button>
            </div>
            <div class="fud-section-body">
              <div class="d-flex flex-wrap align-items-center mb-2" style="gap:.5rem">
                <input class="form-control fud-search mb-0" type="search" id="memberSearch" placeholder="Search name, staff number, department…">
                <select class="form-control form-control-sm" id="memberFilter" style="max-width:220px">
                  <option value="">All members</option>
                  <option value="loans">Repaying a loan</option>
                  <option value="commodity">Owing for commodity</option>
                  <option value="shares">Shareholders</option>
                  <option value="noshares">No shares</option>
                  <option value="low">Savings below ₦<?php echo number_format($minMonthlySavings); ?></option>
                  <option value="negative">Negative savings balance</option>
                </select>
                <span class="small text-muted ml-auto" id="memberCount"></span>
              </div>
              <div class="table-responsive">
                <table class="table fud-table" id="memberTable">
                  <thead>
                    <tr>
                      <th data-sort="text">Member</th>
                      <th data-sort="text">Department</th>
                      <th class="num" data-sort="num">Monthly</th>
                      <th class="num" data-sort="num">Savings balance</th>
                      <th class="num" data-sort="num">Shares</th>
                      <th class="num" data-sort="num">Loans owed</th>
                      <th class="text-right"></th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php foreach ($registered as $m) {
                      $name = preg_replace('/\s+/', ' ', trim($m['fname'] . ' ' . $m['lname'] . ' ' . $m['oname']));
                      $flags = trim(($m['open_loans'] > 0 ? 'loans ' : '') . ($m['commodity_outstanding'] > 0 ? 'commodity ' : '')
                          . ($m['shares'] > 0 ? 'shares ' : 'noshares ') . ($m['savings_balance'] < $minMonthlySavings ? 'low ' : '') . ($m['savings_balance'] < 0 ? 'negative' : '')); ?>
                    <tr data-flags="<?php echo $flags; ?>" data-sp="<?php echo $h($m['sp_no']); ?>" data-com="<?php echo (float) $m['commodity_outstanding']; ?>">
                      <td data-v="<?php echo $h($name); ?>" style="white-space:normal;min-width:180px">
                        <a href="member_profile.php?member_id=<?php echo (int) $m['member_id']; ?>" class="font-weight-bold text-dark"><?php echo $h($name); ?></a>
                        <div class="small text-muted"><?php echo $h($m['sp_no']); ?><?php echo $m['cadre'] ? ' · ' . $h($m['cadre']) : ''; ?></div>
                      </td>
                      <td data-v="<?php echo $h($m['dept']); ?>" class="small" style="white-space:normal;min-width:140px"><?php echo $h($m['dept'] ?: '–'); ?></td>
                      <td class="num" data-v="<?php echo (float) $m['proposed_monthly_savings']; ?>"><?php echo $money($m['proposed_monthly_savings']); ?></td>
                      <td class="num" data-v="<?php echo (float) $m['savings_balance']; ?>"><strong<?php echo $m['savings_balance'] < 0 ? ' class="text-danger"' : ''; ?>><?php echo $money($m['savings_balance']); ?></strong></td>
                      <td class="num" data-v="<?php echo (float) $m['shares']; ?>"><?php echo $m['shares'] > 0 ? $money($m['shares']) : '<span class="text-muted">–</span>'; ?></td>
                      <td class="num" data-v="<?php echo (float) $m['loans_outstanding']; ?>">
                        <?php if ($m['open_loans'] > 0) { ?><?php echo $money($m['loans_outstanding']); ?>
                          <div class="small text-muted"><?php echo (int) $m['open_loans']; ?> loan<?php echo $m['open_loans'] == 1 ? '' : 's'; ?></div>
                        <?php } else { ?><span class="text-muted">–</span><?php } ?>
                        <?php if ($m['commodity_outstanding'] > 0) { ?><div class="small text-muted">+ <?php echo $money($m['commodity_outstanding']); ?> commodity</div><?php } ?>
                      </td>
                      <td class="text-right"><a href="member_profile.php?member_id=<?php echo (int) $m['member_id']; ?>" class="btn btn-sm btn-outline-primary text-nowrap"><i class="mdi mdi-account-details"></i> View record</a></td>
                    </tr>
                  <?php } ?>
                  </tbody>
                </table>
              </div>
              <p class="fud-empty" id="memberNone" style="display:none">No member matches your search.</p>
              <div class="d-flex justify-content-between align-items-center mt-2" id="memberPager">
                <button type="button" class="btn btn-sm btn-light" data-page="-1"><i class="mdi mdi-chevron-left"></i> Previous</button>
                <span class="small text-muted" id="memberPage"></span>
                <button type="button" class="btn btn-sm btn-light" data-page="1">Next <i class="mdi mdi-chevron-right"></i></button>
              </div>
            </div>
          </div>

          <!-- Applicants -->
          <div class="fud-section" data-panel="applicants"<?php echo $tab === 'applicants' ? '' : ' style="display:none"'; ?>>
            <div class="fud-section-head"><h5><i class="mdi mdi-account-clock"></i> Membership applications</h5></div>
            <div class="fud-section-body">
              <p class="small text-muted">
                Review each applicant and fix their monthly savings (minimum ₦<?php echo number_format($minMonthlySavings); ?>,
                <a href="savings_settings.php">change minimum</a>). The application then goes to the Chairman, and you can still
                change the amount until the Chairman authorizes it.
              </p>
              <?php if (!$applicants) { ?>
                <p class="fud-empty"><i class="mdi mdi-check-circle-outline"></i> No applications waiting.</p>
              <?php } else { ?>
                <input class="form-control fud-search" type="search" id="applicantSearch" placeholder="Search applicants…">
                <div class="table-responsive">
                  <table class="table fud-table" id="applicantTable">
                    <thead><tr><th>Applicant</th><th>Department</th><th>Status</th><th class="num">Monthly savings</th><th class="text-right"></th></tr></thead>
                    <tbody>
                    <?php foreach ($applicants as $m) { ?>
                      <tr>
                        <td style="white-space:normal;min-width:180px"><strong><?php echo $h(trim($m['fname'] . ' ' . $m['lname'])); ?></strong>
                          <div class="small text-muted"><?php echo $h($m['sp_no']); ?><?php echo $m['cadre'] ? ' · ' . $h($m['cadre']) : ''; ?></div></td>
                        <td class="small" style="white-space:normal"><?php echo $h($m['dept'] ?: '–'); ?></td>
                        <td><?php echo $m['treasurer_approval'] == 1 ? '<span class="fud-pill wait">Awaiting Chairman</span>' : '<span class="fud-pill no">Awaiting you</span>'; ?></td>
                        <td class="num"><?php echo $money($m['proposed_monthly_savings']); ?></td>
                        <td class="text-right">
                          <a href="view_proposed_member_details.php?employee_id=<?php echo (int) $m['employee_id']; ?>"
                             class="btn btn-sm text-nowrap <?php echo $m['treasurer_approval'] == 1 ? 'btn-outline-primary' : 'btn-primary'; ?>">
                            <?php echo $m['treasurer_approval'] == 1 ? 'Review / change amount' : 'Review &amp; approve'; ?></a>
                        </td>
                      </tr>
                    <?php } ?>
                    </tbody>
                  </table>
                </div>
              <?php } ?>
            </div>
          </div>

        </div>
        <?php echo $view->subFooter('../'); ?>

<script>
(function () {
    // Tabs
    var tabs = document.querySelectorAll('#memberTabs a');
    tabs.forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            var t = a.getAttribute('data-tab');
            tabs.forEach(function (x) { x.classList.toggle('active', x === a); });
            document.querySelectorAll('[data-panel]').forEach(function (p) { p.style.display = p.getAttribute('data-panel') === t ? '' : 'none'; });
            if (history.replaceState) { history.replaceState(null, '', '?tab=' + t); }
        });
    });

    // Members: search, filter, sort, pages of 25, CSV of the matching rows
    var table = document.getElementById('memberTable');
    var rows = Array.prototype.slice.call(table.tBodies[0].rows);
    var search = document.getElementById('memberSearch'), filter = document.getElementById('memberFilter');
    var page = 0, size = 25, matching = rows;
    function apply() {
        var q = search.value.toLowerCase().trim(), f = filter.value;
        matching = rows.filter(function (r) {
            return (!q || r.textContent.toLowerCase().indexOf(q) !== -1) && (!f || (' ' + r.getAttribute('data-flags') + ' ').indexOf(' ' + f + ' ') !== -1);
        });
        var pages = Math.max(1, Math.ceil(matching.length / size));
        page = Math.min(page, pages - 1);
        rows.forEach(function (r) { r.style.display = 'none'; });
        matching.slice(page * size, page * size + size).forEach(function (r) { r.style.display = ''; });
        document.getElementById('memberCount').textContent = matching.length.toLocaleString() + ' of ' + rows.length.toLocaleString() + ' members';
        document.getElementById('memberPage').textContent = 'Page ' + (page + 1) + ' of ' + pages;
        document.getElementById('memberNone').style.display = matching.length ? 'none' : '';
        document.querySelector('#memberPager [data-page="-1"]').disabled = page === 0;
        document.querySelector('#memberPager [data-page="1"]').disabled = page >= pages - 1;
    }
    search.addEventListener('input', function () { page = 0; apply(); });
    filter.addEventListener('change', function () { page = 0; apply(); });
    document.querySelectorAll('#memberPager [data-page]').forEach(function (b) {
        b.addEventListener('click', function () { page += parseInt(b.getAttribute('data-page'), 10); apply(); table.scrollIntoView({ block: 'start' }); });
    });
    table.querySelectorAll('th[data-sort]').forEach(function (th, i) {
        th.style.cursor = 'pointer';
        th.title = 'Sort';
        th.addEventListener('click', function () {
            var num = th.getAttribute('data-sort') === 'num', dir = th.getAttribute('data-dir') === 'asc' ? -1 : 1;
            table.querySelectorAll('th[data-sort]').forEach(function (x) { x.removeAttribute('data-dir'); x.textContent = x.textContent.replace(/ [▲▼]$/, ''); });
            th.setAttribute('data-dir', dir === 1 ? 'asc' : 'desc');
            th.textContent += dir === 1 ? ' ▲' : ' ▼';
            rows.sort(function (a, b) {
                var x = a.cells[i].getAttribute('data-v'), y = b.cells[i].getAttribute('data-v');
                return (num ? (parseFloat(x) - parseFloat(y)) : x.localeCompare(y)) * dir;
            });
            rows.forEach(function (r) { table.tBodies[0].appendChild(r); });
            page = 0;
            apply();
        });
    });
    document.getElementById('exportMembers').addEventListener('click', function () {
        var esc = function (v) { v = String(v).replace(/\s+/g, ' ').trim(); return /[",\n]/.test(v) ? '"' + v.replace(/"/g, '""') + '"' : v; };
        var lines = [['Name', 'Staff number', 'Department', 'Monthly savings', 'Savings balance', 'Shares', 'Loans owed', 'Commodity owed'].join(',')];
        matching.forEach(function (r) {
            var c = r.cells;
            lines.push([c[0].getAttribute('data-v'), r.getAttribute('data-sp'), c[1].getAttribute('data-v'), c[2].getAttribute('data-v'),
                        c[3].getAttribute('data-v'), c[4].getAttribute('data-v'), c[5].getAttribute('data-v'), r.getAttribute('data-com')].map(esc).join(','));
        });
        var a = document.createElement('a');
        a.href = URL.createObjectURL(new Blob(['﻿' + lines.join('\r\n')], { type: 'text/csv' }));
        a.download = 'members_' + new Date().toISOString().slice(0, 10) + '.csv';
        document.body.appendChild(a); a.click(); a.remove();
    });
    apply();

    // Applicants search
    var as = document.getElementById('applicantSearch');
    if (as) {
        as.addEventListener('input', function () {
            var q = as.value.toLowerCase();
            document.querySelectorAll('#applicantTable tbody tr').forEach(function (r) { r.style.display = r.textContent.toLowerCase().indexOf(q) !== -1 ? '' : 'none'; });
        });
    }
})();
</script>
