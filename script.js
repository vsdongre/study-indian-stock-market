// ── VB Classic menu behaviour ──

(function () {
  "use strict";

  var menuItems = document.querySelectorAll(".menu-item");
  var overlay   = document.getElementById("menu-overlay");

  function closeAll() {
    menuItems.forEach(function (mi) { mi.classList.remove("open"); });
    if (overlay) overlay.style.display = "none";
  }

  menuItems.forEach(function (mi) {
    mi.querySelector(".menu-label").addEventListener("click", function (e) {
      e.stopPropagation();
      var wasOpen = mi.classList.contains("open");
      closeAll();
      if (!wasOpen) {
        mi.classList.add("open");
        if (overlay) overlay.style.display = "block";
      }
    });
  });

  if (overlay) {
    overlay.addEventListener("click", closeAll);
  }

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") closeAll();
  });

  // ── Live clock in status bar ──
  var clockEl = document.getElementById("status-clock");
  function tick() {
    if (!clockEl) return;
    var now = new Date();
    var h = String(now.getHours()).padStart(2, "0");
    var m = String(now.getMinutes()).padStart(2, "0");
    var s = String(now.getSeconds()).padStart(2, "0");
    clockEl.textContent = h + ":" + m + ":" + s;
  }
  tick();
  setInterval(tick, 1000);

  // ── MDI window drag ──
  var mdiWin = document.querySelector(".mdi-window");
  var mdiTitle = mdiWin && mdiWin.querySelector(".mdi-title");
  if (mdiWin && mdiTitle) {
    var dragging = false, ox = 0, oy = 0;
    mdiTitle.addEventListener("mousedown", function (e) {
      if (e.target.classList.contains("win-btn")) return;
      dragging = true;
      ox = e.clientX - mdiWin.offsetLeft;
      oy = e.clientY - mdiWin.offsetTop;
      e.preventDefault();
    });
    document.addEventListener("mousemove", function (e) {
      if (!dragging) return;
      mdiWin.style.left = (e.clientX - ox) + "px";
      mdiWin.style.top  = (e.clientY - oy) + "px";
    });
    document.addEventListener("mouseup", function () { dragging = false; });
  }
}());
