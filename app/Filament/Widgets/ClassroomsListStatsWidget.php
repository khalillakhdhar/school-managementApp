<?php
namespace App\Filament\Widgets;

use App\Models\Classroom;
use App\Models\Student;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ClassroomsListStatsWidget extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        return request()->routeIs('filament.admin.resources.classrooms.index');
    }

    protected function getStats(): array
    {
        $totalClasses   = Classroom::count();
        $totalCapacity  = (int) Classroom::sum('capacity');
        $activeStudents = Student::where('status', 'active')->count();
        $noTeacher      = Classroom::whereNull('teacher_id')->count();
        $avgPerClass    = $totalClasses > 0 ? round($activeStudents / $totalClasses, 1) : 0;
        $occupancyRate  = $totalCapacity > 0 ? round($activeStudents / $totalCapacity * 100, 1) : 0;

        return [
            Stat::make('Total classes', $totalClasses)
                ->description("{$noTeacher} sans enseignant")
                ->descriptionIcon('heroicon-m-building-office')
                ->color($noTeacher > 0 ? 'warning' : 'success'),

            Stat::make('Capacité totale', $totalCapacity.' places')
                ->description($activeStudents.' élèves inscrits')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Taux d\'occupation', $occupancyRate.'%')
                ->description($activeStudents.' / '.$totalCapacity.' places')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($occupancyRate >= 90 ? 'danger' : ($occupancyRate >= 70 ? 'warning' : 'success')),

            Stat::make('Moy. élèves/classe', $avgPerClass)
                ->description(__('Répartition par classe'))
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info'),
        ];
    }
}
