<?php
// Generate Report page shared by Chairman/reports.php, Treasurer/reports.php and Secretary/reports.php.
// The including page sets $reportRole ('chairman', 'treasurer' or 'secretary') before requiring this file.
// Query string: report=<key>&from=Y-m-d&to=Y-m-d&staff=<staff no>&format=csv|pdf
require_once(__DIR__ . '/../classes/View.php');
require_once(__DIR__ . '/../classes/User.php');
require_once(__DIR__ . '/../classes/Report.php');

$roles = [
    'chairman'  => ['guard' => 'isChairman',  'sidebar' => 'subSideNav',       'heading' => 'COOPERATIVE CHAIRMAN ACCOUNT'],
    'treasurer' => ['guard' => 'isBur',       'sidebar' => 'treasurerSideNav', 'heading' => 'TREASURER ACCOUNT'],
    'secretary' => ['guard' => 'isSecretary', 'sidebar' => 'SecretarySideNav', 'heading' => 'SECRETARY-GENERAL ACCOUNT'],
];
if (!isset($reportRole, $roles[$reportRole])) {
    exit();
}
$role = $roles[$reportRole];
if (!User::is_authenticated('../auth/') || !call_user_func(['User', $role['guard']], '../auth/logout.php')) {
    exit();
}

// ---------- filters ----------
$catalogue = Report::catalogue();
$reportKey = isset($_GET['report'], $catalogue[$_GET['report']]) ? $_GET['report'] : 'summary';

function reportDateParam($name, $default) {
    $value = isset($_GET[$name]) ? $_GET[$name] : '';
    $d = DateTime::createFromFormat('Y-m-d', $value);
    return $d && $d->format('Y-m-d') === $value ? $value : $default;
}
$from = reportDateParam('from', date('Y-01-01'));
$to = reportDateParam('to', date('Y-m-d'));
if ($from > $to) {
    list($from, $to) = [$to, $from];
}
$staffNo = isset($_GET['staff']) ? trim($_GET['staff']) : '';
$needsStaff = $catalogue[$reportKey][2];
$format = isset($_GET['format']) ? $_GET['format'] : 'html';

$report = (!$needsStaff || $staffNo !== '') ? (new Report())->build($reportKey, $from, $to, $staffNo) : null;
$generatedBy = isset($_SESSION['username']) ? $_SESSION['username'] : '';
$generatedText = 'Generated on ' . date('d M Y H:i') . ' by ' . $generatedBy;

// ---------- formatting ----------
function reportCell($value, $type, $forExport = false) {
    if ($value === null || $value === '') {
        return '';
    }
    switch ($type) {
        case 'money':
            return $forExport ? round((float) $value, 2) : number_format((float) $value, 2);
        case 'int':
            return $forExport ? (int) $value : number_format((float) $value);
        case 'date':
            $t = strtotime($value);
            return $t ? date($forExport ? 'Y-m-d' : 'd M Y', $t) : $value;
        default:
            return $value;
    }
}

function reportFileName($report, $ext) {
    return preg_replace('/[^A-Za-z0-9]+/', '_', $report['title']) . '_' . date('Ymd_His') . '.' . $ext;
}

// ---------- CSV (opens in Excel) ----------
if ($format === 'csv' && $report) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . reportFileName($report, 'csv') . '"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows names correctly
    fputcsv($out, ['FUD STAFF COOPERATIVE SOCIETY LIMITED']);
    fputcsv($out, [$report['title']]);
    fputcsv($out, [$report['subtitle']]);
    fputcsv($out, [$generatedText]);
    if ($report['summary']) {
        fputcsv($out, []);
        fputcsv($out, ['Summary']);
        foreach ($report['summary'] as $s) {
            fputcsv($out, [$s[0], reportCell($s[1], $s[2], true)]);
        }
    }
    foreach ($report['tables'] as $table) {
        fputcsv($out, []);
        fputcsv($out, [$table['heading']]);
        fputcsv($out, array_column($table['columns'], 0));
        foreach ($table['rows'] as $row) {
            fputcsv($out, array_map(function ($v, $c) { return reportCell($v, $c[1], true); }, $row, $table['columns']));
        }
        if ($table['totals'] && $table['rows']) {
            fputcsv($out, array_map(function ($v, $c) { return reportCell($v, $c[1], true); }, $table['totals'], $table['columns']));
        }
    }
    foreach ($report['notes'] as $note) {
        fputcsv($out, []);
        fputcsv($out, ['Note: ' . $note]);
    }
    fclose($out);
    exit();
}

// ---------- HTML for one report (screen and PDF) ----------
function reportBodyHtml($report, $forPdf = false) {
    $h = '';
    if ($report['summary']) {
        if ($forPdf) {
            $h .= '<table class="summary">';
            foreach ($report['summary'] as $s) {
                $value = $s[2] === 'money' ? '₦' . reportCell($s[1], 'money') : reportCell($s[1], $s[2]);
                $h .= '<tr><th>' . htmlspecialchars($s[0]) . '</th><td>' . htmlspecialchars((string) $value) . '</td></tr>';
            }
            $h .= '</table>';
        } else {
            $h .= '<div class="report-stats">';
            foreach ($report['summary'] as $s) {
                $value = $s[2] === 'money' ? '₦' . reportCell($s[1], 'money') : reportCell($s[1], $s[2]);
                $h .= '<div class="report-stat"><div class="report-stat-label">' . htmlspecialchars($s[0]) . '</div>'
                    . '<div class="report-stat-value' . ($s[2] === 'text' ? ' report-stat-text' : '') . '">' . htmlspecialchars((string) $value) . '</div></div>';
            }
            $h .= '</div>';
        }
    }
    foreach ($report['tables'] as $table) {
        $h .= '<h5 class="report-table-heading">' . htmlspecialchars($table['heading'])
            . ' <span class="report-count">(' . number_format(count($table['rows'])) . ')</span></h5>';
        if (!$table['rows']) {
            $h .= '<p class="report-empty">No records.</p>';
            continue;
        }
        $h .= $forPdf ? '<table class="data">' : '<div class="table-responsive"><table class="table table-sm table-bordered table-hover report-table">';
        $h .= '<thead><tr>';
        foreach ($table['columns'] as $c) {
            $h .= '<th class="' . ($c[1] === 'money' || $c[1] === 'int' ? 'num' : '') . '">' . htmlspecialchars($c[0]) . ($c[1] === 'money' ? ' (₦)' : '') . '</th>';
        }
        $h .= '</tr></thead><tbody>';
        foreach ($table['rows'] as $row) {
            $h .= '<tr>';
            foreach ($row as $i => $v) {
                $type = $table['columns'][$i][1];
                $h .= '<td class="' . ($type === 'money' || $type === 'int' ? 'num' : '') . '">' . htmlspecialchars((string) reportCell($v, $type)) . '</td>';
            }
            $h .= '</tr>';
        }
        $h .= '</tbody>';
        if ($table['totals']) {
            $h .= '<tfoot><tr>';
            foreach ($table['totals'] as $i => $v) {
                $type = $i === 0 ? 'text' : $table['columns'][$i][1];
                $h .= '<th class="' . ($type === 'money' || $type === 'int' ? 'num' : '') . '">' . htmlspecialchars((string) reportCell($v, $type)) . '</th>';
            }
            $h .= '</tr></tfoot>';
        }
        $h .= $forPdf ? '</table>' : '</table></div>';
    }
    foreach ($report['notes'] as $note) {
        $h .= '<p class="report-note">Note: ' . htmlspecialchars($note) . '</p>';
    }
    return $h;
}

// ---------- PDF ----------
if ($format === 'pdf' && $report) {
    require_once(__DIR__ . '/../vendor/autoload.php');
    $mpdf = new \Mpdf\Mpdf(['format' => 'A4-L', 'margin_top' => 12, 'margin_bottom' => 14, 'margin_left' => 10, 'margin_right' => 10,
                            'simpleTables' => true, 'packTableData' => true]);
    $mpdf->SetTitle($report['title']);
    $mpdf->SetFooter($generatedText . '||Page {PAGENO} of {nbpg}');
    $logo = realpath(__DIR__ . '/../../images/logo-mini.png');
    $css = '
        body { font-family: dejavusans; font-size: 8.5pt; color: #222; }
        .head { text-align: center; margin-bottom: 8px; }
        .head h2 { margin: 0; font-size: 14pt; }
        .head h3 { margin: 2px 0; font-size: 12pt; color: #2f5bb7; }
        .head p { margin: 0; color: #555; }
        table.summary { border-collapse: collapse; margin: 6px 0 10px; }
        table.summary th { text-align: left; padding: 3px 12px 3px 0; font-weight: normal; color: #555; }
        table.summary td { padding: 3px 0; font-weight: bold; text-align: right; }
        h5 { font-size: 10pt; margin: 12px 0 4px; color: #2f5bb7; }
        .report-count { color: #888; font-weight: normal; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th { background: #e9eef8; border: 0.5px solid #b9c3d6; padding: 3px; text-align: left; }
        table.data td { border: 0.5px solid #d3d9e4; padding: 3px; }
        table.data tfoot th { background: #f3f5f9; }
        .num { text-align: right; }
        .report-empty, .report-note { color: #666; font-style: italic; }
    ';
    $html = '<div class="head">' . ($logo ? '<img src="' . $logo . '" height="45"><br>' : '')
          . '<h2>FUD STAFF COOPERATIVE SOCIETY LIMITED</h2>'
          . '<h3>' . htmlspecialchars($report['title']) . '</h3>'
          . '<p>' . htmlspecialchars($report['subtitle']) . '</p></div>'
          . reportBodyHtml($report, true);
    $mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);
    $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
    $mpdf->Output(reportFileName($report, 'pdf'), 'D');
    exit();
}

// ---------- screen ----------
$view = new View();
$sidebar = $role['sidebar'];
function reportUrl($params) {
    return 'reports.php?' . http_build_query($params);
}
$baseParams = ['report' => $reportKey, 'from' => $from, 'to' => $to] + ($needsStaff ? ['staff' => $staffNo] : []);

echo $view->subHeader('../');
?>
<style>
  .report-menu .list-group-item { border: 0; border-radius: 8px !important; padding: .55rem .8rem; font-size: .86rem; color: #3b4252; }
  .report-menu .list-group-item.active { background: rgba(77, 131, 255, .1); color: #4d83ff; font-weight: 600; }
  .report-menu .report-group { font-size: .68rem; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; color: #8a94a6; margin: .9rem .8rem .3rem; }
  .report-stats { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: .75rem; margin-bottom: 1.25rem; }
  .report-stat { background: #f7f9fd; border: 1px solid #e6ebf4; border-radius: 10px; padding: .75rem .9rem; }
  .report-stat-label { font-size: .75rem; color: #6b7487; }
  .report-stat-value { font-size: 1.15rem; font-weight: 600; color: #1f2a44; margin-top: .2rem; word-break: break-word; }
  .report-stat-value.report-stat-text { font-size: .95rem; }
  .report-table-heading { margin: 1.4rem 0 .6rem; color: #2f5bb7; font-size: 1rem; }
  .report-count { color: #8a94a6; font-weight: normal; font-size: .85rem; }
  .report-table th, .report-table td { font-size: .8rem; white-space: nowrap; }
  .report-table .num { text-align: right; }
  .report-table tfoot th { background: #f3f5f9; }
  .report-empty, .report-note { color: #6b7487; font-style: italic; font-size: .85rem; }
  .report-print-head { display: none; }
  @media print {
    .sidebar, .navbar, .footer, .report-menu-col, .report-filters, .report-actions { display: none !important; }
    .main-panel, .page-body-wrapper { width: 100% !important; margin: 0 !important; padding: 0 !important; }
    .content-wrapper { padding: 0 !important; background: #fff !important; }
    .report-output-col { flex: 0 0 100%; max-width: 100%; }
    .report-print-head { display: block; text-align: center; margin-bottom: 1rem; }
    .card { border: 0 !important; box-shadow: none !important; }
  }
</style>
    <!-- partial:partials/_navbar.html -->
    <?php echo $view->subPartialNav('../'); ?>
    <!-- partial -->
    <div class="container-fluid page-body-wrapper">
      <?php echo $view->$sidebar(); ?>
      <div class="main-panel">
        <div class="content-wrapper">
          <div class="row">
            <div class="col-md-12 grid-margin">
              <h2><?php echo $role['heading']; ?></h2>
              <h4 class="text-primary mb-0">Generate Report</h4>
            </div>
          </div>

          <div class="row">
            <!-- Report list -->
            <div class="col-lg-3 grid-margin report-menu-col">
              <div class="card">
                <div class="card-body p-2 report-menu">
                  <?php
                    $group = '';
                    foreach ($catalogue as $key => $info) {
                        if ($info[1] !== $group) {
                            $group = $info[1];
                            echo '<div class="report-group">' . htmlspecialchars($group) . '</div>';
                        }
                        $params = ['report' => $key, 'from' => $from, 'to' => $to];
                        echo '<a class="list-group-item list-group-item-action' . ($key === $reportKey ? ' active' : '') . '" href="'
                            . htmlspecialchars(reportUrl($params)) . '" title="' . htmlspecialchars($info[3]) . '">' . htmlspecialchars($info[0]) . '</a>';
                    }
                  ?>
                </div>
              </div>
            </div>

            <!-- Filters and output -->
            <div class="col-lg-9 grid-margin report-output-col">
              <div class="card">
                <div class="card-body">
                  <form method="GET" class="report-filters mb-3">
                    <input type="hidden" name="report" value="<?php echo htmlspecialchars($reportKey); ?>">
                    <p class="text-muted mb-2"><?php echo htmlspecialchars($catalogue[$reportKey][3]); ?></p>
                    <div class="form-row align-items-end">
                      <div class="col-sm-6 col-md-3 mb-2">
                        <label class="mb-1 small"><?php echo $needsStaff ? 'Transactions up to' : 'From'; ?></label>
                        <?php if ($needsStaff) { ?>
                          <input type="hidden" name="from" value="<?php echo htmlspecialchars($from); ?>">
                          <input type="date" name="to" class="form-control form-control-sm" value="<?php echo htmlspecialchars($to); ?>" required>
                        <?php } else { ?>
                          <input type="date" name="from" class="form-control form-control-sm" value="<?php echo htmlspecialchars($from); ?>" required>
                        <?php } ?>
                      </div>
                      <?php if ($needsStaff) { ?>
                        <div class="col-sm-6 col-md-4 mb-2">
                          <label class="mb-1 small">Staff number</label>
                          <input type="text" name="staff" class="form-control form-control-sm" value="<?php echo htmlspecialchars($staffNo); ?>" placeholder="e.g. SP/RA/123" required>
                        </div>
                      <?php } else { ?>
                        <div class="col-sm-6 col-md-3 mb-2">
                          <label class="mb-1 small">To</label>
                          <input type="date" name="to" class="form-control form-control-sm" value="<?php echo htmlspecialchars($to); ?>" required>
                        </div>
                      <?php } ?>
                      <div class="col-md-3 mb-2">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="mdi mdi-file-chart"></i> Generate</button>
                      </div>
                    </div>
                  </form>

                  <?php if (!$report) { ?>
                    <div class="alert alert-info mb-0">Enter a staff number and click Generate.</div>
                  <?php } else { ?>
                    <div class="report-print-head">
                      <h4>FUD STAFF COOPERATIVE SOCIETY LIMITED</h4>
                    </div>
                    <div class="d-flex flex-wrap justify-content-between align-items-start mb-3">
                      <div class="mb-2">
                        <h3 class="mb-1"><?php echo htmlspecialchars($report['title']); ?></h3>
                        <div class="text-muted"><?php echo htmlspecialchars($report['subtitle']); ?></div>
                        <div class="text-muted small"><?php echo htmlspecialchars($generatedText); ?></div>
                      </div>
                      <?php if ($report['summary'] || $report['tables']) { ?>
                        <div class="report-actions mb-2">
                          <a class="btn btn-sm btn-success" href="<?php echo htmlspecialchars(reportUrl($baseParams + ['format' => 'csv'])); ?>"><i class="mdi mdi-file-excel"></i> Excel</a>
                          <a class="btn btn-sm btn-danger" href="<?php echo htmlspecialchars(reportUrl($baseParams + ['format' => 'pdf'])); ?>"><i class="mdi mdi-file-pdf"></i> PDF</a>
                          <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()"><i class="mdi mdi-printer"></i> Print</button>
                        </div>
                      <?php } ?>
                    </div>
                    <?php echo reportBodyHtml($report); ?>
                  <?php } ?>
                </div>
              </div>
            </div>
          </div>

        </div>
        <!-- content-wrapper ends -->
        <?php echo $view->subFooter('../'); ?>
