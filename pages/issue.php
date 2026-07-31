<?php
// ── pages/issue.php ─────────────────────────────────────────────────────────
require_once '../config/db.php';

$pageTitle = 'Issue to Department';
$assetRoot = '..';
require_once '../includes/header.php';
?>

<div class="mdi-window">
  <div class="mdi-title">
    <span>📤 Issue to Department</span>
    <div class="win-controls">
      <span class="win-btn">─</span><span class="win-btn">□</span><span class="win-btn">✕</span>
    </div>
  </div>
  <div class="mdi-body">
    <div class="section-title">Issue to Department</div>
    <p style="color:#808080;font-style:italic;">
      This module is under construction. Table structure will be added as provided.
    </p>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
