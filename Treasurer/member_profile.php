<?php
    // One member's full record for the Treasurer (from member_list.php): personal details, savings statement,
    // withdrawals, shares, loans, loans they guarantee and commodity. Read-only; each item links to its own page.
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    require_once('../config/classes/Dashboard.php');
    require_once('../config/classes/LoanControls.php');
    require_once('../config/classes/MemberG2.php');
    require_once('../config/classes/MemberG3.php');
    require_once('../config/classes/CommodityLoan.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../auth/') || !User::isBur('../auth/logout.php')) {
        exit();
    }
    $h = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
    $money = function ($v) { return ((float) $v < 0 ? '−' : '') . '₦' . number_format(abs((float) $v), 2); };
    $day = function ($d) { return $d && substr($d, 0, 4) > '1990' ? date('d M Y', strtotime($d)) : '–'; };

    $memberId = isset($_GET['member_id']) ? (int) $_GET['member_id'] : 0;
    $con = (new DB())->getConnection();
    $stmt = $con->prepare("SELECT m.*, e.* , m.employee_id AS employee_id FROM fudscoops_member m
                           JOIN employee e ON e.employee_id = m.employee_id WHERE m.member_id = :m");
    $stmt->execute([':m' => $memberId]);
    $m = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($m) {
        $employeeId = (int) $m['employee_id'];
        $name = preg_replace('/\s+/', ' ', trim($m['title'] . ' ' . $m['fname'] . ' ' . $m['lname'] . ' ' . $m['oname']));
        $approved = $m['treasurer_approval'] == 1 && $m['chairman_approval'] == 1;

        // Savings
        $statement = (new Dashboard())->memberStatement($memberId, $employeeId);
        $balance = LoanControls::savingsBalance($memberId);
        $saved = array_sum(array_column($statement['lines'], 'credit'));
        $withdrawn = array_sum(array_column($statement['lines'], 'debit'));
        $max = LoanControls::maxWithdrawal($memberId);

        // Withdrawal requests (every stage)
        $stmt = $con->prepare("SELECT w.*, b.name AS bank_name FROM fudscoops_withrawals w LEFT JOIN banks b ON b.id = w.bank_id
                               WHERE w.member_id = :m AND COALESCE(w.withrawals_is_deleted, 0) = 0 ORDER BY w.withrawals_id DESC");
        $stmt->execute([':m' => $memberId]);
        $withdrawals = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = $con->prepare("SELECT * FROM fudscoops_complete_withrawal WHERE employee_id = :e AND COALESCE(complete_withrawal_is_deleted, 0) = 0
                               ORDER BY comp_withdrwal_id DESC");
        $stmt->execute([':e' => $employeeId]);
        $completes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Shares
        $shareHolding = MemberG2::getShareHolding($memberId);
        $unitPrice = (float) $con->query("SELECT price FROM fudscoops_share_unit ORDER BY shares_unit_id LIMIT 1")->fetchColumn();
        $stmt = $con->prepare("SELECT * FROM fudscoops_shares WHERE member_id = :m AND shares_is_deleted = 0 ORDER BY date DESC, shares_id DESC");
        $stmt->execute([':m' => $memberId]);
        $shares = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Loans, guarantees, commodity
        $g3 = new MemberG3();
        $loans = $g3->myLoans($memberId);
        $loanOwed = 0;
        $openLoans = 0;
        foreach ($loans as $l) {
            if ($l['stage'] === 'Repaying') {
                $loanOwed += $l['balance'];
                $openLoans++;
            }
        }
        $guarantees = $g3->guaranteeRequests($employeeId);
        $activeGuarantees = count(array_filter($guarantees, function ($g) { return $g['stage'] === 'Repaying'; }));
        $commodity = CommodityLoan::memberRequests($memberId);
        $commodityOwed = 0;
        foreach ($commodity as $c) {
            $commodityOwed += $c['status'] === 'supplied' ? $c['balance'] : 0;
        }
        $overdue = LoanControls::overdueLoans($memberId);
        $overpayments = LoanControls::overpayments(null, $memberId);

        // Payslips for the last three months of this year (slip.php only serves the current year)
        $payslips = [];
        for ($k = 1; $k <= 3; $k++) {
            $time = strtotime("first day of -$k month");
            if (date('Y', $time) == date('Y')) {
                $payslips[date('n', $time)] = date('F', $time);
            }
        }
        $loanPill = function ($stage) {
            $class = strpos($stage, 'Declined') === 0 ? 'no' : (in_array($stage, ['Repaying', 'Fully repaid'], true) ? 'ok' : 'wait');
            return '<span class="fud-pill ' . $class . '">' . htmlspecialchars($stage) . '</span>';
        };
        $withdrawalPill = function ($w) {
            if ((int) $w['status'] === -1) { return '<span class="fud-pill no">Rejected by Treasurer</span>'; }
            if ((int) $w['chairman_approval'] === -1) { return '<span class="fud-pill no">Rejected by Chairman</span>'; }
            if ((int) $w['chairman_approval'] === 1) { return '<span class="fud-pill ok">Approved</span>'; }
            return '<span class="fud-pill wait">' . ((int) $w['status'] === 1 ? 'Awaiting Chairman' : 'Awaiting Treasurer') . '</span>';
        };
        $tabs = [
            'savings' => ['Savings', count($statement['lines'])],
            'withdrawals' => ['Withdrawals', count($withdrawals) + count($completes)],
            'shares' => ['Shares', count($shares)],
            'loans' => ['Loans', count($loans)],
            'guarantees' => ['Guarantees', count($guarantees)],
            'commodity' => ['Commodity', count($commodity)],
        ];
    }
    echo $view->subHeader('../');
?>
    <?php echo $view->subPartialNav('../'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->treasurerSideNav(); ?>
      <div class="main-panel">
        <div class="content-wrapper fud-dash fud-apply">

          <?php if (!$m) { ?>
            <div class="fud-notice"><i class="mdi mdi-alert-circle"></i><span>Member not found.</span>
              <a href="member_list.php">Members <i class="mdi mdi-arrow-right"></i></a></div>
          <?php } else { ?>

          <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div>
              <h3 class="mb-1"><?php echo $h($name); ?>
                <?php echo $approved ? '<span class="fud-pill ok" style="font-size:.8rem">Member</span>' : '<span class="fud-pill wait" style="font-size:.8rem">Applicant</span>'; ?></h3>
              <div class="text-muted"><?php echo $h($m['sp_no']); ?><?php echo $m['dept'] ? ' · ' . $h($m['dept']) : ''; ?> · member since <?php echo $day($m['reg_date']); ?></div>
            </div>
            <a href="member_list.php" class="btn btn-light btn-sm"><i class="mdi mdi-arrow-left"></i> All members</a>
          </div>

          <div class="fud-stats">
            <div class="fud-stat"><div class="fud-stat-icon fud-green"><i class="mdi mdi-safe"></i></div>
              <div><div class="fud-stat-label">Savings balance</div><div class="fud-stat-value<?php echo $balance < 0 ? ' text-danger' : ''; ?>"><?php echo $money($balance); ?></div>
                <div class="fud-stat-sub"><?php echo $money($m['proposed_monthly_savings']); ?> a month · <?php echo $money(max(0, $max)); ?> withdrawable</div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-purple"><i class="mdi mdi-chart-pie"></i></div>
              <div><div class="fud-stat-label">Shares</div><div class="fud-stat-value"><?php echo $money($shareHolding); ?></div>
                <div class="fud-stat-sub"><?php echo $unitPrice > 0 ? number_format($shareHolding / $unitPrice, 2) . ' units at ' . $money($unitPrice) : '&nbsp;'; ?></div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon <?php echo $overdue ? 'fud-rose' : 'fud-amber'; ?>"><i class="mdi mdi-cash-multiple"></i></div>
              <div><div class="fud-stat-label">Loans owed</div><div class="fud-stat-value"><?php echo $money($loanOwed); ?></div>
                <div class="fud-stat-sub"><?php echo $openLoans; ?> being repaid<?php echo $commodityOwed > 0 ? ' · ' . $money($commodityOwed) . ' commodity' : ''; ?></div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-blue"><i class="mdi mdi-shield-account"></i></div>
              <div><div class="fud-stat-label">Guaranteeing</div><div class="fud-stat-value"><?php echo $activeGuarantees; ?></div>
                <div class="fud-stat-sub">active loan<?php echo $activeGuarantees === 1 ? '' : 's'; ?> for others</div></div></div>
          </div>

          <?php if ($balance < 0) { ?>
            <div class="fud-notice"><i class="mdi mdi-alert-circle"></i>
              <span>Withdrawals (<?php echo $money($withdrawn); ?>) are more than the savings recorded (<?php echo $money($saved); ?>). Some savings may not have been uploaded; check the statement below.</span></div>
          <?php } ?>
          <?php foreach ($overdue as $od) { ?>
            <div class="fud-notice"><i class="mdi mdi-alert-circle"></i>
              <span><?php echo $h($od['loan_type_name']); ?> #<?php echo (int) $od['loan_id']; ?> is overdue: <?php echo $money($od['arrears']); ?> behind
                (<?php echo (int) $od['missed']; ?> month<?php echo $od['missed'] == 1 ? '' : 's'; ?> missed).</span>
              <a href="view_loan_details.php?loan_id=<?php echo (int) $od['loan_id']; ?>">Loan <i class="mdi mdi-arrow-right"></i></a></div>
          <?php } ?>
          <?php foreach ($overpayments as $op) { ?>
            <div class="fud-notice info"><i class="mdi mdi-cash-refund"></i>
              <span><?php echo $h($op['loan_type_name']); ?> overpaid by <?php echo $money($op['amount']); ?>
                <?php echo $op['status'] === 'open' ? '(waiting for the member to choose)' : '(member has chosen; to settle)'; ?>.</span>
              <a href="overpayments.php">Overpayments <i class="mdi mdi-arrow-right"></i></a></div>
          <?php } ?>
          <?php if ($statement['pending']) { ?>
            <div class="fud-notice"><i class="mdi mdi-clock-outline"></i>
              <span>Not yet in the balance: <?php echo $h(implode('; ', array_map(function ($p) use ($money) { return $p['label'] . ' ' . $money(abs($p['amount'])); }, $statement['pending']))); ?>.</span></div>
          <?php } ?>

          <div class="row">
            <div class="col-lg-4">
              <div class="fud-section">
                <div class="fud-section-head"><h5><i class="mdi mdi-account"></i> Personal details</h5></div>
                <div class="fud-section-body">
                  <div class="fud-facts">
                    <div><span>Staff number</span><?php echo $h($m['sp_no']); ?></div>
                    <div><span>Department / unit</span><?php echo $h($m['dept'] ?: '–'); ?></div>
                    <div><span>Cadre</span><?php echo $h($m['cadre'] ?: '–'); ?></div>
                    <div><span>Rank</span><?php echo $h($m['rank'] ?: '–'); ?></div>
                    <div><span>Grade level / step</span><?php echo $h(trim($m['grade_level'] . ' / ' . $m['step'], ' /') ?: '–'); ?></div>
                    <div><span>Appointment</span><?php echo $h($m['nature_of_appo'] ?: '–'); ?></div>
                    <div><span>Phone</span><?php echo $h($m['phone_no'] ?: '–'); ?></div>
                    <div><span>Email</span><?php echo $h($m['email'] ?: ($m['corporate_mail'] ?: '–')); ?></div>
                    <div><span>Home address</span><?php echo $h($m['permanent_address'] ?: '–'); ?></div>
                  </div>
                  <?php if ($payslips) { ?>
                    <p class="mt-3 mb-0"><strong>Payslips:</strong>
                      <?php foreach ($payslips as $mo => $mn) { ?><a class="ml-2" href="slip.php?legacy=<?php echo urlencode($m['sp_no']); ?>&s=<?php echo $mo; ?>"><?php echo $mn; ?></a><?php } ?></p>
                  <?php } ?>
                </div>
              </div>
              <div class="fud-section">
                <div class="fud-section-head"><h5><i class="mdi mdi-account-heart"></i> Next of kin</h5></div>
                <div class="fud-section-body">
                  <div class="fud-facts">
                    <div><span>Name</span><?php echo $h($m['next_of_kin_name'] ?: '–'); ?></div>
                    <div><span>Phone</span><?php echo $h($m['next_of_kin_gsm'] ?: '–'); ?></div>
                    <div><span>Address</span><?php echo $h($m['next_of_kin_address'] ?: '–'); ?></div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-lg-8">
              <div class="fud-tabs" id="recordTabs">
                <?php $first = true; foreach ($tabs as $key => $t) { ?>
                  <a href="#<?php echo $key; ?>" data-tab="<?php echo $key; ?>" class="<?php echo $first ? 'active' : ''; ?>"><?php echo $t[0]; ?> <span><?php echo $t[1]; ?></span></a>
                <?php $first = false; } ?>
              </div>

              <!-- Savings statement -->
              <div class="fud-section" data-panel="savings">
                <div class="fud-section-head"><h5><i class="mdi mdi-book-open-variant"></i> Savings statement</h5></div>
                <div class="fud-section-body">
                  <div class="fud-facts mb-3">
                    <div><span>Total saved</span><?php echo $money($saved); ?></div>
                    <div><span>Total withdrawn</span><?php echo $money($withdrawn); ?></div>
                    <div><span>Balance</span><strong><?php echo $money($statement['balance']); ?></strong></div>
                  </div>
                  <?php if (!$statement['lines']) { ?><p class="fud-empty">No savings recorded.</p><?php } else { ?>
                  <div class="table-responsive" style="max-height:520px;overflow-y:auto">
                    <table class="table fud-table mb-0">
                      <thead><tr><th>Date</th><th>Description</th><th class="num">Amount</th><th class="num">Balance</th></tr></thead>
                      <tbody>
                      <?php foreach (array_reverse($statement['lines']) as $l) { ?>
                        <tr><td class="text-nowrap"><?php echo $l['exact'] ? date('d M Y', strtotime($l['date'])) : date('M Y', strtotime($l['date'])); ?></td>
                          <td><?php echo $h($l['label']); ?></td>
                          <td class="num <?php echo $l['credit'] ? 'text-success' : 'text-danger'; ?>"><?php echo ($l['credit'] ? '+' : '−') . number_format($l['credit'] ?: $l['debit'], 2); ?></td>
                          <td class="num"><?php echo number_format($l['balance'], 2); ?></td></tr>
                      <?php } ?>
                      </tbody>
                    </table>
                  </div>
                  <?php } ?>
                </div>
              </div>

              <!-- Withdrawals -->
              <div class="fud-section" data-panel="withdrawals" style="display:none">
                <div class="fud-section-head"><h5><i class="mdi mdi-bank-transfer-out"></i> Withdrawal requests</h5></div>
                <div class="fud-section-body">
                  <?php if (!$withdrawals && !$completes) { ?><p class="fud-empty">No withdrawal requests.</p><?php } else { ?>
                  <div class="table-responsive">
                    <table class="table fud-table mb-0">
                      <thead><tr><th>Requested</th><th class="num">Asked</th><th class="num">Approved</th><th>Pay into</th><th>Status</th><th></th></tr></thead>
                      <tbody>
                      <?php foreach ($completes as $c) { ?>
                        <tr><td class="text-nowrap"><?php echo $day($c['date_applied']); ?><div class="small text-muted">Complete withdrawal</div></td>
                          <td class="num">–</td>
                          <td class="num"><?php echo (float) $c['approved_amount'] > 0 ? $money($c['approved_amount']) : '–'; ?></td>
                          <td class="small"><?php echo $h($c['acount_number']); ?></td>
                          <td><?php echo $c['chairman_approval'] == 1 ? '<span class="fud-pill ok">Approved</span>' : '<span class="fud-pill wait">Pending</span>'; ?></td>
                          <td class="text-right"><a href="view_com_withdrawal_details.php?withrawals_id=<?php echo (int) $c['comp_withdrwal_id']; ?>&sp_no=<?php echo urlencode($m['sp_no']); ?>">Open</a></td></tr>
                      <?php } ?>
                      <?php foreach ($withdrawals as $w) { ?>
                        <tr><td class="text-nowrap"><?php echo $day($w['proposed_date'] ?: $w['approved_date']); ?></td>
                          <td class="num"><?php echo $w['proposed_withrawal_amount'] !== null ? $money($w['proposed_withrawal_amount']) : '–'; ?></td>
                          <td class="num"><?php echo (float) $w['approved_withrawal_amount'] > 0 ? $money($w['approved_withrawal_amount']) : '–'; ?></td>
                          <td class="small" style="white-space:normal"><?php echo $h($w['bank_name'] ?: $w['bank_id']); ?><div class="text-muted"><?php echo $h($w['acount_number']); ?></div></td>
                          <td><?php echo $withdrawalPill($w); ?></td>
                          <td class="text-right"><a href="view_withdrawal_details.php?withrawals_id=<?php echo (int) $w['withrawals_id']; ?>">Open</a></td></tr>
                      <?php } ?>
                      </tbody>
                    </table>
                  </div>
                  <?php } ?>
                </div>
              </div>

              <!-- Shares -->
              <div class="fud-section" data-panel="shares" style="display:none">
                <div class="fud-section-head"><h5><i class="mdi mdi-chart-pie"></i> Shares</h5></div>
                <div class="fud-section-body">
                  <?php if (!$shares) { ?><p class="fud-empty">No share purchases.</p><?php } else { ?>
                  <div class="table-responsive">
                    <table class="table fud-table mb-0">
                      <thead><tr><th>Date</th><th>Entry</th><th class="num">Amount</th><th class="num">Units</th><th>Status</th><th></th></tr></thead>
                      <tbody>
                      <?php foreach ($shares as $s) {
                          $price = (float) ($s['unit_price'] ?: $unitPrice); ?>
                        <tr><td class="text-nowrap"><?php echo $day($s['date']); ?></td>
                          <td><?php echo $h(ucfirst($s['entry_type'])); ?></td>
                          <td class="num"><?php echo $money($s['amount_paid']); ?></td>
                          <td class="num"><?php echo $price > 0 ? number_format($s['amount_paid'] / $price, 2) : '–'; ?></td>
                          <td><?php echo $s['share_status'] == 1 ? '<span class="fud-pill ok">Confirmed</span>' : ($s['chairman_approval'] == -1 ? '<span class="fud-pill no">Declined</span>' : '<span class="fud-pill wait">Pending</span>'); ?></td>
                          <td class="text-right"><a href="view_share_details.php?shares_id=<?php echo (int) $s['shares_id']; ?>">Open</a></td></tr>
                      <?php } ?>
                      </tbody>
                    </table>
                  </div>
                  <?php } ?>
                </div>
              </div>

              <!-- Loans -->
              <div class="fud-section" data-panel="loans" style="display:none">
                <div class="fud-section-head"><h5><i class="mdi mdi-cash-multiple"></i> Loans</h5></div>
                <div class="fud-section-body">
                  <?php if (!$loans) { ?><p class="fud-empty">No loans.</p><?php } else { ?>
                  <div class="table-responsive">
                    <table class="table fud-table mb-0">
                      <thead><tr><th>Loan</th><th class="num">Total</th><th class="num">Repaid</th><th class="num">Balance</th><th>Status</th><th></th></tr></thead>
                      <tbody>
                      <?php foreach ($loans as $l) { ?>
                        <tr><td class="text-nowrap"><strong><?php echo $h($l['loan_type_name']); ?></strong> #<?php echo (int) $l['loan_id']; ?>
                            <div class="small text-muted"><?php echo $day($l['loan_date']); ?> · <?php echo (int) $l['loan_tenor']; ?> months<?php echo $l['monthly'] > 0 ? ' · ' . $money($l['monthly']) . '/month' : ''; ?></div>
                            <div class="small text-muted">Guarantor: <?php echo $h(trim($l['g_fname'] . ' ' . $l['g_lname']) ?: '–'); ?><?php echo $l['g_sp_no'] ? ' (' . $h($l['g_sp_no']) . ')' : ''; ?></div></td>
                          <td class="num"><?php echo $money($l['total']); ?></td>
                          <td class="num"><?php echo $l['is_debt'] ? $money($l['repaid']) : '–'; ?></td>
                          <td class="num"><?php echo $l['is_debt'] ? '<strong>' . $money($l['balance']) . '</strong>' : '–'; ?></td>
                          <td><?php echo $loanPill($l['stage']); ?><?php if ($l['decline_reason']) { ?><div class="small text-muted" style="white-space:normal"><?php echo $h($l['decline_reason']); ?></div><?php } ?></td>
                          <td class="text-right"><a href="view_loan_details.php?loan_id=<?php echo (int) $l['loan_id']; ?>">Open</a></td></tr>
                      <?php } ?>
                      </tbody>
                    </table>
                  </div>
                  <?php } ?>
                </div>
              </div>

              <!-- Guarantees -->
              <div class="fud-section" data-panel="guarantees" style="display:none">
                <div class="fud-section-head"><h5><i class="mdi mdi-shield-account"></i> Loans this member guarantees</h5></div>
                <div class="fud-section-body">
                  <?php if (!$guarantees) { ?><p class="fud-empty">Not a guarantor on any loan.</p><?php } else { ?>
                  <div class="table-responsive">
                    <table class="table fud-table mb-0">
                      <thead><tr><th>Borrower</th><th>Loan</th><th class="num">Balance</th><th>Status</th><th></th></tr></thead>
                      <tbody>
                      <?php foreach ($guarantees as $g) { ?>
                        <tr><td style="white-space:normal"><?php echo $h(trim($g['fname'] . ' ' . $g['lname'])); ?><div class="small text-muted"><?php echo $h($g['sp_no']); ?></div></td>
                          <td class="text-nowrap"><?php echo $h($g['loan_type_name']); ?> #<?php echo (int) $g['loan_id']; ?><div class="small text-muted"><?php echo $day($g['loan_date']); ?></div></td>
                          <td class="num"><?php echo $g['stage'] === 'Repaying' ? $money($g['balance']) : '–'; ?></td>
                          <td><?php echo $loanPill($g['stage']); ?></td>
                          <td class="text-right"><a href="view_loan_details.php?loan_id=<?php echo (int) $g['loan_id']; ?>">Open</a></td></tr>
                      <?php } ?>
                      </tbody>
                    </table>
                  </div>
                  <?php } ?>
                </div>
              </div>

              <!-- Commodity -->
              <div class="fud-section" data-panel="commodity" style="display:none">
                <div class="fud-section-head"><h5><i class="mdi mdi-cart"></i> Commodity requests</h5></div>
                <div class="fud-section-body">
                  <?php if (!$commodity) { ?><p class="fud-empty">No commodity requests.</p><?php } else { ?>
                  <div class="table-responsive">
                    <table class="table fud-table mb-0">
                      <thead><tr><th>Supply</th><th class="num">Total</th><th class="num">Repaid</th><th class="num">Balance</th><th>Status</th><th></th></tr></thead>
                      <tbody>
                      <?php foreach ($commodity as $c) { ?>
                        <tr><td style="white-space:normal"><strong><?php echo $h($c['commodity_name'] ?: 'Commodity'); ?></strong> #<?php echo (int) $c['application_id']; ?>
                            <div class="small text-muted"><?php echo $day($c['application_date'] ?? ''); ?><?php echo !empty($c['tenor']) ? ' · ' . (int) $c['tenor'] . ' months' : ''; ?></div></td>
                          <td class="num"><?php echo $money($c['payable']); ?></td>
                          <td class="num"><?php echo $money($c['repaid']); ?></td>
                          <td class="num"><?php echo $c['status'] === 'supplied' ? '<strong>' . $money($c['balance']) . '</strong>' : '–'; ?></td>
                          <td><?php echo CommodityLoan::stagePill($c); ?></td>
                          <td class="text-right"><a href="viewRequestDetails.php?applicationId=<?php echo (int) $c['application_id']; ?>">Open</a></td></tr>
                      <?php } ?>
                      </tbody>
                    </table>
                  </div>
                  <?php } ?>
                </div>
              </div>
            </div>
          </div>
          <?php } ?>

        </div>
        <?php echo $view->subFooter('../'); ?>

<script>
(function () {
    var tabs = document.querySelectorAll('#recordTabs a');
    function show(key) {
        var found = false;
        tabs.forEach(function (a) { var on = a.getAttribute('data-tab') === key; a.classList.toggle('active', on); found = found || on; });
        if (!found) { return; }
        document.querySelectorAll('[data-panel]').forEach(function (p) { p.style.display = p.getAttribute('data-panel') === key ? '' : 'none'; });
    }
    tabs.forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            show(a.getAttribute('data-tab'));
            if (history.replaceState) { history.replaceState(null, '', '#' + a.getAttribute('data-tab')); }
        });
    });
    if (location.hash) { show(location.hash.slice(1)); }
})();
</script>
