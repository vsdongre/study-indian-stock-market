<?php
// Consolidation/Reports/report_balance_sheet_progressive_consolidated_home.php
// Progressive Balance Sheet (Home) — Consolidated (all branches), user selects two dates

require_once __DIR__ . '/../db.php';

$now = new DateTime();
$now->modify('last day of this month');
$defaultTo   = $now->format('Y-m-d');
$defaultFrom = (clone $now)->modify('last day of previous month')->format('Y-m-d');

$reportPath = "/Consolidation/Reports/report_balance_sheet_progressive_consolidated_view.php";
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Progressive Balance Sheet — Consolidated</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

  <style>
    body { background: #f6f8fb; }
    .wrap { max-width: 760px; margin: 18px auto 40px; padding: 0 12px; }
    .cardX { border-radius: 18px; background: #fff; border: 1px solid rgba(148,163,184,.35);
      box-shadow: 0 12px 35px rgba(2,6,23,.07); overflow: hidden; }
    .headX { padding: 14px 16px; border-bottom: 1px solid rgba(148,163,184,.35); display: flex; justify-content: space-between; align-items: center; }
    .title { font-weight: 950; margin: 0; font-size: 20px; }
    .hint  { color: #64748b; font-size: 12px; }
  </style>
</head>
<body>
<div class="wrap">
  <div class="cardX">
    <div class="headX">
      <div>
        <div class="title"><i class="fa fa-globe me-2 text-warning"></i>Progressive Balance Sheet — Consolidated</div>
        <div class="hint">Select Date 1 + Date 2 (report will show all-branch consolidated figures side-by-side)</div>
      </div>
      <a class="btn btn-outline-secondary" href="/Consolidation/index.php"><i class="fa fa-arrow-left me-1"></i> Back</a>
    </div>

    <div class="p-3">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Date 1</label>
          <input id="dtDate1" type="date" class="form-control" value="<?php echo htmlspecialchars($defaultFrom, ENT_QUOTES, 'UTF-8'); ?>">
          <div class="form-text">If exact date is not available, nearest entry date (on/before) will be used.</div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Date 2</label>
          <input id="dtDate2" type="date" class="form-control" value="<?php echo htmlspecialchars($defaultTo, ENT_QUOTES, 'UTF-8'); ?>">
        </div>
      </div>

      <div id="alertBox" class="alert d-none mt-3 mb-0"></div>

      <div class="d-flex gap-2 flex-wrap mt-3">
        <button class="btn btn-success" id="btnOpen"><i class="fa fa-up-right-from-square me-1"></i> Open Report</button>
        <button class="btn btn-outline-primary" id="btnPrint"><i class="fa fa-print me-1"></i> Print / PDF</button>
        <button class="btn btn-outline-success" id="btnExcel"><i class="fa fa-file-excel me-1"></i> Export CSV</button>
      </div>
    </div>
  </div>
</div>

<script>
  const reportBase = <?php echo json_encode($reportPath); ?>;
  const d1         = document.getElementById('dtDate1');
  const d2         = document.getElementById('dtDate2');
  const elAlert    = document.getElementById('alertBox');

  document.getElementById('btnOpen').addEventListener('click', () => openReport('open'));
  document.getElementById('btnPrint').addEventListener('click', () => openReport('print'));
  document.getElementById('btnExcel').addEventListener('click', () => openReport('excel'));

  function showAlert(type, msg) {
    elAlert.className = 'alert alert-' + type + ' mt-3 mb-0';
    elAlert.textContent = msg;
    elAlert.classList.remove('d-none');
  }
  function hideAlert() { elAlert.classList.add('d-none'); elAlert.textContent = ''; }

  function buildUrl(mode) {
    let url = reportBase
      + '?dtDate1=' + encodeURIComponent(d1.value)
      + '&dtDate2=' + encodeURIComponent(d2.value);
    if (mode === 'excel') url += '&export=csv';
    return url;
  }

  function validate() {
    hideAlert();
    if (!d1.value)          return showAlert('danger', 'Select Date 1.'), false;
    if (!d2.value)          return showAlert('danger', 'Select Date 2.'), false;
    if (d1.value > d2.value) return showAlert('warning', 'Date 1 is greater than Date 2. Please correct.'), false;
    return true;
  }

  function openReport(mode) {
    if (!validate()) return;
    const w = window.open(buildUrl(mode), '_blank', 'noopener');
    if (!w) showAlert('warning', 'Popup blocked. Please allow popups.');
  }
</script>
</body>
</html>
