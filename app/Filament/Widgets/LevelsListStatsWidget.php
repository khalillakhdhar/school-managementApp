<?php
namespace App\Filament\Widgets;

use App\Models\Classroom;
use App\Models\Level;
use App\Models\Student;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LevelsListStatsWidget extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        return request()->routeIs('filament.admin.resources.levels.index');
    }

    protected function getStats(): array
    {
        $total       = Level::count();
        $withClasses = Level::has('classrooms')->count();
        $totalClasses = Classroom::count();
        $activeStudents = Student::where('status', 'active')->count();

        return [
            Stat::make('Niveaux scolaires', $total)
                ->description("{$withClasses} avec classe(s)")
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('primary'),

            Stat::make('Classes rattachées', $totalClasses)
                ->description($total > 0 ? round($totalClasses / max($total, 1), 1).' classe(s) / niveau' : '—')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('info'),

            Stat::make('Élèves répartis', $activeStudents)
                ->description(__('Élèves actifs tous niveaux'))
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make('Niveaux vides', $total - $withClasses)
                ->description(($total - $withClasses) > 0 ? 'Aucune classe créée' : 'Tous utilisés')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($total - $withClasses > 0 ? 'warning' : 'gray'),
        ];
    }
}
