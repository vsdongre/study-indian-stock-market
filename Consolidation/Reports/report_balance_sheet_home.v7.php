<?php
// Consolidation/Reports/report_balance_sheet_home.v7.php
// Branch-Wise Balance Sheet (Home) — user selects Branch + month-end Date

require_once __DIR__ . '/../db.php';

$pdo = getPDOConnection();
$branches = $pdo->query("SELECT BranchID, BranchName FROM Branch WHERE Status = 1 ORDER BY BranchName")
               ->fetchAll(PDO::FETCH_ASSOC);

// Default date: last day of current month
$now = new DateTime();
$now->modify('last day of this month');
$defaultDate = $now->format('Y-m-d');

// View file this home page submits to
$viewPath = "/Consolidation/Reports/report_balance_sheet_view.v7.php";
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Balance Sheet — Branch Wise</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

  <style>
    body { background: #f6f8fb; }
    .wrap { max-width: 760px; margin: 32px auto 60px; padding: 0 12px; }
    .cardX {
      border-radius: 18px; background: #fff;
      border: 1px solid rgba(148,163,184,.35);
      box-shadow: 0 12px 35px rgba(2,6,23,.07); overflow: hidden;
    }
    .headX {
      padding: 14px 18px; border-bottom: 1px solid rgba(148,163,184,.35);
      display: flex; justify-content: space-between; align-items: center;
    }
    .heroIcon {
      width: 48px; height: 48px; border-radius: 14px;
      display: flex; align-items: center; justify-content: center;
      background: linear-gradient(180deg, #0d6efd, #20c997);
      color: #fff; font-size: 20px; flex-shrink: 0;
    }
    .title { font-weight: 950; margin: 0; font-size: 20px; }
    .hint  { color: #64748b; font-size: 12px; margin-top: 3px; }
    .body  { padding: 20px 18px; }
    #alertBox { font-size: 13px; }
  </style>
</head>
<body>
<div class="wrap">
  <div class="cardX">
    <div class="headX">
      <div class="d-flex align-items-center gap-3">
        <div class="heroIcon"><i class="fa fa-scale-balanced"></i></div>
        <div>
          <div class="title">Balance Sheet — Branch Wise</div>
          <div class="hint">Select a Branch and a month-end date to view the balance sheet</div>
        </div>
      </div>
      <a class="btn btn-outline-secondary btn-sm" href="/Consolidation/index.php">
        <i class="fa fa-arrow-left me-1"></i> Back
      </a>
    </div>

    <div class="body">
      <div class="row g-3">
        <div class="col-md-7">
          <label class="form-label fw-semibold">Branch</label>
          <select id="BranchID" class="form-select">
            <option value="">— Select Branch —</option>
            <?php foreach ($branches as $b): ?>
              <option value="<?php echo (int)$b['BranchID']; ?>">
                <?php echo htmlspecialchars($b['BranchName'], ENT_QUOTES, 'UTF-8'); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-5">
          <label class="form-label fw-semibold">As On (month-end date)</label>
          <input id="dtDate" type="date" class="form-control"
                 value="<?php echo htmlspecialchars($defaultDate, ENT_QUOTES, 'UTF-8'); ?>">
          <div class="form-text">Date must be the last day of a month.</div>
        </div>
      </div>

      <div id="alertBox" class="alert d-none mt-3 mb-0"></div>

      <div class="mt-4 d-flex gap-2">
        <button id="btnView" class="btn btn-primary px-4">
          <i class="fa fa-eye me-1"></i> View Report
        </button>
        <a id="btnExport" href="#" class="btn btn-outline-success d-none">
          <i class="fa fa-file-excel me-1"></i> Export CSV
        </a>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  var viewPath   = <?php echo json_encode($viewPath); ?>;
  var alertBox   = document.getElementById('alertBox');
  var btnView    = document.getElementById('btnView');
  var btnExport  = document.getElementById('btnExport');

  function showAlert(msg, type) {
    alertBox.className = 'alert alert-' + type + ' mt-3 mb-0';
    alertBox.textContent = msg;
  }

  function isMonthEnd(dateStr) {
    if (!dateStr) return false;
    var d = new Date(dateStr + 'T00:00:00');
    if (isNaN(d.getTime())) return false;
    // Check if next day is in a different month
    var next = new Date(d);
    next.setDate(next.getDate() + 1);
    return next.getMonth() !== d.getMonth();
  }

  btnView.addEventListener('click', function () {
    var branchID = document.getElementById('BranchID').value;
    var dtDate   = document.getElementById('dtDate').value;

    if (!branchID) {
      showAlert('Please select a Branch.', 'warning');
      return;
    }
    if (!dtDate) {
      showAlert('Please enter a date.', 'warning');
      return;
    }
    if (!isMonthEnd(dtDate)) {
      showAlert('Date must be the last day of a month (e.g. 2024-03-31).', 'warning');
      return;
    }

    alertBox.className = 'alert d-none mt-3 mb-0';
    var url = viewPath + '?BranchID=' + encodeURIComponent(branchID) + '&dtDate=' + encodeURIComponent(dtDate);
    btnExport.href = url + '&export=csv';
    btnExport.classList.remove('d-none');
    window.open(url, '_blank');
  });
})();
</script>
</body>
</html>
