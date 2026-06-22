<?php
namespace App\Filament\Widgets;

use App\Models\Service;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ServicesListStatsWidget extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        return request()->routeIs('filament.admin.resources.services.index');
    }

    protected function getStats(): array
    {
        $total      = Service::count();
        $active     = Service::where('is_active', true)->count();
        $subscribed = Service::has('students')->count();
        $avgAmount  = round((float) Service::where('is_active', true)->avg('amount') ?? 0, 3);

        return [
            Stat::make(__('Total services'), $total)
                ->description(__(':n actif(s)', ['n' => $active]))
                ->descriptionIcon('heroicon-m-squares-plus')
                ->color('primary'),

            Stat::make(__('Services souscrits'), $subscribed)
                ->description($total > 0 ? __(':pct% utilisés', ['pct' => round($subscribed / $total * 100)]) : '—')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),

            Stat::make(__('Tarif moyen'), number_format($avgAmount, 3).' TND')
                ->description(__('Services actifs'))
                ->descriptionIcon('heroicon-m-tag')
                ->color('info'),

            Stat::make(__('Inactifs'), $total - $active)
                ->description(($total - $active) > 0 ? __('Non proposés actuellement') : __('Tous actifs'))
                ->descriptionIcon('heroicon-m-pause-circle')
                ->color($total - $active > 0 ? 'warning' : 'gray'),
        ];
    }
}
