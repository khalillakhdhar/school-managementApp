<?php

namespace App\Providers\Filament;

use App\Models\School;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * PHASE 0 SPIKE — throwaway panel to validate Filament v5 tenancy in isolation,
 * WITHOUT breaking the 3 production panels (whose resources have no school_id yet).
 *
 * Registers only the pilot SpikeStudentResource, scoped to the current School tenant.
 * Routes: /spike/{school}/...  ·  Delete this file + provider entry after validation.
 */
class SpikePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('spike')
            ->path('spike')
            ->login()
            ->tenant(School::class, slugAttribute: 'slug', ownershipRelationship: 'school')
            // ── Per-tenant branding (PHASE 1) ─────────────────────────────
            ->brandName(fn (): string => Filament::getTenant()?->name ?? 'EliteCampus')
            ->brandLogo(fn (): ?string => Filament::getTenant()?->logoUrl())
            ->brandLogoHeight('2rem')
            ->renderHook(
                'panels::head.end',
                fn (): string => ($color = Filament::getTenant()?->brandColor())
                    ? "<style>:root{--tenant-accent:{$color}}"
                        . '.fi-btn-color-primary{background:var(--tenant-accent)!important;border-color:var(--tenant-accent)!important}'
                        . '.fi-sidebar-item.fi-active .fi-sidebar-item-btn{background:var(--tenant-accent)!important}'
                        . '</style>'
                    : ''
            )
            ->discoverResources(in: app_path('Filament/Spike/Resources'), for: 'App\\Filament\\Spike\\Resources')
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
            ]);
    }
}
