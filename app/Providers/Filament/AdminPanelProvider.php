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
/* ── Inter font ─────────────────────────────────────────── */
*{font-family:\'Inter\',sans-serif!important}

/* ── Dark sidebar ──────────────────────────────────────── */
.fi-sidebar,.fi-sidebar-header,.fi-sidebar-nav,
nav.fi-sidebar-nav>div,div.fi-sidebar-nav{background:#0f172a!important;border-color:rgba(255,255,255,0.06)!important}
.fi-sidebar{border-right:1px solid rgba(255,255,255,0.06)!important}
.fi-sidebar-header{border-bottom:1px solid rgba(255,255,255,0.06)!important;padding:16px!important}
.fi-sidebar-group-label{color:#475569!important;font-size:10px!important;letter-spacing:1.5px!important;text-transform:uppercase!important;font-weight:700!important;padding:0 12px!important;margin-bottom:2px!important}
.fi-sidebar-item-button{color:#94a3b8!important;border-radius:8px!important;padding:8px 12px!important;margin:1px 6px!important;transition:all .15s!important}
.fi-sidebar-item-button:hover{background:rgba(255,255,255,0.07)!important;color:#e2e8f0!important}
.fi-sidebar-item-active>.fi-sidebar-item-button,.fi-sidebar-item-button[aria-current="page"]{background:rgba(29,78,216,0.25)!important;color:#60a5fa!important}
.fi-sidebar-item-icon{color:inherit!important}
.fi-sidebar-group>button{color:#475569!important}
.fi-sidebar-footer{background:#0f172a!important;border-top:1px solid rgba(255,255,255,0.06)!important}
.fi-brand-name,.fi-sidebar-header .fi-logo{color:white!important}
.fi-nav-groups{padding:6px 4px!important}

/* ── Topbar ─────────────────────────────────────────────── */
.fi-topbar{background:white!important;border-bottom:1px solid #f1f5f9!important;box-shadow:0 1px 3px rgba(0,0,0,0.04)!important}

/* ── Main background ────────────────────────────────────── */
.fi-main-ctn,.fi-simple-main-ctn{background:#f1f5f9!important}
.fi-main{padding:20px 24px!important}

/* ── Page header ────────────────────────────────────────── */
.fi-header{background:white!important;border-bottom:1px solid #f1f5f9!important;padding:16px 24px!important;margin-bottom:0!important}
.fi-page-header-heading{font-size:20px!important;font-weight:800!important;color:#0f172a!important}
.fi-breadcrumbs-item{font-size:12px!important}

/* ── Section cards ──────────────────────────────────────── */
.fi-section,.fi-card{border-radius:14px!important;border-color:#f1f5f9!important;box-shadow:0 1px 3px rgba(0,0,0,0.05),0 0 0 1px rgba(0,0,0,0.03)!important}
.fi-section-header{padding:16px 20px!important;border-bottom-color:#f8fafc!important}
.fi-section-header-heading{font-size:14px!important;font-weight:700!important;color:#0f172a!important}
.fi-section-content,.fi-section-content-ctn{padding:16px 20px!important}

/* ── Tables ─────────────────────────────────────────────── */
.fi-ta-ctn{border-radius:14px!important;overflow:hidden!important;box-shadow:0 1px 3px rgba(0,0,0,0.05),0 0 0 1px rgba(0,0,0,0.03)!important;background:white!important}
.fi-ta-header-row>th,.fi-ta-header-row th{background:#f8fafc!important;font-size:10px!important;font-weight:700!important;text-transform:uppercase!important;letter-spacing:0.6px!important;color:#64748b!important;padding:12px 16px!important;border-bottom:1px solid #f1f5f9!important}
.fi-ta-row>td,.fi-ta-row td{padding:12px 16px!important;border-bottom-color:#f8fafc!important;font-size:13px!important;color:#334155!important}
.fi-ta-row:hover>td{background:#fafafa!important}
.fi-ta-empty-state-icon{color:#cbd5e1!important}

/* ── Badges ─────────────────────────────────────────────── */
.fi-badge{border-radius:6px!important;font-weight:600!important;font-size:11px!important;padding:2px 8px!important}

/* ── Buttons ────────────────────────────────────────────── */
.fi-btn{border-radius:8px!important;font-weight:600!important;font-size:13px!important}
.fi-btn-color-primary{background:linear-gradient(135deg,#2563eb,#1d4ed8)!important;border:none!important;box-shadow:0 1px 3px rgba(29,78,216,0.3)!important}
.fi-btn-color-primary:hover{background:linear-gradient(135deg,#1d4ed8,#1e40af)!important}

/* ── Form inputs ────────────────────────────────────────── */
.fi-input,.fi-select,.fi-textarea{border-radius:8px!important;border-color:#e2e8f0!important;font-size:13px!important}
.fi-input:focus,.fi-select:focus{border-color:#1d4ed8!important;box-shadow:0 0 0 3px rgba(29,78,216,0.1)!important}
.fi-fo-field-wrp-label{font-size:12px!important;font-weight:600!important;color:#374151!important}

/* ── Widgets on dashboard ───────────────────────────────── */
.fi-wi-stats-overview-stat{border-radius:14px!important;box-shadow:0 1px 3px rgba(0,0,0,0.05)!important}

/* ── Nav badges (notification counts) ──────────────────── */
.fi-sidebar-item-badge{background:#1d4ed8!important;color:white!important;font-size:10px!important;font-weight:700!important}

/* ── Dropdown ───────────────────────────────────────────── */
.fi-dropdown-list{border-radius:12px!important;box-shadow:0 8px 24px rgba(0,0,0,0.12)!important;border:1px solid #f1f5f9!important}
.fi-dropdown-item{font-size:13px!important;border-radius:6px!important;margin:1px 4px!important}
</style>'
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
