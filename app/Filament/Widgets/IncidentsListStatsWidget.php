<?php
namespace App\Filament\Widgets;

use App\Models\Incident;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class IncidentsListStatsWidget extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        return request()->routeIs('filament.admin.resources.incidents.index');
    }

    protected function getStats(): array
    {
        $total         = Incident::count();
        $thisMonth     = Incident::whereMonth('incident_date', now()->month)
            ->whereYear('incident_date', now()->year)->count();
        $unnotified    = Incident::where('parent_notified', false)->count();
        $highSeverity  = Incident::where('severity', 'high')->count();
        $mediumSeverity = Incident::where('severity', 'medium')->count();

        $monthlyTrend = collect(range(5, 0))->map(fn ($i) =>
            Incident::whereMonth('incident_date', now()->subMonths($i)->month)
                ->whereYear('incident_date', now()->subMonths($i)->year)->count()
        )->toArray();

        return [
            Stat::make('Total incidents', $total)
                ->description("{$thisMonth} ce mois")
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('primary')
                ->chart($monthlyTrend),

            Stat::make('Parents non notifiés', $unnotified)
                ->description(__('Notifications en attente'))
                ->descriptionIcon('heroicon-m-bell-alert')
                ->color($unnotified > 0 ? 'warning' : 'success'),

            Stat::make('Incidents graves', $highSeverity)
                ->description("{$mediumSeverity} de gravité moyenne")
                ->descriptionIcon('heroicon-m-fire')
                ->color($highSeverity > 0 ? 'danger' : 'success'),

            Stat::make('Ce mois', $thisMonth)
                ->description(now()->locale('fr')->isoFormat('MMMM YYYY'))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color($thisMonth > 5 ? 'danger' : ($thisMonth > 2 ? 'warning' : 'success')),
        ];
    }
}
