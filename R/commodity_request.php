<?php
    // Commodity Request (member). Workflow and checks: config/classes/CommodityLoan.php; saved by ajax/commodity_workflow.php
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    require_once('../config/classes/CommodityLoan.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../auth/') || !User::isStaff('../auth/logout.php')) {
        exit();
    }
    $memberId = $user->getMemberId($user->getEmployeeId($_SESSION['username']));
    $supply = CommodityLoan::openSupply();
    $existing = $supply ? CommodityLoan::memberRequestInSupply($memberId, $supply['commodity_name_id']) : null;
    $items = ($supply && !$existing) ? CommodityLoan::supplyItems($supply['commodity_name_id'], $_SESSION['username']) : [];
    $rate = CommodityLoan::interestRate();
    $maxTenor = CommodityLoan::maxTenor();
    $h = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };

    echo $view->subHeader('../');
?>
    <?php echo $view->subPartialNav('../'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->staffSideNav(); ?>
      <div class="main-panel">
        <div class="content-wrapper fud-dash fud-apply">

          <div class="fud-hero">
            <div class="fud-hero-date">FUD Staff Cooperative Society Limited</div>
            <h2><i class="mdi mdi-cart-outline"></i> Commodity Request</h2>
            <p>Choose items from the current supply and repay by monthly salary deduction.</p>
            <?php if ($supply) { ?>
              <div class="fud-hero-actions"><span class="fud-hero-chip"><i class="mdi mdi-calendar-clock"></i> <?php echo $h($supply['commodity_name']); ?> ·
                closes <?php echo date('d M Y, g:ia', strtotime($supply['closing_date'])); ?> · <span id="countdown" data-close="<?php echo $h(date('c', strtotime($supply['closing_date']))); ?>"></span></span></div>
            <?php } ?>
          </div>

          <?php if (!$supply) { ?>
            <div class="fud-notice info"><i class="mdi mdi-information-outline"></i>
              <span>No commodity supply is open for requests at the moment.</span>
              <a href="view_commodity_request.php">My requests <i class="mdi mdi-arrow-right"></i></a></div>
          <?php } elseif ($existing) { ?>
            <div class="fud-notice"><i class="mdi mdi-information-outline"></i>
              <span>You already have a request for <?php echo $h($supply['commodity_name']); ?> (<?php echo strtolower(CommodityLoan::stageLabel($existing)); ?>).</span>
              <a href="viewRequestDetails.php?applicationId=<?php echo (int) $existing['application_id']; ?>">View it <i class="mdi mdi-arrow-right"></i></a></div>
          <?php } else { ?>

          <form id="commodityRequest" autocomplete="off" data-rate="<?php echo $rate; ?>">
            <input type="hidden" name="action" value="submit_request">
            <input type="hidden" name="supply_id" value="<?php echo (int) $supply['commodity_name_id']; ?>">
            <div class="row">
              <div class="col-lg-8">
                <div class="fud-section">
                  <div class="fud-section-head"><h5><span class="fud-num">1</span> Choose items</h5>
                    <small class="text-muted">Limits depend on your grade and what is left</small></div>
                  <div class="fud-section-body">
                    <?php if (!$items) { ?><p class="fud-empty">No items in this supply.</p><?php } else { ?>
                    <div class="table-responsive">
                      <table class="table fud-table">
                        <thead><tr><th>Item</th><th class="num">Unit price (₦)</th><th class="num">You can take</th><th class="num" style="width:120px">Quantity</th><th class="num">Amount (₦)</th></tr></thead>
                        <tbody>
                        <?php foreach ($items as $item) { $max = (int) $item['max_for_member']; ?>
                          <tr class="<?php echo $max < 1 ? 'text-muted' : ''; ?>">
                            <td><?php echo $h($item['commodity_item']); ?><?php if (strcasecmp(trim($item['commodity_description']), trim($item['commodity_item'])) !== 0) { ?><div class="text-muted small"><?php echo $h($item['commodity_description']); ?></div><?php } ?></td>
                            <td class="num"><?php echo number_format($item['unit_price'], 2); ?></td>
                            <td class="num"><?php echo $max < 1 ? 'Sold out' : $max; ?></td>
                            <td class="num"><input type="number" class="form-control form-control-sm fud-qty" name="qty[<?php echo (int) $item['commodity_supply_item_id']; ?>]"
                                   min="0" max="<?php echo $max; ?>" value="0" data-price="<?php echo (float) $item['unit_price']; ?>" <?php echo $max < 1 ? 'disabled' : ''; ?>></td>
                            <td class="num fud-line-total">0.00</td>
                          </tr>
                        <?php } ?>
                        </tbody>
                      </table>
                    </div>
                    <?php } ?>
                  </div>
                </div>

                <div class="fud-section">
                  <div class="fud-section-head"><h5><span class="fud-num">2</span> Repay over</h5></div>
                  <div class="fud-section-body">
                    <div class="fud-tenors">
                      <?php for ($m = 1; $m <= $maxTenor; $m++) { ?>
                        <label class="fud-tenor"><input type="radio" name="tenor" value="<?php echo $m; ?>" <?php echo $m === 1 ? 'required' : ''; ?>>
                          <span><strong><?php echo $m; ?></strong> month<?php echo $m > 1 ? 's' : ''; ?></span></label>
                      <?php } ?>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-lg-4">
                <div class="fud-section fud-summary">
                  <div class="fud-section-head"><h5><i class="mdi mdi-file-document-box"></i> Summary</h5></div>
                  <div class="fud-section-body">
                    <dl class="fud-sum">
                      <dt>Items</dt><dd id="sumAmount">–</dd>
                      <dt>Interest (<?php echo rtrim(rtrim(number_format($rate, 2), '0'), '.'); ?>%)</dt><dd id="sumInterest">–</dd>
                      <dt class="fud-total">Total to repay</dt><dd class="fud-total" id="sumTotal">–</dd>
                      <dt>Monthly deduction</dt><dd id="sumMonthly">–</dd>
                    </dl>
                    <p class="fud-sum-note">An estimate. The Treasurer may adjust quantities and months; the Chairman then authorizes the request.</p>
                    <div class="custom-control custom-checkbox mb-3">
                      <input type="checkbox" class="custom-control-input" id="agree" required>
                      <label class="custom-control-label" for="agree">I agree that the monthly deduction is taken from my salary until the commodity is repaid.</label>
                    </div>
                    <div class="msg"></div>
                    <button type="submit" class="btn btn-primary btn-block btn-lg" id="submitBtn"><i class="mdi mdi-send"></i> Submit request</button>
                    <div class="fud-next">
                      <div class="fud-next-title">What happens next</div>
                      <ol><li>The Treasurer fixes the quantities</li><li>The Chairman authorizes them</li>
                        <li>You collect the items</li><li>Deductions start the following month</li></ol>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </form>
          <?php } ?>

        </div>
        <?php echo $view->subFooter('../'); ?>

<script>
(function () {
    var countdown = document.getElementById('countdown');
    if (countdown) {
        var close = new Date(countdown.dataset.close).getTime();
        var tick = function () {
            var left = close - Date.now();
            if (left <= 0) { countdown.textContent = 'closed'; return; }
            var d = Math.floor(left / 86400000), hrs = Math.floor(left % 86400000 / 3600000), min = Math.floor(left % 3600000 / 60000);
            countdown.textContent = (d ? d + 'd ' : '') + hrs + 'h ' + min + 'm left';
            setTimeout(tick, 30000);
        };
        tick();
    }

    var form = document.getElementById('commodityRequest');
    if (!form) { return; }
    var rate = parseFloat(form.dataset.rate) || 0;
    function money(v) { return '₦' + v.toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
    function update() {
        var total = 0;
        form.querySelectorAll('.fud-qty').forEach(function (input) {
            var max = parseInt(input.max, 10) || 0, qty = parseInt(input.value, 10) || 0;
            if (qty > max) { qty = max; input.value = max; }
            if (qty < 0) { qty = 0; input.value = 0; }
            var line = qty * parseFloat(input.dataset.price);
            input.closest('tr').querySelector('.fud-line-total').textContent = line.toLocaleString('en-NG', { minimumFractionDigits: 2 });
            total += line;
        });
        var months = parseInt((form.querySelector('input[name="tenor"]:checked') || {}).value, 10) || 0;
        var interest = Math.round(total * rate) / 100;
        document.getElementById('sumAmount').textContent = total ? money(total) : '–';
        document.getElementById('sumInterest').textContent = total ? money(interest) : '–';
        document.getElementById('sumTotal').textContent = total ? money(total + interest) : '–';
        document.getElementById('sumMonthly').textContent = total && months ? money(Math.round((total + interest) / months * 100) / 100) + ' × ' + months : '–';
        return total;
    }
    form.addEventListener('input', update);
    form.addEventListener('change', update);

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var total = update();
        var msg = form.querySelector('.msg');
        if (!total) { msg.innerHTML = '<div class="alert alert-warning">Enter a quantity for at least one item.</div>'; return; }
        if (!confirm('Submit this commodity request (' + money(total) + ' of items)?')) { return; }
        var btn = document.getElementById('submitBtn');
        btn.disabled = true;
        msg.innerHTML = '<div class="alert alert-primary">Submitting, please wait...</div>';
        fetch('../ajax/commodity_workflow.php', { method: 'POST', body: new FormData(form), credentials: 'same-origin' })
            .then(function (r) { return r.text(); })
            .then(function (reply) {
                reply = reply.trim();
                if (reply === '1') {
                    msg.innerHTML = '<div class="alert alert-success">Request submitted.</div>';
                    setTimeout(function () { window.location = 'view_commodity_request.php'; }, 800);
                    return;
                }
                msg.innerHTML = '<div class="alert alert-danger"></div>';
                msg.firstChild.textContent = reply || 'Could not submit the request. Please try again.';
                btn.disabled = false;
            })
            .catch(function () { msg.innerHTML = '<div class="alert alert-danger">Network error. Please try again.</div>'; btn.disabled = false; });
    });
})();
</script>
