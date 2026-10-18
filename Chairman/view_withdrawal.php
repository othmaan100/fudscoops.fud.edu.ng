<?php
    // One savings withdrawal for the Chairman (from withdrawal_endorsement.php): the member, the amount the Treasurer
    // approved, where it is paid, the savings left, and Authorize / Decline (ajax/chairman_decisions.php, type withdrawal).
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
    $id = isset($_GET['withrawals_id']) ? (int) $_GET['withrawals_id'] : 0;
    $stmt = (new DB())->getConnection()->prepare("SELECT w.*, m.member_id, m.employee_id, m.proposed_monthly_savings, e.sp_no, e.title, e.fname, e.lname, e.oname,
                                                         e.dept, e.phone_no, e.nature_of_appo, b.name AS bank_name
                                                  FROM fudscoops_withrawals w
                                                  JOIN fudscoops_member m ON m.member_id = w.member_id
                                                  JOIN employee e ON e.employee_id = m.employee_id
                                                  LEFT JOIN banks b ON b.id = w.bank_id
                                                  WHERE w.withrawals_id = :id AND COALESCE(w.withrawals_is_deleted, 0) = 0");
    $stmt->execute([':id' => $id]);
    $w = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($w) {
        $name = preg_replace('/\s+/', ' ', trim($w['title'] . ' ' . $w['fname'] . ' ' . $w['lname'] . ' ' . $w['oname']));
        $balance = LoanControls::savingsBalance($w['member_id']);
        $min = LoanControls::withdrawalMinBalance();
        $amount = (float) $w['approved_withrawal_amount'];
        $statement = (new Dashboard())->memberStatement($w['member_id'], $w['employee_id']);
        $recent = array_slice(array_reverse($statement['lines']), 0, 6);
        $open = (int) $w['status'] === 1 && (int) $w['chairman_approval'] === 0 && $amount > 0;
        $blocked = [];
        if ($open && ($limit = LoanControls::withdrawalLimitError($w['member_id'], $amount))) {
            $blocked[] = $limit;
        }
        if ((int) $w['chairman_approval'] === 1) {
            $closed = 'Approved' . (substr((string) $w['approved_date'], 0, 4) > '2000' ? ' on ' . date('d M Y', strtotime($w['approved_date'])) : '') . ' for ' . $money($amount) . '.';
        } elseif ((int) $w['chairman_approval'] === -1) {
            $closed = 'You declined this request' . ($w['chairman_comment'] ? ': ' . $w['chairman_comment'] : '.');
        } elseif ((int) $w['status'] === -1) {
            $closed = 'Rejected by the Treasurer' . ($w['treasurer_comment'] ? ': ' . $w['treasurer_comment'] : '.');
        } else {
            $closed = 'Awaiting the Treasurer, who sets the amount before it comes to you.';
        }
        $stage = $open ? ['Awaiting you', 'wait'] : ((int) $w['chairman_approval'] === 1 ? ['Approved', 'ok'] : (((int) $w['chairman_approval'] === -1 || (int) $w['status'] === -1) ? ['Declined', 'no'] : ['Awaiting Treasurer', 'wait']));
    }
    echo $view->subHeader('../');
?>
    <?php echo $view->subPartialNav('../'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->subSideNav(); ?>
      <div class="main-panel">
        <div class="content-wrapper fud-dash fud-apply">
          <?php if (!$w) { ?>
            <div class="fud-notice"><i class="mdi mdi-alert-circle"></i><span>Withdrawal request not found.</span>
              <a href="withdrawal_endorsement.php">Savings withdrawals <i class="mdi mdi-arrow-right"></i></a></div>
          <?php } else { ?>
          <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div>
              <h3 class="mb-1">Savings withdrawal #<?php echo (int) $w['withrawals_id']; ?> <span class="fud-pill <?php echo $stage[1]; ?>" style="font-size:.8rem"><?php echo $stage[0]; ?></span></h3>
              <div class="text-muted"><?php echo $h($name); ?> (<?php echo $h($w['sp_no']); ?>)<?php echo $w['proposed_date'] ? ' · requested ' . date('d M Y', strtotime($w['proposed_date'])) : ''; ?></div>
            </div>
            <a href="member_profile.php?member_id=<?php echo (int) $w['member_id']; ?>" class="btn btn-light btn-sm"><i class="mdi mdi-account-details"></i> Member record</a>
          </div>

          <div class="fud-stats">
            <div class="fud-stat"><div class="fud-stat-icon fud-blue"><i class="mdi mdi-bank-transfer-out"></i></div>
              <div><div class="fud-stat-label">Treasurer approved</div><div class="fud-stat-value"><?php echo $amount > 0 ? $money($amount) : '–'; ?></div>
                <div class="fud-stat-sub">asked <?php echo $money($w['proposed_withrawal_amount']); ?></div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-green"><i class="mdi mdi-safe"></i></div>
              <div><div class="fud-stat-label">Savings balance</div><div class="fud-stat-value"><?php echo $money($balance); ?></div>
                <div class="fud-stat-sub"><?php echo $money($w['proposed_monthly_savings']); ?> a month</div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon <?php echo $blocked ? 'fud-rose' : 'fud-purple'; ?>"><i class="mdi mdi-scale-balance"></i></div>
              <div><div class="fud-stat-label">Left after this</div><div class="fud-stat-value"><?php echo $money($balance - ((int) $w['chairman_approval'] === 1 ? 0 : $amount)); ?></div>
                <div class="fud-stat-sub"><?php echo $money($min); ?> must stay in savings</div></div></div>
          </div>

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
                    <div><span>Bank</span><?php echo $h($w['bank_name'] ?: $w['bank_id'] ?: '–'); ?></div>
                    <div><span>Account number</span><?php echo $h($w['acount_number'] ?: '–'); ?></div>
                    <div><span>Account name</span><?php echo $h($w['account_name'] ?: '–'); ?></div>
                  </div>
                  <?php if ($w['treasurer_comment']) { ?>
                    <p class="small text-muted mt-3 mb-0"><i class="mdi mdi-comment-text-outline"></i> Treasurer<?php echo $w['treasurer_decided_by'] ? ' (' . $h($w['treasurer_decided_by']) . ')' : ''; ?>: <?php echo $h($w['treasurer_comment']); ?></p>
                  <?php } ?>
                </div>
              </div>
              <div class="fud-section">
                <div class="fud-section-head"><h5><span class="fud-num">2</span> Recent savings and withdrawals</h5></div>
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
              <?php echo chairmanDecisionPanel('withdrawal', $w['withrawals_id'], $name . ' – ' . $money($amount), 'withdrawal_endorsement.php', $open, $open ? '' : $closed, $blocked, true,
                  'Authorizing pays the Treasurer\'s amount out of the member\'s savings.'); ?>
            </div>
          </div>
          <?php } ?>
        </div>
        <?php echo $view->subFooter('../'); ?>
