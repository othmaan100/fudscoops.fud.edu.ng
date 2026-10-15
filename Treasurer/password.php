<?php
    // Password Reset (Treasurer): look up a member or applicant by staff number and reset their password to the
    // staff number. Done by ajax/reset.php (User::resetPassword; officer accounts cannot be reset here).
    require_once('../config/classes/View.php');
    require_once('../config/classes/User.php');
    $view = new View();
    $user = new User();
    if (!User::is_authenticated('../auth/') || !User::isBur('../auth/logout.php')) {
        exit();
    }
    echo $view->subHeader('../');
?>
    <?php echo $view->subPartialNav('../'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->treasurerSideNav(); ?>
      <div class="main-panel">
        <div class="content-wrapper fud-dash fud-apply">

          <div class="fud-hero">
            <div class="fud-hero-date">Settings</div>
            <h2><i class="mdi mdi-lock-reset"></i> Password Reset</h2>
            <p>Reset a member's or applicant's password when they cannot log in. The new password is their staff number.</p>
          </div>

          <div class="row">
            <div class="col-lg-7">
              <div class="fud-section">
                <div class="fud-section-head"><h5><span class="fud-num">1</span> Find the staff member</h5></div>
                <div class="fud-section-body">
                  <form id="resetLookup" autocomplete="off">
                    <label for="resetUsername">Staff number</label>
                    <div class="input-group">
                      <input type="text" class="form-control" id="resetUsername" placeholder="e.g. SP/RA/1234" required>
                      <div class="input-group-append"><button type="submit" class="btn btn-primary"><i class="mdi mdi-magnify"></i> Find</button></div>
                    </div>
                  </form>
                  <div id="resetResult" class="mt-3"></div>
                </div>
              </div>
            </div>
            <div class="col-lg-5">
              <div class="fud-section">
                <div class="fud-section-head"><h5><i class="mdi mdi-information-outline"></i> How it works</h5></div>
                <div class="fud-section-body">
                  <ul class="fud-checks mb-0">
                    <li>The password becomes the member's <strong>staff number</strong> (e.g. SP/RA/1234), in capitals.</li>
                    <li>Tell the member to log in with it and change it at once (profile menu → Change Password).</li>
                    <li>Only member and applicant accounts can be reset here. Officer accounts cannot.</li>
                    <li>Confirm who you are speaking to before resetting a password.</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

        </div>
        <?php echo $view->subFooter('../'); ?>

<script>
(function () {
    var result = document.getElementById('resetResult');
    function call(action, username) {
        var data = new FormData();
        data.append('action', action);
        data.append('username', username);
        return fetch('../ajax/reset.php', { method: 'POST', body: data, credentials: 'same-origin' }).then(function (r) { return r.json(); });
    }
    function notice(type, text) {
        result.innerHTML = '<div class="fud-notice ' + type + ' mb-0"><i class="mdi ' + (type === 'success' ? 'mdi-check-circle' : 'mdi-alert-circle') + '"></i><span></span></div>';
        result.querySelector('span').textContent = text;
    }
    document.getElementById('resetLookup').addEventListener('submit', function (e) {
        e.preventDefault();
        var username = document.getElementById('resetUsername').value.trim().toUpperCase();
        if (!username) { return; }
        result.innerHTML = '<p class="text-muted mb-0">Looking up ' + username.replace(/</g, '') + '…</p>';
        call('lookup', username).then(function (res) {
            if (!res.ok) { notice('', res.message); return; }
            result.innerHTML =
                '<div class="fud-facts mb-3"><div><span>Name</span><b class="js-name"></b></div><div><span>Staff number</span><b class="js-sp"></b></div>'
              + '<div><span>Department</span><b class="js-dept"></b></div><div><span>Account</span><b class="js-role"></b></div></div>'
              + (res.can_reset
                    ? '<h6 class="mb-2"><span class="fud-num">2</span> Reset the password</h6><button type="button" class="btn btn-danger js-reset"><i class="mdi mdi-lock-reset"></i> Reset password to staff number</button>'
                    : '<div class="fud-notice mb-0"><i class="mdi mdi-lock-outline"></i><span>This is an officer account; it cannot be reset here.</span></div>');
            result.querySelector('.js-name').textContent = res.name || '–';
            result.querySelector('.js-sp').textContent = res.username;
            result.querySelector('.js-dept').textContent = res.dept || '–';
            result.querySelector('.js-role').textContent = res.role;
            var button = result.querySelector('.js-reset');
            if (button) {
                button.addEventListener('click', function () {
                    if (!confirm('Reset the password of ' + (res.name || res.username) + ' (' + res.username + ')?\nThe new password will be: ' + res.username)) { return; }
                    button.disabled = true;
                    call('reset', res.username).then(function (r) {
                        if (r.ok) {
                            notice('success', r.message);
                            var tip = document.createElement('p');
                            tip.className = 'mt-2 mb-0';
                            tip.innerHTML = 'New password: <code class="fud-code"></code>';
                            tip.querySelector('code').textContent = res.username;
                            result.appendChild(tip);
                        } else { notice('', r.message); }
                    }).catch(function () { button.disabled = false; notice('', 'Network or server error. Please try again.'); });
                });
            }
        }).catch(function () { notice('', 'Network or server error. Please try again.'); });
    });
})();
</script>
