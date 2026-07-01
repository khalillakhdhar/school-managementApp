<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
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

/**
 * PHASE 7 — Super-admin panel for the SaaS operator, ABOVE all tenants.
 *
 * Deliberately has NO ->tenant(): it manages the client schools themselves
 * (create, suspend, inspect, impersonate). Reserved to the `platform_admin`
 * role (see User::canAccessPanel). Its SchoolResource operates on the tenant
 * model, which is not tenant-scoped, so it sees every school.
 */
class PlatformPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('platform')
            ->path('platform')
            ->login()
            ->brandName('EliteCampus — Plateforme')
            ->favicon(asset('favicon.svg'))
            ->colors([
                'primary' => Color::hex('#7C3AED'), // violet — visually distinct from tenant panels
                'gray'    => Color::Slate,
                'success' => Color::hex('#10B981'),
                'warning' => Color::hex('#F59E0B'),
                'danger'  => Color::hex('#EF4444'),
            ])
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Platform/Resources'), for: 'App\\Filament\\Platform\\Resources')
            ->discoverWidgets(in: app_path('Filament/Platform/Widgets'), for: 'App\\Filament\\Platform\\Widgets')
            ->pages([Dashboard::class])
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
