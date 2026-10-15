<?php
// Officers' loans page, shared by Treasurer/, Chairman/ and Secretary/ loans.php.
// Expects: $view, $role ('treasurer' / 'chairman' / 'secretary'), $sideNav (View method for the role's menu).
// ?tab=<key> filters by stage (MemberG3::officerLoanTabs); ?export=csv downloads the current tab.
require_once __DIR__ . '/../classes/MemberG3.php';

$g3 = new MemberG3();
$tabs = MemberG3::officerLoanTabs($role);
$tab = isset($_GET['tab'], $tabs[$_GET['tab']]) ? $_GET['tab'] : 'all';
$all = $g3->officerLoans();

$inTab = function ($loan, $key) use ($tabs) {
    if ($key === 'all') {
        return true;
    }
    if ($key === 'overdue') {
        return $loan['arrears'] > 0;
    }
    return in_array($loan['stage'], $tabs[$key][1], true);
};
$counts = [];
foreach ($tabs as $key => $t) {
    $counts[$key] = count(array_filter($all, function ($l) use ($inTab, $key) { return $inTab($l, $key); }));
}
$rows = array_values(array_filter($all, function ($l) use ($inTab, $tab) { return $inTab($l, $tab); }));
$who = function ($l) { return preg_replace('/\s+/', ' ', trim($l['title'] . ' ' . $l['fname'] . ' ' . $l['lname'] . ' ' . $l['oname'])); };

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="loans_' . $tab . '_' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Loan', 'Staff number', 'Member', 'Department', 'Type', 'Applied on', 'Amount applied', 'Approved', 'Interest',
                   'Total to repay', 'Months', 'Repaid', 'Balance', 'Behind by', 'Stage']);
    foreach ($rows as $l) {
        fputcsv($out, [$l['loan_id'], $l['sp_no'], $who($l), $l['dept'], $l['loan_type_name'], substr($l['loan_date'], 0, 10),
                       $l['loan_amount'], $l['fixed'] ? $l['amount_recommended'] : '', $l['fixed'] ? $l['interest_amount'] : '',
                       $l['fixed'] || $l['is_debt'] ? round($l['total'], 2) : '', $l['loan_tenor'], $l['is_debt'] ? $l['repaid'] : '',
                       $l['is_debt'] ? round($l['balance'], 2) : '', $l['arrears'] ?: '', $l['stage']]);
    }
    fclose($out);
    exit();
}

$repaying = array_filter($all, function ($l) { return $l['stage'] === 'Repaying'; });
$owed = array_sum(array_column($repaying, 'balance'));
$overdueCount = $counts['overdue'];
$h = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
$pill = function ($stage) { return in_array($stage, ['Repaying', 'Fully repaid'], true) ? 'ok' : (strpos($stage, 'Declined') === 0 ? 'no' : 'wait'); };
$todo = ['treasurer' => [['review', 'Applications to review', 'mdi-clipboard-check-outline'], ['payout', 'Loans to pay out', 'mdi-bank-transfer']],
         'chairman' => [['authorize', 'Loans to authorize', 'mdi-gavel']],
         'secretary' => [['review', 'With the Treasurer', 'mdi-clipboard-check-outline'], ['authorize', 'With the Chairman', 'mdi-gavel']]];
$intro = ['treasurer' => 'Review applications, fix the amount, record payments and follow repayments.',
          'chairman'  => 'Authorize the amounts the Treasurer has fixed, and follow repayments.',
          'secretary' => 'View loan applications and repayments. Decisions are taken by the Treasurer and the Chairman.'];

echo $view->subHeader('../');
?>
    <?php echo $view->subPartialNav('../'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->$sideNav(); ?>
      <div class="main-panel">
        <div class="content-wrapper fud-dash">

          <div class="fud-hero">
            <div class="fud-hero-date">Loans</div>
            <h2><i class="mdi mdi-cash-multiple"></i> Loans</h2>
            <p><?php echo $intro[$role]; ?></p>
            <div class="fud-hero-actions">
              <a href="overdue_loans.php"><i class="mdi mdi-alert-circle"></i> Overdue loans</a>
              <?php if ($role === 'treasurer') { ?>
                <a href="loan_deduction.php"><i class="mdi mdi-file-document-box"></i> Loan deductions</a>
                <a href="batch_loan_deduction.php"><i class="mdi mdi-cloud-upload"></i> Upload deductions</a>
              <?php } ?>
            </div>
          </div>

          <div class="fud-stats">
            <?php foreach ($todo[$role] as $t) { ?>
              <a class="fud-stat fud-stat-link" href="?tab=<?php echo $t[0]; ?>">
                <div class="fud-stat-icon <?php echo $counts[$t[0]] ? 'fud-amber' : 'fud-green'; ?>"><i class="mdi <?php echo $t[2]; ?>"></i></div>
                <div><div class="fud-stat-label"><?php echo $t[1]; ?></div><div class="fud-stat-value"><?php echo $counts[$t[0]]; ?></div></div>
              </a>
            <?php } ?>
            <a class="fud-stat fud-stat-link" href="?tab=repaying">
              <div class="fud-stat-icon fud-blue"><i class="mdi mdi-cash-multiple"></i></div>
              <div><div class="fud-stat-label">Still owed to the cooperative</div><div class="fud-stat-value">₦<?php echo number_format($owed, 2); ?></div>
                <div class="fud-stat-sub"><?php echo count($repaying); ?> loans being repaid</div></div>
            </a>
            <a class="fud-stat fud-stat-link" href="?tab=overdue">
              <div class="fud-stat-icon <?php echo $overdueCount ? 'fud-rose' : 'fud-green'; ?>"><i class="mdi mdi-alert-circle"></i></div>
              <div><div class="fud-stat-label">Overdue</div><div class="fud-stat-value"><?php echo $overdueCount; ?></div></div>
            </a>
          </div>

          <div class="fud-section">
            <div class="fud-section-body">
              <div class="fud-tabs">
                <?php foreach ($tabs as $key => $t) { ?>
                  <a class="<?php echo $tab === $key ? 'active' : ''; ?>" href="?tab=<?php echo $key; ?>"><?php echo $h($t[0]); ?> <span><?php echo $counts[$key]; ?></span></a>
                <?php } ?>
              </div>

              <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <input type="search" id="loanSearch" class="form-control fud-search" placeholder="Search by name, staff number or loan #">
                <a class="btn btn-outline-secondary btn-sm" href="?tab=<?php echo $tab; ?>&amp;export=csv"><i class="mdi mdi-download"></i> Download CSV</a>
              </div>

              <?php if (!$rows) { ?>
                <p class="fud-empty">No loans here.</p>
              <?php } else { ?>
              <div class="table-responsive">
                <table class="table fud-table" id="loanList">
                  <thead><tr><th>#</th><th>Member</th><th>Loan</th><th>Applied</th><th class="num">Amount (₦)</th>
                    <th class="num">To repay (₦)</th><th class="num">Balance (₦)</th><th>Stage</th><th></th></tr></thead>
                  <tbody>
                  <?php foreach ($rows as $l) { ?>
                    <tr data-search="<?php echo $h(strtolower($who($l) . ' ' . $l['sp_no'] . ' #' . $l['loan_id'] . ' ' . $l['loan_id'])); ?>">
                      <td class="text-muted"><?php echo (int) $l['loan_id']; ?></td>
                      <td class="fud-wrap"><?php echo $h($who($l)); ?><div class="text-muted small"><?php echo $h($l['sp_no']); ?></div></td>
                      <td><?php echo $h($l['loan_type_name']); ?><div class="text-muted small"><?php echo (int) $l['loan_tenor']; ?> months</div></td>
                      <td><?php echo date('d M Y', strtotime(substr($l['loan_date'], 0, 10))); ?></td>
                      <td class="num"><?php echo number_format((float) ($l['fixed'] ? $l['amount_recommended'] : $l['loan_amount']), 2); ?>
                        <?php if ($l['fixed'] && abs((float) $l['amount_recommended'] - (float) $l['loan_amount']) >= 1) { ?><div class="text-muted small">applied <?php echo number_format((float) $l['loan_amount'], 2); ?></div><?php } ?></td>
                      <td class="num"><?php echo $l['fixed'] || $l['is_debt'] ? number_format($l['total'], 2) : '–'; ?></td>
                      <td class="num"><?php echo $l['is_debt'] ? number_format($l['balance'], 2) : '–'; ?></td>
                      <td><span class="fud-pill <?php echo $pill($l['stage']); ?>"><?php echo $h($l['stage']); ?></span>
                        <?php if ($l['arrears'] > 0) { ?><div><span class="fud-pill no">₦<?php echo number_format($l['arrears'], 2); ?> behind</span></div><?php } ?></td>
                      <td><a class="btn btn-sm btn-outline-primary" href="view_loan_details.php?loan_id=<?php echo (int) $l['loan_id']; ?>">Open</a></td>
                    </tr>
                  <?php } ?>
                  </tbody>
                </table>
              </div>
              <p class="text-muted small mb-0 mt-2" id="loanCount"><?php echo count($rows); ?> loan(s)</p>
              <?php } ?>
            </div>
          </div>

        </div>
        <?php echo $view->subFooter('../'); ?>

<script>
(function () {
    var search = document.getElementById('loanSearch');
    var rows = document.querySelectorAll('#loanList tbody tr');
    var count = document.getElementById('loanCount');
    if (!search || !rows.length) { return; }
    search.addEventListener('input', function () {
        var q = search.value.trim().toLowerCase(), shown = 0;
        rows.forEach(function (row) {
            var match = !q || row.getAttribute('data-search').indexOf(q) !== -1;
            row.style.display = match ? '' : 'none';
            if (match) { shown++; }
        });
        count.textContent = shown + ' loan(s)';
    });
})();
</script>
