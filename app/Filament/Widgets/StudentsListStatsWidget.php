<?php
namespace App\Filament\Widgets;

use App\Models\Student;
use App\Models\StudentAttendance;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StudentsListStatsWidget extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        return request()->routeIs('filament.admin.resources.students.index');
    }

    protected function getStats(): array
    {
        $total   = Student::count();
        $active  = Student::where('status', 'active')->count();
        $inactive = Student::where('status', 'inactive')->count();
        $graduated = Student::where('status', 'graduated')->count();
        $newMonth = Student::where('status', 'active')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $suspended = Student::where('status', 'suspended')->count();

        $totalAtt   = StudentAttendance::whereMonth('date', now()->month)->whereYear('date', now()->year)->count();
        $presentAtt = StudentAttendance::whereMonth('date', now()->month)->whereYear('date', now()->year)
            ->whereIn('status', ['present', 'late'])->count();
        $attendanceRate = $totalAtt > 0 ? round($presentAtt / $totalAtt * 100, 1) : 0;

        $trend = collect(range(5, 0))->map(fn ($i) =>
            Student::where('status', 'active')
                ->where('created_at', '<=', now()->subMonths($i)->endOfMonth())
                ->count()
        )->toArray();

        return [
            Stat::make(__('Total élèves'), $total)
                ->description(__(':active actifs · :inactive inactifs', ['active' => $active, 'inactive' => $inactive]))
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('primary')
                ->chart($trend),

            Stat::make(__('Élèves actifs'), $active)
                ->description($total > 0 ? __(':pct% du total', ['pct' => round($active / $total * 100)]) : '—')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('Nouveaux ce mois'), $newMonth)
                ->description(ucfirst(now()->locale(app()->getLocale())->isoFormat('MMMM YYYY')))
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('info'),

            Stat::make(__('Taux de présence'), $attendanceRate.'%')
                ->description(__(':n présences saisies ce mois', ['n' => $totalAtt]))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($attendanceRate >= 80 ? 'success' : ($attendanceRate >= 60 ? 'warning' : 'danger')),
        ];
    }
}
