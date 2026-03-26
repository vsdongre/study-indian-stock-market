<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Consolidation Reports</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    body { background: #f6f8fb; }
    .wrap { max-width: 760px; margin: 32px auto 60px; padding: 0 12px; }
    .cardX { border-radius: 18px; background: #fff; border: 1px solid rgba(148,163,184,.35);
      box-shadow: 0 12px 35px rgba(2,6,23,.07); overflow: hidden; }
    .headX { padding: 14px 18px; border-bottom: 1px solid rgba(148,163,184,.35); }
    .title { font-weight: 950; margin: 0; font-size: 22px; }
    .hint  { color: #64748b; font-size: 13px; }
    .rpt-link { display: block; padding: 14px 18px; border-bottom: 1px solid rgba(148,163,184,.2);
      text-decoration: none; color: #0f172a; transition: background .15s; }
    .rpt-link:last-child { border-bottom: 0; }
    .rpt-link:hover { background: #f1f5f9; }
    .rpt-title { font-weight: 700; font-size: 15px; }
    .rpt-desc  { font-size: 12px; color: #64748b; margin-top: 2px; }
  </style>
</head>
<body>
<div class="wrap">
  <div class="cardX">
    <div class="headX">
      <p class="title"><i class="fa fa-layer-group me-2 text-primary"></i>Consolidation Reports</p>
      <div class="hint">Select a report to view</div>
    </div>
    <div>
      <a class="rpt-link" href="/Consolidation/Reports/report_balance_sheet_progressive_home.v6.php">
        <div class="rpt-title"><i class="fa fa-scale-balanced me-2 text-success"></i>Progressive Balance Sheet — Branch Wise</div>
        <div class="rpt-desc">Compare two user-selected dates for a single branch</div>
      </a>
      <a class="rpt-link" href="/Consolidation/Reports/report_balance_sheet_progressive_region_home.php">
        <div class="rpt-title"><i class="fa fa-map me-2 text-info"></i>Progressive Balance Sheet — Region Wise</div>
        <div class="rpt-desc">Compare two user-selected dates for a single region</div>
      </a>
      <a class="rpt-link" href="/Consolidation/Reports/report_balance_sheet_progressive_consolidated_home.php">
        <div class="rpt-title"><i class="fa fa-globe me-2 text-warning"></i>Progressive Balance Sheet — Consolidated</div>
        <div class="rpt-desc">Compare two user-selected dates across all branches (consolidated)</div>
      </a>
    </div>
  </div>
</div>
</body>
</html>
