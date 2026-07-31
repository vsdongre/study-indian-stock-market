<?php
// ── pages/items.php ─────────────────────────────────────────────────────────
require_once '../config/db.php';

$pageTitle = 'Items / Products';
$assetRoot = '..';
require_once '../includes/header.php';
?>

<div class="mdi-window">
  <div class="mdi-title">
    <span>📦 Items / Products</span>
    <div class="win-controls">
      <span class="win-btn">─</span><span class="win-btn">□</span><span class="win-btn">✕</span>
    </div>
  </div>
  <div class="mdi-body">
    <div class="section-title">Items / Products</div>
    <p style="color:#808080;font-style:italic;">
      This module is under construction. Table structure will be added as provided.
    </p>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
