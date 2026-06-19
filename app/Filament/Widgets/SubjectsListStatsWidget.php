<?php
namespace App\Filament\Widgets;

use App\Models\Subject;
use App\Models\TimetableEntry;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SubjectsListStatsWidget extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        return request()->routeIs('filament.admin.resources.subjects.index');
    }

    protected function getStats(): array
    {
        $total    = Subject::count();
        $active   = Subject::where('is_active', true)->count();
        $scheduled = TimetableEntry::distinct('subject_id')->count('subject_id');
        $unscheduled = max(0, $active - $scheduled);
        $avgCoef  = round((float) Subject::avg('coefficient') ?? 0, 1);

        return [
            Stat::make('Total matières', $total)
                ->description("{$active} active(s)")
                ->descriptionIcon('heroicon-m-book-open')
                ->color('primary'),

            Stat::make('Matières planifiées', $scheduled)
                ->description($active > 0 ? round($scheduled / max($active, 1) * 100).'% couvertes' : '—')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('success'),

            Stat::make('Non planifiées', $unscheduled)
                ->description($unscheduled > 0 ? 'Aucun créneau assigné' : 'Toutes planifiées')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($unscheduled > 0 ? 'warning' : 'gray'),

            Stat::make('Coefficient moyen', $avgCoef)
                ->description(__('Pondération moyenne'))
                ->descriptionIcon('heroicon-m-scale')
                ->color('info'),
        ];
    }
}
