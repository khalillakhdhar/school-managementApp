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
            Stat::make(__('Niveaux scolaires'), $total)
                ->description(__(':n avec classe(s)', ['n' => $withClasses]))
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('primary'),

            Stat::make(__('Classes rattachées'), $totalClasses)
                ->description($total > 0 ? __(':n classe(s) / niveau', ['n' => round($totalClasses / max($total, 1), 1)]) : '—')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('info'),

            Stat::make(__('Élèves répartis'), $activeStudents)
                ->description(__('Élèves actifs tous niveaux'))
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make(__('Niveaux vides'), $total - $withClasses)
                ->description(($total - $withClasses) > 0 ? __('Aucune classe créée') : __('Tous utilisés'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($total - $withClasses > 0 ? 'warning' : 'gray'),
        ];
    }
}
