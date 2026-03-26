<?php
// Consolidation/Reports/report_balance_sheet_view.v7.php
// Branch-Wise Balance Sheet — single month-end date
//
// Tables:
//   BalanceSheetBranch(b) : BranchID, HeadID, dtDate, BalBal
//   Head(h)               : HeadID, HeadGroupID, HeadNo, HeadDescription, HeadSRNO, HeadBalance
//   HeadGroup(g)          : HeadGroupID, HeadGroupName, HeadGroupSRNO
//   Branch                : BranchID, BranchName
//
// HeadBalance meanings (Head table):
//   1 = Asset  (shown on RIGHT / Assets side)
//   2 = Liability (shown on LEFT / Liabilities side)
//   other / 0 = classified by sign of BalBal (negative → Liability, positive → Asset)
//
// Params (GET):
//   BranchID  (int, required)
//   dtDate    (YYYY-MM-DD, must be month-end, required)
//   export    (optional: "csv")

require_once __DIR__ . '/../db.php';

// ── Helpers ───────────────────────────────────────────────────────────────────

function h($s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function money($n): string {
    return number_format((float)$n, 2, '.', ',');
}

/**
 * Maximum absolute difference (in currency units) that is treated as "balanced".
 * Float arithmetic and rounding in stored data can produce sub-cent discrepancies,
 * so differences below this threshold are shown as balanced rather than as a
 * profit/loss figure.
 */
define('BALANCE_TOLERANCE', 0.005);

/**
 * Returns true if $ymd (YYYY-MM-DD) is the last day of its month.
 */
function isMonthEnd(string $ymd): bool {
    $dt = DateTime::createFromFormat('Y-m-d', $ymd);
    if (!$dt) return false;
    $clone = clone $dt;
    $clone->modify('last day of this month');
    return $dt->format('Y-m-d') === $clone->format('Y-m-d');
}

/**
 * Classify a head as Asset ('A') or Liability ('L') using HeadBalance first,
 * falling back to sign of BalBal for unclassified heads (HeadBalance = 0 or other).
 *
 * HeadBalance: 1 = Asset, 2 = Liability, other = sign-based fallback.
 */
function sideForHead(int $headBalance, float $balBal): string {
    if ($headBalance === 1) return 'A';
    if ($headBalance === 2) return 'L';
    // Fallback: negative balance → Liability, positive/zero → Asset
    return ($balBal < 0) ? 'L' : 'A';
}

// ── Input validation ──────────────────────────────────────────────────────────

$BranchID = isset($_GET['BranchID']) ? (int)$_GET['BranchID'] : 0;
$dtDate   = isset($_GET['dtDate'])   ? trim((string)$_GET['dtDate']) : '';
$export   = isset($_GET['export'])   ? strtolower(trim((string)$_GET['export'])) : '';

if ($BranchID <= 0
    || $dtDate === ''
    || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dtDate)
    || !isMonthEnd($dtDate)
) {
    http_response_code(400);
    echo "Invalid parameters. Please provide a valid BranchID and a month-end date (YYYY-MM-DD) via the home page.";
    exit;
}

// ── Database ──────────────────────────────────────────────────────────────────

$pdo = getPDOConnection();

// Fetch branch name
$stmtB = $pdo->prepare("SELECT BranchName FROM Branch WHERE BranchID = ?");
$stmtB->execute([$BranchID]);
$BranchName = $stmtB->fetchColumn() ?: ('BranchID ' . $BranchID);

// Fetch all head balances for the branch on the given month-end date.
// Group by head so duplicate date entries (if any) are summed.
$sql = "
    SELECT
        h.HeadID,
        h.HeadGroupID,
        h.HeadNo,
        h.HeadDescription,
        ISNULL(h.HeadSRNO,    2147483647) AS HeadSRNO,
        ISNULL(h.HeadBalance, 0)          AS HeadBalance,
        ISNULL(g.HeadGroupName,  'UNGROUPED') AS HeadGroupName,
        ISNULL(g.HeadGroupSRNO,  2147483647)  AS HeadGroupSRNO,
        SUM(ISNULL(b.BalBal, 0))              AS BalBal
    FROM BalanceSheetBranch b
    INNER JOIN Head h       ON h.HeadID      = b.HeadID
    LEFT  JOIN HeadGroup g  ON g.HeadGroupID = h.HeadGroupID
    WHERE b.BranchID = ?
      AND CAST(b.dtDate AS date) = CONVERT(date, ?, 120)
    GROUP BY
        h.HeadID, h.HeadGroupID, h.HeadNo, h.HeadDescription,
        h.HeadSRNO, h.HeadBalance,
        g.HeadGroupName, g.HeadGroupSRNO
    ORDER BY
        ISNULL(g.HeadGroupSRNO, 2147483647),
        ISNULL(h.HeadSRNO,      2147483647),
        h.HeadNo,
        h.HeadDescription
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$BranchID, $dtDate]);
$dbRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── Classify into Liabilities / Assets, grouped by HeadGroup ─────────────────

// Structure: $liabGroups[$gKey] = ['name' => string, 'items' => [...], 'total' => float]
// 'total' stores the sum of BalBal values (signed, not abs) to give correct accounting totals.
$liabGroups  = [];
$assetGroups = [];
$liabTotal   = 0.0;
$assetTotal  = 0.0;

foreach ($dbRows as $r) {
    $gName = trim((string)($r['HeadGroupName'] ?? ''));
    if ($gName === '') $gName = 'UNGROUPED';
    $gSr  = (int)($r['HeadGroupSRNO'] ?? 0);
    // Sort key: zero-padded serial + name to preserve HeadGroupSRNO ordering
    $gKey = str_pad((string)$gSr, 6, '0', STR_PAD_LEFT) . '|' . $gName;

    $balBal      = (float)($r['BalBal']      ?? 0);
    $headBalance = (int)($r['HeadBalance'] ?? 0);

    $side = sideForHead($headBalance, $balBal);
    // Display absolute value; totals also use abs to show gross figures per side.
    $amtDisplay = abs($balBal);

    $item = [
        'HeadNo'          => (string)($r['HeadNo']          ?? ''),
        'HeadDescription' => (string)($r['HeadDescription'] ?? ''),
        'Amount'          => $amtDisplay,
        'RawBal'          => $balBal,
    ];

    if ($side === 'L') {
        if (!isset($liabGroups[$gKey]))
            $liabGroups[$gKey] = ['name' => $gName, 'items' => [], 'total' => 0.0];
        $liabGroups[$gKey]['items'][]     = $item;
        $liabGroups[$gKey]['total']      += $amtDisplay;
        $liabTotal                       += $amtDisplay;
    } else {
        if (!isset($assetGroups[$gKey]))
            $assetGroups[$gKey] = ['name' => $gName, 'items' => [], 'total' => 0.0];
        $assetGroups[$gKey]['items'][]    = $item;
        $assetGroups[$gKey]['total']     += $amtDisplay;
        $assetTotal                      += $amtDisplay;
    }
}

// Difference: positive means Assets exceed Liabilities (add to Liabilities side as profit/surplus),
// negative means Liabilities exceed Assets (add to Assets side as loss/deficit).
$difference = $assetTotal - $liabTotal;

// ── Flatten groups into display rows ─────────────────────────────────────────

function flattenGroups(array $groups): array {
    $out = [];
    foreach ($groups as $g) {
        $out[] = ['type' => 'group', 'name' => $g['name']];
        foreach ($g['items'] as $it) {
            $out[] = ['type' => 'item'] + $it;
        }
        $out[] = ['type' => 'gTotal', 'total' => $g['total']];
        $out[] = ['type' => 'spacer'];
    }
    return $out;
}

$liabRows  = flattenGroups($liabGroups);
$assetRows = flattenGroups($assetGroups);
$maxRows   = max(count($liabRows), count($assetRows), 1);

// ── Formatted strings ─────────────────────────────────────────────────────────

$asOnDisplay = DateTime::createFromFormat('Y-m-d', $dtDate)->format('d-M-Y');
$printTime   = date('d-m-Y H:i:s');

// ── CSV Export ────────────────────────────────────────────────────────────────

if ($export === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="BalanceSheet_Branch_' . $BranchID . '_' . $dtDate . '.csv"');
    $out = fopen('php://output', 'w');

    fputcsv($out, ['Balance Sheet — Branch Wise']);
    fputcsv($out, ['Branch', $BranchName, 'BranchID', $BranchID]);
    fputcsv($out, ['As On', $asOnDisplay]);
    fputcsv($out, ['Generated', $printTime]);
    fputcsv($out, []);

    fputcsv($out, ['LIABILITIES']);
    fputcsv($out, ['Group', 'HeadNo', 'Description', 'Amount']);
    foreach ($liabGroups as $g) {
        fputcsv($out, [$g['name'], '', '', '']);
        foreach ($g['items'] as $it) {
            fputcsv($out, ['', $it['HeadNo'], $it['HeadDescription'], $it['Amount']]);
        }
        fputcsv($out, ['  Group Total', '', '', $g['total']]);
        fputcsv($out, []);
    }
    fputcsv($out, ['TOTAL LIABILITIES', '', '', $liabTotal]);
    if ($difference > 0) {
        fputcsv($out, ['Profit / Surplus (balancing)', '', '', $difference]);
    }
    fputcsv($out, []);

    fputcsv($out, ['ASSETS']);
    fputcsv($out, ['Group', 'HeadNo', 'Description', 'Amount']);
    foreach ($assetGroups as $g) {
        fputcsv($out, [$g['name'], '', '', '']);
        foreach ($g['items'] as $it) {
            fputcsv($out, ['', $it['HeadNo'], $it['HeadDescription'], $it['Amount']]);
        }
        fputcsv($out, ['  Group Total', '', '', $g['total']]);
        fputcsv($out, []);
    }
    fputcsv($out, ['TOTAL ASSETS', '', '', $assetTotal]);
    if ($difference < 0) {
        fputcsv($out, ['Loss / Deficit (balancing)', '', '', abs($difference)]);
    }
    fputcsv($out, []);
    fputcsv($out, ['Difference (Assets - Liabilities)', '', '', $difference]);

    fclose($out);
    exit;
}

// ── HTML Render ───────────────────────────────────────────────────────────────
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
    :root {
      --ink: #0f172a; --muted: #475569; --line: #cbd5e1; --soft: #f8fafc;
      --liab-clr: #1d4ed8; --asset-clr: #047857;
    }
    body { background: #f6f8fb; color: var(--ink); font-family: Arial, Helvetica, sans-serif; }
    .wrap { max-width: 1280px; margin: 0 auto; padding: 18px 12px 40px; }

    /* Header bar */
    .hero {
      border-radius: 18px; padding: 14px 16px; margin-bottom: 14px;
      background: linear-gradient(90deg, rgba(29,78,216,.10), rgba(4,120,87,.08));
      box-shadow: 0 10px 30px rgba(2,6,23,.08);
      display: flex; gap: 14px; align-items: center;
    }
    .heroIcon {
      width: 54px; height: 54px; border-radius: 14px; flex-shrink: 0;
      display: flex; align-items: center; justify-content: center;
      background: linear-gradient(180deg, #0d6efd, #20c997);
      color: #fff; font-size: 20px;
    }
    .meta { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 4px; }
    .pill {
      border: 1px solid rgba(148,163,184,.45); background: #fff;
      padding: 4px 10px; border-radius: 999px; font-size: 12px;
      color: #0f172a; font-weight: 700;
    }

    /* Two-panel grid */
    .grid { display: grid; grid-template-columns: 1fr 10px 1fr; gap: 0; }
    .sep {
      width: 10px;
      background: repeating-linear-gradient(to bottom, transparent, transparent 7px, rgba(203,213,225,.9) 7px, rgba(203,213,225,.9) 9px);
      border-radius: 12px;
    }
    .panel { border: 1px solid var(--line); border-radius: 14px; overflow: hidden; background: #fff; }
    .panelHeader {
      padding: 10px 12px; background: var(--soft); border-bottom: 1px solid var(--line);
      display: flex; justify-content: space-between; align-items: center;
      font-weight: 900; text-transform: uppercase; font-size: 12px; letter-spacing: .4px;
    }
    .badge2 {
      font-size: 11px; font-weight: 900; padding: 3px 8px;
      border-radius: 999px; border: 1px solid rgba(148,163,184,.5); background: #fff;
    }
    .badge2.liab  { color: var(--liab-clr); }
    .badge2.asset { color: var(--asset-clr); }

    table { width: 100%; border-collapse: collapse; table-layout: fixed; }
    th, td { border-top: 1px solid var(--line); padding: 7px 8px; font-size: 12px; vertical-align: top; }
    th { color: var(--muted); font-size: 11px; text-transform: uppercase; letter-spacing: .3px; }
    td.code { width: 82px; font-weight: 800; }
    td.amt  { width: 160px; text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
    tr.groupRow   td { background: rgba(2,6,23,.04); font-weight: 900; }
    tr.groupTotal td { background: rgba(255,247,237,.75); font-weight: 900; }
    tr.spacer     td { padding: 5px 0; background: #fff; border-top: 0; }
    .totalRow     td { font-weight: 900; background: #fff7ed; }
    .diffRow      td { font-weight: 900; background: rgba(254,243,199,.6); font-style: italic; }

    /* Summary bar */
    .summary {
      margin-top: 14px; border-radius: 14px; background: #fff;
      border: 1px solid rgba(148,163,184,.35);
      box-shadow: 0 8px 24px rgba(2,6,23,.06);
      padding: 12px 14px;
      display: flex; justify-content: space-between; gap: 10px; flex-wrap: wrap;
      font-size: 12px; color: var(--muted);
    }
    .pill.balanced { border-color: rgba(4,120,87,.3);  background: rgba(4,120,87,.07);  color: var(--asset-clr); }
    .pill.surplus  { border-color: rgba(29,78,216,.3); background: rgba(29,78,216,.07); color: var(--liab-clr); }
    .pill.deficit  { border-color: rgba(180,35,24,.3); background: rgba(180,35,24,.07); color: #b42318; }

    @media print {
      .no-print { display: none !important; }
      body { background: #fff; }
      .wrap { max-width: none; padding: 0; }
      .panel, .summary { box-shadow: none; }
    }
  </style>
</head>
<body>
<div class="wrap">

  <!-- Header / toolbar -->
  <div class="hero no-print">
    <div class="heroIcon"><i class="fa-solid fa-scale-balanced"></i></div>
    <div class="flex-fill">
      <h4 class="mb-1" style="font-weight:900;">Balance Sheet — Branch Wise</h4>
      <div class="meta">
        <span class="pill">Branch: <?php echo h($BranchName); ?> (<?php echo $BranchID; ?>)</span>
        <span class="pill">As On: <?php echo h($asOnDisplay); ?></span>
        <span class="pill">Generated: <?php echo h($printTime); ?></span>
      </div>
    </div>
    <div class="ms-auto d-flex gap-2 flex-shrink-0">
      <a class="btn btn-sm btn-outline-secondary"
         href="/Consolidation/Reports/report_balance_sheet_home.v7.php">
        <i class="fa fa-arrow-left me-1"></i> Back
      </a>
      <a class="btn btn-sm btn-outline-success"
         href="<?php echo h($_SERVER['PHP_SELF'] . '?BranchID=' . $BranchID . '&dtDate=' . urlencode($dtDate) . '&export=csv'); ?>">
        <i class="fa fa-file-excel me-1"></i> Export CSV
      </a>
      <button class="btn btn-sm btn-primary" onclick="window.print()">
        <i class="fa fa-print me-1"></i> Print / PDF
      </button>
    </div>
  </div>

  <!-- Two-column Balance Sheet -->
  <div class="grid">

    <!-- LEFT: Liabilities -->
    <div class="panel">
      <div class="panelHeader">
        <span>Liabilities</span>
        <span class="badge2 liab">Total: <?php echo money($liabTotal); ?></span>
      </div>
      <table>
        <thead>
          <tr>
            <th style="width:82px;">Head</th>
            <th>Description</th>
            <th style="width:160px;text-align:right;">Amount</th>
          </tr>
        </thead>
        <tbody>
          <?php for ($i = 0; $i < $maxRows; $i++): ?>
            <?php $row = $liabRows[$i] ?? null; ?>
            <?php if ($row === null): ?>
              <tr><td class="code">&nbsp;</td><td>&nbsp;</td><td class="amt">&nbsp;</td></tr>
            <?php elseif ($row['type'] === 'group'): ?>
              <tr class="groupRow"><td colspan="3"><?php echo h($row['name']); ?></td></tr>
            <?php elseif ($row['type'] === 'gTotal'): ?>
              <tr class="groupTotal">
                <td colspan="2" style="text-align:right;">Group Total</td>
                <td class="amt"><?php echo money($row['total']); ?></td>
              </tr>
            <?php elseif ($row['type'] === 'spacer'): ?>
              <tr class="spacer"><td colspan="3"></td></tr>
            <?php else: ?>
              <tr>
                <td class="code"><?php echo h($row['HeadNo']); ?></td>
                <td><?php echo h($row['HeadDescription']); ?></td>
                <td class="amt"><?php echo money($row['Amount']); ?></td>
              </tr>
            <?php endif; ?>
          <?php endfor; ?>

          <!-- Balancing row: profit/surplus appears on Liabilities side -->
          <?php if ($difference > BALANCE_TOLERANCE): ?>
          <tr class="diffRow">
            <td colspan="2" style="text-align:right;">Profit / Surplus (balancing)</td>
            <td class="amt"><?php echo money($difference); ?></td>
          </tr>
          <?php endif; ?>

          <tr class="totalRow">
            <td colspan="2" style="text-align:right;">Total Liabilities</td>
            <td class="amt"><?php echo money($liabTotal + max(0.0, $difference)); ?></td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="sep"></div>

    <!-- RIGHT: Assets -->
    <div class="panel">
      <div class="panelHeader">
        <span>Assets</span>
        <span class="badge2 asset">Total: <?php echo money($assetTotal); ?></span>
      </div>
      <table>
        <thead>
          <tr>
            <th style="width:82px;">Head</th>
            <th>Description</th>
            <th style="width:160px;text-align:right;">Amount</th>
          </tr>
        </thead>
        <tbody>
          <?php for ($i = 0; $i < $maxRows; $i++): ?>
            <?php $row = $assetRows[$i] ?? null; ?>
            <?php if ($row === null): ?>
              <tr><td class="code">&nbsp;</td><td>&nbsp;</td><td class="amt">&nbsp;</td></tr>
            <?php elseif ($row['type'] === 'group'): ?>
              <tr class="groupRow"><td colspan="3"><?php echo h($row['name']); ?></td></tr>
            <?php elseif ($row['type'] === 'gTotal'): ?>
              <tr class="groupTotal">
                <td colspan="2" style="text-align:right;">Group Total</td>
                <td class="amt"><?php echo money($row['total']); ?></td>
              </tr>
            <?php elseif ($row['type'] === 'spacer'): ?>
              <tr class="spacer"><td colspan="3"></td></tr>
            <?php else: ?>
              <tr>
                <td class="code"><?php echo h($row['HeadNo']); ?></td>
                <td><?php echo h($row['HeadDescription']); ?></td>
                <td class="amt"><?php echo money($row['Amount']); ?></td>
              </tr>
            <?php endif; ?>
          <?php endfor; ?>

          <!-- Balancing row: loss/deficit appears on Assets side -->
          <?php if ($difference < -BALANCE_TOLERANCE): ?>
          <tr class="diffRow">
            <td colspan="2" style="text-align:right;">Loss / Deficit (balancing)</td>
            <td class="amt"><?php echo money(abs($difference)); ?></td>
          </tr>
          <?php endif; ?>

          <tr class="totalRow">
            <td colspan="2" style="text-align:right;">Total Assets</td>
            <td class="amt"><?php echo money($assetTotal + max(0.0, -$difference)); ?></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Summary footer -->
  <div class="summary">
    <div class="d-flex gap-2 flex-wrap align-items-center">
      <?php if (abs($difference) <= BALANCE_TOLERANCE): ?>
        <span class="pill balanced"><i class="fa fa-check-circle me-1"></i> Balanced (diff: 0.00)</span>
      <?php elseif ($difference > 0): ?>
        <span class="pill surplus">Assets &gt; Liabilities by: <?php echo money($difference); ?> (Profit/Surplus)</span>
      <?php else: ?>
        <span class="pill deficit">Liabilities &gt; Assets by: <?php echo money(abs($difference)); ?> (Loss/Deficit)</span>
      <?php endif; ?>
      <span class="pill">Total Liabilities: <?php echo money($liabTotal); ?></span>
      <span class="pill">Total Assets: <?php echo money($assetTotal); ?></span>
    </div>
    <div class="text-muted" style="font-size:11px;">
      Print / PDF → use browser Print. Export CSV → download spreadsheet.
    </div>
  </div>

</div>
</body>
</html>
