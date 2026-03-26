<?php
// Consolidation/Reports/report_balance_sheet_progressive_region_view.php
//
// Progressive Balance Sheet (Region Wise) — user-selected Date1 vs Date2
//
// Behavior:
//   For each date, resolve EntryDate as:
//     1) exact match
//     2) latest date in same month/year <= target
//     3) latest date overall <= target
//
// Params:
//   RegionID  (int)
//   dtDate1   (YYYY-MM-DD)
//   dtDate2   (YYYY-MM-DD)
// Optional:
//   export=csv

require_once __DIR__ . '/../db.php';

function h($s)      { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function money($n)  { return number_format((float)$n, 2, '.', ','); }

function sideForAmount(int $headBalance, float $amount): string {
    if ($headBalance === 2) return 'L';
    if ($headBalance === 1) return 'A';
    return ($amount < 0) ? 'L' : 'A';
}

/**
 * Resolve the closest available EntryDate for a given RegionID and target date:
 *  1) Exact match
 *  2) Latest date in same month/year <= target
 *  3) Latest date overall <= target
 */
function resolveEntryDate(PDO $pdo, int $RegionID, string $dt): string {
    // 1) Exact match
    $stmt = $pdo->prepare("
        SELECT TOP 1 CONVERT(varchar(10), CAST(dtDate AS date), 120) AS EntryDate
        FROM BalanceSheetRegion
        WHERE RegionID = ?
          AND CAST(dtDate AS date) = CONVERT(date, ?, 120)
        GROUP BY CAST(dtDate AS date)
    ");
    $stmt->execute([$RegionID, $dt]);
    $d = (string)$stmt->fetchColumn();
    if ($d !== '') return $d;

    // 2) Same month/year <= dt
    $stmt = $pdo->prepare("
        SELECT TOP 1 CONVERT(varchar(10), CAST(dtDate AS date), 120) AS EntryDate
        FROM BalanceSheetRegion
        WHERE RegionID = ?
          AND YEAR(CAST(dtDate AS date))  = YEAR(CONVERT(date, ?, 120))
          AND MONTH(CAST(dtDate AS date)) = MONTH(CONVERT(date, ?, 120))
          AND CAST(dtDate AS date) <= CONVERT(date, ?, 120)
        GROUP BY CAST(dtDate AS date)
        ORDER BY CAST(dtDate AS date) DESC
    ");
    $stmt->execute([$RegionID, $dt, $dt, $dt]);
    $d = (string)$stmt->fetchColumn();
    if ($d !== '') return $d;

    // 3) Fallback — latest <= dt
    $stmt = $pdo->prepare("
        SELECT TOP 1 CONVERT(varchar(10), CAST(dtDate AS date), 120) AS EntryDate
        FROM BalanceSheetRegion
        WHERE RegionID = ?
          AND CAST(dtDate AS date) <= CONVERT(date, ?, 120)
        GROUP BY CAST(dtDate AS date)
        ORDER BY CAST(dtDate AS date) DESC
    ");
    $stmt->execute([$RegionID, $dt]);
    return (string)$stmt->fetchColumn();
}

// ── Input validation ──────────────────────────────────────────────────────────
$RegionID = isset($_GET['RegionID']) ? (int)$_GET['RegionID'] : 0;
$dtDate1  = isset($_GET['dtDate1'])  ? trim((string)$_GET['dtDate1']) : '';
$dtDate2  = isset($_GET['dtDate2'])  ? trim((string)$_GET['dtDate2']) : '';
$export   = isset($_GET['export'])   ? strtolower(trim((string)$_GET['export'])) : '';

if ($RegionID <= 0
    || $dtDate1 === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dtDate1)
    || $dtDate2 === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dtDate2)
) {
    http_response_code(400);
    echo "Invalid parameters. Use RegionID, dtDate1, dtDate2 (YYYY-MM-DD).";
    exit;
}

$pdo = getPDOConnection();

$stmtR = $pdo->prepare("SELECT RegionName FROM Region WHERE RegionID = ?");
$stmtR->execute([$RegionID]);
$RegionName = $stmtR->fetchColumn() ?: ("RegionID " . $RegionID);

$entry1 = resolveEntryDate($pdo, $RegionID, $dtDate1);
$entry2 = resolveEntryDate($pdo, $RegionID, $dtDate2);

if ($entry1 === '' || $entry2 === '') {
    http_response_code(404);
    echo "No BalanceSheetRegion data found for RegionID={$RegionID} for the selected dates.";
    exit;
}

// ── Fetch balances for both resolved entry dates ──────────────────────────────
$sql = "
    SELECT
        h.HeadID,
        h.HeadGroupID,
        h.HeadNo,
        h.HeadDescription,
        ISNULL(h.HeadSRNO,    2147483647) AS HeadSRNO,
        ISNULL(h.HeadBalance, 0)          AS HeadBalance,
        ISNULL(g.HeadGroupName,  'UNGROUPED')  AS HeadGroupName,
        ISNULL(g.HeadGroupSRNO,  2147483647)   AS HeadGroupSRNO,
        SUM(CASE WHEN CAST(b.dtDate AS date) = ? THEN b.BalBal ELSE 0 END) AS Bal1,
        SUM(CASE WHEN CAST(b.dtDate AS date) = ? THEN b.BalBal ELSE 0 END) AS Bal2
    FROM BalanceSheetRegion b
    INNER JOIN Head h ON h.HeadID = b.HeadID
    LEFT  JOIN HeadGroup g ON g.HeadGroupID = h.HeadGroupID
    WHERE b.RegionID = ?
      AND (CAST(b.dtDate AS date) = ? OR CAST(b.dtDate AS date) = ?)
    GROUP BY
        h.HeadID, h.HeadGroupID, h.HeadNo, h.HeadDescription, h.HeadSRNO, h.HeadBalance,
        g.HeadGroupName, g.HeadGroupSRNO
    ORDER BY
        ISNULL(g.HeadGroupSRNO, 2147483647),
        ISNULL(h.HeadSRNO,      2147483647),
        h.HeadNo,
        h.HeadDescription
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$entry1, $entry2, $RegionID, $entry1, $entry2]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── Group rows into Liabilities / Assets ─────────────────────────────────────
$liabGroups = [];
$assetGroups = [];
$liab1Total = 0.0; $liab2Total = 0.0;
$asset1Total = 0.0; $asset2Total = 0.0;

foreach ($data as $r) {
    $gName = trim((string)($r['HeadGroupName'] ?? ''));
    if ($gName === '') $gName = 'UNGROUPED';
    $gSr   = (int)($r['HeadGroupSRNO'] ?? 0);
    $gKey  = str_pad((string)$gSr, 6, '0', STR_PAD_LEFT) . '|' . $gName;

    $bal1        = (float)($r['Bal1'] ?? 0);
    $bal2        = (float)($r['Bal2'] ?? 0);
    $headBalance = (int)($r['HeadBalance'] ?? 0);

    $side1       = sideForAmount($headBalance, $bal1);
    $side2       = sideForAmount($headBalance, $bal2);
    $displaySide = ($bal2 != 0.0) ? $side2 : $side1;

    $item = [
        'HeadNo'          => (string)($r['HeadNo'] ?? ''),
        'HeadDescription' => (string)($r['HeadDescription'] ?? ''),
        'Bal1'            => $bal1,
        'Bal2'            => $bal2,
    ];

    if ($displaySide === 'L') {
        if (!isset($liabGroups[$gKey])) $liabGroups[$gKey] = ['name' => $gName, 'items' => [], 't1' => 0.0, 't2' => 0.0];
        $liabGroups[$gKey]['items'][] = $item;
        $liabGroups[$gKey]['t1'] += $bal1;
        $liabGroups[$gKey]['t2'] += $bal2;
        $liab1Total += $bal1;
        $liab2Total += $bal2;
    } else {
        if (!isset($assetGroups[$gKey])) $assetGroups[$gKey] = ['name' => $gName, 'items' => [], 't1' => 0.0, 't2' => 0.0];
        $assetGroups[$gKey]['items'][] = $item;
        $assetGroups[$gKey]['t1'] += $bal1;
        $assetGroups[$gKey]['t2'] += $bal2;
        $asset1Total += $bal1;
        $asset2Total += $bal2;
    }
}

function flatten(array $groups): array {
    $out = [];
    foreach ($groups as $g) {
        $out[] = ['type' => 'group', 'name' => $g['name']];
        foreach ($g['items'] as $it) $out[] = ['type' => 'item'] + $it;
        $out[] = ['type' => 'gTotal', 't1' => $g['t1'], 't2' => $g['t2']];
        $out[] = ['type' => 'spacer'];
    }
    return $out;
}

$liabRows  = flatten($liabGroups);
$assetRows = flatten($assetGroups);
$maxRows   = max(count($liabRows), count($assetRows));

// ── Export CSV ────────────────────────────────────────────────────────────────
if ($export === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="BalanceSheet_Region_' . $RegionID . '_' . $entry1 . '_vs_' . $entry2 . '.csv"');
    $out = fopen('php://output', 'w');

    fputcsv($out, ['Progressive Balance Sheet — Region Wise']);
    fputcsv($out, ['Region', $RegionName, 'RegionID', $RegionID]);
    fputcsv($out, ['Date 1 (selected)', $dtDate1, 'Entry date used', $entry1]);
    fputcsv($out, ['Date 2 (selected)', $dtDate2, 'Entry date used', $entry2]);
    fputcsv($out, []);

    fputcsv($out, ['LIABILITIES']);
    fputcsv($out, ['Group', 'HeadNo', 'HeadDescription', 'Bal1', 'Bal2']);
    foreach ($liabGroups as $g) {
        fputcsv($out, [$g['name'], '', '', '', '']);
        foreach ($g['items'] as $it) fputcsv($out, ['', $it['HeadNo'], $it['HeadDescription'], $it['Bal1'], $it['Bal2']]);
        fputcsv($out, ['Group Total', '', '', $g['t1'], $g['t2']]);
        fputcsv($out, []);
    }
    fputcsv($out, ['TOTAL LIABILITIES', '', '', $liab1Total, $liab2Total]);
    fputcsv($out, []);

    fputcsv($out, ['ASSETS']);
    fputcsv($out, ['Group', 'HeadNo', 'HeadDescription', 'Bal1', 'Bal2']);
    foreach ($assetGroups as $g) {
        fputcsv($out, [$g['name'], '', '', '', '']);
        foreach ($g['items'] as $it) fputcsv($out, ['', $it['HeadNo'], $it['HeadDescription'], $it['Bal1'], $it['Bal2']]);
        fputcsv($out, ['Group Total', '', '', $g['t1'], $g['t2']]);
        fputcsv($out, []);
    }
    fputcsv($out, ['TOTAL ASSETS', '', '', $asset1Total, $asset2Total]);

    fclose($out);
    exit;
}

// ── HTML Render ───────────────────────────────────────────────────────────────
$dt1Obj    = DateTime::createFromFormat('Y-m-d', $entry1);
$dt2Obj    = DateTime::createFromFormat('Y-m-d', $entry2);
if ($dt1Obj === false || $dt2Obj === false) {
    http_response_code(500);
    echo "Unable to parse resolved entry dates.";
    exit;
}
$fmt1      = $dt1Obj->format('d-m-y');
$fmt2      = $dt2Obj->format('d-m-y');
$asOnText  = $dt2Obj->format('d-M-y');
$printTime = date('d-m-Y H:i:s');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Progressive Balance Sheet — Region Wise</title>
  <style>
    body { font-family: Arial, Helvetica, sans-serif; background: #fff; color: #0f172a; }
    .page { max-width: 1280px; margin: 0 auto; padding: 14px 12px 30px; }
    .toolbar { display: flex; gap: 10px; justify-content: flex-end; margin-bottom: 10px; }
    .toolbar a, .toolbar button { padding: 8px 10px; border: 1px solid #cbd5e1; background: #f8fafc; border-radius: 10px;
      text-decoration: none; color: #0f172a; cursor: pointer; font-size: 13px; }
    .hdr { border: 1px solid #cbd5e1; border-radius: 14px; padding: 12px 14px; background: #f8fafc; margin-bottom: 12px; }
    .t1  { font-weight: 900; font-size: 16px; margin: 0; }
    .sub { color: #475569; font-size: 12px; margin-top: 4px; display: flex; gap: 14px; flex-wrap: wrap; }
    .sub b { color: #0f172a; }
    .grid { display: grid; grid-template-columns: 1fr 8px 1fr; }
    .sep  { width: 8px; background: repeating-linear-gradient(to bottom, transparent, transparent 7px, #cbd5e1 7px, #cbd5e1 9px); border-radius: 10px; }
    .panel { border: 1px solid #cbd5e1; border-radius: 14px; overflow: hidden; }
    .ph   { background: #f8fafc; border-bottom: 1px solid #cbd5e1; padding: 10px 12px; font-weight: 900; text-transform: uppercase;
      font-size: 12px; letter-spacing: .4px; display: flex; justify-content: space-between; }
    table { width: 100%; border-collapse: collapse; table-layout: fixed; }
    th, td { border-top: 1px solid #cbd5e1; padding: 7px 8px; font-size: 12px; vertical-align: top; }
    th { color: #475569; font-size: 11px; text-transform: uppercase; letter-spacing: .3px; }
    td.code { width: 78px; font-weight: 800; }
    td.amt  { width: 140px; text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
    tr.groupRow   td { background: rgba(2,6,23,.04); font-weight: 900; }
    tr.groupTotal td { background: rgba(255,247,237,.75); font-weight: 900; }
    tr.spacer     td { padding: 5px 0; background: #fff; border-top: 0; }
    .totalRow     td { font-weight: 900; background: #fff7ed; }
    @media print { .toolbar { display: none; } .page { max-width: none; padding: 0; } }
  </style>
</head>
<body>
<div class="page">
  <div class="toolbar">
    <a href="/Consolidation/index.php">Back</a>
    <button onclick="window.print()">Print</button>
  </div>

  <div class="hdr">
    <p class="t1">Progressive Balance Sheet — Region Wise &nbsp;|&nbsp; As On: <?php echo h($asOnText); ?></p>
    <div class="sub">
      <div>Region: <b><?php echo h($RegionName); ?></b></div>
      <div>Date 1 (Entry): <b><?php echo h($fmt1); ?></b></div>
      <div>Date 2 (Entry): <b><?php echo h($fmt2); ?></b></div>
      <div>Generated: <b><?php echo h($printTime); ?></b></div>
    </div>
  </div>

  <div class="grid">
    <!-- Liabilities -->
    <div class="panel">
      <div class="ph"><span>L I A B I L I T I E S</span><span><?php echo h($fmt1); ?> / <?php echo h($fmt2); ?></span></div>
      <table>
        <thead>
          <tr>
            <th style="width:78px;">Head</th>
            <th>Description</th>
            <th style="width:140px;text-align:right;"><?php echo h($fmt1); ?></th>
            <th style="width:140px;text-align:right;"><?php echo h($fmt2); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php for ($i = 0; $i < $maxRows; $i++): ?>
            <?php $row = $liabRows[$i] ?? null; ?>
            <?php if ($row === null): ?>
              <tr><td class="code">&nbsp;</td><td>&nbsp;</td><td class="amt">&nbsp;</td><td class="amt">&nbsp;</td></tr>
            <?php elseif ($row['type'] === 'group'): ?>
              <tr class="groupRow"><td colspan="4"><?php echo h($row['name']); ?></td></tr>
            <?php elseif ($row['type'] === 'gTotal'): ?>
              <tr class="groupTotal">
                <td colspan="2" style="text-align:right;">Group Total</td>
                <td class="amt"><?php echo money($row['t1']); ?></td>
                <td class="amt"><?php echo money($row['t2']); ?></td>
              </tr>
            <?php elseif ($row['type'] === 'spacer'): ?>
              <tr class="spacer"><td colspan="4"></td></tr>
            <?php else: ?>
              <tr>
                <td class="code"><?php echo h($row['HeadNo']); ?></td>
                <td><?php echo h($row['HeadDescription']); ?></td>
                <td class="amt"><?php echo money($row['Bal1']); ?></td>
                <td class="amt"><?php echo money($row['Bal2']); ?></td>
              </tr>
            <?php endif; ?>
          <?php endfor; ?>
          <tr class="totalRow">
            <td colspan="2" style="text-align:right;">Total Liabilities</td>
            <td class="amt"><?php echo money($liab1Total); ?></td>
            <td class="amt"><?php echo money($liab2Total); ?></td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="sep"></div>

    <!-- Assets -->
    <div class="panel">
      <div class="ph"><span>A S S E T S</span><span><?php echo h($fmt1); ?> / <?php echo h($fmt2); ?></span></div>
      <table>
        <thead>
          <tr>
            <th style="width:78px;">Head</th>
            <th>Description</th>
            <th style="width:140px;text-align:right;"><?php echo h($fmt1); ?></th>
            <th style="width:140px;text-align:right;"><?php echo h($fmt2); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php for ($i = 0; $i < $maxRows; $i++): ?>
            <?php $row = $assetRows[$i] ?? null; ?>
            <?php if ($row === null): ?>
              <tr><td class="code">&nbsp;</td><td>&nbsp;</td><td class="amt">&nbsp;</td><td class="amt">&nbsp;</td></tr>
            <?php elseif ($row['type'] === 'group'): ?>
              <tr class="groupRow"><td colspan="4"><?php echo h($row['name']); ?></td></tr>
            <?php elseif ($row['type'] === 'gTotal'): ?>
              <tr class="groupTotal">
                <td colspan="2" style="text-align:right;">Group Total</td>
                <td class="amt"><?php echo money($row['t1']); ?></td>
                <td class="amt"><?php echo money($row['t2']); ?></td>
              </tr>
            <?php elseif ($row['type'] === 'spacer'): ?>
              <tr class="spacer"><td colspan="4"></td></tr>
            <?php else: ?>
              <tr>
                <td class="code"><?php echo h($row['HeadNo']); ?></td>
                <td><?php echo h($row['HeadDescription']); ?></td>
                <td class="amt"><?php echo money($row['Bal1']); ?></td>
                <td class="amt"><?php echo money($row['Bal2']); ?></td>
              </tr>
            <?php endif; ?>
          <?php endfor; ?>
          <tr class="totalRow">
            <td colspan="2" style="text-align:right;">Total Assets</td>
            <td class="amt"><?php echo money($asset1Total); ?></td>
            <td class="amt"><?php echo money($asset2Total); ?></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>
