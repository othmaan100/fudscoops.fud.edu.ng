<?php
    // Commodity Supplies: open a supply to members for a period, change its dates, close it, and go on to add its items.
    // Saved by ajax/commodity_supply.php (CommodityLoan::saveSupply). Members see the open supply on R/commodity_request.php.
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    require_once('../config/classes/CommodityLoan.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../auth/') || !User::isSecretary('../auth/logout.php')) {
        exit();
    }
    $supplies = CommodityLoan::supplies();
    $open = null;
    $upcoming = [];
    foreach ($supplies as $s) {
        if ($s['state'] === 'open' && !$open) {
            $open = $s;
        } elseif ($s['state'] === 'upcoming') {
            $upcoming[] = $s;
        }
    }
    $h = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
    $when = function ($v) { return date('d M Y, g:ia', strtotime($v)); };
    $local = function ($v) { return date('Y-m-d\TH:i', strtotime($v)); };
    $pill = ['open' => ['Open now', 'ok'], 'upcoming' => ['Upcoming', 'wait'], 'closed' => ['Closed', '']];
    // Suggested period for a new supply: from now (or after the last supply closes) for two weeks
    $lastClose = $supplies ? max(array_column($supplies, 'closing_date')) : null;
    $suggestOpen = $lastClose && $lastClose > date('Y-m-d H:i:s') ? date('Y-m-d\TH:i', strtotime($lastClose . ' +1 minute')) : date('Y-m-d\TH:i');
    $suggestClose = date('Y-m-d', strtotime(str_replace('T', ' ', $suggestOpen) . ' +14 days')) . 'T23:59';

    echo $view->subHeader('../');
?>
    <?php echo $view->subPartialNav('../'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->SecretarySideNav(); ?>
      <div class="main-panel">
        <div class="content-wrapper fud-dash fud-apply">

          <div class="fud-hero">
            <div class="fud-hero-date">Commodity</div>
            <h2><i class="mdi mdi-calendar-clock"></i> Commodity Supplies</h2>
            <p>Open a supply to members for a set period. While it is open, members can request its items on Commodity Request.</p>
            <div class="fud-hero-actions">
              <a href="view_items.php"><i class="mdi mdi-format-list-bulleted"></i> Items</a>
              <a href="view_commodity_request.php"><i class="mdi mdi-cart-outline"></i> Requests</a>
            </div>
          </div>

          <?php if ($open) { ?>
            <div class="fud-notice success"><i class="mdi mdi-store"></i>
              <span><strong><?php echo $h($open['commodity_name']); ?></strong> is open to members until <strong><?php echo $when($open['closing_date']); ?></strong>
                · <?php echo (int) $open['items']; ?> item(s) · <?php echo (int) $open['requests']; ?> request(s)
                <?php if (!$open['items']) { ?><br><strong>It has no items yet, so members cannot request anything.</strong><?php } ?></span>
              <a href="addCommodityItems.php?commodity_name_id=<?php echo (int) $open['commodity_name_id']; ?>"><?php echo $open['items'] ? 'Items' : 'Add items'; ?> <i class="mdi mdi-arrow-right"></i></a></div>
          <?php } else { ?>
            <div class="fud-notice info"><i class="mdi mdi-information-outline"></i>
              <span>No supply is open to members right now<?php echo $upcoming ? '. Next: <strong>' . $h(end($upcoming)['commodity_name']) . '</strong> opens ' . $when(end($upcoming)['opening_date']) : ''; ?>.</span></div>
          <?php } ?>

          <div class="row">
            <div class="col-lg-5">
              <div class="fud-section fud-summary">
                <div class="fud-section-head"><h5><i class="mdi mdi-plus-circle"></i> Open a new supply</h5></div>
                <div class="fud-section-body">
                  <form id="supplyForm" autocomplete="off">
                    <div class="form-group">
                      <label for="supplyName">Name</label>
                      <input type="text" class="form-control" id="supplyName" name="name" maxlength="100" placeholder="e.g. NOVEMBER 2026 SUPPLY" required>
                    </div>
                    <div class="form-group">
                      <label for="supplyOpens">Opens</label>
                      <input type="datetime-local" class="form-control" id="supplyOpens" name="opens" value="<?php echo $suggestOpen; ?>" required>
                    </div>
                    <div class="form-group">
                      <label for="supplyCloses">Closes</label>
                      <input type="datetime-local" class="form-control" id="supplyCloses" name="closes" value="<?php echo $suggestClose; ?>" required>
                      <small class="text-muted d-block mt-1" id="supplyLength"></small>
                    </div>
                    <div class="msg"></div>
                    <button type="submit" class="btn btn-primary btn-block btn-lg mt-2"><i class="mdi mdi-content-save"></i> Save supply</button>
                  </form>
                  <div class="fud-next">
                    <div class="fud-next-title">After saving</div>
                    <ol>
                      <li>Add the items members can request, with prices and quantities</li>
                      <li>Members request items while the supply is open</li>
                      <li>The Treasurer reviews the requests; the Chairman authorizes them</li>
                    </ol>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-lg-7">
              <div class="fud-section">
                <div class="fud-section-head"><h5>All supplies</h5><small class="text-muted">Periods may not overlap</small></div>
                <div class="fud-section-body">
                  <?php if (!$supplies) { ?>
                    <p class="fud-empty">No supply has been opened yet.</p>
                  <?php } ?>
                  <div class="fud-supply-list">
                    <?php foreach ($supplies as $s) { $p = $pill[$s['state']]; ?>
                      <div class="fud-supply <?php echo $s['state']; ?>" data-id="<?php echo (int) $s['commodity_name_id']; ?>">
                        <div class="d-flex flex-wrap justify-content-between align-items-start">
                          <div>
                            <strong><?php echo $h($s['commodity_name']); ?></strong>
                            <span class="fud-pill <?php echo $p[1]; ?> ml-1"><?php echo $p[0]; ?></span>
                            <div class="text-muted small mt-1"><i class="mdi mdi-calendar"></i> <?php echo $when($s['opening_date']); ?> → <?php echo $when($s['closing_date']); ?></div>
                            <div class="small mt-1"><?php echo (int) $s['items']; ?> item(s) · <?php echo (int) $s['requests']; ?> request(s)
                              <?php if (!$s['items'] && $s['state'] !== 'closed') { ?><span class="text-danger"> · no items yet</span><?php } ?></div>
                          </div>
                          <div class="fud-supply-actions">
                            <a class="btn btn-sm btn-outline-primary" href="addCommodityItems.php?commodity_name_id=<?php echo (int) $s['commodity_name_id']; ?>"><i class="mdi mdi-format-list-bulleted"></i> Items</a>
                            <button type="button" class="btn btn-sm btn-outline-secondary js-dates"><i class="mdi mdi-calendar-edit"></i> Dates</button>
                            <?php if ($s['state'] === 'open') { ?>
                              <button type="button" class="btn btn-sm btn-outline-danger js-close"><i class="mdi mdi-lock-outline"></i> Close now</button>
                            <?php } ?>
                          </div>
                        </div>
                        <form class="fud-supply-dates form-row align-items-end mt-3" style="display:none">
                          <div class="col-sm-5 mb-2"><label class="small mb-1">Opens</label>
                            <input type="datetime-local" class="form-control form-control-sm" name="opens" value="<?php echo $local($s['opening_date']); ?>" required></div>
                          <div class="col-sm-5 mb-2"><label class="small mb-1">Closes</label>
                            <input type="datetime-local" class="form-control form-control-sm" name="closes" value="<?php echo $local($s['closing_date']); ?>" required></div>
                          <div class="col-sm-2 mb-2"><button type="submit" class="btn btn-sm btn-primary btn-block">Save</button></div>
                          <div class="col-12 msg"></div>
                        </form>
                      </div>
                    <?php } ?>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
        <?php echo $view->subFooter('../'); ?>

<script>
(function () {
    function post(data, box, onOk) {
        var msg = box.querySelector('.msg');
        msg.innerHTML = '<div class="alert alert-primary mt-2 mb-0">Saving…</div>';
        fetch('../ajax/commodity_supply.php', { method: 'POST', body: data, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                msg.innerHTML = '<div class="alert mt-2 mb-0 alert-' + (res.ok ? 'success' : 'danger') + '"></div>';
                msg.firstChild.textContent = res.message || (res.ok ? 'Saved.' : 'Could not save.');
                if (res.ok) { onOk(res); }
            })
            .catch(function () { msg.innerHTML = '<div class="alert alert-danger mt-2 mb-0">Network or server error. Please try again.</div>'; });
    }

    // New supply
    var form = document.getElementById('supplyForm');
    var name = document.getElementById('supplyName');
    name.addEventListener('input', function () { this.value = this.value.toUpperCase(); });
    function showLength() {
        var o = new Date(form.opens.value), c = new Date(form.closes.value), out = document.getElementById('supplyLength');
        if (isNaN(o) || isNaN(c)) { out.textContent = ''; return; }
        var days = (c - o) / 86400000;
        out.textContent = days > 0 ? 'Open for about ' + (days >= 1 ? Math.round(days) + ' day(s)' : Math.round(days * 24) + ' hour(s)') + '.' : 'The closing time must be after the opening time.';
        out.className = 'd-block mt-1 ' + (days > 0 ? 'text-muted' : 'text-danger');
    }
    form.addEventListener('input', showLength);
    showLength();
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!confirm('Open "' + name.value + '" to members from ' + form.opens.value.replace('T', ' ') + ' to ' + form.closes.value.replace('T', ' ') + '?')) { return; }
        var data = new FormData(form);
        data.append('action', 'save');
        post(data, form, function (res) {
            setTimeout(function () { window.location = 'addCommodityItems.php?commodity_name_id=' + res.id; }, 1200);
        });
    });

    // Change dates / close now on a supply
    document.querySelectorAll('.fud-supply').forEach(function (card) {
        var dates = card.querySelector('.fud-supply-dates');
        card.querySelector('.js-dates').addEventListener('click', function () {
            dates.style.display = dates.style.display === 'none' ? '' : 'none';
        });
        dates.addEventListener('submit', function (e) {
            e.preventDefault();
            var data = new FormData(dates);
            data.append('action', 'save');
            data.append('supply_id', card.dataset.id);
            post(data, dates, function () { setTimeout(function () { location.reload(); }, 800); });
        });
        var close = card.querySelector('.js-close');
        if (close) {
            close.addEventListener('click', function () {
                if (!confirm('Close this supply now? Members will no longer be able to request items; requests already made carry on.')) { return; }
                var data = new FormData();
                data.append('action', 'close');
                data.append('supply_id', card.dataset.id);
                dates.style.display = '';
                post(data, dates, function () { setTimeout(function () { location.reload(); }, 800); });
            });
        }
    });
})();
</script>
