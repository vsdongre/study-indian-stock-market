<?php
// ── pages/suppliers.php ─────────────────────────────────────────────────────────
require_once '../config/db.php';

$pageTitle = 'Suppliers';
$assetRoot = '..';
require_once '../includes/header.php';
?>

<div class="mdi-window">
  <div class="mdi-title">
    <span>🏭 Suppliers</span>
    <div class="win-controls">
      <span class="win-btn">─</span><span class="win-btn">□</span><span class="win-btn">✕</span>
    </div>
  </div>
  <div class="mdi-body">
    <div class="section-title">Suppliers</div>
    <p style="color:#808080;font-style:italic;">
      This module is under construction. Table structure will be added as provided.
    </p>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
