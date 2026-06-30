<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
    :root {
        --sidebar-width: 18rem !important;
    }

    * {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif !important;
    }

    html:not(.dark) body,
    html:not(.dark) .fi-main-ctn {
        background: #eef2f7 !important;
    }

    html:not(.dark) .fi-topbar {
        background: #ffffff !important;
        border-bottom: 1px solid #dde3ea !important;
        box-shadow: 0 1px 0 rgba(15, 23, 42, .04) !important;
    }

    html:not(.dark) .fi-page-content {
        padding: 8px 20px 28px !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 18px !important;
    }

    html:not(.dark) .fi-page-header {
        background: transparent !important;
        border-bottom: none !important;
        padding: 20px 20px 4px !important;
        margin: 0 !important;
    }

    html:not(.dark) .fi-page-header-heading {
        color: #0f172a !important;
        font-size: 26px !important;
        font-weight: 800 !important;
        letter-spacing: 0 !important;
    }

    html:not(.dark) .fi-section,
    html:not(.dark) .fi-ta-ctn {
        background: #ffffff !important;
        border: 1px solid #dde3ea !important;
        border-radius: 10px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .05) !important;
        overflow: hidden !important;
    }

    html:not(.dark) .fi-section-header,
    html:not(.dark) .fi-ta-header {
        background: #ffffff !important;
        border-bottom: 1px solid #f1f4f8 !important;
        padding: 12px 16px !important;
    }

    html:not(.dark) .fi-ta-header-row > th,
    html:not(.dark) .fi-ta-header-row th {
        background: #fafbfc !important;
        border-bottom: 1px solid #eaeef3 !important;
        color: #64748b !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        letter-spacing: .04em !important;
        padding: 13px 18px !important;
        text-transform: uppercase !important;
    }

    html:not(.dark) .fi-ta-row > td {
        background: transparent !important;
        border-bottom: 1px solid #f1f4f8 !important;
        color: #1e293b !important;
        font-size: 13.5px !important;
        padding: 14px 18px !important;
        vertical-align: middle !important;
    }

    html:not(.dark) .fi-ta-row:hover > td {
        background: #f8fafc !important;
    }

    .fi-btn {
        border-radius: 9px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
    }

    .fi-btn-color-primary {
        background: #2563eb !important;
        border: none !important;
        box-shadow: 0 1px 2px rgba(37, 99, 235, .3) !important;
        color: #ffffff !important;
    }

    .fi-sidebar,
    html.dark .fi-sidebar {
        background: #0f172a !important;
        border-right: 1px solid rgba(255, 255, 255, .05) !important;
    }

    .fi-sidebar-header,
    .fi-sidebar-nav,
    .fi-sidebar-footer,
    .fi-sidebar-group,
    .fi-sidebar-group-btn,
    .fi-sidebar-group-items,
    .fi-sidebar-item,
    .fi-sidebar-item-btn,
    .fi-sidebar-nav-groups {
        background: transparent !important;
    }

    .fi-sidebar-nav {
        gap: 0 !important;
        padding: 10px 14px 20px !important;
        scrollbar-color: #334155 #0f172a !important;
        scrollbar-width: thin !important;
    }

    .fi-sidebar-group-label {
        color: #94a3b8 !important;
        font-size: 11.5px !important;
        font-weight: 600 !important;
        letter-spacing: .04em !important;
        text-transform: uppercase !important;
    }

    .fi-sidebar-item-btn {
        align-items: center !important;
        border-radius: 10px !important;
        color: #cbd5e1 !important;
        display: flex !important;
        gap: 13px !important;
        justify-content: flex-start !important;
        padding: 11px 14px !important;
        text-decoration: none !important;
        transition: background .12s, color .12s !important;
        width: 100% !important;
    }

    .fi-sidebar-item-has-url .fi-sidebar-item-btn:hover {
        background: #1e293b !important;
        color: #ffffff !important;
    }

    .fi-sidebar-item-label {
        color: inherit !important;
        flex: 1 !important;
        font-size: 15px !important;
        font-weight: 500 !important;
        letter-spacing: 0 !important;
        min-width: 0 !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        white-space: nowrap !important;
    }

    .fi-sidebar-item.fi-active .fi-sidebar-item-btn {
        background: #2563eb !important;
        box-shadow: 0 4px 12px rgba(37, 99, 235, .4) !important;
        color: #ffffff !important;
        font-weight: 600 !important;
    }

    .language-switch-trigger {
        background: #f8fafc !important;
        border: none !important;
        box-shadow: none !important;
        color: #334155 !important;
        outline: none !important;
        --tw-ring-shadow: none !important;
        --tw-ring-offset-shadow: none !important;
    }

    .language-switch-trigger:hover {
        background: #eef2f7 !important;
        color: #0f172a !important;
    }

    .fi-topbar .fi-dropdown.fi-user-menu {
        border-inline-start: none !important;
        margin-inline-start: 0 !important;
        padding-inline-start: 0 !important;
    }

    .fi-topbar .fi-dropdown.fi-user-menu::before,
    .fi-topbar .fi-dropdown.fi-user-menu::after {
        content: none !important;
        display: none !important;
    }
</style>

<script>
    window.addEventListener('pageshow', function (event) {
        const navigation = window.performance && performance.getEntriesByType('navigation')[0];

        if (event.persisted || (navigation && navigation.type === 'back_forward')) {
            location.reload();
        }
    });
</script>
