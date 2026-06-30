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
                'primary' => Color::hex('#2563EB'),
                'gray'    => Color::Slate,
                'info'    => Color::Sky,
                'success' => Color::hex('#10B981'),
                'warning' => Color::hex('#F59E0B'),
                'danger'  => Color::hex('#EF4444'),
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
   ELITECAMPUS — Professional ERP Design System v5
   All selectors verified against Filament v5 blade templates
   ═══════════════════════════════════════════════════════════════ */

*{font-family:\'Inter\',-apple-system,system-ui,sans-serif!important}

/* ── SHELL ───────────────────────────────────────────────────── */
html:not(.dark) body,
html:not(.dark) .fi-main-ctn{background:#eef2f7!important}

html:not(.dark) .fi-topbar{
  background:#ffffff!important;border-bottom:1px solid #dde3ea!important;
  box-shadow:0 1px 0 rgba(0,0,0,.04)!important;
}

/* ── PAGE LAYOUT ─────────────────────────────────────────────── */
html:not(.dark) .fi-page,.fi-page-ctn{gap:0!important}
html:not(.dark) .fi-page-content{
  padding:8px 20px 28px!important;
  display:flex!important;flex-direction:column!important;gap:18px!important;
}

/* ── PAGE HEADER — large SaaS title (ref: "Analytics") ───────── */
html:not(.dark) .fi-page-header{
  background:transparent!important;border-bottom:none!important;
  padding:20px 20px 4px!important;margin:0!important;
}
html:not(.dark) .fi-page-header-heading{
  font-size:26px!important;font-weight:800!important;
  color:#0f172a!important;letter-spacing:-.6px!important;
}
html:not(.dark) .fi-header-subheading{
  font-size:14px!important;color:#64748b!important;margin-top:4px!important;
}
html:not(.dark) .fi-breadcrumbs-item-label{color:#9ca3af!important;font-size:12px!important}
html:not(.dark) .fi-breadcrumbs-item:last-child .fi-breadcrumbs-item-label{
  color:#111827!important;font-weight:600!important;
}

/* ── CARDS / SECTIONS ────────────────────────────────────────── */
html:not(.dark) .fi-section{
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

/* ── STATS / KPI CARDS — SaaS style (ref: HR dashboard) ──────── */
html:not(.dark) .fi-wi-stats-overview{gap:16px!important}
html:not(.dark) .fi-wi-stats-overview-stat{
  border-radius:14px!important;border:1px solid #e5e9f0!important;
  box-shadow:0 1px 2px rgba(16,24,40,.04),0 1px 3px rgba(16,24,40,.06)!important;
  padding:18px 20px!important;background:#ffffff!important;
  transition:box-shadow .15s,transform .15s!important;
}
html:not(.dark) .fi-wi-stats-overview-stat:hover{
  box-shadow:0 4px 16px rgba(16,24,40,.1)!important;transform:translateY(-2px)!important;
}
html:not(.dark) .fi-wi-stats-overview-stat-label{
  font-size:12.5px!important;font-weight:600!important;
  text-transform:none!important;letter-spacing:0!important;color:#64748b!important;
}
html:not(.dark) .fi-wi-stats-overview-stat-value{
  font-size:28px!important;font-weight:800!important;
  color:#0f172a!important;letter-spacing:-.6px!important;margin-top:6px!important;line-height:1.1!important;
}
html:not(.dark) .fi-wi-stats-overview-stat-description{
  font-size:12.5px!important;color:#64748b!important;margin-top:6px!important;font-weight:500!important;
}
.fi-wi-stats-overview{gap:16px!important}
.fi-wi-stats-overview-stat-description{font-size:12.5px!important}

/* ── WIDGET GRID ─────────────────────────────────────────────── */
.fi-dashboard-widgets,.fi-page-widgets{gap:12px!important}
.fi-wi{margin:0!important}

/* ── TABLES — enterprise software (ref: SEMrush/competitors) ──── */
html:not(.dark) .fi-ta-ctn{
  background:#ffffff!important;border:1px solid #e5e9f0!important;
  border-radius:14px!important;box-shadow:0 1px 3px rgba(16,24,40,.05)!important;
  overflow:hidden!important;
}
html:not(.dark) .fi-ta-header-row>th,html:not(.dark) .fi-ta-header-row th{
  background:#fafbfc!important;font-size:11px!important;font-weight:600!important;
  text-transform:uppercase!important;letter-spacing:.5px!important;
  color:#64748b!important;padding:13px 18px!important;
  border-bottom:1px solid #eaeef3!important;
}
html:not(.dark) .fi-ta-row>td{
  padding:14px 18px!important;border-bottom:1px solid #f1f4f8!important;
  font-size:13.5px!important;color:#1e293b!important;background:transparent!important;
  vertical-align:middle!important;
}
html:not(.dark) .fi-ta-row:last-child>td{border-bottom:none!important}
html:not(.dark) .fi-ta-row:hover>td{background:#f8fafc!important}
html:not(.dark) .fi-ta-header{
  padding:16px 18px!important;border-bottom:1px solid #f1f4f8!important;
  background:#ffffff!important;
}
html:not(.dark) .fi-ta-empty-state{padding:56px 20px!important}
html:not(.dark) .fi-ta-empty-state-icon-ctn{
  background:#eff4ff!important;width:64px!important;height:64px!important;border-radius:16px!important;
}
html:not(.dark) .fi-ta-empty-state-heading{
  font-size:16px!important;font-weight:700!important;color:#1e293b!important;margin-top:16px!important;
}
html:not(.dark) .fi-ta-empty-state-description{font-size:13.5px!important;color:#94a3b8!important;max-width:420px!important;margin:6px auto 0!important}

/* ── FORMS ───────────────────────────────────────────────────── */
html:not(.dark) .fi-fo-field-wrp-label label{
  font-size:12px!important;font-weight:600!important;color:#374151!important;
}
html:not(.dark) .fi-fo-helper-text{font-size:11px!important;color:#9ca3af!important}

/* ── DROPDOWN / MODAL ────────────────────────────────────────── */
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

/* ── GLOBAL COMPONENTS ───────────────────────────────────────── */
.fi-btn{border-radius:9px!important;font-weight:600!important;font-size:13px!important}
.fi-btn-color-primary{
  background:#2563eb!important;border:none!important;color:#fff!important;
  box-shadow:0 1px 2px rgba(37,99,235,.3)!important;
}
.fi-btn-color-primary:hover{background:#1d4ed8!important}
.fi-no{border-radius:14px!important;box-shadow:0 8px 30px rgba(16,24,40,.14)!important}
.fi-dropdown-item{font-size:13px!important;border-radius:7px!important;margin:1px!important}
.fi-pagination{font-size:13px!important}

/* ── BADGES — soft pill, beautiful (ref: status pills) ───────── */
html:not(.dark) .fi-badge{
  border-radius:7px!important;font-weight:600!important;font-size:11.5px!important;
  padding:3px 10px!important;letter-spacing:.1px!important;
}

/* ════════════════════════════════════════════════════════════════
   SIDEBAR — Dark navy, Filament v5 exact class names
   Source: vendor/filament/filament/resources/views/components/sidebar/
   ════════════════════════════════════════════════════════════════ */

/* ── Width — wider, more premium (288px) ──────────────────────── */
:root{--sidebar-width:18rem!important}
.fi-main-ctn{--sidebar-width:18rem!important}

/* ── Base background — override Filament\'s bg-white / bg-gray-900 ── */
.fi-sidebar,html.dark .fi-sidebar{
  background:#0f172a!important;
  border-right:1px solid rgba(255,255,255,.05)!important;
}
/* Transparent children so parent dark bg shows through */
.fi-sidebar-header,.fi-sidebar-nav,.fi-sidebar-footer,
.fi-sidebar-group,.fi-sidebar-group-btn,.fi-sidebar-group-items,
.fi-sidebar-item,.fi-sidebar-item-btn,.fi-sidebar-nav-groups{
  background:transparent!important;
}
.fi-sidebar-header{border-bottom:1px solid rgba(255,255,255,.07)!important}
.fi-sidebar-footer{border-top:1px solid rgba(255,255,255,.06)!important}

/* ── Kill the light gap/scrollbar strip between sidebar & content ── */
.fi-sidebar-nav{scrollbar-gutter:auto!important;scrollbar-width:thin!important;scrollbar-color:#334155 #0f172a!important}
.fi-sidebar-nav::-webkit-scrollbar{width:6px!important}
.fi-sidebar-nav::-webkit-scrollbar-track{background:#0f172a!important}
.fi-sidebar-nav::-webkit-scrollbar-thumb{background:#334155!important;border-radius:3px!important}
.fi-sidebar-nav::-webkit-scrollbar-thumb:hover{background:#475569!important}
/* Main content sits flush against the sidebar */
html:not(.dark) .fi-main-ctn{border-left:none!important}
.fi-main{margin-inline-start:0!important}

/* ── Nav padding ──────────────────────────────────────────────── */
.fi-sidebar-nav{padding:10px 14px 20px!important;gap:0!important}
.fi-sidebar-nav-groups{margin-left:0!important;margin-right:0!important;gap:6px!important}

/* ── Groups ───────────────────────────────────────────────────── */
.fi-sidebar-group{gap:3px!important}
.fi-sidebar-group-btn{padding:14px 10px 6px!important;gap:8px!important}
.fi-sidebar-group-items{gap:3px!important}

/* GROUP SECTION LABEL — "Académique", "RH", etc. */
.fi-sidebar-group-label{
  color:#94a3b8!important;
  font-size:11.5px!important;font-weight:600!important;
  letter-spacing:0.6px!important;
  text-transform:uppercase!important;
}
/* Group icon */
.fi-sidebar-group-btn .fi-icon{color:#64748b!important;width:16px!important;height:16px!important}
.fi-sidebar-group-collapse-btn{color:#475569!important}

/* ── KILL TIMELINE BORDER LINES ───────────────────────────────── */
.fi-sidebar-item-grouped-border,
.fi-sidebar-item-grouped-border-part,
.fi-sidebar-item-grouped-border-part-not-first,
.fi-sidebar-item-grouped-border-part-not-last{
  display:none!important;width:0!important;height:0!important;
}

/* ── NAV ITEM BUTTON (the <a class="fi-sidebar-item-btn">) ────── */
.fi-sidebar-item-btn{
  display:flex!important;align-items:center!important;gap:13px!important;
  width:100%!important;padding:11px 14px!important;border-radius:10px!important;
  color:#cbd5e1!important;background:transparent!important;
  text-decoration:none!important;border:none!important;cursor:pointer!important;
  transition:background .12s,color .12s!important;justify-content:flex-start!important;
}
.fi-sidebar-item-has-url .fi-sidebar-item-btn:hover{
  background:#1e293b!important;color:#ffffff!important;
}

/* ── NAV ITEM LABEL — readable from across the room ──────────── */
.fi-sidebar-item-label{
  flex:1!important;min-width:0!important;
  font-size:15px!important;font-weight:500!important;
  color:inherit!important;
  text-transform:none!important;
  letter-spacing:0!important;
  white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;
}

/* ── ICON in nav items ────────────────────────────────────────── */
.fi-sidebar-item-btn .fi-icon{
  color:inherit!important;flex-shrink:0!important;
  width:21px!important;height:21px!important;opacity:.85!important;
}

/* ── ACTIVE ITEM (fi-active on <li>) — solid primary blue ─────── */
.fi-sidebar-item.fi-active .fi-sidebar-item-btn{
  background:#2563eb!important;color:#ffffff!important;font-weight:600!important;
  box-shadow:0 4px 12px rgba(37,99,235,.4)!important;
}
.fi-sidebar-item.fi-active .fi-sidebar-item-btn .fi-icon{
  color:#ffffff!important;opacity:1!important;
}
.fi-sidebar-item.fi-active .fi-sidebar-item-label{color:#ffffff!important;font-weight:600!important}
.fi-sidebar-item.fi-active .fi-sidebar-item-btn:hover{background:#1d4ed8!important}

/* ── NAVIGATION BADGE — compact pill ─────────────────────────── */
/* Container */
.fi-sidebar-item-badge-ctn{
  margin-left:auto!important;flex-shrink:0!important;
  display:flex!important;align-items:center!important;
}
/* The actual <x-filament::badge> inside renders as .fi-badge */
.fi-sidebar-item-badge-ctn .fi-badge{
  /* Override ALL default badge styles */
  background:rgba(239,68,68,.2)!important;color:#fca5a5!important;
  font-size:10px!important;font-weight:700!important;line-height:1.4!important;
  padding:2px 7px!important;border-radius:10px!important;
  white-space:nowrap!important;min-width:18px!important;text-align:center!important;
  display:inline-flex!important;align-items:center!important;justify-content:center!important;
  box-shadow:none!important;border:none!important;
  --tw-ring-shadow:none!important;--tw-shadow:0 0 #0000!important;
}
/* Active item badge */
.fi-sidebar-item.fi-active .fi-sidebar-item-badge-ctn .fi-badge{
  background:rgba(255,255,255,.2)!important;color:#ffffff!important;
}

/* ── Brand / logo text ────────────────────────────────────────── */
.fi-logo span,.fi-brand-name{color:#e2e8f0!important}

/* ── User footer menu ─────────────────────────────────────────── */
.fi-sidebar-footer a,.fi-sidebar-footer button{color:#4a6480!important;font-size:12.5px!important}
.fi-sidebar-footer a:hover,.fi-sidebar-footer button:hover{color:#8dafc8!important}

/* Topbar toggle buttons */
.fi-topbar-open-sidebar-btn{color:#64748b!important}

/* Language switcher: cleaner topbar control without separator line */
.language-switch-trigger{
  border:none!important;box-shadow:none!important;outline:none!important;
  --tw-ring-shadow:none!important;--tw-ring-offset-shadow:none!important;
  background:#f8fafc!important;color:#334155!important;
}
.language-switch-trigger:hover{background:#eef2f7!important;color:#0f172a!important}
.fi-topbar .fi-dropdown.fi-user-menu{border-inline-start:none!important;margin-inline-start:0!important;padding-inline-start:0!important}
.fi-topbar .fi-dropdown.fi-user-menu::before,
.fi-topbar .fi-dropdown.fi-user-menu::after{display:none!important;content:none!important}
</style>'
                       . '<script>
/* Force a fresh load when the page is restored from the browser back-forward
   cache — guarantees the latest design/CSS is always shown, never a stale snapshot. */
window.addEventListener("pageshow", function (e) {
    if (e.persisted || (window.performance && performance.getEntriesByType("navigation")[0] && performance.getEntriesByType("navigation")[0].type === "back_forward")) {
        location.reload();
    }
});
</script>'
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
                \App\Http\Middleware\ForcePasswordChange::class,
            ]);
    }
}
