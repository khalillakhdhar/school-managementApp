<?php
namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\Employee;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AttendancesListStatsWidget extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        return request()->routeIs('filament.admin.resources.attendances.index');
    }

    protected function getStats(): array
    {
        $today    = now()->toDateString();
        $totalEmp = Employee::where('is_active', true)->count();

        $presentToday = Attendance::whereDate('date', $today)
            ->whereIn('status', ['present', 'late'])->count();
        $absentToday  = Attendance::whereDate('date', $today)
            ->where('status', 'absent')->count();
        $notMarked    = max(0, $totalEmp - Attendance::whereDate('date', $today)->count());

        $month = now()->month;
        $year  = now()->year;
        $totalMonth   = Attendance::whereMonth('date', $month)->whereYear('date', $year)->count();
        $presentMonth = Attendance::whereMonth('date', $month)->whereYear('date', $year)
            ->whereIn('status', ['present', 'late'])->count();
        $rate = $totalMonth > 0 ? round($presentMonth / $totalMonth * 100, 1) : 0;

        return [
            Stat::make(__("Présents aujourd'hui"), $presentToday)
                ->description($totalEmp > 0 ? __('sur :n employés actifs', ['n' => $totalEmp]) : __('Aucun employé'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__("Absents aujourd'hui"), $absentToday)
                ->description($absentToday > 0 ? __('Absence(s) signalée(s)') : __('Aucune absence'))
                ->descriptionIcon('heroicon-m-x-circle')
                ->color($absentToday > 0 ? 'danger' : 'gray'),

            Stat::make(__('Non pointés'), $notMarked)
                ->description($notMarked > 0 ? __('En attente de pointage') : __('Tous pointés'))
                ->descriptionIcon('heroicon-m-question-mark-circle')
                ->color($notMarked > 0 ? 'warning' : 'success'),

            Stat::make(__('Taux de présence (mois)'), $rate.'%')
                ->description(__(':n pointages ce mois', ['n' => $totalMonth]))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($rate >= 90 ? 'success' : ($rate >= 75 ? 'warning' : 'danger')),
        ];
    }
}
