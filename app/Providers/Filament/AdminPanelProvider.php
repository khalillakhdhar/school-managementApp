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
/* ═══════════════════════════════════════════════════════════════
   ELITECAMPUS — Design System
   STRATEGY:
   - Light mode rules are scoped to html:not(.dark) — this lets
     Filament\'s own dark mode Tailwind variants work untouched.
   - Sidebar rules have NO mode qualifier — sidebar is always
     dark navy regardless of light/dark toggle.
   - Primary button & focus ring apply globally (no color change).
   ═══════════════════════════════════════════════════════════════ */

/* 1. FONT — global */
*{font-family:\'Inter\',-apple-system,system-ui,sans-serif!important}

/* ══════════════════════════════════════════════════════════════
   2. LIGHT MODE — page shell
   Scoped with html:not(.dark) so Filament\'s dark: variants win
   in dark mode without any conflict.
   ══════════════════════════════════════════════════════════════ */

html:not(.dark) .fi-topbar,html:not(.dark) nav.fi-topbar{
  background:#ffffff!important;
  border-bottom:1px solid #e2e8f0!important;
  box-shadow:0 1px 0 rgba(0,0,0,.04)!important;
}
html:not(.dark) .fi-main-ctn,html:not(.dark) .fi-simple-main-ctn{
  background:#f1f5f9!important;
}
html:not(.dark) .fi-page-content{padding:20px 24px!important}

/* Page header */
html:not(.dark) .fi-header,html:not(.dark) header.fi-header,
html:not(.dark) .fi-page-header{
  background:#ffffff!important;
  border-bottom:1px solid #e2e8f0!important;
  padding:14px 24px!important;margin-bottom:0!important;
}
html:not(.dark) .fi-page-header-heading,html:not(.dark) .fi-header h1{
  font-size:18px!important;font-weight:800!important;
  color:#0f172a!important;letter-spacing:-.3px!important;
}
html:not(.dark) .fi-breadcrumbs-item-label{color:#94a3b8!important;font-size:12px!important}
html:not(.dark) .fi-breadcrumbs-item:last-child .fi-breadcrumbs-item-label{
  color:#0f172a!important;font-weight:600!important;
}

/* Sections / Cards */
html:not(.dark) .fi-section,html:not(.dark) .fi-card{
  background:#ffffff!important;border:1px solid #e2e8f0!important;
  border-radius:12px!important;box-shadow:0 1px 3px rgba(0,0,0,.04)!important;
}
html:not(.dark) .fi-section-header{
  padding:14px 18px!important;border-bottom:1px solid #f1f5f9!important;
}
html:not(.dark) .fi-section-header-heading{
  font-size:14px!important;font-weight:700!important;color:#0f172a!important;
}
html:not(.dark) .fi-section-content,html:not(.dark) .fi-section-content-ctn{
  padding:16px 18px!important;
}

/* Tables */
html:not(.dark) .fi-ta-ctn{
  background:#ffffff!important;border:1px solid #e2e8f0!important;
  border-radius:12px!important;box-shadow:0 1px 3px rgba(0,0,0,.04)!important;
  overflow:hidden!important;
}
html:not(.dark) .fi-ta-header-row>th,html:not(.dark) .fi-ta-header-row th{
  background:#f8fafc!important;font-size:10px!important;font-weight:700!important;
  text-transform:uppercase!important;letter-spacing:.7px!important;
  color:#64748b!important;padding:11px 16px!important;
  border-bottom:1px solid #e2e8f0!important;
}
html:not(.dark) .fi-ta-row>td,html:not(.dark) .fi-ta-row td{
  padding:12px 16px!important;border-bottom:1px solid #f8fafc!important;
  font-size:13px!important;color:#334155!important;background:transparent!important;
}
html:not(.dark) .fi-ta-row:last-child>td{border-bottom:none!important}
html:not(.dark) .fi-ta-row:hover>td{background:#f8fafc!important}
html:not(.dark) .fi-ta-header{
  padding:12px 16px!important;border-bottom:1px solid #f1f5f9!important;
  background:#ffffff!important;
}
html:not(.dark) .fi-ta-empty-state{padding:40px 20px!important}
html:not(.dark) .fi-ta-empty-state-heading{
  font-size:15px!important;font-weight:700!important;color:#374151!important;margin-top:12px!important;
}
html:not(.dark) .fi-ta-empty-state-description{font-size:13px!important;color:#94a3b8!important}

/* Forms */
html:not(.dark) .fi-input,html:not(.dark) .fi-select,html:not(.dark) .fi-textarea{
  border-radius:8px!important;border-color:#dde3ea!important;
  font-size:13px!important;background:#ffffff!important;color:#0f172a!important;
}
html:not(.dark) .fi-fo-field-wrp-label label{
  font-size:12px!important;font-weight:600!important;color:#374151!important;
}
html:not(.dark) .fi-fo-helper-text{font-size:11px!important;color:#94a3b8!important}

/* Stats widgets */
html:not(.dark) .fi-wi-stats-overview-stat{
  border-radius:12px!important;border:1px solid #e2e8f0!important;
  box-shadow:0 1px 3px rgba(0,0,0,.04)!important;
  padding:18px 20px!important;background:#ffffff!important;
}
html:not(.dark) .fi-wi-stats-overview-stat-label{
  font-size:11px!important;font-weight:700!important;
  text-transform:uppercase!important;letter-spacing:.6px!important;color:#64748b!important;
}
html:not(.dark) .fi-wi-stats-overview-stat-value{
  font-size:26px!important;font-weight:800!important;
  color:#0f172a!important;letter-spacing:-.5px!important;
}

/* Dropdown */
html:not(.dark) .fi-dropdown-list,html:not(.dark) .fi-dropdown-panel{
  border-radius:10px!important;background:#ffffff!important;
  border:1px solid #e2e8f0!important;padding:4px!important;
  box-shadow:0 8px 24px rgba(0,0,0,.1)!important;
}

/* Modal */
html:not(.dark) .fi-modal-window,html:not(.dark) .fi-modal-content{
  background:#ffffff!important;border:1px solid #e2e8f0!important;border-radius:14px!important;
}
html:not(.dark) .fi-modal-header{border-bottom:1px solid #f1f5f9!important;padding:16px 20px!important}
html:not(.dark) .fi-modal-header-heading{
  font-size:15px!important;font-weight:700!important;color:#0f172a!important;
}

/* ══════════════════════════════════════════════════════════════
   3. GLOBAL — apply in both light and dark mode
   ══════════════════════════════════════════════════════════════ */
.fi-btn{border-radius:8px!important;font-weight:600!important;font-size:13px!important}
.fi-btn-color-primary{
  background:linear-gradient(135deg,#2563eb,#1d4ed8)!important;
  border:none!important;color:#ffffff!important;
  box-shadow:0 1px 3px rgba(29,78,216,.3)!important;
}
.fi-btn-color-primary:hover{
  background:linear-gradient(135deg,#1d4ed8,#1e40af)!important;
}
.fi-input:focus,.fi-select:focus,.fi-textarea:focus{
  border-color:#1d4ed8!important;
  box-shadow:0 0 0 3px rgba(29,78,216,.1)!important;
}
.fi-badge{border-radius:5px!important;font-weight:600!important;font-size:11px!important}
.fi-no{border-radius:12px!important;box-shadow:0 4px 16px rgba(0,0,0,.12)!important}
.fi-pagination{font-size:13px!important}
.fi-pagination-item{border-radius:6px!important}
.fi-dropdown-item{font-size:13px!important;border-radius:6px!important;margin:1px!important}
.fi-wi-stats-overview{gap:14px!important}
.fi-wi-stats-overview-stat-description{font-size:12px!important;font-weight:500!important}

/* ══════════════════════════════════════════════════════════════
   4. SIDEBAR — Always dark navy, both modes
   Uses element-type selectors (ul/li/a/nav) for max reliability
   across Filament class name changes.
   ══════════════════════════════════════════════════════════════ */

/* --- Base background ---------------------------------------- */
.fi-sidebar{
  background:#0f172a!important;
  border-right:1px solid rgba(255,255,255,.06)!important;
}
.fi-sidebar nav,.fi-sidebar header,.fi-sidebar footer,
.fi-sidebar ul,.fi-sidebar li,
.fi-sidebar [class*="fi-sidebar-"]{
  background:#0f172a!important;
  border-color:transparent!important;
}
/* Force dark in dark mode too (override Filament\'s dark: variants) */
html.dark .fi-sidebar,html.dark .fi-sidebar nav,html.dark .fi-sidebar header,
html.dark .fi-sidebar footer,html.dark .fi-sidebar ul,html.dark .fi-sidebar li,
html.dark .fi-sidebar [class*="fi-sidebar-"]{
  background:#0f172a!important;
  border-color:transparent!important;
}

.fi-sidebar header{
  border-bottom:1px solid rgba(255,255,255,.07)!important;
  padding:12px 16px!important;
}
.fi-sidebar footer{
  border-top:1px solid rgba(255,255,255,.07)!important;
  padding:10px 12px!important;
}

/* --- NUCLEAR TIMELINE ELIMINATION --------------------------- */
/* Kill ALL pseudo-elements inside the sidebar */
.fi-sidebar *::before,.fi-sidebar *::after{
  display:none!important;
  content:\'\'!important;
  border:none!important;
  background:none!important;
  width:0!important;height:0!important;
  position:static!important;
}
/* Kill border-left and indent on every structural element */
.fi-sidebar ul,.fi-sidebar li,.fi-sidebar nav,
.fi-sidebar [class*="fi-sidebar-group"],
.fi-sidebar [class*="fi-sidebar-item"],
.fi-sidebar [class*="fi-nav-group"],
.fi-sidebar [class*="fi-sidebar-nav"]{
  border-left:0!important;border-left-width:0!important;
  border-right:0!important;
  padding-left:0!important;margin-left:0!important;
  list-style:none!important;list-style-type:none!important;
}

/* --- Group spacing ------------------------------------------ */
.fi-sidebar [class*="fi-sidebar-nav-groups"],
.fi-sidebar [class*="fi-nav-groups"]{
  padding:4px 8px 16px!important;gap:0!important;
}
.fi-sidebar [class*="fi-sidebar-group"]{padding:0!important;margin:0!important}
.fi-sidebar [class*="fi-sidebar-group"]+[class*="fi-sidebar-group"]{
  border-top:1px solid rgba(255,255,255,.05)!important;
  margin-top:6px!important;padding-top:6px!important;
}

/* --- Group labels ------------------------------------------- */
.fi-sidebar [class*="fi-sidebar-group-label"],
.fi-sidebar [class*="fi-sidebar-group"] span[class*="label"]{
  color:#6b7fa0!important;
  font-size:9.5px!important;font-weight:700!important;
  letter-spacing:1.6px!important;text-transform:uppercase!important;
  padding:10px 10px 5px!important;display:block!important;line-height:1!important;
}
.fi-sidebar [class*="fi-sidebar-group"]>button,
.fi-sidebar [class*="fi-sidebar-group-btn"]{
  background:transparent!important;border:none!important;
  padding:0!important;width:100%!important;cursor:pointer!important;
}
.fi-sidebar [class*="fi-sidebar-group"]>button:hover{background:transparent!important}
.fi-sidebar [class*="fi-sidebar-group"]>button svg{color:#2d3e53!important}

/* --- Nav item links / buttons ---
   Target by HTML element type <a> and <button> for reliability  */
.fi-sidebar a,.fi-sidebar [class*="fi-sidebar-item-button"]{
  display:flex!important;align-items:center!important;gap:8px!important;
  width:100%!important;padding:7px 10px!important;
  border-radius:7px!important;margin:1px 0!important;
  color:#b0c4d8!important;
  font-size:13px!important;font-weight:500!important;line-height:1.35!important;
  text-decoration:none!important;background:transparent!important;
  border:none!important;cursor:pointer!important;
  transition:background .12s,color .12s!important;
}
.fi-sidebar a:hover,.fi-sidebar [class*="fi-sidebar-item-button"]:hover{
  background:rgba(255,255,255,.07)!important;color:#e2e8f0!important;
}
/* Force text inside spans to inherit sidebar colors */
.fi-sidebar a span,.fi-sidebar [class*="fi-sidebar-item-button"] span{
  color:inherit!important;
}

/* --- Active nav item — Solid Elite Blue -------------------- */
.fi-sidebar [aria-current="page"],
.fi-sidebar [class*="fi-sidebar-item-active"]>a,
.fi-sidebar [class*="fi-sidebar-item-active"] [class*="fi-sidebar-item-button"],
.fi-sidebar [class*="fi-sidebar-item-button-active"]{
  background:#1d4ed8!important;
  color:#ffffff!important;
  font-weight:600!important;
  box-shadow:0 1px 6px rgba(29,78,216,.4)!important;
}
.fi-sidebar [aria-current="page"] span,
.fi-sidebar [class*="fi-sidebar-item-active"]>a span,
.fi-sidebar [class*="fi-sidebar-item-active"] [class*="fi-sidebar-item-button"] span{
  color:#ffffff!important;
}

/* --- Icons ------------------------------------------------- */
.fi-sidebar a svg,.fi-sidebar [class*="fi-sidebar-item-button"] svg{
  color:inherit!important;flex-shrink:0!important;
  width:16px!important;height:16px!important;opacity:.8!important;
}
.fi-sidebar [aria-current="page"] svg,
.fi-sidebar [class*="fi-sidebar-item-active"]>a svg{
  opacity:1!important;color:#ffffff!important;
}

/* --- Badge -------------------------------------------------- */
.fi-sidebar [class*="fi-sidebar-item-badge"]{
  margin-left:auto!important;background:rgba(239,68,68,.22)!important;
  color:#fca5a5!important;font-size:9px!important;font-weight:700!important;
  padding:2px 6px!important;border-radius:10px!important;flex-shrink:0!important;
}
.fi-sidebar [aria-current="page"] [class*="fi-sidebar-item-badge"],
.fi-sidebar [class*="fi-sidebar-item-active"] [class*="fi-sidebar-item-badge"]{
  background:rgba(255,255,255,.25)!important;color:#ffffff!important;
}

/* --- Brand / logo ------------------------------------------ */
.fi-sidebar [class*="fi-brand-name"],.fi-sidebar header a span{color:#ffffff!important}

/* --- User footer ------------------------------------------- */
.fi-sidebar footer a,.fi-sidebar footer button{color:#4d6680!important;font-size:13px!important}
.fi-sidebar footer a:hover,.fi-sidebar footer button:hover{color:#8b949e!important}
.fi-sidebar [class*="fi-avatar"] img,.fi-sidebar [class*="fi-user-avatar"] img{
  border:2px solid rgba(255,255,255,.1)!important;border-radius:8px!important;
}

/* Sidebar toggle */
.fi-sidebar-close-overlay-btn,.fi-topbar-open-sidebar-btn{color:#4d6680!important}
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
