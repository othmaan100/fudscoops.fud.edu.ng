<?php
    // My Withdrawals (member): every savings withdrawal request and complete withdrawal, at every stage, with the
    // amount asked for and approved, where it was paid, and the reason when rejected. ?tab= filters; ?export=csv downloads.
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    require_once('../config/classes/LoanControls.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../auth/') || !User::isStaff('../auth/logout.php')) {
        exit();
    }
    $employeeId = $user->getEmployeeId($_SESSION['username']);
    $memberId = $user->getMemberId($employeeId);
    $h = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
    $money = function ($v) { return '₦' . number_format((float) $v, 2); };
    $day = function ($d) { return $d && substr($d, 0, 4) > '1990' ? date('d M Y', strtotime($d)) : ''; };

    $rows = [];
    if ($memberId) {
        $con = (new DB())->getConnection();
        $stmt = $con->prepare("SELECT w.*, b.name AS bank_name FROM fudscoops_withrawals w LEFT JOIN banks b ON b.id = w.bank_id
                               WHERE w.member_id = :m AND COALESCE(w.withrawals_is_deleted, 0) = 0");
        $stmt->execute([':m' => $memberId]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $w) {
            if ((int) $w['status'] === -1) {
                $stage = ['Rejected', 'no', 'rejected', 'Treasurer: ' . $w['treasurer_comment']];
            } elseif ((int) $w['chairman_approval'] === -1) {
                $stage = ['Rejected', 'no', 'rejected', 'Chairman: ' . $w['chairman_comment']];
            } elseif ((int) $w['chairman_approval'] === 1) {
                $stage = ['Approved', 'ok', 'approved', ''];
            } elseif ((int) $w['status'] === 1) {
                $stage = ['Awaiting Chairman', 'wait', 'pending', ''];
            } else {
                $stage = ['Awaiting Treasurer', 'wait', 'pending', ''];
            }
            $rows[] = ['kind' => 'Savings withdrawal', 'asked' => $w['proposed_withrawal_amount'], 'approved' => (float) $w['approved_withrawal_amount'],
                       'requested' => $w['proposed_date'], 'decided' => $stage[2] === 'approved' ? $w['approved_date'] : ($w['chairman_decided_at'] ?: $w['treasurer_decided_at']),
                       'bank' => $w['bank_name'] ?: $w['bank_id'], 'account' => $w['acount_number'], 'name' => $w['account_name'], 'stage' => $stage,
                       'note' => $stage[2] === 'pending' && $w['treasurer_comment'] ? 'Treasurer: ' . $w['treasurer_comment'] : ''];
        }
        $stmt = $con->prepare("SELECT c.*, b.name AS bank_name FROM fudscoops_complete_withrawal c LEFT JOIN banks b ON b.id = c.bank_id
                               WHERE c.employee_id = :e AND COALESCE(c.complete_withrawal_is_deleted, 0) = 0");
        $stmt->execute([':e' => $employeeId]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $c) {
            if ((int) $c['chairman_approval'] === -1) {
                $stage = ['Rejected', 'no', 'rejected', ($c['chairman_decided_at'] ? 'Chairman: ' . $c['chairman_comment'] : 'Treasurer: ' . $c['treasurer_comment'])];
            } elseif ((int) $c['chairman_approval'] === 1) {
                $stage = ['Approved', 'ok', 'approved', ''];
            } else {
                $stage = [(float) $c['approved_amount'] > 0 ? 'Awaiting Chairman' : 'Awaiting Treasurer', 'wait', 'pending', ''];
            }
            $note = $c['settled_at'] && (float) $c['amount_owed'] > 0 ? $money($c['amount_owed']) . ' paid to loans/commodity from savings' : '';
            $rows[] = ['kind' => 'Complete withdrawal', 'asked' => null, 'approved' => (float) $c['approved_amount'],
                       'requested' => $c['date_applied'], 'decided' => $stage[2] === 'approved' ? $c['date_approved'] : ($c['chairman_decided_at'] ?: $c['treasurer_decided_at']),
                       'bank' => $c['bank_name'] ?: $c['bank_id'], 'account' => $c['acount_number'], 'name' => $c['account_name'], 'stage' => $stage, 'note' => $note];
        }
        // Newest first (uploaded withdrawals have no request date: use the payment date)
        usort($rows, function ($a, $b) { return strcmp((string) ($b['requested'] ?: $b['decided']), (string) ($a['requested'] ?: $a['decided'])); });
    }

    $tabs = ['all' => 'All', 'approved' => 'Approved', 'pending' => 'Pending', 'rejected' => 'Rejected'];
    $count = ['all' => count($rows), 'approved' => 0, 'pending' => 0, 'rejected' => 0];
    $withdrawn = 0;
    $waiting = 0;
    foreach ($rows as $r) {
        $count[$r['stage'][2]]++;
        $withdrawn += $r['stage'][2] === 'approved' ? $r['approved'] : 0;
        $waiting += $r['stage'][2] === 'pending' ? ($r['approved'] > 0 ? $r['approved'] : (float) $r['asked']) : 0;
    }
    $tab = isset($_GET['tab'], $tabs[$_GET['tab']]) ? $_GET['tab'] : 'all';
    $shown = array_values(array_filter($rows, function ($r) use ($tab) { return $tab === 'all' || $r['stage'][2] === $tab; }));
    $lastPaid = null;
    foreach ($rows as $r) {
        if ($r['stage'][2] === 'approved' && (!$lastPaid || strcmp((string) $r['decided'], (string) $lastPaid['decided']) > 0)) {
            $lastPaid = $r;
        }
    }

    if (isset($_GET['export']) && $_GET['export'] === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="my_withdrawals_' . date('Y-m-d') . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Type', 'Requested', 'Amount asked', 'Amount approved', 'Decided', 'Bank', 'Account number', 'Account name', 'Status', 'Note']);
        foreach ($shown as $r) {
            fputcsv($out, [$r['kind'], $day($r['requested']), $r['asked'], $r['approved'] ?: '', $day($r['decided']), $r['bank'], $r['account'], $r['name'],
                           $r['stage'][0], trim($r['stage'][3] . ' ' . $r['note'])]);
        }
        fclose($out);
        exit();
    }

    $available = $memberId ? LoanControls::maxWithdrawal($memberId) : 0;
    echo $view->subHeader('../');
?>
    <?php echo $view->subPartialNav('../'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->staffSideNav(); ?>
      <div class="main-panel">
        <div class="content-wrapper fud-dash">

          <div class="fud-hero">
            <div class="fud-hero-date">Savings</div>
            <h2><i class="mdi mdi-bank-transfer-out"></i> My withdrawals</h2>
            <p>Every withdrawal you have asked for: what you asked, what was approved and paid, and where each request is now.</p>
            <div class="fud-hero-actions">
              <a href="savings/savings_withdrawal.php"><i class="mdi mdi-plus-circle"></i> Withdraw savings</a>
              <a href="savings/savings_history.php"><i class="mdi mdi-book-open-variant"></i> Savings history</a>
            </div>
          </div>

          <?php if (!$memberId) { ?>
            <div class="fud-notice"><i class="mdi mdi-alert-circle"></i><span>Your membership record was not found. Withdrawals are available once you are a member.</span>
              <a href="index.php">Dashboard <i class="mdi mdi-arrow-right"></i></a></div>
          <?php } else { ?>

          <div class="fud-stats">
            <div class="fud-stat"><div class="fud-stat-icon fud-green"><i class="mdi mdi-check-circle"></i></div>
              <div><div class="fud-stat-label">Total withdrawn</div><div class="fud-stat-value"><?php echo $money($withdrawn); ?></div>
                <div class="fud-stat-sub"><?php echo $count['approved']; ?> approved</div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-amber"><i class="mdi mdi-clock-outline"></i></div>
              <div><div class="fud-stat-label">Waiting for approval</div><div class="fud-stat-value"><?php echo $money($waiting); ?></div>
                <div class="fud-stat-sub"><?php echo $count['pending']; ?> request<?php echo $count['pending'] === 1 ? '' : 's'; ?></div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-blue"><i class="mdi mdi-safe"></i></div>
              <div><div class="fud-stat-label">You can withdraw now</div><div class="fud-stat-value"><?php echo $money($available); ?></div>
                <div class="fud-stat-sub"><?php echo $money(LoanControls::withdrawalMinBalance()); ?> stays in savings</div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-purple"><i class="mdi mdi-calendar-check"></i></div>
              <div><div class="fud-stat-label">Last paid</div><div class="fud-stat-value"><?php echo $lastPaid ? $money($lastPaid['approved']) : '–'; ?></div>
                <div class="fud-stat-sub"><?php echo $lastPaid && $day($lastPaid['decided']) ? $day($lastPaid['decided']) : 'no withdrawals yet'; ?></div></div></div>
          </div>

          <div class="fud-section">
            <div class="fud-section-body">
              <div class="d-flex flex-wrap justify-content-between align-items-center mb-2" style="gap:.5rem">
                <div class="fud-tabs mb-0">
                  <?php foreach ($tabs as $k => $label) { ?>
                    <a href="?tab=<?php echo $k; ?>" class="<?php echo $tab === $k ? 'active' : ''; ?>"><?php echo $label; ?> <span><?php echo $count[$k]; ?></span></a>
                  <?php } ?>
                </div>
                <?php if ($shown) { ?><a class="btn btn-sm btn-outline-secondary" href="?tab=<?php echo $tab; ?>&amp;export=csv"><i class="mdi mdi-download"></i> Download CSV</a><?php } ?>
              </div>

              <?php if (!$shown) { ?>
                <p class="fud-empty"><i class="mdi mdi-information-outline"></i> <?php echo $tab === 'all' ? 'You have not made any withdrawal requests.' : 'Nothing here.'; ?>
                  <?php if ($tab === 'all') { ?><a href="savings/savings_withdrawal.php">Withdraw savings</a><?php } ?></p>
              <?php } else { ?>
              <div class="table-responsive">
                <table class="table fud-table mb-0">
                  <thead><tr><th>Date</th><th class="num">Asked</th><th class="num">Approved</th><th>Paid into</th><th>Status</th></tr></thead>
                  <tbody>
                  <?php foreach ($shown as $r) { ?>
                    <tr>
                      <td class="text-nowrap"><?php echo $day($r['requested']) ?: ($day($r['decided']) ?: '<span class="text-muted">–</span>'); ?>
                        <div class="small text-muted"><?php echo $r['kind']; ?><?php echo !$day($r['requested']) ? ' · uploaded by the Treasurer' : ''; ?></div></td>
                      <td class="num text-nowrap"><?php echo $r['asked'] !== null && $r['asked'] !== '' ? $money($r['asked']) : '<span class="text-muted">–</span>'; ?></td>
                      <td class="num text-nowrap"><?php echo $r['approved'] > 0 ? '<strong>' . $money($r['approved']) . '</strong>' : '<span class="text-muted">–</span>'; ?>
                        <?php if ($r['approved'] > 0 && $r['asked'] !== null && $r['asked'] !== '' && abs($r['approved'] - (float) $r['asked']) > 0.005) { ?>
                          <div class="small text-muted"><?php echo $r['approved'] < (float) $r['asked'] ? 'less than asked' : 'more than asked'; ?></div><?php } ?></td>
                      <td class="small" style="white-space:normal;min-width:150px"><?php echo $h($r['bank'] ?: '–'); ?>
                        <div class="text-muted"><?php echo $h($r['account'] ?: ''); ?><?php echo $r['name'] ? ' · ' . $h($r['name']) : ''; ?></div></td>
                      <td style="white-space:normal;min-width:160px"><span class="fud-pill <?php echo $r['stage'][1]; ?>"><?php echo $r['stage'][0]; ?></span>
                        <?php if ($day($r['decided'])) { ?><span class="small text-muted ml-1"><?php echo $day($r['decided']); ?></span><?php } ?>
                        <?php if (trim(substr($r['stage'][3], strpos($r['stage'][3], ':') + 1)) !== '') { ?><div class="small text-danger mt-1"><?php echo $h($r['stage'][3]); ?></div><?php } ?>
                        <?php if ($r['note']) { ?><div class="small text-muted mt-1"><?php echo $h($r['note']); ?></div><?php } ?></td>
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
