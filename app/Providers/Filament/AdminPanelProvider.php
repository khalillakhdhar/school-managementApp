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
                'panels::head.end',
                fn () => '<link rel="preconnect" href="https://fonts.googleapis.com">'
                       . '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>'
                       . '<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">'
                       . '<style>
/* ════════════════════════════════════════════════════════════
   ELITECAMPUS DESIGN SYSTEM — Global Styles
   ════════════════════════════════════════════════════════════ */

/* ── Inter font ─────────────────────────────────────────── */
*{font-family:\'Inter\',sans-serif!important}

/* ════════════════════════════════════════════════════════════
   SIDEBAR — Complete Professional Overhaul (Linear/Stripe style)
   ════════════════════════════════════════════════════════════ */

/* Base — dark navy background everywhere in sidebar */
.fi-sidebar,.fi-sidebar-header,.fi-sidebar-nav,.fi-sidebar-footer,
nav.fi-sidebar-nav,nav.fi-sidebar-nav>*,div.fi-sidebar-nav,
.fi-sidebar-nav-groups,.fi-sidebar-group,.fi-sidebar-group-items,
.fi-sidebar-item{background:#0f172a!important;border-color:transparent!important}

/* Sidebar outer border */
.fi-sidebar{border-right:1px solid rgba(255,255,255,0.06)!important}
.fi-sidebar-header{border-bottom:1px solid rgba(255,255,255,0.07)!important;padding:14px 16px!important}
.fi-sidebar-footer{border-top:1px solid rgba(255,255,255,0.07)!important;padding:10px 12px!important}

/* ── KILL the vertical timeline/connector line (all possible implementations) ── */
.fi-sidebar-group::before,.fi-sidebar-group::after,
.fi-sidebar-group-items::before,.fi-sidebar-group-items::after,
.fi-sidebar-item::before,.fi-sidebar-item::after,
.fi-sidebar-nav-groups::before,.fi-sidebar-nav-groups::after,
.fi-sidebar-nav-group::before,.fi-sidebar-nav-group::after{
    display:none!important;content:none!important;
    border:none!important;background:none!important;
    background-image:none!important;width:0!important;
}
.fi-sidebar-group-items,.fi-sidebar-nav-group-items{
    border-left:none!important;border-top:none!important;
    padding-left:0!important;margin-left:0!important;
}
.fi-sidebar-item{
    border-left:none!important;padding-left:0!important;
    margin-left:0!important;list-style:none!important;
}

/* ── Nav container padding ── */
.fi-sidebar-nav-groups,.fi-nav-groups{padding:4px 6px 8px!important}

/* ── Group section ── */
.fi-sidebar-group{padding:0!important;margin-bottom:6px!important}
.fi-sidebar-group-items{padding:2px 0 0!important;margin:0!important;gap:1px!important}

/* ── Group label ── */
.fi-sidebar-group-label,
.fi-sidebar-group>button .fi-sidebar-group-label,
.fi-sidebar-group-label span{
    color:#475569!important;font-size:9.5px!important;font-weight:700!important;
    letter-spacing:1.8px!important;text-transform:uppercase!important;
    padding:8px 10px 3px!important;display:block!important;
}
.fi-sidebar-group>button{
    background:transparent!important;color:#475569!important;
    padding:0!important;width:100%!important;text-align:left!important;
}
.fi-sidebar-group>button:hover{background:transparent!important}

/* ── Nav item (the clickable element) ── */
.fi-sidebar-item-button{
    display:flex!important;align-items:center!important;gap:9px!important;
    width:100%!important;padding:7px 10px!important;
    border-radius:7px!important;margin:0!important;
    color:#94a3b8!important;font-size:13px!important;font-weight:500!important;
    transition:background .12s,color .12s!important;
    text-decoration:none!important;position:relative!important;
    border-left:none!important;
}
.fi-sidebar-item-button:hover{
    background:rgba(255,255,255,0.07)!important;
    color:#cbd5e1!important;
}

/* ── Active nav item ── */
.fi-sidebar-item-active>.fi-sidebar-item-button,
.fi-sidebar-item-button[aria-current="page"],
.fi-sidebar-item-button.active{
    background:rgba(37,99,235,0.18)!important;
    color:#93c5fd!important;font-weight:600!important;
}
.fi-sidebar-item-active>.fi-sidebar-item-button::before,
.fi-sidebar-item-button[aria-current="page"]::before{
    content:""!important;display:block!important;
    position:absolute!important;left:0!important;top:50%!important;
    transform:translateY(-50%)!important;
    width:3px!important;height:60%!important;
    background:#3b82f6!important;border-radius:0 3px 3px 0!important;
}

/* ── Icon ── */
.fi-sidebar-item-icon,.fi-sidebar-item-button svg{
    color:inherit!important;flex-shrink:0!important;
    width:16px!important;height:16px!important;
}

/* ── Nav badge ── */
.fi-sidebar-item-badge{
    background:#1d4ed8!important;color:white!important;
    font-size:9px!important;font-weight:700!important;
    border-radius:10px!important;padding:1px 6px!important;
    margin-left:auto!important;
}

/* ── Brand in sidebar ── */
.fi-brand-name,.fi-sidebar-header .fi-logo,
.fi-sidebar-header a span{color:white!important}

/* ── User avatar / footer ── */
.fi-sidebar-footer a,.fi-sidebar-footer button{
    color:#94a3b8!important;font-size:13px!important;
}
.fi-user-avatar{border:2px solid rgba(255,255,255,0.15)!important}

/* ── Collapsible sidebar button ── */
.fi-sidebar-close-overlay-btn,.fi-topbar-open-sidebar-btn{
    color:#94a3b8!important;
}

/* ════════════════════════════════════════════════════════════
   TOPBAR
   ════════════════════════════════════════════════════════════ */
.fi-topbar{
    background:white!important;
    border-bottom:1px solid #e8edf2!important;
    box-shadow:0 1px 0 rgba(0,0,0,0.05)!important;
}
.fi-topbar-breadcrumbs .fi-breadcrumbs-item{font-size:12px!important}

/* ════════════════════════════════════════════════════════════
   MAIN CONTENT AREA
   ════════════════════════════════════════════════════════════ */
.fi-main-ctn,.fi-simple-main-ctn{background:#f0f4f8!important}
.fi-main{padding:20px 24px!important;max-width:100%!important}

/* ── Page header ── */
.fi-header{
    background:white!important;border-bottom:1px solid #e8edf2!important;
    padding:14px 24px!important;margin-bottom:0!important;
}
.fi-page-header-heading,.fi-header h1{
    font-size:19px!important;font-weight:800!important;color:#0f172a!important;letter-spacing:-0.3px!important;
}
.fi-breadcrumbs ol,.fi-breadcrumbs-item{font-size:12px!important;color:#94a3b8!important}
.fi-breadcrumbs-item-label{color:#64748b!important}
.fi-breadcrumbs-item:last-child .fi-breadcrumbs-item-label{color:#0f172a!important;font-weight:600!important}

/* ════════════════════════════════════════════════════════════
   SECTION CARDS
   ════════════════════════════════════════════════════════════ */
.fi-section,.fi-card{
    border-radius:12px!important;
    border:1px solid #e8edf2!important;
    box-shadow:0 1px 3px rgba(0,0,0,0.04)!important;
    background:white!important;
}
.fi-section-header{
    padding:14px 18px!important;
    border-bottom:1px solid #f1f5f9!important;
}
.fi-section-header-heading{font-size:14px!important;font-weight:700!important;color:#0f172a!important}
.fi-section-content,.fi-section-content-ctn{padding:14px 18px!important}

/* ════════════════════════════════════════════════════════════
   TABLES — Premium styling
   ════════════════════════════════════════════════════════════ */
.fi-ta-ctn{
    border-radius:12px!important;overflow:hidden!important;
    border:1px solid #e8edf2!important;
    box-shadow:0 1px 3px rgba(0,0,0,0.04)!important;
    background:white!important;
}
.fi-ta-header-row>th,.fi-ta-header-row th{
    background:#f8fafc!important;
    font-size:10px!important;font-weight:700!important;
    text-transform:uppercase!important;letter-spacing:0.7px!important;
    color:#64748b!important;padding:11px 16px!important;
    border-bottom:1px solid #e8edf2!important;
}
.fi-ta-row>td,.fi-ta-row td{
    padding:12px 16px!important;
    border-bottom:1px solid #f8fafc!important;
    font-size:13px!important;color:#334155!important;
}
.fi-ta-row:last-child>td,.fi-ta-row:last-child td{border-bottom:none!important}
.fi-ta-row:hover>td,.fi-ta-row:hover td{background:#f8fafc!important;transition:background .1s!important}

/* Table toolbar */
.fi-ta-header{padding:12px 16px!important;border-bottom:1px solid #f1f5f9!important}
.fi-ta-filters-form{padding:12px 16px!important}

/* Empty state */
.fi-ta-empty-state{padding:40px 20px!important}
.fi-ta-empty-state-icon{
    color:#cbd5e1!important;width:44px!important;height:44px!important;
}
.fi-ta-empty-state-heading{
    font-size:15px!important;font-weight:700!important;color:#374151!important;
    margin-top:12px!important;
}
.fi-ta-empty-state-description{font-size:13px!important;color:#94a3b8!important;margin-top:4px!important}

/* ════════════════════════════════════════════════════════════
   BADGES
   ════════════════════════════════════════════════════════════ */
.fi-badge{
    border-radius:5px!important;font-weight:600!important;
    font-size:11px!important;padding:2px 8px!important;
}

/* ════════════════════════════════════════════════════════════
   BUTTONS
   ════════════════════════════════════════════════════════════ */
.fi-btn{border-radius:8px!important;font-weight:600!important;font-size:13px!important}
.fi-btn-color-primary{
    background:linear-gradient(135deg,#2563eb 0%,#1d4ed8 100%)!important;
    border:none!important;box-shadow:0 1px 3px rgba(29,78,216,0.3)!important;
    color:white!important;
}
.fi-btn-color-primary:hover{
    background:linear-gradient(135deg,#1d4ed8 0%,#1e40af 100%)!important;
    box-shadow:0 2px 6px rgba(29,78,216,0.4)!important;
}

/* ════════════════════════════════════════════════════════════
   FORM FIELDS
   ════════════════════════════════════════════════════════════ */
.fi-input,.fi-select,.fi-textarea{
    border-radius:8px!important;border-color:#dde3ea!important;font-size:13px!important;
}
.fi-input:focus,.fi-select:focus,.fi-textarea:focus{
    border-color:#1d4ed8!important;box-shadow:0 0 0 3px rgba(29,78,216,0.08)!important;
}
.fi-fo-field-wrp-label label{
    font-size:12px!important;font-weight:600!important;color:#374151!important;
}
.fi-fo-helper-text{font-size:11px!important;color:#94a3b8!important}
.fi-fo-field-wrp.fi-fo-field-wrp-has-error .fi-input,
.fi-fo-field-wrp.fi-fo-field-wrp-has-error .fi-select{
    border-color:#ef4444!important;
}

/* ════════════════════════════════════════════════════════════
   STATS OVERVIEW WIDGETS
   ════════════════════════════════════════════════════════════ */
.fi-wi-stats-overview{gap:14px!important}
.fi-wi-stats-overview-stat{
    border-radius:12px!important;
    border:1px solid #e8edf2!important;
    box-shadow:0 1px 3px rgba(0,0,0,0.04)!important;
    padding:18px 20px!important;
    background:white!important;
}
.fi-wi-stats-overview-stat-label{
    font-size:11px!important;font-weight:700!important;
    text-transform:uppercase!important;letter-spacing:0.6px!important;color:#64748b!important;
}
.fi-wi-stats-overview-stat-value{
    font-size:26px!important;font-weight:800!important;
    color:#0f172a!important;letter-spacing:-0.5px!important;
}
.fi-wi-stats-overview-stat-description{font-size:12px!important;font-weight:500!important}
.fi-wi-stats-overview-stat-chart{opacity:.7!important}

/* ════════════════════════════════════════════════════════════
   DROPDOWN
   ════════════════════════════════════════════════════════════ */
.fi-dropdown-list{
    border-radius:10px!important;
    box-shadow:0 8px 24px rgba(0,0,0,0.1)!important;
    border:1px solid #e8edf2!important;
    padding:4px!important;
}
.fi-dropdown-item{
    font-size:13px!important;border-radius:6px!important;margin:1px!important;
}

/* ════════════════════════════════════════════════════════════
   NOTIFICATIONS & MODALS
   ════════════════════════════════════════════════════════════ */
.fi-fo-component-ctn.fi-modal-content,.fi-modal .fi-section{border-radius:14px!important}
.fi-no{border-radius:12px!important;box-shadow:0 4px 16px rgba(0,0,0,0.1)!important}

/* ════════════════════════════════════════════════════════════
   MISC
   ════════════════════════════════════════════════════════════ */
.fi-pagination{font-size:13px!important}
.fi-pagination-item{border-radius:6px!important}
.fi-pagination-item-label{font-weight:500!important}
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
