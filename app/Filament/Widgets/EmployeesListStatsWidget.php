<?php
namespace App\Filament\Widgets;

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
        $contractors = Employee::where('contract_type', 'contract')->count();

        $pendingPayroll = Payroll::whereIn('status', ['draft', 'finalized'])->count();
        $pendingAmount  = (float) Payroll::whereIn('status', ['draft', 'finalized'])->sum('net_salary');

        $avgSalary = round((float) Employee::where('is_active', true)
            ->where('contract_type', '!=', 'contract')
            ->avg('salary_base') ?? 0, 3);

        return [
            Stat::make('Total employés', $total)
                ->description("{$active} actifs")
                ->descriptionIcon('heroicon-m-identification')
                ->color('primary'),

            Stat::make('Enseignants actifs', $teachers)
                ->description($total > 0 ? round($teachers / $total * 100).'% du personnel' : '—')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('info'),

            Stat::make('Salaire moyen', number_format($avgSalary, 3).' TND')
                ->description('Employés à salaire fixe')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Fiches paie en attente', $pendingPayroll)
                ->description(number_format($pendingAmount, 3).' TND à verser')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingPayroll > 0 ? 'warning' : 'success'),
        ];
    }
}
