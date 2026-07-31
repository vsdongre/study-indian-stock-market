<?php
// ── includes/footer.php ───────────────────────────────────────────────────────
// Closes <main class="workspace"> opened in header.php.
// ─────────────────────────────────────────────────────────────────────────────
if (!isset($assetRoot)) { $assetRoot = '.'; }
?>
</main><!-- /.workspace -->

<!-- ═══ STATUS BAR ═══════════════════════════════════════════════════════════ -->
<footer class="status-bar" role="contentinfo">
  <span class="status-panel">Ready</span>
  <span class="status-panel">Stationery Management System</span>
  <span class="status-panel"><?= htmlspecialchars($pageTitle ?? '', ENT_QUOTES, 'UTF-8') ?></span>
  <span class="status-panel" id="status-clock">--:--:--</span>
</footer>

<script src="<?= $assetRoot ?>/assets/js/script.js"></script>
</body>
</html>
