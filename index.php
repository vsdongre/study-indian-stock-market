<?php
// ── index.php — Dashboard / Homepage ─────────────────────────────────────────
require_once 'config/db.php';

$pageTitle  = 'Dashboard';
$assetRoot  = '.';
require_once 'includes/header.php';

// ── Summary counters (will be live once tables exist) ─────────────────────────
$summary = [
    'Total Items'       => 0,
    'Low Stock Items'   => 0,
    'Pending Orders'    => 0,
    'Departments'       => 0,
];

try {
    $db = getDB();
    // Uncomment each line after the corresponding table is created:
    // $summary['Total Items']     = $db->query('SELECT COUNT(*) FROM items')->fetchColumn();
    // $summary['Low Stock Items'] = $db->query('SELECT COUNT(*) FROM items WHERE qty_on_hand <= reorder_level')->fetchColumn();
    // $summary['Pending Orders']  = $db->query("SELECT COUNT(*) FROM purchase_orders WHERE status='Pending'")->fetchColumn();
    // $summary['Departments']     = $db->query('SELECT COUNT(*) FROM departments')->fetchColumn();
} catch (Exception $e) {
    // DB not connected yet — show zeros.
}

$icons = ['Total Items' => '📦', 'Low Stock Items' => '⚠️', 'Pending Orders' => '🛒', 'Departments' => '🏢'];
?>

<div class="mdi-window">
  <div class="mdi-title">
    <span>🏠 Dashboard — Stationery Management</span>
    <div class="win-controls">
      <span class="win-btn">─</span>
      <span class="win-btn">□</span>
      <span class="win-btn">✕</span>
    </div>
  </div>
  <div class="mdi-body">

    <!-- Summary tiles -->
    <div class="section-title">Summary</div>
    <div class="dash-tiles">
      <?php foreach ($summary as $label => $count): ?>
      <div class="dash-tile">
        <span class="dash-tile-icon"><?= $icons[$label] ?></span>
        <span class="dash-tile-count"><?= (int)$count ?></span>
        <span class="dash-tile-label"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Quick links -->
    <div class="section-title" style="margin-top:16px;">Quick Access</div>
    <div class="quick-links">
      <a class="ql-btn" href="pages/items.php">📦 Items</a>
      <a class="ql-btn" href="pages/categories.php">🗂️ Categories</a>
      <a class="ql-btn" href="pages/suppliers.php">🏭 Suppliers</a>
      <a class="ql-btn" href="pages/departments.php">🏢 Departments</a>
      <a class="ql-btn" href="pages/purchase.php">🛒 Purchase Order</a>
      <a class="ql-btn" href="pages/issue.php">📤 Issue Stock</a>
      <a class="ql-btn" href="pages/stock.php">📋 Stock Report</a>
      <a class="ql-btn" href="pages/reports.php">🖨️ Reports</a>
    </div>

  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
