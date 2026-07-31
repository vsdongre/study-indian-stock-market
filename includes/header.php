<?php
// ── includes/header.php ───────────────────────────────────────────────────────
// Usage:  $pageTitle = 'My Page';  require 'includes/header.php';
// Provides: <html> open tag, <head>, title bar, VB menu bar, toolbar.
// ─────────────────────────────────────────────────────────────────────────────
if (!isset($pageTitle)) {
    $pageTitle = 'Stationery Management';
}
// Resolve asset root relative to repository root.
$assetRoot = rtrim(
    str_repeat('../', substr_count(
        str_replace('\\', '/', $_SERVER['SCRIPT_NAME']),
        '/'
    ) - 2),
    '/'
);
if ($assetRoot === '') { $assetRoot = '.'; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> – Stationery Management</title>
  <link rel="stylesheet" href="<?= $assetRoot ?>/assets/css/style.css" />
</head>
<body>

<!-- ═══ TITLE BAR ════════════════════════════════════════════════════════════ -->
<div class="title-bar">
  <span class="title-text">🗂️ Stationery Management System
    <?php if (!empty($pageTitle)): ?>
      — <?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>
    <?php endif; ?>
  </span>
  <div class="win-controls">
    <span class="win-btn" title="Minimize">─</span>
    <span class="win-btn" title="Maximize">□</span>
    <span class="win-btn" title="Close">✕</span>
  </div>
</div>

<!-- ═══ VB CLASSIC MENU BAR ══════════════════════════════════════════════════ -->
<div id="menu-overlay"></div>

<nav class="menu-bar" role="menubar">

  <!-- File -->
  <div class="menu-item" role="none">
    <span class="menu-label" role="menuitem" tabindex="0">File</span>
    <ul class="dropdown" role="menu">
      <li><a href="<?= $assetRoot ?>/index.php" role="menuitem">🏠 Home<span class="shortcut">Alt+H</span></a></li>
      <hr />
      <li><a href="<?= $assetRoot ?>/pages/reports.php" role="menuitem">🖨️ Print Report<span class="shortcut">Ctrl+P</span></a></li>
      <hr />
      <li><a href="#" role="menuitem" onclick="window.close();return false;">❌ Exit<span class="shortcut">Alt+F4</span></a></li>
    </ul>
  </div>

  <!-- Masters -->
  <div class="menu-item" role="none">
    <span class="menu-label" role="menuitem" tabindex="0">Masters</span>
    <ul class="dropdown" role="menu">
      <li><a href="<?= $assetRoot ?>/pages/items.php"       role="menuitem">📦 Items / Products</a></li>
      <li><a href="<?= $assetRoot ?>/pages/categories.php"  role="menuitem">🗂️ Categories</a></li>
      <li><a href="<?= $assetRoot ?>/pages/suppliers.php"   role="menuitem">🏭 Suppliers</a></li>
      <li><a href="<?= $assetRoot ?>/pages/departments.php" role="menuitem">🏢 Departments</a></li>
      <li><a href="<?= $assetRoot ?>/pages/employees.php"   role="menuitem">👤 Employees</a></li>
    </ul>
  </div>

  <!-- Transactions -->
  <div class="menu-item" role="none">
    <span class="menu-label" role="menuitem" tabindex="0">Transactions</span>
    <ul class="dropdown" role="menu">
      <li><a href="<?= $assetRoot ?>/pages/purchase.php"  role="menuitem">🛒 Purchase Order</a></li>
      <li><a href="<?= $assetRoot ?>/pages/receive.php"   role="menuitem">📥 Goods Receipt</a></li>
      <li><a href="<?= $assetRoot ?>/pages/issue.php"     role="menuitem">📤 Issue to Department</a></li>
      <li><a href="<?= $assetRoot ?>/pages/return.php"    role="menuitem">🔄 Return / Rejection</a></li>
      <hr />
      <li><a href="<?= $assetRoot ?>/pages/stock.php"     role="menuitem">📋 Current Stock</a></li>
    </ul>
  </div>

  <!-- Reports -->
  <div class="menu-item" role="none">
    <span class="menu-label" role="menuitem" tabindex="0">Reports</span>
    <ul class="dropdown" role="menu">
      <li><a href="<?= $assetRoot ?>/pages/reports.php?type=stock"     role="menuitem">📊 Stock Report</a></li>
      <li><a href="<?= $assetRoot ?>/pages/reports.php?type=purchase"  role="menuitem">📄 Purchase Report</a></li>
      <li><a href="<?= $assetRoot ?>/pages/reports.php?type=issue"     role="menuitem">📤 Issue Report</a></li>
      <li><a href="<?= $assetRoot ?>/pages/reports.php?type=supplier"  role="menuitem">🏭 Supplier Report</a></li>
    </ul>
  </div>

  <!-- Settings -->
  <div class="menu-item" role="none">
    <span class="menu-label" role="menuitem" tabindex="0">Settings</span>
    <ul class="dropdown" role="menu">
      <li><a href="<?= $assetRoot ?>/pages/users.php"    role="menuitem">👥 User Management</a></li>
      <li><a href="<?= $assetRoot ?>/pages/settings.php" role="menuitem">⚙️ System Settings</a></li>
    </ul>
  </div>

  <!-- Help -->
  <div class="menu-item" role="none">
    <span class="menu-label" role="menuitem" tabindex="0">Help</span>
    <ul class="dropdown" role="menu">
      <li><a href="<?= $assetRoot ?>/pages/help.php"  role="menuitem">📖 Help<span class="shortcut">F1</span></a></li>
      <li><a href="<?= $assetRoot ?>/pages/about.php" role="menuitem">ℹ️ About</a></li>
    </ul>
  </div>

</nav>

<!-- ═══ TOOLBAR ══════════════════════════════════════════════════════════════ -->
<div class="toolbar" role="toolbar" aria-label="Quick actions">
  <a class="tb-btn" href="<?= $assetRoot ?>/index.php"              title="Home">🏠</a>
  <span class="tb-sep"></span>
  <a class="tb-btn" href="<?= $assetRoot ?>/pages/items.php"        title="Items">📦</a>
  <a class="tb-btn" href="<?= $assetRoot ?>/pages/suppliers.php"    title="Suppliers">🏭</a>
  <a class="tb-btn" href="<?= $assetRoot ?>/pages/departments.php"  title="Departments">🏢</a>
  <span class="tb-sep"></span>
  <a class="tb-btn" href="<?= $assetRoot ?>/pages/purchase.php"     title="Purchase Order">🛒</a>
  <a class="tb-btn" href="<?= $assetRoot ?>/pages/issue.php"        title="Issue Stock">📤</a>
  <a class="tb-btn" href="<?= $assetRoot ?>/pages/stock.php"        title="Current Stock">📋</a>
  <span class="tb-sep"></span>
  <a class="tb-btn" href="<?= $assetRoot ?>/pages/reports.php"      title="Reports">🖨️</a>
  <a class="tb-btn" href="<?= $assetRoot ?>/pages/settings.php"     title="Settings">⚙️</a>
</div>

<!-- ═══ PAGE CONTENT STARTS ══════════════════════════════════════════════════ -->
<main class="workspace" role="main">
