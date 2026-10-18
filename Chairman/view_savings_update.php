<?php
    // One monthly savings update request for the Chairman (from update_savings.php): the member, the current and new
    // monthly amount, their savings, and Authorize / Decline (ajax/chairman_decisions.php, type savings_update).
    // Authorizing sends it to the Treasurer, who fixes the new amount.
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    require_once('../config/classes/Dashboard.php');
    require_once('../config/classes/LoanControls.php');
    require_once('../config/classes/SavingsG1.php');
    require_once('../config/chairman/decisions.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../auth/') || !User::isChairman('../auth/logout.php')) {
        exit();
    }
    $h = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
    $money = function ($v) { return ((float) $v < 0 ? '−' : '') . '₦' . number_format(abs((float) $v), 2); };
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    $stmt = (new DB())->getConnection()->prepare("SELECT u.*, m.member_id, m.proposed_monthly_savings, e.sp_no, e.title, e.fname, e.lname, e.oname, e.dept, e.cadre, e.phone_no
                                                  FROM fudscoops_update_savings u
                                                  JOIN fudscoops_member m ON m.employee_id = u.employee_id
                                                  JOIN employee e ON e.employee_id = u.employee_id
                                                  WHERE u.id = :id");
    $stmt->execute([':id' => $id]);
    $r = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($r) {
        $name = preg_replace('/\s+/', ' ', trim($r['title'] . ' ' . $r['fname'] . ' ' . $r['lname'] . ' ' . $r['oname']));
        $change = (float) $r['savings_update_amount'] - (float) $r['proposed_monthly_savings'];
        $balance = LoanControls::savingsBalance($r['member_id']);
        $minimum = SavingsG1::getMinMonthlySavings();
        $statement = (new Dashboard())->memberStatement($r['member_id'], $r['employee_id']);
        $recent = array_slice(array_reverse($statement['lines']), 0, 6);
        $open = (int) $r['is_secretary_approved'] === 0;
        $closed = (int) $r['is_secretary_approved'] === 1
            ? 'You authorized this request' . ((int) $r['is_treasurer_approved'] === 1 ? '; the Treasurer fixed the new amount at ' . $money($r['treasurer_approved_amount']) . '.' : ((int) $r['is_treasurer_approved'] === -1 ? '; the Treasurer rejected it.' : '; it is with the Treasurer.'))
            : 'This request was declined.';
    }
    echo $view->subHeader('../');
?>
    <?php echo $view->subPartialNav('../'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->subSideNav(); ?>
      <div class="main-panel">
        <div class="content-wrapper fud-dash fud-apply">
          <?php if (!$r) { ?>
            <div class="fud-notice"><i class="mdi mdi-alert-circle"></i><span>Savings update request not found.</span>
              <a href="update_savings.php">Savings updates <i class="mdi mdi-arrow-right"></i></a></div>
          <?php } else { ?>
          <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div>
              <h3 class="mb-1">Monthly savings update #<?php echo (int) $r['id']; ?>
                <span class="fud-pill <?php echo $open ? 'wait' : ((int) $r['is_secretary_approved'] === 1 ? 'ok' : 'no'); ?>" style="font-size:.8rem"><?php echo $open ? 'Awaiting you' : ((int) $r['is_secretary_approved'] === 1 ? 'Authorized' : 'Declined'); ?></span></h3>
              <div class="text-muted"><?php echo $h($name); ?> (<?php echo $h($r['sp_no']); ?>) · requested <?php echo date('d M Y', strtotime($r['update_proposed_date'])); ?></div>
            </div>
            <a href="member_profile.php?member_id=<?php echo (int) $r['member_id']; ?>" class="btn btn-light btn-sm"><i class="mdi mdi-account-details"></i> Member record</a>
          </div>

          <div class="fud-stats">
            <div class="fud-stat"><div class="fud-stat-icon fud-blue"><i class="mdi mdi-calendar-clock"></i></div>
              <div><div class="fud-stat-label">Current monthly savings</div><div class="fud-stat-value"><?php echo $money($r['proposed_monthly_savings']); ?></div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon <?php echo $r['savings_update_amount'] < $minimum ? 'fud-rose' : 'fud-purple'; ?>"><i class="mdi mdi-calendar-edit"></i></div>
              <div><div class="fud-stat-label">New monthly savings</div><div class="fud-stat-value"><?php echo $money($r['savings_update_amount']); ?></div>
                <div class="fud-stat-sub <?php echo $change > 0 ? 'text-success' : ($change < 0 ? 'text-danger' : ''); ?>"><?php echo ($change > 0 ? '+' : '') . $money($change); ?> a month</div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-green"><i class="mdi mdi-safe"></i></div>
              <div><div class="fud-stat-label">Savings balance</div><div class="fud-stat-value"><?php echo $money($balance); ?></div></div></div>
          </div>
          <?php if ($r['savings_update_amount'] < $minimum) { ?>
            <div class="fud-notice"><i class="mdi mdi-alert-circle"></i><span>The new amount is below the minimum monthly savings of <?php echo $money($minimum); ?>.</span></div>
          <?php } ?>

          <div class="row">
            <div class="col-lg-7">
              <div class="fud-section">
                <div class="fud-section-head"><h5><span class="fud-num">1</span> Member</h5></div>
                <div class="fud-section-body">
                  <div class="fud-facts">
                    <div><span>Name</span><?php echo $h($name); ?></div>
                    <div><span>Staff number</span><?php echo $h($r['sp_no']); ?></div>
                    <div><span>Department / unit</span><?php echo $h($r['dept'] ?: '–'); ?></div>
                    <div><span>Cadre</span><?php echo $h($r['cadre'] ?: '–'); ?></div>
                    <div><span>Phone</span><?php echo $h($r['phone_no'] ?: '–'); ?></div>
                  </div>
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
              <?php echo chairmanDecisionPanel('savings_update', $r['id'], $name . ' → ' . $money($r['savings_update_amount']) . ' a month', 'update_savings.php', $open, $open ? '' : $closed,
                  [], true, 'Authorizing sends the request to the Treasurer, who fixes the new monthly amount.'); ?>
            </div>
          </div>
          <?php } ?>
        </div>
        <?php echo $view->subFooter('../'); ?>
