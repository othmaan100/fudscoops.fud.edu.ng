<?php
    // One complete withdrawal (member leaving) for the Chairman (from complete_withdrawals.php): savings, what the member
    // owes or guarantees, the Treasurer's amount, where it is paid, and Authorize / Decline
    // (ajax/chairman_decisions.php, type complete_withdrawal). ?id=
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    require_once('../config/classes/Dashboard.php');
    require_once('../config/classes/LoanControls.php');
    require_once('../config/chairman/decisions.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../auth/') || !User::isChairman('../auth/logout.php')) {
        exit();
    }
    $h = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
    $money = function ($v) { return ((float) $v < 0 ? '−' : '') . '₦' . number_format(abs((float) $v), 2); };
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    $stmt = (new DB())->getConnection()->prepare("SELECT c.*, m.member_id, m.proposed_monthly_savings, m.reg_date, e.sp_no, e.title, e.fname, e.lname, e.oname,
                                                         e.dept, e.phone_no, b.name AS bank_name
                                                  FROM fudscoops_complete_withrawal c
                                                  JOIN fudscoops_member m ON m.employee_id = c.employee_id
                                                  JOIN employee e ON e.employee_id = c.employee_id
                                                  LEFT JOIN banks b ON b.id = c.bank_id
                                                  WHERE c.comp_withdrwal_id = :id AND COALESCE(c.complete_withrawal_is_deleted, 0) = 0");
    $stmt->execute([':id' => $id]);
    $w = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($w) {
        $name = preg_replace('/\s+/', ' ', trim($w['title'] . ' ' . $w['fname'] . ' ' . $w['lname'] . ' ' . $w['oname']));
        $balance = LoanControls::savingsBalance($w['member_id']);
        $ob = LoanControls::leavingObligations($w['member_id'], $w['employee_id'], $balance);
        $amount = (float) $w['approved_amount'];
        $owed = $w['settle_from_savings'] ? $ob['total_owed'] : 0;
        $statement = (new Dashboard())->memberStatement($w['member_id'], $w['employee_id']);
        $recent = array_slice(array_reverse($statement['lines']), 0, 6);
        $open = (int) $w['chairman_approval'] === 0 && $amount > 0;
        // The same checks as MemberG1::chairmanApproveComWithdrawal
        $blocked = [];
        if ($open) {
            foreach ($ob['blockers'] as $b) {
                $blocked[] = str_replace(['Your ', 'You are '], ['The member\'s ', 'The member is '], $b);
            }
            if ($ob['total_owed'] > 0 && !$w['settle_from_savings']) {
                $blocked[] = 'Owes ' . $money($ob['total_owed']) . ' and did not choose to pay it from savings.';
            }
            if ($limit = LoanControls::withdrawalLimitError($w['member_id'], $amount, $owed)) {
                $blocked[] = $limit;
            }
            if (!preg_match('/^\d{10}$/', (string) $w['acount_number'])) {
                $blocked[] = 'The account number is missing; the Treasurer must enter it.';
            }
        }
        $short = $open && !$blocked ? LoanControls::completePayoutShortError($ob, $amount) : null;
        if ((int) $w['chairman_approval'] === 1) {
            $closed = 'Approved' . ($w['date_approved'] ? ' on ' . date('d M Y', strtotime($w['date_approved'])) : '') . ' for ' . $money($amount) . '.';
        } elseif ((int) $w['chairman_approval'] === -1) {
            $closed = $w['chairman_decided_at'] ? 'You declined this request' . ($w['chairman_comment'] ? ': ' . $w['chairman_comment'] : '.')
                                                : 'Rejected by the Treasurer' . ($w['treasurer_comment'] ? ': ' . $w['treasurer_comment'] : '.');
        } else {
            $closed = 'Awaiting the Treasurer, who sets the amount before it comes to you.';
        }
        $stage = $open ? ['Awaiting you', 'wait'] : ((int) $w['chairman_approval'] === 1 ? ['Approved', 'ok'] : ((int) $w['chairman_approval'] === -1 ? ['Declined', 'no'] : ['Awaiting Treasurer', 'wait']));
    }
    echo $view->subHeader('../');
?>
    <?php echo $view->subPartialNav('../'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->subSideNav(); ?>
      <div class="main-panel">
        <div class="content-wrapper fud-dash fud-apply">
          <?php if (!$w) { ?>
            <div class="fud-notice"><i class="mdi mdi-alert-circle"></i><span>Complete withdrawal request not found.</span>
              <a href="complete_withdrawals.php">Complete withdrawals <i class="mdi mdi-arrow-right"></i></a></div>
          <?php } else { ?>
          <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div>
              <h3 class="mb-1">Complete withdrawal #<?php echo (int) $w['comp_withdrwal_id']; ?> <span class="fud-pill <?php echo $stage[1]; ?>" style="font-size:.8rem"><?php echo $stage[0]; ?></span></h3>
              <div class="text-muted"><?php echo $h($name); ?> (<?php echo $h($w['sp_no']); ?>)<?php echo $w['date_applied'] ? ' · applied ' . date('d M Y', strtotime($w['date_applied'])) : ''; ?></div>
            </div>
            <a href="member_profile.php?member_id=<?php echo (int) $w['member_id']; ?>" class="btn btn-light btn-sm"><i class="mdi mdi-account-details"></i> Member record</a>
          </div>

          <div class="fud-stats">
            <div class="fud-stat"><div class="fud-stat-icon fud-green"><i class="mdi mdi-safe"></i></div>
              <div><div class="fud-stat-label">Savings balance</div><div class="fud-stat-value"><?php echo $money($balance); ?></div>
                <div class="fud-stat-sub">member since <?php echo $w['reg_date'] ? date('M Y', strtotime($w['reg_date'])) : '–'; ?></div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon <?php echo $ob['total_owed'] > 0 ? 'fud-rose' : 'fud-purple'; ?>"><i class="mdi mdi-cash-multiple"></i></div>
              <div><div class="fud-stat-label">Owes</div><div class="fud-stat-value"><?php echo $money($ob['total_owed']); ?></div>
                <div class="fud-stat-sub"><?php echo $ob['total_owed'] > 0 ? ($w['settle_from_savings'] ? 'paid from savings on approval' : 'not paying from savings') : 'loans and commodity'; ?></div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-blue"><i class="mdi mdi-bank-transfer-out"></i></div>
              <div><div class="fud-stat-label">Treasurer's amount</div><div class="fud-stat-value"><?php echo $amount > 0 ? $money($amount) : '–'; ?></div>
                <div class="fud-stat-sub">full amount due <?php echo $money($ob['payout']); ?></div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-amber"><i class="mdi mdi-lock"></i></div>
              <div><div class="fud-stat-label">Left in savings</div><div class="fud-stat-value"><?php echo $money($balance - ((int) $w['chairman_approval'] === 1 ? 0 : $owed + $amount)); ?></div>
                <div class="fud-stat-sub"><?php echo $money($ob['min_balance']); ?> minimum</div></div></div>
          </div>
          <?php if ($short) { ?>
            <div class="fud-notice info"><i class="mdi mdi-information-outline"></i><span><?php echo $h(str_replace('A complete withdrawal pays out everything the member is due', 'The full amount due to this member is', $short)); ?> You can still approve the Treasurer's amount.</span></div>
          <?php } ?>

          <div class="row">
            <div class="col-lg-7">
              <div class="fud-section">
                <div class="fud-section-head"><h5><span class="fud-num">1</span> Member and payment</h5></div>
                <div class="fud-section-body">
                  <div class="fud-facts">
                    <div><span>Name</span><?php echo $h($name); ?></div>
                    <div><span>Staff number</span><?php echo $h($w['sp_no']); ?></div>
                    <div><span>Department / unit</span><?php echo $h($w['dept'] ?: '–'); ?></div>
                    <div><span>Phone</span><?php echo $h($w['phone_no'] ?: '–'); ?></div>
                    <div><span>Bank</span><?php echo $h($w['bank_name'] ?: '–'); ?></div>
                    <div><span>Account number</span><?php echo preg_match('/^\d{10}$/', (string) $w['acount_number']) ? $h($w['acount_number']) : '<span class="text-danger">Missing</span>'; ?></div>
                    <div><span>Account name</span><?php echo $h($w['account_name'] ?: '–'); ?></div>
                  </div>
                  <?php if ($w['treasurer_comment']) { ?>
                    <p class="small text-muted mt-3 mb-0"><i class="mdi mdi-comment-text-outline"></i> Treasurer<?php echo $w['treasurer_decided_by'] ? ' (' . $h($w['treasurer_decided_by']) . ')' : ''; ?>: <?php echo $h($w['treasurer_comment']); ?></p>
                  <?php } ?>
                </div>
              </div>
              <div class="fud-section">
                <div class="fud-section-head"><h5><span class="fud-num">2</span> Loans, commodity and guarantees</h5></div>
                <div class="fud-section-body">
                  <?php if (!$ob['owed'] && !$ob['blockers']) { ?>
                    <p class="mb-0 text-success"><i class="mdi mdi-check-circle"></i> Owes no loan or commodity and is not guaranteeing an open loan.</p>
                  <?php } else { ?>
                    <?php if ($ob['owed']) { ?>
                      <table class="table fud-table mb-2"><thead><tr><th>Owed</th><th class="num">Balance</th></tr></thead><tbody>
                        <?php foreach ($ob['owed'] as $o) { ?><tr><td><?php echo $h($o['label']); ?></td><td class="num"><?php echo $money($o['balance']); ?></td></tr><?php } ?>
                      </tbody></table>
                    <?php } ?>
                    <?php foreach ($ob['blockers'] as $b) { ?><p class="small text-danger mb-1"><i class="mdi mdi-block-helper"></i> <?php echo $h(str_replace(['Your ', 'You are '], ['The member\'s ', 'The member is '], $b)); ?></p><?php } ?>
                  <?php } ?>
                </div>
              </div>
              <div class="fud-section">
                <div class="fud-section-head"><h5><span class="fud-num">3</span> Recent savings and withdrawals</h5></div>
                <div class="fud-section-body">
                  <?php if (!$recent) { ?><p class="fud-empty">No savings recorded.</p><?php } else { ?>
                  <table class="table fud-table mb-0">
                    <thead><tr><th>Date</th><th>Description</th><th class="num">Amount (₦)</th><th class="num">Balance (₦)</th></tr></thead>
                    <tbody>
                    <?php foreach ($recent as $l) { ?>
                      <tr><td class="text-nowrap"><?php echo $l['exact'] ? date('d M Y', strtotime($l['date'])) : date('M Y', strtotime($l['date'])); ?></td>
                        <td><?php echo $h($l['label']); ?></td>
                        <td class="num <?php echo $l['credit'] ? 'text-success' : 'text-danger'; ?>"><?php echo ($l['credit'] ? '+' : '−') . number_format($l['credit'] ?: $l['debit'], 2); ?></td>
                        <td class="num"><?php echo number_format($l['balance'], 2); ?></td></tr>
                    <?php } ?>
                    </tbody>
                  </table>
                  <?php } ?>
                </div>
              </div>
            </div>
            <div class="col-lg-5">
              <?php echo chairmanDecisionPanel('complete_withdrawal', $w['comp_withdrwal_id'], $name . ' – ' . $money($amount), 'complete_withdrawals.php', $open, $open ? '' : $closed, $blocked, true,
                  $owed > 0 ? 'Authorizing pays the ' . $money($owed) . ' the member owes from savings, then pays out the Treasurer\'s amount.' : 'Authorizing pays out the Treasurer\'s amount.'); ?>
            </div>
          </div>
          <?php } ?>
        </div>
        <?php echo $view->subFooter('../'); ?>
