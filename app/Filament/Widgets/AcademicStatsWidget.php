<?php
namespace App\Filament\Widgets;

use App\Models\Classroom;
use App\Models\Employee;
use App\Models\Subject;
use App\Models\TimetableEntry;
use Filament\Widgets\Widget;

class AcademicStatsWidget extends Widget
{
    protected string $view = 'filament.widgets.academic-stats-widget';
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';

    public function getStats(): array
    {
        $totalSubjects  = Subject::active()->count();
        $totalSessions  = TimetableEntry::count();
        $activeTeachers = Employee::active()->teachers()
            ->whereHas('timetableEntries')
            ->count();
        $totalHours = TimetableEntry::get()->sum('duration_hours');
        $classrooms = Classroom::count();
        $coveredClasses = TimetableEntry::distinct('classroom_id')->count('classroom_id');

        return [
            [
                'label' => __('Matières actives'),
                'value' => $totalSubjects,
                'icon'  => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
                'color' => '#1d4ed8',
                'bg'    => '#eff6ff',
            ],
            [
                'label' => __('Séances / semaine'),
                'value' => $totalSessions,
                'icon'  => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                'color' => '#059669',
                'bg'    => '#ecfdf5',
            ],
            [
                'label' => __('Active Teachers'),
                'value' => $activeTeachers,
                'icon'  => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0',
                'color' => '#7c3aed',
                'bg'    => '#f5f3ff',
            ],
            [
                'label' => __('Heures planifiées'),
                'value' => number_format($totalHours, 1) . 'h',
                'icon'  => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                'color' => '#d97706',
                'bg'    => '#fffbeb',
            ],
            [
                'label' => __('Classes couvertes'),
                'value' => $coveredClasses . ' / ' . $classrooms,
                'icon'  => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                'color' => '#0891b2',
                'bg'    => '#ecfeff',
            ],
        ];
    }
}
