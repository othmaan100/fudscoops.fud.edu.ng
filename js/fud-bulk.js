/*
 * Chairman authorizations (ajax/chairman_decisions.php), shared by the Chairman's lists and detail pages.
 *
 * Bulk, on a list:  <div data-bulk="TYPE" [data-no-decline]> ... <table> with
 *     <input type="checkbox" class="bulk-all"> in the heading and, per request that can be decided,
 *     <input type="checkbox" class="bulk-check" value="ID" data-label="Name – ₦amount">
 *   A bar with "Authorize selected" / "Decline selected" is added at the top. "Select all" ticks the rows shown
 *   (after any search). Declining asks for one reason for all the ticked requests.
 *
 * Detail page:  <div data-decision="TYPE" data-id="ID" data-label="..." data-back="list.php" [data-no-decline]>
 *     with buttons [data-act="approve"] / [data-act="decline"] and a <textarea class="decision-reason">.
 */
(function () {
    var endpoint = (typeof fudBase !== 'undefined' ? fudBase : '../') + 'ajax/chairman_decisions.php';

    function send(type, action, ids, reason, done) {
        var data = new FormData();
        data.append('type', type);
        data.append('action', action);
        ids.forEach(function (id) { data.append('ids[]', id); });
        if (reason) { data.append('reason', reason); }
        fetch(endpoint, { method: 'POST', body: data, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(done)
            .catch(function () { done({ success: false, message: 'Network or server error. Please try again.', results: [] }); });
    }

    function showResult(box, res, labels) {
        var failed = (res.results || []).filter(function (r) { return !r.ok; });
        var alert = document.createElement('div');
        alert.className = 'alert alert-' + (res.success && !failed.length ? 'success' : (res.done ? 'warning' : 'danger'));
        alert.textContent = res.message;
        failed.forEach(function (r) {
            var line = document.createElement('div');
            line.className = 'small mt-1';
            line.textContent = (labels[r.id] || '#' + r.id) + ': ' + r.message;
            alert.appendChild(line);
        });
        box.innerHTML = '';
        box.appendChild(alert);
        box.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return failed.length;
    }

    // ------------------------------------------------------------ lists
    document.querySelectorAll('[data-bulk]').forEach(function (wrap) {
        var type = wrap.getAttribute('data-bulk');
        var canDecline = !wrap.hasAttribute('data-no-decline');
        var checks = function () { return Array.prototype.slice.call(wrap.querySelectorAll('.bulk-check:not(:disabled)')); };
        if (!wrap.querySelector('.bulk-check')) { return; }
        var visible = function (c) { var tr = c.closest('tr'); return !tr || tr.style.display !== 'none'; };
        var selected = function () { return checks().filter(function (c) { return c.checked && visible(c); }); };

        var bar = document.createElement('div');
        bar.className = 'fud-bulk-bar d-flex flex-wrap align-items-center mb-3';
        bar.style.gap = '.5rem';
        bar.innerHTML = '<span class="small text-muted fud-bulk-count">Tick requests to authorize several at once.</span>'
            + '<span class="ml-auto"></span>'
            + '<button type="button" class="btn btn-sm btn-success" data-bulk-act="approve" disabled><i class="mdi mdi-check-all"></i> Authorize selected</button>'
            + (canDecline ? '<button type="button" class="btn btn-sm btn-outline-danger" data-bulk-act="decline" disabled><i class="mdi mdi-close"></i> Decline selected</button>' : '');
        var panel = document.createElement('div');
        panel.className = 'border rounded p-3 mb-3';
        panel.style.display = 'none';
        panel.innerHTML = '<label class="font-weight-bold mb-1">Reason for declining <small class="text-muted">(required; the member sees it)</small></label>'
            + '<div class="small text-muted mb-2 fud-bulk-who"></div>'
            + '<textarea class="form-control mb-2" rows="2" maxlength="500"></textarea>'
            + '<button type="button" class="btn btn-sm btn-danger" data-bulk-confirm>Decline</button> '
            + '<button type="button" class="btn btn-sm btn-light" data-bulk-cancel>Cancel</button>';
        var msg = document.createElement('div');
        wrap.insertBefore(msg, wrap.firstChild);
        wrap.insertBefore(panel, msg.nextSibling);
        wrap.insertBefore(bar, panel);

        function refresh() {
            var n = selected().length;
            bar.querySelector('.fud-bulk-count').textContent = n ? n + ' selected' : 'Tick requests to authorize several at once.';
            bar.querySelectorAll('[data-bulk-act]').forEach(function (b) { b.disabled = !n; });
            var all = wrap.querySelector('.bulk-all');
            if (all) {
                var shown = checks().filter(visible);
                all.checked = shown.length > 0 && shown.every(function (c) { return c.checked; });
            }
        }
        function labels() {
            var map = {};
            wrap.querySelectorAll('.bulk-check').forEach(function (c) { map[c.value] = c.getAttribute('data-label') || '#' + c.value; });
            return map;
        }
        function run(action, reason) {
            var ids = selected().map(function (c) { return c.value; });
            var names = labels();
            bar.querySelectorAll('button').forEach(function (b) { b.disabled = true; });
            send(type, action, ids, reason, function (res) {
                var failed = showResult(msg, res, names);
                if (res.done) { setTimeout(function () { location.reload(); }, failed ? 4000 : 1200); } else { refresh(); }
            });
        }

        wrap.addEventListener('change', function (e) {
            if (e.target.classList.contains('bulk-all')) {
                var on = e.target.checked;
                checks().forEach(function (c) { if (visible(c)) { c.checked = on; } });
            }
            if (e.target.classList.contains('bulk-all') || e.target.classList.contains('bulk-check')) { refresh(); }
        });
        bar.addEventListener('click', function (e) {
            var b = e.target.closest('[data-bulk-act]');
            if (!b) { return; }
            var ticked = selected(), names = labels();
            var list = ticked.slice(0, 15).map(function (c) { return '• ' + names[c.value]; }).join('\n') + (ticked.length > 15 ? '\n… and ' + (ticked.length - 15) + ' more' : '');
            if (b.getAttribute('data-bulk-act') === 'approve') {
                if (confirm('Authorize ' + ticked.length + ' request' + (ticked.length === 1 ? '' : 's') + '?\n\n' + list)) { run('approve'); }
            } else {
                panel.style.display = '';
                panel.querySelector('.fud-bulk-who').textContent = ticked.length + ' request' + (ticked.length === 1 ? '' : 's') + ': '
                    + ticked.slice(0, 15).map(function (c) { return names[c.value]; }).join(' · ');
                panel.querySelector('textarea').focus();
            }
        });
        panel.querySelector('[data-bulk-cancel]').addEventListener('click', function () { panel.style.display = 'none'; });
        panel.querySelector('[data-bulk-confirm]').addEventListener('click', function () {
            var reason = panel.querySelector('textarea').value.trim();
            if (!reason && type !== 'savings_update') { alert('Give the reason for declining; the member will see it.'); return; }
            panel.style.display = 'none';
            run('decline', reason);
        });
        // A search box on the page changes which rows "Select all" covers
        document.addEventListener('input', function (e) { if (e.target.type === 'search') { setTimeout(refresh, 0); } });
        refresh();
    });

    // ------------------------------------------------------------ detail pages
    document.querySelectorAll('[data-decision]').forEach(function (box) {
        var type = box.getAttribute('data-decision'), id = box.getAttribute('data-id');
        var label = box.getAttribute('data-label') || '#' + id, back = box.getAttribute('data-back');
        var msg = box.querySelector('.decision-msg') || box.appendChild(document.createElement('div'));
        box.querySelectorAll('[data-act]').forEach(function (b) {
            b.addEventListener('click', function () {
                var action = b.getAttribute('data-act');
                var reasonBox = box.querySelector('.decision-reason');
                var reason = reasonBox ? reasonBox.value.trim() : '';
                if (action === 'decline' && !reason && !box.hasAttribute('data-reason-optional')) { alert('Give the reason for declining; the member will see it.'); if (reasonBox) { reasonBox.focus(); } return; }
                if (!confirm((action === 'approve' ? 'Authorize ' : 'Decline ') + label + '?')) { return; }
                var buttons = box.querySelectorAll('button');
                buttons.forEach(function (x) { x.disabled = true; });
                var names = {}; names[id] = label;
                send(type, action, [id], reason, function (res) {
                    var failed = showResult(msg, res, names);
                    if (res.done && !failed) { setTimeout(function () { if (back) { location.href = back; } else { location.reload(); } }, 1200); }
                    else { buttons.forEach(function (x) { x.disabled = false; }); }
                });
            });
        });
    });
})();
