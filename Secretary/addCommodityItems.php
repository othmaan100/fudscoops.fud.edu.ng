<?php
    // Items of one commodity supply: see and update what the supply already has, and add more items.
    // Existing items are changed by ajax/commodity_supply.php (CommodityLoan::updateSupplyItem / removeSupplyItem);
    // new items are saved by ajax/commodity_ajax.php (action add_commodity_supply_items, Commodity::saveCommoditySupplyItems).
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    require_once('../config/classes/Commodity.php');
    require_once('../config/classes/CommodityLoan.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../auth/') || !User::isSecretary('../auth/logout.php')) {
        exit();
    }
    $commodity = new Commodity();
    $supplyId = isset($_GET['commodity_name_id']) ? (int) $_GET['commodity_name_id'] : 0;
    $supply = CommodityLoan::supply($supplyId);
    $items = $supply ? CommodityLoan::supplyItemsList($supplyId) : [];
    $h = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
    $when = function ($v) { return date('d M Y, g:ia', strtotime($v)); };
    $pill = ['open' => ['Open now', 'ok'], 'upcoming' => ['Upcoming', 'wait'], 'closed' => ['Closed', '']];
    $totalValue = array_sum(array_map(function ($i) { return $i['unit_price'] * $i['allocated_quantity']; }, $items));
    $totalRequested = array_sum(array_map(function ($i) { return $i['unit_price'] * $i['requested']; }, $items));
    $itemNames = array_count_values(array_column($items, 'item_id'));

    echo $view->subHeader('../');
?>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <?php echo $view->subPartialNav('../'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->SecretarySideNav(); ?>
      <div class="main-panel">
        <div class="content-wrapper fud-dash fud-apply">

          <?php if (!$supply) { ?>
            <div class="fud-notice"><i class="mdi mdi-alert-circle"></i><span>This commodity supply was not found.</span>
              <a href="add_commodity_name.php">Commodity Supplies <i class="mdi mdi-arrow-right"></i></a></div>
          <?php } else { $p = $pill[$supply['state']]; ?>

          <div class="fud-hero">
            <div class="fud-hero-date">Commodity supply</div>
            <h2><i class="mdi mdi-format-list-bulleted"></i> <?php echo $h($supply['commodity_name']); ?> <span class="fud-hero-chip"><?php echo $p[0]; ?></span></h2>
            <p><?php echo $when($supply['opening_date']); ?> → <?php echo $when($supply['closing_date']); ?></p>
            <div class="fud-hero-actions">
              <a href="add_commodity_name.php"><i class="mdi mdi-arrow-left"></i> Commodity Supplies</a>
              <a href="view_items.php"><i class="mdi mdi-package-variant"></i> All items</a>
            </div>
          </div>

          <div class="fud-stats">
            <div class="fud-stat"><div class="fud-stat-icon fud-blue"><i class="mdi mdi-format-list-bulleted"></i></div>
              <div><div class="fud-stat-label">Items in this supply</div><div class="fud-stat-value"><?php echo count($items); ?></div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-purple"><i class="mdi mdi-cash-multiple"></i></div>
              <div><div class="fud-stat-label">Value of all items</div><div class="fud-stat-value">₦<?php echo number_format($totalValue, 2); ?></div></div></div>
            <div class="fud-stat"><div class="fud-stat-icon fud-green"><i class="mdi mdi-cart-outline"></i></div>
              <div><div class="fud-stat-label">Requested by members</div><div class="fud-stat-value">₦<?php echo number_format($totalRequested, 2); ?></div>
                <div class="fud-stat-sub"><?php echo (int) $supply['requests']; ?> request(s)</div></div></div>
          </div>

          <!-- Items already in the supply -->
          <div class="fud-section">
            <div class="fud-section-head"><h5><i class="mdi mdi-package-variant-closed"></i> Items in this supply</h5>
              <small class="text-muted">Change a price or quantity, then Save</small></div>
            <div class="fud-section-body">
              <?php if (!$items) { ?>
                <p class="fud-empty">This supply has no items yet<?php echo $supply['state'] === 'open' ? ', so members cannot request anything' : ''; ?>. Add them below.</p>
              <?php } else { ?>
              <div class="table-responsive">
                <table class="table fud-table" id="supplyItems">
                  <thead><tr><th>Item</th><th class="num">Unit price (₦)</th><th class="num">Quantity</th><th class="num">Requested</th>
                    <th class="num">Left</th><th class="num">Value (₦)</th><th></th></tr></thead>
                  <tbody>
                  <?php foreach ($items as $i) { $dup = $itemNames[$i['item_id']] > 1; ?>
                    <tr data-row="<?php echo (int) $i['commodity_supply_item_id']; ?>" data-requested="<?php echo (int) $i['requested']; ?>">
                      <td class="fud-wrap"><?php echo $h($i['commodity_item'] ?: 'Item #' . $i['item_id']); ?>
                        <?php if ($dup) { ?><span class="fud-pill wait ml-1" title="This item appears more than once in the supply">listed twice</span><?php } ?>
                        <?php if ($i['request_count']) { ?><div class="text-muted small"><?php echo (int) $i['request_count']; ?> request(s)</div><?php } ?></td>
                      <td class="num"><input type="number" class="form-control form-control-sm fud-inline js-price" min="0.01" step="0.01" value="<?php echo (float) $i['unit_price']; ?>"></td>
                      <td class="num"><input type="number" class="form-control form-control-sm fud-inline js-qty" min="<?php echo max(1, (int) $i['requested']); ?>" step="1" value="<?php echo (int) $i['allocated_quantity']; ?>"></td>
                      <td class="num"><?php echo (int) $i['requested']; ?></td>
                      <td class="num js-left"><?php echo (int) $i['remaining_quantity']; ?></td>
                      <td class="num js-value"><?php echo number_format($i['unit_price'] * $i['allocated_quantity'], 2); ?></td>
                      <td class="text-nowrap">
                        <button type="button" class="btn btn-sm btn-primary js-save" disabled>Save</button>
                        <?php if (!$i['request_count']) { ?>
                          <button type="button" class="btn btn-sm btn-outline-danger js-remove" title="Remove from this supply"><i class="mdi mdi-delete"></i></button>
                        <?php } ?>
                      </td>
                    </tr>
                  <?php } ?>
                  </tbody>
                </table>
              </div>
              <p class="text-muted small mb-0 mt-2">The quantity cannot be lower than what members have requested. Items members have requested cannot be removed.</p>
              <?php } ?>
            </div>
          </div>

          <!-- Add more items -->
          <div class="row">
            <div class="col-lg-5">
              <div class="fud-section">
                <div class="fud-section-head"><h5><i class="mdi mdi-plus-circle"></i> Add items</h5></div>
                <div class="fud-section-body">
                  <div class="form-group">
                    <label for="item_id">Item</label>
                    <select id="item_id" class="form-control">
                      <option value="">-- Select item --</option>
                      <?php echo $commodity->getAllItemsDropdown(); ?>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="quantity">Quantity</label>
                    <input type="number" class="form-control" id="quantity" placeholder="e.g. 50" min="1" step="1">
                  </div>
                  <p class="text-danger small" id="errorTxt"></p>
                  <button type="button" class="btn btn-outline-primary btn-block" id="addToSupply"><i class="mdi mdi-playlist-plus"></i> Add to the list</button>
                  <small class="text-muted d-block mt-2">An item already in the supply gets the extra quantity and the item's current price.</small>
                </div>
              </div>
            </div>
            <div class="col-lg-7">
              <div class="fud-section">
                <div class="fud-section-head"><h5>To be added</h5></div>
                <div class="fud-section-body">
                  <div class="table-responsive">
                    <table class="table fud-table">
                      <thead><tr><th>Item</th><th class="num">Quantity</th><th class="num">Unit price (₦)</th><th class="num">Value (₦)</th><th></th></tr></thead>
                      <tbody id="tb"><tr><td colspan="5" class="fud-empty">Nothing added yet.</td></tr></tbody>
                    </table>
                  </div>
                  <button type="button" class="btn btn-primary btn-lg" id="btnSaveSupply" style="display:none"><i class="mdi mdi-content-save"></i> Save to this supply</button>
                </div>
              </div>
            </div>
          </div>

          <?php } ?>
        </div>
        <?php echo $view->subFooter('../'); ?>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
(function () {
    var supplyId = <?php echo (int) $supplyId; ?>;
    if (!document.getElementById('item_id')) { return; }
    var money = function (v) { return Number(v).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); };

    // ---------- items already in the supply: edit price / quantity, remove ----------
    document.querySelectorAll('#supplyItems tbody tr').forEach(function (tr) {
        var price = tr.querySelector('.js-price'), qty = tr.querySelector('.js-qty'), save = tr.querySelector('.js-save');
        var requested = parseInt(tr.dataset.requested, 10) || 0;
        var start = price.value + '|' + qty.value;
        function changed() {
            var q = parseInt(qty.value, 10) || 0, p = parseFloat(price.value) || 0;
            tr.querySelector('.js-left').textContent = Math.max(0, q - requested);
            tr.querySelector('.js-value').textContent = money(p * q);
            save.disabled = (price.value + '|' + qty.value) === start;
        }
        price.addEventListener('input', changed);
        qty.addEventListener('input', changed);
        save.addEventListener('click', function () { send(tr, 'item_update', { price: price.value, allocated: qty.value }); });
        var remove = tr.querySelector('.js-remove');
        if (remove) {
            remove.addEventListener('click', function () {
                if (confirm('Remove ' + tr.cells[0].innerText.split('\n')[0] + ' from this supply?')) { send(tr, 'item_remove', {}); }
            });
        }
    });
    function send(tr, action, fields) {
        var data = new FormData();
        data.append('action', action);
        data.append('row_id', tr.dataset.row);
        Object.keys(fields).forEach(function (k) { data.append(k, fields[k]); });
        fetch('../ajax/commodity_supply.php', { method: 'POST', body: data, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (res) { if (res.ok) { location.reload(); } else { alert(res.message || 'Could not save.'); } })
            .catch(function () { alert('Network or server error. Please try again.'); });
    }

    // ---------- add new items ----------
    $('#item_id').select2({ placeholder: '-- Select item --', allowClear: true, width: '100%' });
    var list = [];
    function render() {
        var tb = document.getElementById('tb'), total = 0;
        if (!list.length) {
            tb.innerHTML = '<tr><td colspan="5" class="fud-empty">Nothing added yet.</td></tr>';
            document.getElementById('btnSaveSupply').style.display = 'none';
            return;
        }
        tb.innerHTML = '';
        list.forEach(function (item, i) {
            var value = item.unitPrice * item.quantity;
            total += value;
            var tr = document.createElement('tr');
            tr.innerHTML = '<td></td><td class="num">' + item.quantity + '</td><td class="num">' + money(item.unitPrice) + '</td><td class="num">' + money(value)
                + '</td><td><button type="button" class="btn btn-sm btn-outline-danger" data-i="' + i + '"><i class="mdi mdi-close"></i></button></td>';
            tr.cells[0].textContent = item.itemName;
            tb.appendChild(tr);
        });
        var foot = document.createElement('tr');
        foot.innerHTML = '<td colspan="3"><strong>Total</strong></td><td class="num"><strong>' + money(total) + '</strong></td><td></td>';
        tb.appendChild(foot);
        document.getElementById('btnSaveSupply').style.display = '';
    }
    document.getElementById('tb').addEventListener('click', function (e) {
        var b = e.target.closest('button[data-i]');
        if (b) { list.splice(parseInt(b.dataset.i, 10), 1); render(); }
    });
    document.getElementById('addToSupply').addEventListener('click', function () {
        var err = document.getElementById('errorTxt');
        err.textContent = '';
        var opt = $('#item_id').find(':selected'), itemId = $('#item_id').val(), qty = parseInt($('#quantity').val(), 10);
        if (!itemId) { err.textContent = 'Choose an item.'; return; }
        if (isNaN(qty) || qty < 1) { err.textContent = 'Enter a quantity of at least 1.'; return; }
        if (list.some(function (i) { return i.itemId === itemId; })) { err.textContent = 'This item is already in the list.'; return; }
        var price = parseFloat(opt.data('unit-price')) || 0;
        if (price <= 0) { err.textContent = 'This item has no price. Set it on All items first.'; return; }
        list.push({ itemId: itemId, itemName: opt.text().split(' (')[0], quantity: qty, unitPrice: price });
        render();
        $('#quantity').val('');
        $('#item_id').val('').trigger('change');
    });
    document.getElementById('btnSaveSupply').addEventListener('click', function () {
        if (!list.length || !confirm('Save these ' + list.length + ' item(s) to this supply?')) { return; }
        var button = this;
        button.disabled = true;
        $.ajax({
            url: fudBase + 'ajax/commodity_ajax.php', method: 'POST', dataType: 'json',
            data: { items: JSON.stringify(list), commodity_name_id: supplyId, action: 'add_commodity_supply_items' },
            success: function (res) {
                if (res.success) { alert(res.message || 'Saved.'); location.reload(); }
                else { alert(res.message || 'The items could not be saved. Please try again.'); button.disabled = false; }
            },
            error: function () { alert('Network or server error. Reload the page to see whether the items were saved.'); button.disabled = false; }
        });
    });
})();
</script>
