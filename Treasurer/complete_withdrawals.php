<?php
    // Complete withdrawals (members leaving the cooperative). The Treasurer sets the amount to pay, or rejects with a reason
    // (view_com_withdrawal_details.php); the Chairman gives the final approval.
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    require_once('../config/classes/MemberG1.php');
    require_once('../config/classes/LoanControls.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../auth/') || !User::isBur('../auth/logout.php')) {
        exit();
    }
    $h = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
    $money = function ($v) { return ((float) $v < 0 ? '−' : '') . '₦' . number_format(abs((float) $v), 2); };

    $groups = ['treasurer' => [], 'chairman' => [], 'approved' => [], 'rejected' => []];
    foreach ((new MemberG1())->treasurerCompleteWithdrawals() as $r) {
        $r['name'] = preg_replace('/\s+/', ' ', trim($r['fname'] . ' ' . $r['lname'] . ' ' . $r['oname']));
        if ((int) $r['chairman_approval'] === 1) {
            $groups['approved'][] = $r;
        } elseif ((int) $r['chairman_approval'] === -1) {
            $groups['rejected'][] = $r;
        } else {
            // Still open: what the member has, owes and would be paid now
            $r['balance'] = LoanControls::savingsBalance($r['member_id']);
            $r['ob'] = LoanControls::leavingObligations($r['member_id'], $r['employee_id'], $r['balance']);
            $groups[(float) $r['approved_amount'] > 0 ? 'chairman' : 'treasurer'][] = $r;
        }
    }
    $toPay = array_sum(array_map(function ($r) { return $r['ob']['payout']; }, $groups['treasurer']));
    $blocked = count(array_filter($groups['treasurer'], function ($r) { return $r['ob']['blockers'] || ($r['ob']['total_owed'] > 0 && !$r['settle_from_savings']) || $r['ob']['payout'] <= 0; }));
    $tab = isset($_GET['tab']) && isset($groups[$_GET['tab']]) ? $_GET['tab'] : 'treasurer';
    $labels = ['treasurer' => 'Awaiting you', 'chairman' => 'Awaiting Chairman', 'approved' => 'Approved', 'rejected' => 'Rejected'];

    echo $view->subHeader('../');
?>
    <?php echo $view->subPartialNav('../'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->treasurerSideNav(); ?>
      <div class="main-panel">
        <div class="content-wrapper fud-dash">

          <div class="fud-hero">
            <div class="fud-hero-date">Savings withdrawals</div>
            <h2><i class="mdi mdi-account-remove"></i> Complete withdrawals</h2>
            <p>Members leaving the cooperative. They are paid their savings after any loan or commodity they owe, less the minimum balance that stays in savings.
              Check each request, set the amount to pay (or reject with a reason), and the Chairman gives the final approval.</p>
            <div class="fud-hero-actions">
              <a href="withdrawal_endorsement.php"><i class="mdi mdi-bank-transfer-out"></i> Savings withdrawals</a>
            </div>
          </div>

          <div class="fud-stats">
            <div class="fud-stat"><div class="fud-stat-icon fud-amber"><i class="mdi mdi-clock-outline"></i></div>
              <div><div class="fud-stat-label">Awaiting you</div><div class="fud-stat-value"><?php echo count($groups['treasurer']); ?></div>
                <div class="fud-stat-sub"><?php echo $money($toPay); ?> to pay out</div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon <?php echo $blocked ? 'fud-rose' : 'fud-green'; ?>"><i class="mdi mdi-alert-circle-outline"></i></div>
              <div><div class="fud-stat-label">Cannot be paid yet</div><div class="fud-stat-value"><?php echo $blocked; ?></div>
                <div class="fud-stat-sub">owe, guarantee or have nothing to pay</div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-blue"><i class="mdi mdi-gavel"></i></div>
              <div><div class="fud-stat-label">Awaiting Chairman</div><div class="fud-stat-value"><?php echo count($groups['chairman']); ?></div>
                <div class="fud-stat-sub"><?php echo $money(array_sum(array_column($groups['chairman'], 'approved_amount'))); ?></div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-green"><i class="mdi mdi-check-all"></i></div>
              <div><div class="fud-stat-label">Approved</div><div class="fud-stat-value"><?php echo count($groups['approved']); ?></div>
                <div class="fud-stat-sub"><?php echo $money(array_sum(array_column($groups['approved'], 'approved_amount'))); ?> paid</div></div></div>
          </div>

          <div class="fud-tabs" id="cwTabs">
            <?php foreach ($labels as $key => $label) { ?>
              <a href="?tab=<?php echo $key; ?>" data-tab="<?php echo $key; ?>" class="<?php echo $tab === $key ? 'active' : ''; ?>"><?php echo $label; ?> <span><?php echo count($groups[$key]); ?></span></a>
            <?php } ?>
          </div>

          <?php foreach ($groups as $key => $rows) { ?>
          <div class="fud-section" data-panel="<?php echo $key; ?>"<?php echo $tab === $key ? '' : ' style="display:none"'; ?>>
            <div class="fud-section-head"><h5><?php echo $labels[$key]; ?></h5></div>
            <div class="fud-section-body">
              <?php if (!$rows) { ?>
                <p class="fud-empty"><i class="mdi mdi-check-circle-outline"></i> Nothing here.</p>
              <?php } else { ?>
                <input class="form-control fud-search cw-search" type="search" placeholder="Search name, staff number, department…">
                <div class="table-responsive">
                  <table class="table fud-table mb-0">
                    <thead>
                      <tr>
                        <th>Member</th>
                        <th>Applied</th>
                        <?php if ($key === 'treasurer' || $key === 'chairman') { ?>
                          <th class="num">Savings</th><th class="num">Owes</th>
                        <?php } ?>
                        <th class="num"><?php echo $key === 'treasurer' ? 'To pay' : ($key === 'rejected' ? '' : 'Amount'); ?></th>
                        <th>Pay into</th>
                        <th><?php echo $key === 'rejected' ? 'Reason' : 'Check'; ?></th>
                        <th class="text-right"></th>
                      </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rows as $r) {
                        $open = isset($r['ob']);
                        $noAccount = !preg_match('/^\d{10}$/', (string) $r['acount_number']); ?>
                      <tr>
                        <td style="white-space:normal;min-width:170px"><strong><?php echo $h($r['name']); ?></strong>
                          <div class="small text-muted"><?php echo $h($r['sp_no']); ?><?php echo $r['dept'] ? ' · ' . $h($r['dept']) : ''; ?></div></td>
                        <td class="text-nowrap"><?php echo $r['date_applied'] ? date('d M Y', strtotime($r['date_applied'])) : '–'; ?>
                          <?php if ($key === 'approved' && $r['date_approved']) { ?><div class="small text-muted">approved <?php echo date('d M Y', strtotime($r['date_approved'])); ?></div><?php } ?></td>
                        <?php if ($open) { ?>
                          <td class="num text-nowrap"><?php echo $money($r['balance']); ?></td>
                          <td class="num"><?php echo $r['ob']['total_owed'] > 0 ? '<span class="text-danger text-nowrap">' . $money($r['ob']['total_owed']) . '</span>' : '<span class="text-muted">–</span>'; ?></td>
                        <?php } ?>
                        <td class="num"><strong><?php echo $key === 'treasurer' ? $money($r['ob']['payout']) : ($key === 'rejected' ? '' : $money($r['approved_amount'])); ?></strong></td>
                        <td class="small" style="white-space:normal;min-width:140px"><?php echo $h($r['bank_name'] ?: '–'); ?>
                          <div class="<?php echo $noAccount ? 'text-danger' : 'text-muted'; ?>"><?php echo $noAccount ? 'Account number missing' : $h($r['acount_number']); ?></div></td>
                        <td style="white-space:normal">
                          <?php if ($key === 'rejected') { ?>
                            <span class="small"><?php echo $r['chairman_decided_at'] ? '<strong>Chairman:</strong> ' . $h($r['chairman_comment']) : $h($r['treasurer_comment']); ?></span>
                          <?php } elseif ($open) {
                              if ($r['ob']['blockers']) { ?><span class="fud-pill no" title="<?php echo $h(implode(' ', $r['ob']['blockers'])); ?>">Guarantor / request open</span>
                              <?php } elseif ($r['ob']['total_owed'] > 0 && !$r['settle_from_savings']) { ?><span class="fud-pill no">Owes; not paying from savings</span>
                              <?php } elseif ($r['ob']['payout'] <= 0) { ?><span class="fud-pill no">Nothing to pay</span>
                              <?php } elseif ($r['ob']['total_owed'] > 0) { ?><span class="fud-pill wait">Pays debts from savings</span>
                              <?php } else { ?><span class="fud-pill ok">Clear</span><?php }
                              if ($noAccount) { ?> <span class="fud-pill no">No account</span><?php }
                          } else { ?><span class="fud-pill ok">Approved</span><?php } ?>
                        </td>
                        <td class="text-right"><a href="view_com_withdrawal_details.php?withrawals_id=<?php echo (int) $r['comp_withdrwal_id']; ?>" class="btn btn-sm text-nowrap <?php echo $key === 'treasurer' ? 'btn-primary' : 'btn-outline-primary'; ?>"><?php echo $key === 'treasurer' ? 'Review' : 'Open'; ?></a></td>
                      </tr>
                    <?php } ?>
                    </tbody>
                  </table>
                </div>
              <?php } ?>
            </div>
          </div>
          <?php } ?>

        </div>
        <?php echo $view->subFooter('../'); ?>

<script>
(function () {
    var tabs = document.querySelectorAll('#cwTabs a');
    tabs.forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            var t = a.getAttribute('data-tab');
            tabs.forEach(function (x) { x.classList.toggle('active', x === a); });
            document.querySelectorAll('[data-panel]').forEach(function (p) { p.style.display = p.getAttribute('data-panel') === t ? '' : 'none'; });
            if (history.replaceState) { history.replaceState(null, '', '?tab=' + t); }
        });
    });
    document.querySelectorAll('.cw-search').forEach(function (input) {
        input.addEventListener('input', function () {
            var q = input.value.toLowerCase();
            input.parentNode.querySelectorAll('tbody tr').forEach(function (r) { r.style.display = r.textContent.toLowerCase().indexOf(q) !== -1 ? '' : 'none'; });
        });
    });
})();
</script>
