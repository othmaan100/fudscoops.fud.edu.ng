<?php
    // Special Commodity Request (member): items the cooperative does not stock, each with an estimated price.
    // Workflow and checks: config/classes/CommodityLoan.php (request_type 'special'); saved by ajax/commodity_workflow.php.
    // The Treasurer corrects the prices and approves some or all items, the Chairman authorizes, the Treasurer releases
    // the items, and deductions start the month after.
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    require_once('../config/classes/CommodityLoan.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../auth/') || !User::isStaff('../auth/logout.php')) {
        exit();
    }
    $memberId = $user->getMemberId($user->getEmployeeId($_SESSION['username']));
    $open = $memberId ? CommodityLoan::openSpecialRequest($memberId) : null;
    $past = $memberId ? array_values(array_filter(CommodityLoan::memberRequests($memberId), function ($r) { return CommodityLoan::isSpecial($r); })) : [];
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
            <h2><i class="mdi mdi-cart-plus"></i> Special Commodity Request</h2>
            <p>Ask for items the cooperative does not have in stock. List each item with what you expect it to cost; the Treasurer
              confirms the prices, and you repay by monthly salary deduction.</p>
            <div class="fud-hero-actions">
              <a href="commodity_request.php"><i class="mdi mdi-cart-outline"></i> Items in stock</a>
              <a href="view_commodity_request.php"><i class="mdi mdi-format-list-bulleted"></i> My requests</a>
            </div>
          </div>

          <?php if (!$memberId) { ?>
            <div class="fud-notice"><i class="mdi mdi-alert-circle"></i><span>Special requests are open to approved members only.</span>
              <a href="index.php">Dashboard <i class="mdi mdi-arrow-right"></i></a></div>
          <?php } elseif ($open) { ?>
            <div class="fud-notice info"><i class="mdi mdi-information-outline"></i>
              <span>Your special request #<?php echo (int) $open['application_id']; ?> is <?php echo strtolower(CommodityLoan::stageLabel($open)); ?>.
                You can make another once its items are released or it is declined.</span>
              <a href="viewRequestDetails.php?applicationId=<?php echo (int) $open['application_id']; ?>">View it <i class="mdi mdi-arrow-right"></i></a></div>
          <?php } else { ?>

          <form id="specialRequest" autocomplete="off" data-rate="<?php echo $rate; ?>">
            <input type="hidden" name="action" value="submit_special">
            <input type="hidden" name="items_json" id="itemsJson">
            <div class="row">
              <div class="col-lg-8">
                <div class="fud-section">
                  <div class="fud-section-head"><h5><span class="fud-num">1</span> Items you need</h5>
                    <small class="text-muted">Up to <?php echo CommodityLoan::MAX_SPECIAL_ITEMS; ?> items</small></div>
                  <div class="fud-section-body">
                    <div class="table-responsive">
                      <table class="table fud-table mb-2" id="itemTable">
                        <thead><tr><th style="min-width:160px">Item</th><th style="min-width:160px">Description <small class="text-muted">(brand, size…)</small></th>
                          <th class="num" style="width:90px">Qty</th><th class="num" style="width:140px">Estimated price each (₦)</th><th class="num">Amount (₦)</th><th></th></tr></thead>
                        <tbody></tbody>
                      </table>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="addItem"><i class="mdi mdi-plus"></i> Add another item</button>
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
                  <div class="fud-section-head"><h5><i class="mdi mdi-file-document-box"></i> Estimate</h5></div>
                  <div class="fud-section-body">
                    <dl class="fud-sum">
                      <dt>Items</dt><dd id="sumAmount">–</dd>
                      <dt>Interest (<?php echo rtrim(rtrim(number_format($rate, 2), '0'), '.'); ?>%)</dt><dd id="sumInterest">–</dd>
                      <dt class="fud-total">Total to repay</dt><dd class="fud-total" id="sumTotal">–</dd>
                      <dt>Monthly deduction</dt><dd id="sumMonthly">–</dd>
                    </dl>
                    <p class="fud-sum-note">Based on your estimated prices. The Treasurer confirms the real prices and may approve only some of the items; you will see the final amounts on your request.</p>
                    <div class="custom-control custom-checkbox mb-3">
                      <input type="checkbox" class="custom-control-input" id="agree" required>
                      <label class="custom-control-label" for="agree">I agree that the monthly deduction is taken from my salary until the items are repaid.</label>
                    </div>
                    <div class="msg"></div>
                    <button type="submit" class="btn btn-primary btn-block btn-lg" id="submitBtn"><i class="mdi mdi-send"></i> Submit request</button>
                    <div class="fud-next">
                      <div class="fud-next-title">What happens next</div>
                      <ol><li>The Treasurer confirms the prices and approves the items</li><li>The Chairman authorizes the request</li>
                        <li>The Treasurer releases the items to you</li><li>Deductions start the following month</li></ol>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </form>
          <?php } ?>

          <?php if ($past) { ?>
            <div class="fud-section">
              <div class="fud-section-head"><h5><i class="mdi mdi-history"></i> My special requests</h5></div>
              <div class="fud-section-body">
                <div class="table-responsive">
                  <table class="table fud-table mb-0">
                    <thead><tr><th>#</th><th>Requested</th><th class="num">Items (₦)</th><th class="num">To repay (₦)</th><th class="num">Balance (₦)</th><th>Stage</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($past as $r) { $repaying = in_array($r['status'], ['supplied', 'repaid'], true); ?>
                      <tr><td><?php echo (int) $r['application_id']; ?></td>
                        <td class="text-nowrap"><?php echo date('d M Y', strtotime($r['application_date'])); ?></td>
                        <td class="num"><?php echo number_format($r['total_amount'], 2); ?></td>
                        <td class="num"><?php echo $r['total_payable'] !== null ? number_format($r['payable'], 2) : '–'; ?></td>
                        <td class="num"><?php echo $repaying ? number_format($r['balance'], 2) : '–'; ?></td>
                        <td><?php echo CommodityLoan::stagePill($r); ?></td>
                        <td><a class="btn btn-sm btn-outline-primary" href="viewRequestDetails.php?applicationId=<?php echo (int) $r['application_id']; ?>">Open</a></td></tr>
                    <?php } ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          <?php } ?>

        </div>
        <?php echo $view->subFooter('../'); ?>

<script>
(function () {
    var form = document.getElementById('specialRequest');
    if (!form) { return; }
    var rate = parseFloat(form.dataset.rate) || 0;
    var maxItems = <?php echo CommodityLoan::MAX_SPECIAL_ITEMS; ?>;
    var body = document.querySelector('#itemTable tbody');
    function money(v) { return '₦' + v.toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
    function num(v) { return parseFloat(String(v).replace(/[^0-9.]/g, '')) || 0; }

    function addRow() {
        if (body.rows.length >= maxItems) { alert('You can request at most ' + maxItems + ' items at a time.'); return; }
        var tr = document.createElement('tr');
        tr.innerHTML = '<td><input type="text" class="form-control form-control-sm it-name" maxlength="100" placeholder="e.g. Refrigerator"></td>'
            + '<td><input type="text" class="form-control form-control-sm it-desc" maxlength="255" placeholder="e.g. LG 2-door, 300 litres"></td>'
            + '<td class="num"><input type="number" class="form-control form-control-sm it-qty" min="1" value="1" style="text-align:right"></td>'
            + '<td class="num"><input type="text" inputmode="decimal" class="form-control form-control-sm it-price" placeholder="0.00" style="text-align:right"></td>'
            + '<td class="num it-total">0.00</td>'
            + '<td><button type="button" class="btn btn-sm btn-light it-remove" title="Remove"><i class="mdi mdi-close"></i></button></td>';
        body.appendChild(tr);
        tr.querySelector('.it-name').focus();
        update();
    }
    function items() {
        return Array.prototype.map.call(body.rows, function (tr) {
            return { name: tr.querySelector('.it-name').value.trim(), desc: tr.querySelector('.it-desc').value.trim(),
                     qty: parseInt(tr.querySelector('.it-qty').value, 10) || 0, price: num(tr.querySelector('.it-price').value) };
        });
    }
    function update() {
        var total = 0;
        Array.prototype.forEach.call(body.rows, function (tr, i) {
            var it = items()[i], line = it.qty * it.price;
            tr.querySelector('.it-total').textContent = line.toLocaleString('en-NG', { minimumFractionDigits: 2 });
            tr.querySelector('.it-remove').disabled = body.rows.length === 1;
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
    document.getElementById('addItem').addEventListener('click', addRow);
    body.addEventListener('click', function (e) {
        var b = e.target.closest('.it-remove');
        if (b && body.rows.length > 1) { b.closest('tr').remove(); update(); }
    });
    form.addEventListener('input', update);
    form.addEventListener('change', update);
    addRow();

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var msg = form.querySelector('.msg');
        var list = items().filter(function (it) { return it.name || it.desc || it.price; });
        var bad = list.filter(function (it) { return !it.name || it.qty < 1 || it.price <= 0; });
        if (!list.length) { msg.innerHTML = '<div class="alert alert-warning">Add at least one item.</div>'; return; }
        if (bad.length) { msg.innerHTML = '<div class="alert alert-warning">Give each item a name, a quantity and an estimated price.</div>'; return; }
        var total = update();
        if (!confirm('Submit this special request for ' + list.length + ' item' + (list.length === 1 ? '' : 's') + ' (about ' + money(total) + ')?')) { return; }
        document.getElementById('itemsJson').value = JSON.stringify(list);
        var btn = document.getElementById('submitBtn');
        btn.disabled = true;
        msg.innerHTML = '<div class="alert alert-primary">Submitting, please wait...</div>';
        fetch('../ajax/commodity_workflow.php', { method: 'POST', body: new FormData(form), credentials: 'same-origin' })
            .then(function (r) { return r.text(); })
            .then(function (reply) {
                reply = reply.trim();
                if (reply === '1') {
                    msg.innerHTML = '<div class="alert alert-success">Request submitted.</div>';
                    setTimeout(function () { location.reload(); }, 800);
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
