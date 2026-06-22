<?php
namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Payroll;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EmployeesListStatsWidget extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        return request()->routeIs('filament.admin.resources.employees.index');
    }

    protected function getStats(): array
    {
        $total     = Employee::count();
        $active    = Employee::where('is_active', true)->count();
        $teachers  = Employee::where('is_teacher', true)->where('is_active', true)->count();

        // ── Présences du jour ──────────────────────────────────────────────
        $today        = now()->toDateString();
        $presentToday = Attendance::whereDate('date', $today)
            ->whereIn('status', ['present', 'late'])->count();
        $absentToday  = Attendance::whereDate('date', $today)
            ->where('status', 'absent')->count();
        $notMarked    = max(0, $active - Attendance::whereDate('date', $today)->count());

        // ── Coût de la paie (mois en cours) ────────────────────────────────
        $month         = now()->month;
        $year          = now()->year;
        $payrollCost   = (float) Payroll::whereMonth('period_from', $month)
            ->whereYear('period_from', $year)->sum('net_salary');
        $pendingPayroll = Payroll::whereIn('status', ['draft', 'finalized'])->count();

        return [
            Stat::make(__('Total employés'), $total)
                ->description(__(':active actif(s) · :teachers enseignant(s)', ['active' => $active, 'teachers' => $teachers]))
                ->descriptionIcon('heroicon-m-identification')
                ->color('primary'),

            Stat::make(__("Présents aujourd'hui"), $presentToday)
                ->description($notMarked > 0 ? __(':n non pointé(s)', ['n' => $notMarked]) : __('Tous pointés'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__("Absents aujourd'hui"), $absentToday)
                ->description($absentToday > 0 ? __('Absence(s) signalée(s)') : __('Aucune absence'))
                ->descriptionIcon('heroicon-m-x-circle')
                ->color($absentToday > 0 ? 'danger' : 'gray'),

            Stat::make(__('Coût paie ce mois'), number_format($payrollCost, 3).' TND')
                ->description($pendingPayroll > 0 ? __(':n fiche(s) en attente', ['n' => $pendingPayroll]) : __('Paie à jour'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($pendingPayroll > 0 ? 'warning' : 'success'),
        ];
    }
}
