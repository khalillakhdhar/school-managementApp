<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
/* ═══ ELITECAMPUS — Shared Portal Theme (Parent / Staff) ═══ */
*{font-family:'Inter',-apple-system,system-ui,sans-serif!important}

/* Shell */
html:not(.dark) body,html:not(.dark) .fi-main-ctn{background:#eef2f7!important}
html:not(.dark) .fi-topbar{background:#fff!important;border-bottom:1px solid #dde3ea!important}

/* Page layout */
html:not(.dark) .fi-page,.fi-page-ctn{gap:0!important}
html:not(.dark) .fi-page-content{padding:8px 20px 28px!important;display:flex!important;flex-direction:column!important;gap:18px!important}
html:not(.dark) .fi-page-header{background:transparent!important;border-bottom:none!important;padding:20px 20px 4px!important;margin:0!important}
html:not(.dark) .fi-page-header-heading{font-size:26px!important;font-weight:800!important;color:#0f172a!important;letter-spacing:-.6px!important}
html:not(.dark) .fi-header-subheading{font-size:14px!important;color:#64748b!important;margin-top:4px!important}

/* Cards / sections */
html:not(.dark) .fi-section{background:#fff!important;border:1px solid #e5e9f0!important;border-radius:14px!important;box-shadow:0 1px 3px rgba(16,24,40,.05)!important}
html:not(.dark) .fi-section-header{padding:14px 18px!important;border-bottom:1px solid #f3f4f6!important}
html:not(.dark) .fi-section-header-heading{font-size:14px!important;font-weight:700!important;color:#0f172a!important}
html:not(.dark) .fi-section-content,html:not(.dark) .fi-section-content-ctn{padding:16px 18px!important}

/* KPI cards */
html:not(.dark) .fi-wi-stats-overview{gap:16px!important}
html:not(.dark) .fi-wi-stats-overview-stat{border-radius:14px!important;border:1px solid #e5e9f0!important;box-shadow:0 1px 3px rgba(16,24,40,.05)!important;padding:18px 20px!important;background:#fff!important}
html:not(.dark) .fi-wi-stats-overview-stat-label{font-size:12.5px!important;font-weight:600!important;text-transform:none!important;color:#64748b!important}
html:not(.dark) .fi-wi-stats-overview-stat-value{font-size:28px!important;font-weight:800!important;color:#0f172a!important;letter-spacing:-.6px!important;margin-top:6px!important}
html:not(.dark) .fi-wi-stats-overview-stat-description{font-size:12.5px!important;color:#64748b!important;margin-top:6px!important}

/* Tables */
html:not(.dark) .fi-ta-ctn{background:#fff!important;border:1px solid #e5e9f0!important;border-radius:14px!important;box-shadow:0 1px 3px rgba(16,24,40,.05)!important;overflow:hidden!important}
html:not(.dark) .fi-ta-header-row>th{background:#fafbfc!important;font-size:11px!important;font-weight:600!important;text-transform:uppercase!important;letter-spacing:.5px!important;color:#64748b!important;padding:13px 18px!important;border-bottom:1px solid #eaeef3!important}
html:not(.dark) .fi-ta-row>td{padding:14px 18px!important;border-bottom:1px solid #f1f4f8!important;font-size:13.5px!important;color:#1e293b!important}
html:not(.dark) .fi-ta-row:hover>td{background:#f8fafc!important}

/* Buttons / badges */
.fi-btn{border-radius:9px!important;font-weight:600!important;font-size:13px!important}
.fi-btn-color-primary{background:#2563eb!important;border:none!important;color:#fff!important}
.fi-btn-color-primary:hover{background:#1d4ed8!important}
html:not(.dark) .fi-badge{border-radius:7px!important;font-weight:600!important;font-size:11.5px!important;padding:3px 10px!important}

/* ═══ Sidebar — dark navy ═══ */
:root{--sidebar-width:18rem!important}
.fi-sidebar,html.dark .fi-sidebar{background:#0f172a!important;border-inline-end:1px solid rgba(255,255,255,.05)!important}
.fi-sidebar-header,.fi-sidebar-nav,.fi-sidebar-footer,.fi-sidebar-group,.fi-sidebar-group-btn,.fi-sidebar-group-items,.fi-sidebar-item,.fi-sidebar-item-btn,.fi-sidebar-nav-groups{background:transparent!important}
.fi-sidebar-header{border-bottom:1px solid rgba(255,255,255,.07)!important}
.fi-sidebar-footer{border-top:1px solid rgba(255,255,255,.06)!important}
.fi-sidebar-nav{padding:10px 14px 20px!important;gap:0!important;scrollbar-width:thin!important;scrollbar-color:#334155 #0f172a!important}
.fi-sidebar-nav::-webkit-scrollbar{width:6px!important}
.fi-sidebar-nav::-webkit-scrollbar-track{background:#0f172a!important}
.fi-sidebar-nav::-webkit-scrollbar-thumb{background:#334155!important;border-radius:3px!important}
.fi-main{margin-inline-start:0!important}
.fi-sidebar-nav-groups{gap:6px!important}
.fi-sidebar-group-label{color:#94a3b8!important;font-size:11px!important;font-weight:700!important;letter-spacing:.4px!important;text-transform:uppercase!important}
.fi-sidebar-item-grouped-border,.fi-sidebar-item-grouped-border-part,.fi-sidebar-item-grouped-border-part-not-first,.fi-sidebar-item-grouped-border-part-not-last{display:none!important}
.fi-sidebar-item-btn{display:flex!important;align-items:center!important;gap:13px!important;width:100%!important;padding:11px 14px!important;border-radius:10px!important;color:#cbd5e1!important;background:transparent!important;transition:background .12s,color .12s!important}
.fi-sidebar-item-has-url .fi-sidebar-item-btn:hover{background:#1e293b!important;color:#fff!important}
.fi-sidebar-item-label{flex:1!important;min-width:0!important;font-size:15px!important;font-weight:500!important;color:inherit!important;text-transform:none!important;letter-spacing:0!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important}
.fi-sidebar-item-btn .fi-icon{color:inherit!important;flex-shrink:0!important;width:21px!important;height:21px!important;opacity:.85!important}
.fi-sidebar-item.fi-active .fi-sidebar-item-btn{background:#2563eb!important;color:#fff!important;font-weight:600!important;box-shadow:0 4px 12px rgba(37,99,235,.4)!important}
.fi-sidebar-item.fi-active .fi-sidebar-item-btn .fi-icon{color:#fff!important;opacity:1!important}
.fi-sidebar-item.fi-active .fi-sidebar-item-label{color:#fff!important;font-weight:600!important}
.fi-logo span,.fi-brand-name{color:#e2e8f0!important}
.fi-sidebar-footer a,.fi-sidebar-footer button{color:#4a6480!important;font-size:12.5px!important}
.fi-sidebar-footer a:hover,.fi-sidebar-footer button:hover{color:#8dafc8!important}

/* Language switcher: cleaner topbar control without separator line */
.language-switch-trigger{border:none!important;box-shadow:none!important;outline:none!important;--tw-ring-shadow:none!important;--tw-ring-offset-shadow:none!important;background:#f8fafc!important;color:#334155!important}
.language-switch-trigger:hover{background:#eef2f7!important;color:#0f172a!important}
.fi-topbar .fi-dropdown.fi-user-menu{border-inline-start:none!important;margin-inline-start:0!important;padding-inline-start:0!important}
.fi-topbar .fi-dropdown.fi-user-menu::before,.fi-topbar .fi-dropdown.fi-user-menu::after{display:none!important;content:none!important}
</style>
<script>
window.addEventListener("pageshow", function (e) {
    if (e.persisted || (window.performance && performance.getEntriesByType("navigation")[0] && performance.getEntriesByType("navigation")[0].type === "back_forward")) {
        location.reload();
    }
});
</script>
