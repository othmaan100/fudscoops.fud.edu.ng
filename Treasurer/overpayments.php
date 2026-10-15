<?php
    // Treasurer: loans repaid by more than they owed (LoanControls). Settle each one as the member chose.
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    require_once('../config/classes/LoanControls.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../auth/') || !User::isBur('../auth/logout.php')) {
        exit();
    }
    $open = LoanControls::overpayments();
    $settled = LoanControls::overpayments('settled');
    $h = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
    $choiceText = ['savings' => 'Add to savings', 'loan' => 'Pay another loan', 'refund' => 'Refund'];

    echo $view->subHeader('../');
?>
    <?php echo $view->subPartialNav('../'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->treasurerSideNav(); ?>
      <div class="main-panel">
        <div class="content-wrapper fud-dash">

          <div class="fud-hero">
            <div class="fud-hero-date">Loans</div>
            <h2><i class="mdi mdi-cash-refund"></i> Loan Overpayments</h2>
            <p>Deductions recorded past what a loan owed. The member chooses what to do with the excess; you settle it here.</p>
          </div>

          <div class="fud-section">
            <div class="fud-section-head"><h5>To settle</h5></div>
            <div class="fud-section-body">
              <?php if (!$open) { ?><p class="fud-empty">No overpayment is waiting.</p><?php } else { ?>
              <div class="table-responsive"><table class="table fud-table">
                <thead><tr><th>Member</th><th>Loan</th><th class="num">Overpaid (₦)</th><th>Member's choice</th><th style="width:30%">Settle</th></tr></thead><tbody>
                <?php foreach ($open as $op) { ?>
                  <tr>
                    <td><?php echo $h(trim($op['fname'] . ' ' . $op['lname'])); ?><div class="text-muted small"><?php echo $h($op['sp_no']); ?></div></td>
                    <td><a href="view_loan_details.php?loan_id=<?php echo (int) $op['loan_id']; ?>"><?php echo $h($op['loan_type_name']); ?> #<?php echo (int) $op['loan_id']; ?></a></td>
                    <td class="num"><strong><?php echo number_format($op['amount'], 2); ?></strong></td>
                    <td>
                      <?php if ($op['status'] === 'open') { ?>
                        <span class="fud-pill wait">Waiting for the member</span>
                      <?php } else { ?>
                        <?php echo $h($choiceText[$op['member_choice']]); ?>
                        <div class="text-muted small"><?php
                            if ($op['member_choice'] === 'loan') { echo $h($op['target_type'] . ' #' . $op['target_loan_id']); }
                            if ($op['member_choice'] === 'refund') { echo $h($op['bank_name'] . ' · ' . $op['account_number'] . ' · ' . $op['account_name']); }
                        ?></div>
                      <?php } ?>
                    </td>
                    <td>
                      <?php if ($op['status'] === 'chosen') { ?>
                        <form class="settle-form form-inline" data-id="<?php echo (int) $op['overpayment_id']; ?>" data-choice="<?php echo $h($op['member_choice']); ?>">
                          <input type="text" name="note" class="form-control form-control-sm mr-2 mb-1" style="width:150px"
                                 placeholder="<?php echo $op['member_choice'] === 'refund' ? 'Transfer reference' : 'Note (optional)'; ?>" <?php echo $op['member_choice'] === 'refund' ? 'required' : ''; ?>>
                          <button type="submit" class="btn btn-sm btn-primary mb-1">Settle</button>
                        </form>
                      <?php } else { echo '<span class="text-muted small">After the member chooses</span>'; } ?>
                    </td>
                  </tr>
                <?php } ?>
              </tbody></table></div>
              <?php } ?>
              <p class="text-muted small mt-3 mb-0">Settling adds the money to the member's savings for this month, or to the chosen loan's deduction for this month,
                or records the refund. It cannot be undone here.</p>
            </div>
          </div>

          <?php if ($settled) { ?>
          <div class="fud-section">
            <div class="fud-section-head"><h5>Settled</h5></div>
            <div class="fud-section-body"><div class="table-responsive"><table class="table fud-table">
              <thead><tr><th>Member</th><th>Loan</th><th class="num">Amount (₦)</th><th>What was done</th><th>By</th><th>Date</th></tr></thead><tbody>
              <?php foreach ($settled as $s) { ?>
                <tr><td><?php echo $h(trim($s['fname'] . ' ' . $s['lname'])); ?></td><td><?php echo $h($s['loan_type_name']); ?> #<?php echo (int) $s['loan_id']; ?></td>
                  <td class="num"><?php echo number_format($s['amount'], 2); ?></td><td><?php echo $h($s['settle_note']); ?></td>
                  <td><?php echo $h($s['settled_by']); ?></td><td><?php echo date('d/m/Y', strtotime($s['settled_at'])); ?></td></tr>
              <?php } ?>
            </tbody></table></div></div>
          </div>
          <?php } ?>

        </div>
        <?php echo $view->subFooter('../'); ?>

<script>
document.querySelectorAll('.settle-form').forEach(function (form) {
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var what = { savings: 'add this money to the member\'s savings', loan: 'pay this money into the chosen loan', refund: 'record the refund' }[form.dataset.choice];
        if (!confirm('Settle this overpayment: ' + what + '?')) { return; }
        var data = new FormData(form);
        data.append('action', 'settle');
        data.append('overpayment_id', form.dataset.id);
        fetch('../ajax/overpayment.php', { method: 'POST', body: data, credentials: 'same-origin' })
            .then(function (r) { return r.text(); })
            .then(function (reply) { if (reply.trim() === '1') { location.reload(); } else { alert(reply.trim() || 'Could not settle. Please try again.'); } })
            .catch(function () { alert('Network error. Please try again.'); });
    });
});
</script>
