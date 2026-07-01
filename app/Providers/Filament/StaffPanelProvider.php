<?php
namespace App\Providers\Filament;

use App\Models\School;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class StaffPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('staff')
            ->path('staff')
            ->login()
            ->tenant(School::class, slugAttribute: 'slug', ownershipRelationship: 'school')
            ->brandName(fn (): string => (($t = Filament::getTenant()) ? $t->name . ' — ' : '') . 'Espace Personnel')
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
                NavigationGroup::make('Mon espace')->icon('heroicon-o-user'),
                NavigationGroup::make('Enseignement')->icon('heroicon-o-academic-cap'),
            ])
            ->sidebarCollapsibleOnDesktop()
            ->discoverPages(in: app_path('Filament/Staff/Pages'), for: 'App\Filament\Staff\Pages')
            ->discoverWidgets(in: app_path('Filament/Staff/Widgets'), for: 'App\Filament\Staff\Widgets')
            ->renderHook('panels::head.end', fn () => view('filament.portal-theme'))
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
            ])
            ->authGuard('web');
    }
}
