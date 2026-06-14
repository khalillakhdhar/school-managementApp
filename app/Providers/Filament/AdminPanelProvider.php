<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('EliteCampus')
            ->brandLogo(asset('images/logo-elitecampus.svg'))
            ->darkModeBrandLogo(asset('images/logo-elitecampus-white.svg'))
            ->brandLogoHeight('2rem')
            ->favicon(asset('favicon.svg'))
            ->colors([
                'primary' => Color::hex('#1d4ed8'),
                'gray'    => Color::Slate,
                'info'    => Color::Sky,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'danger'  => Color::Rose,
            ])
            ->navigationGroups([
                NavigationGroup::make('Académique')
                    ->icon('heroicon-o-academic-cap'),
                NavigationGroup::make('RH')
                    ->icon('heroicon-o-users'),
                NavigationGroup::make('Finances')
                    ->icon('heroicon-o-banknotes'),
                NavigationGroup::make('Communication')
                    ->icon('heroicon-o-chat-bubble-left-right'),
                NavigationGroup::make('Paramètres')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsed(),
            ])
            ->globalSearch(true)
            ->sidebarCollapsibleOnDesktop()
            ->spa()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([Dashboard::class])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([])

            ->renderHook(
                'panels::sidebar.nav.start',
                fn () => view('filament.sidebar-school-info')
            )

            ->renderHook(
                'panels::head.end',
                fn () => '<link rel="preconnect" href="https://fonts.googleapis.com">'
                       . '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>'
                       . '<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">'
                       . '<style>
/* ═══════════════════════════════════════════════════════
   ELITECAMPUS — Professional ERP Design System v4
   ═══════════════════════════════════════════════════════ */

*{font-family:\'Inter\',-apple-system,system-ui,sans-serif!important}

/* ── SHELL ───────────────────────────────────────────── */
html:not(.dark) body,
html:not(.dark) .fi-main-ctn,
html:not(.dark) .fi-simple-main-ctn{background:#eef2f7!important}

html:not(.dark) .fi-topbar,html:not(.dark) nav.fi-topbar{
  background:#ffffff!important;height:52px!important;
  border-bottom:1px solid #dde3ea!important;
  box-shadow:0 1px 0 rgba(0,0,0,.04)!important;
}

/* ── PAGE LAYOUT — kill all extra whitespace ─────────── */
html:not(.dark) .fi-page,html:not(.dark) .fi-page-ctn{gap:0!important}
html:not(.dark) .fi-page-content{
  padding:12px 16px 20px!important;
  display:flex!important;flex-direction:column!important;gap:12px!important;
}

/* ── PAGE HEADER ─────────────────────────────────────── */
html:not(.dark) .fi-page-header{
  background:#ffffff!important;
  border-bottom:1px solid #dde3ea!important;
  padding:10px 18px!important;margin:0!important;
}
html:not(.dark) .fi-page-header-heading{
  font-size:16px!important;font-weight:700!important;
  color:#111827!important;letter-spacing:-.2px!important;
}
html:not(.dark) .fi-breadcrumbs-item-label{color:#9ca3af!important;font-size:12px!important}
html:not(.dark) .fi-breadcrumbs-item:last-child .fi-breadcrumbs-item-label{
  color:#111827!important;font-weight:600!important;
}

/* ── CARDS / SECTIONS ───────────────────────────────── */
html:not(.dark) .fi-section,html:not(.dark) .fi-card{
  background:#ffffff!important;border:1px solid #dde3ea!important;
  border-radius:10px!important;box-shadow:0 1px 3px rgba(0,0,0,.05)!important;
}
html:not(.dark) .fi-section-header{
  padding:11px 16px!important;border-bottom:1px solid #f3f4f6!important;
}
html:not(.dark) .fi-section-header-heading{
  font-size:13px!important;font-weight:700!important;color:#111827!important;
}
html:not(.dark) .fi-section-content,html:not(.dark) .fi-section-content-ctn{
  padding:14px 16px!important;
}

/* ── STATS WIDGETS ──────────────────────────────────── */
html:not(.dark) .fi-wi-stats-overview{gap:10px!important}
html:not(.dark) .fi-wi-stats-overview-stat{
  border-radius:10px!important;border:1px solid #dde3ea!important;
  box-shadow:0 1px 3px rgba(0,0,0,.05)!important;
  padding:14px 16px!important;background:#ffffff!important;
}
html:not(.dark) .fi-wi-stats-overview-stat-label{
  font-size:10px!important;font-weight:700!important;
  text-transform:uppercase!important;letter-spacing:.9px!important;color:#6b7280!important;
}
html:not(.dark) .fi-wi-stats-overview-stat-value{
  font-size:22px!important;font-weight:800!important;
  color:#111827!important;letter-spacing:-.4px!important;margin-top:2px!important;
}
html:not(.dark) .fi-wi-stats-overview-stat-description{
  font-size:11.5px!important;color:#6b7280!important;margin-top:3px!important;
}
.fi-wi-stats-overview{gap:10px!important}
.fi-wi-stats-overview-stat-description{font-size:11.5px!important}

/* ── WIDGET GRID ─────────────────────────────────────── */
.fi-dashboard-widgets,.fi-page-widgets,.fi-wi-ctn{gap:12px!important}
.fi-wi{margin:0!important}

/* ── TABLES — Professional dense design ─────────────── */
html:not(.dark) .fi-ta-ctn{
  background:#ffffff!important;border:1px solid #dde3ea!important;
  border-radius:10px!important;box-shadow:0 1px 3px rgba(0,0,0,.05)!important;
  overflow:hidden!important;
}
/* Header */
html:not(.dark) .fi-ta-header-row>th,html:not(.dark) .fi-ta-header-row th{
  background:#f7f9fc!important;
  font-size:10px!important;font-weight:700!important;
  text-transform:uppercase!important;letter-spacing:.7px!important;
  color:#6b7280!important;padding:9px 14px!important;
  border-bottom:2px solid #e5eaf0!important;white-space:nowrap!important;
}
/* Rows */
html:not(.dark) .fi-ta-row>td,html:not(.dark) .fi-ta-row td{
  padding:9px 14px!important;border-bottom:1px solid #f3f4f6!important;
  font-size:13px!important;color:#1f2937!important;background:transparent!important;
  vertical-align:middle!important;
}
html:not(.dark) .fi-ta-row:last-child>td{border-bottom:none!important}
html:not(.dark) .fi-ta-row:hover>td{background:#f7f9fc!important}
/* Toolbar */
html:not(.dark) .fi-ta-header{
  padding:10px 14px!important;border-bottom:1px solid #f3f4f6!important;
  background:#ffffff!important;
}
html:not(.dark) .fi-ta-empty-state{padding:40px 20px!important}
html:not(.dark) .fi-ta-empty-state-heading{
  font-size:14px!important;font-weight:700!important;color:#374151!important;margin-top:10px!important;
}
html:not(.dark) .fi-ta-empty-state-description{font-size:12.5px!important;color:#9ca3af!important}

/* ── FORMS ──────────────────────────────────────────── */
html:not(.dark) .fi-input,html:not(.dark) .fi-select,html:not(.dark) .fi-textarea{
  border-radius:7px!important;border-color:#dde3ea!important;
  font-size:13px!important;background:#ffffff!important;color:#111827!important;
}
html:not(.dark) .fi-fo-field-wrp-label label{
  font-size:12px!important;font-weight:600!important;color:#374151!important;
}
html:not(.dark) .fi-fo-helper-text{font-size:11px!important;color:#9ca3af!important}

/* ── DROPDOWN / MODAL ───────────────────────────────── */
html:not(.dark) .fi-dropdown-list,html:not(.dark) .fi-dropdown-panel{
  border-radius:9px!important;background:#ffffff!important;
  border:1px solid #dde3ea!important;padding:4px!important;
  box-shadow:0 8px 24px rgba(0,0,0,.1)!important;
}
html:not(.dark) .fi-modal-window{
  background:#ffffff!important;border:1px solid #dde3ea!important;border-radius:12px!important;
}
html:not(.dark) .fi-modal-header{border-bottom:1px solid #f3f4f6!important;padding:14px 18px!important}
html:not(.dark) .fi-modal-header-heading{
  font-size:14px!important;font-weight:700!important;color:#111827!important;
}

/* ── GLOBAL ELEMENTS ─────────────────────────────────── */
.fi-btn{border-radius:7px!important;font-weight:600!important;font-size:12.5px!important}
.fi-btn-color-primary{
  background:linear-gradient(135deg,#2563eb 0%,#1d4ed8 100%)!important;
  border:none!important;color:#fff!important;
}
.fi-btn-color-primary:hover{background:linear-gradient(135deg,#1d4ed8,#1e40af)!important}
.fi-badge{border-radius:4px!important;font-weight:600!important;font-size:10.5px!important}
.fi-no{border-radius:10px!important;box-shadow:0 6px 24px rgba(0,0,0,.12)!important}
.fi-dropdown-item{font-size:12.5px!important;border-radius:5px!important;margin:1px!important}
.fi-pagination{font-size:12.5px!important}

/* ════════════════════════════════════════════════════════
   SIDEBAR — Professional dark navy, always
   ════════════════════════════════════════════════════════ */
.fi-sidebar,html.dark .fi-sidebar{background:#0f172a!important;border-right:1px solid rgba(255,255,255,.05)!important}
.fi-sidebar nav,.fi-sidebar header,.fi-sidebar footer,
.fi-sidebar ul,.fi-sidebar li,
.fi-sidebar [class*="fi-sidebar-"]{background:#0f172a!important;border-color:transparent!important}
html.dark .fi-sidebar nav,html.dark .fi-sidebar header,html.dark .fi-sidebar footer,
html.dark .fi-sidebar ul,html.dark .fi-sidebar li,
html.dark .fi-sidebar [class*="fi-sidebar-"]{background:#0f172a!important}

.fi-sidebar header{border-bottom:1px solid rgba(255,255,255,.06)!important;padding:10px 12px!important}
.fi-sidebar footer{border-top:1px solid rgba(255,255,255,.06)!important;padding:8px 10px!important}

/* Kill timeline pseudo-elements */
.fi-sidebar *::before,.fi-sidebar *::after{
  display:none!important;content:\'\'!important;border:none!important;
  background:none!important;width:0!important;height:0!important;position:static!important;
}
.fi-sidebar ul,.fi-sidebar li,.fi-sidebar nav,
.fi-sidebar [class*="fi-sidebar-group"],
.fi-sidebar [class*="fi-sidebar-item"],
.fi-sidebar [class*="fi-nav-group"],
.fi-sidebar [class*="fi-sidebar-nav"]{
  border-left:0!important;padding-left:0!important;margin-left:0!important;
  list-style:none!important;
}

/* Nav groups container */
.fi-sidebar [class*="fi-sidebar-nav-groups"],
.fi-sidebar [class*="fi-nav-groups"]{padding:4px 8px 14px!important;gap:0!important}
.fi-sidebar [class*="fi-sidebar-group"]{padding:0!important;margin:0!important}
.fi-sidebar [class*="fi-sidebar-group"]+[class*="fi-sidebar-group"]{
  border-top:1px solid rgba(255,255,255,.04)!important;
  margin-top:4px!important;padding-top:4px!important;
}

/* Group labels */
.fi-sidebar [class*="fi-sidebar-group-label"],
.fi-sidebar [class*="fi-sidebar-group"] span[class*="label"]{
  color:#46607a!important;font-size:9px!important;font-weight:700!important;
  letter-spacing:1.8px!important;text-transform:uppercase!important;
  padding:8px 8px 2px!important;display:block!important;
}
.fi-sidebar [class*="fi-sidebar-group"]>button,
.fi-sidebar [class*="fi-sidebar-group-btn"]{
  background:transparent!important;border:none!important;
  padding:0!important;width:100%!important;cursor:pointer!important;
}
.fi-sidebar [class*="fi-sidebar-group"]>button:hover,
.fi-sidebar [class*="fi-sidebar-group-btn"]:hover{background:transparent!important}
.fi-sidebar [class*="fi-sidebar-group"]>button svg{color:#2d3e53!important}

/* Nav items */
.fi-sidebar a,.fi-sidebar [class*="fi-sidebar-item-button"]{
  display:flex!important;align-items:center!important;gap:7px!important;
  width:100%!important;padding:6px 8px!important;border-radius:6px!important;
  margin:1px 0!important;color:#8fa8c0!important;
  font-size:12.5px!important;font-weight:500!important;
  text-decoration:none!important;background:transparent!important;
  border:none!important;cursor:pointer!important;
  transition:background .1s,color .1s!important;
  overflow:hidden!important;min-width:0!important;
}
.fi-sidebar a:hover,.fi-sidebar [class*="fi-sidebar-item-button"]:hover{
  background:rgba(255,255,255,.06)!important;color:#cbd5e1!important;
}
/* Text spans inside nav items — prevent overflow breaking layout */
.fi-sidebar a span,.fi-sidebar [class*="fi-sidebar-item-button"] span{
  color:inherit!important;white-space:nowrap!important;
  overflow:hidden!important;text-overflow:ellipsis!important;
  flex:1!important;min-width:0!important;
}

/* Active state */
.fi-sidebar [aria-current="page"],
.fi-sidebar [class*="fi-sidebar-item-active"]>a,
.fi-sidebar [class*="fi-sidebar-item-active"] [class*="fi-sidebar-item-button"],
.fi-sidebar [class*="fi-sidebar-item-button-active"]{
  background:#1d4ed8!important;color:#ffffff!important;font-weight:600!important;
  box-shadow:0 1px 5px rgba(29,78,216,.4)!important;
}
.fi-sidebar [aria-current="page"] span,
.fi-sidebar [class*="fi-sidebar-item-active"]>a span,
.fi-sidebar [class*="fi-sidebar-item-active"] [class*="fi-sidebar-item-button"] span{
  color:#ffffff!important;
}

/* Icons */
.fi-sidebar a svg,.fi-sidebar [class*="fi-sidebar-item-button"] svg{
  color:inherit!important;flex-shrink:0!important;width:15px!important;height:15px!important;opacity:.75!important;
}
.fi-sidebar [aria-current="page"] svg,
.fi-sidebar [class*="fi-sidebar-item-active"]>a svg{opacity:1!important;color:#ffffff!important}

/* Navigation badge — compact pill, never wraps */
.fi-sidebar [class*="fi-sidebar-item-badge"],
.fi-sidebar [class*="fi-nav-item-badge"]{
  margin-left:auto!important;flex-shrink:0!important;
  background:rgba(239,68,68,.18)!important;color:#fca5a5!important;
  font-size:9px!important;font-weight:700!important;
  padding:1px 6px!important;border-radius:8px!important;
  line-height:1.6!important;white-space:nowrap!important;
  min-width:18px!important;text-align:center!important;
}
.fi-sidebar [aria-current="page"] [class*="fi-sidebar-item-badge"],
.fi-sidebar [class*="fi-sidebar-item-active"] [class*="fi-sidebar-item-badge"]{
  background:rgba(255,255,255,.2)!important;color:#ffffff!important;
}

/* Brand name */
.fi-sidebar [class*="fi-brand-name"],.fi-sidebar header a span{color:#e2e8f0!important}

/* User menu footer */
.fi-sidebar footer a,.fi-sidebar footer button{color:#46607a!important;font-size:12px!important}
.fi-sidebar footer a:hover,.fi-sidebar footer button:hover{color:#7f96ae!important}

/* Topbar toggle */
.fi-sidebar-close-overlay-btn,.fi-topbar-open-sidebar-btn{color:#64748b!important}
</style>'
            )

            ->renderHook(
                'panels::topbar.end',
                fn () => view('filament.notification-bell')
            )
            ->renderHook(
                'panels::auth.login.form.before',
                fn () => view('filament.auth.login-branding')
            )
            ->renderHook(
                'panels::body.end',
                fn () => view('filament.footer')
            )
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
