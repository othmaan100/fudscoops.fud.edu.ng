<?php
    // Member: loans repaid by more than they owed, and what to do with the excess (LoanControls)
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    require_once('../config/classes/MemberG1.php');
    require_once('../config/classes/LoanControls.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../auth/') || !User::isStaff('../auth/logout.php')) {
        exit();
    }
    $memberId = $user->getMemberId($user->getEmployeeId($_SESSION['username']));
    $open = LoanControls::overpayments(null, $memberId);
    $settled = LoanControls::overpayments('settled', $memberId);
    $loans = LoanControls::loansToPay($memberId);
    $banks = (new MemberG1())->getBanks();
    $h = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
    $choiceText = ['savings' => 'Add to my savings', 'loan' => 'Pay another loan', 'refund' => 'Refund to my bank account'];

    echo $view->subHeader('../');
?>
    <?php echo $view->subPartialNav('../'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->staffSideNav(); ?>
      <div class="main-panel">
        <div class="content-wrapper fud-dash fud-apply">

          <div class="fud-hero">
            <div class="fud-hero-date">Loans</div>
            <h2><i class="mdi mdi-cash-refund"></i> Loan Overpayments</h2>
            <p>More was deducted than your loan owed. Tell the Treasurer what to do with the extra money.</p>
          </div>

          <?php if (!$open) { ?>
            <div class="fud-notice success"><i class="mdi mdi-check-circle"></i><span>You have no overpayment waiting for a decision.</span>
              <a href="index.php">Dashboard <i class="mdi mdi-arrow-right"></i></a></div>
          <?php } ?>

          <?php foreach ($open as $op) { ?>
            <div class="fud-section">
              <div class="fud-section-head"><h5><?php echo $h($op['loan_type_name']); ?> #<?php echo (int) $op['loan_id']; ?>: overpaid by ₦<?php echo number_format($op['amount'], 2); ?></h5>
                <span class="fud-pill wait"><?php echo $op['status'] === 'open' ? 'Your decision needed' : 'With the Treasurer'; ?></span></div>
              <div class="fud-section-body">
                <?php if ($op['status'] === 'chosen') { ?>
                  <div class="fud-notice info mb-3"><i class="mdi mdi-clock-outline"></i><span>You chose: <strong><?php echo $h($choiceText[$op['member_choice']]); ?></strong>
                    <?php if ($op['member_choice'] === 'refund') { echo '(' . $h($op['bank_name'] . ', ' . $op['account_number'] . ', ' . $op['account_name']) . ')'; } ?>.
                    <?php echo $op['member_choice'] === 'savings' ? 'It is waiting for the Treasurer to add ₦' . number_format($op['amount'], 2) . ' to your savings; it then shows in your <a href="savings/savings_history.php">Savings History</a>.' : 'It is waiting for the Treasurer.'; ?>
                    You can change your choice until then.</span></div>
                <?php } ?>
                <form class="overpay-form" data-id="<?php echo (int) $op['overpayment_id']; ?>">
                  <?php foreach ($choiceText as $key => $label) {
                      $disabled = $key === 'loan' && !$loans; ?>
                    <div class="custom-control custom-radio mb-2">
                      <input type="radio" class="custom-control-input" id="c<?php echo $op['overpayment_id'] . $key; ?>" name="choice" value="<?php echo $key; ?>"
                             <?php echo $op['member_choice'] === $key ? 'checked' : ''; ?> <?php echo $disabled ? 'disabled' : ''; ?> required>
                      <label class="custom-control-label" for="c<?php echo $op['overpayment_id'] . $key; ?>"><?php echo $label; ?><?php echo $disabled ? ' <small class="text-muted">(you have no other loan being repaid)</small>' : ''; ?></label>
                    </div>
                  <?php } ?>
                  <div class="choice-loan mt-2" style="display:none">
                    <select name="target_loan_id" class="form-control col-md-6">
                      <?php foreach ($loans as $l) { ?>
                        <option value="<?php echo (int) $l['loan_id']; ?>" <?php echo (int) $op['target_loan_id'] === (int) $l['loan_id'] ? 'selected' : ''; ?>>
                          <?php echo $h($l['loan_type_name']); ?> #<?php echo (int) $l['loan_id']; ?> – balance ₦<?php echo number_format($l['balance'], 2); ?></option>
                      <?php } ?>
                    </select>
                  </div>
                  <div class="choice-refund form-row mt-2" style="display:none">
                    <div class="form-group col-md-4"><label>Bank</label>
                      <select name="bank_name" class="form-control"><option value="">Select a bank</option>
                        <?php foreach ($banks as $b) { ?><option <?php echo $op['bank_name'] === $b['name'] ? 'selected' : ''; ?>><?php echo $h($b['name']); ?></option><?php } ?>
                      </select></div>
                    <div class="form-group col-md-4"><label>Account number</label>
                      <input type="text" inputmode="numeric" name="account_number" class="form-control" maxlength="10" pattern="\d{10}" value="<?php echo $h($op['account_number']); ?>"></div>
                    <div class="form-group col-md-4"><label>Account name</label>
                      <input type="text" name="account_name" class="form-control" value="<?php echo $h($op['account_name']); ?>"></div>
                  </div>
                  <button type="submit" class="btn btn-primary mt-2"><i class="mdi mdi-send"></i> Save my choice</button>
                  <div class="msg"></div>
                </form>
              </div>
            </div>
          <?php } ?>

          <?php if ($settled) { ?>
            <div class="fud-section">
              <div class="fud-section-head"><h5>Settled</h5></div>
              <div class="fud-section-body">
                <table class="table fud-table"><thead><tr><th>Loan</th><th class="num">Amount (₦)</th><th>What was done</th><th>Date</th></tr></thead><tbody>
                  <?php foreach ($settled as $s) { ?>
                    <tr><td><?php echo $h($s['loan_type_name']); ?> #<?php echo (int) $s['loan_id']; ?></td><td class="num"><?php echo number_format($s['amount'], 2); ?></td>
                      <td><?php echo $h($s['settle_note']); ?></td><td><?php echo date('d/m/Y', strtotime($s['settled_at'])); ?></td></tr>
                  <?php } ?>
                </tbody></table>
              </div>
            </div>
          <?php } ?>

        </div>
        <?php echo $view->subFooter('../'); ?>

<script>
document.querySelectorAll('.overpay-form').forEach(function (form) {
    function show() {
        var choice = (form.querySelector('input[name="choice"]:checked') || {}).value;
        form.querySelector('.choice-loan').style.display = choice === 'loan' ? '' : 'none';
        form.querySelector('.choice-refund').style.display = choice === 'refund' ? 'flex' : 'none';
    }
    form.addEventListener('change', show);
    show();
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var data = new FormData(form);
        data.append('action', 'choose');
        data.append('overpayment_id', form.dataset.id);
        var msg = form.querySelector('.msg');
        fetch('../ajax/overpayment.php', { method: 'POST', body: data, credentials: 'same-origin' })
            .then(function (r) { return r.text(); })
            .then(function (reply) {
                if (reply.trim() === '1') { msg.innerHTML = '<div class="alert alert-success mt-3">Saved. The Treasurer will settle it.</div>'; setTimeout(function () { location.reload(); }, 1200); return; }
                msg.innerHTML = '<div class="alert alert-danger mt-3"></div>';
                msg.firstChild.textContent = reply.trim() || 'Could not save. Please try again.';
            })
            .catch(function () { msg.innerHTML = '<div class="alert alert-danger mt-3">Network error. Please try again.</div>'; });
    });
});
</script>
