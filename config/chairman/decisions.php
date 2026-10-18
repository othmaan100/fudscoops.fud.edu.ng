<?php
// Helpers for the Chairman's authorizations (ajax/chairman_decisions.php, js/fud-bulk.js).

// <script> tag for js/fud-bulk.js, with the file time so browsers pick up changes. $root: path to the site root.
function chairmanBulkScript($root = '../')
{
    $file = __DIR__ . '/../../js/fud-bulk.js';
    return '<script src="' . $root . 'js/fud-bulk.js' . (is_file($file) ? '?v=' . filemtime($file) : '') . '"></script>';
}

// The "Your decision" box on a detail page.
//   $type, $id    as ajax/chairman_decisions.php takes them
//   $label        shown in the confirmation, e.g. "Musa Ahmadu – ₦50,000.00"
//   $back         list page to return to after deciding
//   $open         whether the request is waiting for the Chairman; otherwise $closedNote is shown
//   $blocked      reasons it cannot be authorized yet (Authorize is disabled; Decline stays available)
//   $canDecline   false for membership (it can only be authorized)
function chairmanDecisionPanel($type, $id, $label, $back, $open, $closedNote = '', $blocked = [], $canDecline = true, $hint = '')
{
    $reasonOptional = $type === 'savings_update'; // no reason field for savings updates
    $h = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
    ob_start(); ?>
    <div class="fud-section fud-summary">
      <div class="fud-section-head"><h5><i class="mdi mdi-gavel"></i> Your decision</h5></div>
      <div class="fud-section-body">
        <?php if (!$open) { ?>
          <div class="fud-notice <?php echo stripos($closedNote, 'declin') !== false || stripos($closedNote, 'reject') !== false ? '' : 'success'; ?> mb-0">
            <i class="mdi mdi-information-outline"></i><span><?php echo $h($closedNote ?: 'This request is not waiting for you.'); ?></span></div>
        <?php } else { ?>
          <div data-decision="<?php echo $h($type); ?>" data-id="<?php echo $h($id); ?>" data-label="<?php echo $h($label); ?>" data-back="<?php echo $h($back); ?>"<?php echo $reasonOptional ? ' data-reason-optional' : ''; ?>>
            <?php foreach ((array) $blocked as $b) { ?>
              <p class="small text-danger mb-2"><i class="mdi mdi-block-helper"></i> <?php echo $h($b); ?></p>
            <?php } ?>
            <?php if ($hint) { ?><p class="small text-muted"><?php echo $h($hint); ?></p><?php } ?>
            <?php if ($canDecline) { ?>
              <div class="form-group">
                <label for="decisionReason">Reason <small class="text-muted"><?php echo $reasonOptional ? '(optional; not sent to the member)' : '(required to decline; the member sees it)'; ?></small></label>
                <textarea class="form-control decision-reason" id="decisionReason" rows="3" maxlength="500"></textarea>
              </div>
            <?php } ?>
            <div class="decision-msg"></div>
            <button type="button" class="btn btn-success btn-block btn-lg" data-act="approve"<?php echo $blocked ? ' disabled' : ''; ?>><i class="mdi mdi-check"></i> Authorize</button>
            <?php if ($canDecline) { ?><button type="button" class="btn btn-outline-danger btn-block" data-act="decline"><i class="mdi mdi-close"></i> Decline</button><?php } ?>
          </div>
        <?php } ?>
        <p class="small mt-3 mb-0"><a href="<?php echo $h($back); ?>"><i class="mdi mdi-arrow-left"></i> Back to the list</a></p>
      </div>
    </div>
    <?php
    return ob_get_clean() . chairmanBulkScript();
}
