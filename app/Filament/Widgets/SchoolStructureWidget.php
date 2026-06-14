<?php
namespace App\Filament\Widgets;

use App\Models\Classroom;
use App\Models\Employee;
use App\Models\Student;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SchoolStructureWidget extends StatsOverviewWidget
{
    protected static bool $isLazy = true;
    protected static ?int $sort = 3;

    public static function canView(): bool { return false; }

    protected function getStats(): array
    {
        $classrooms     = Classroom::count();
        $teachers       = Employee::where('is_teacher', true)->where('is_active', true)->count();
        $activeStudents = Student::where('status', 'active')->count();
        $noTeacher      = Classroom::whereNull('teacher_id')->count();
        $avgPerClass    = $classrooms > 0 ? round($activeStudents / $classrooms) : 0;

        return [
            Stat::make(__('Classrooms'), $classrooms)
                ->description($noTeacher > 0
                    ? $noTeacher . ' ' . __('without teacher')
                    : __('All assigned'))
                ->descriptionIcon('heroicon-m-building-office')
                ->color($noTeacher > 0 ? 'warning' : 'success'),

            Stat::make(__('Active Teachers'), $teachers)
                ->description(Employee::where('is_teacher', true)->count() . ' ' . __('total teachers'))
                ->descriptionIcon('heroicon-m-user')
                ->color('primary'),

            Stat::make(__('Avg. per Class'), $avgPerClass)
                ->description($activeStudents . ' ' . __('active students total'))
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
        ];
    }
}
