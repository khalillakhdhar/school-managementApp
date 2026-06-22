<?php
namespace App\Filament\Widgets;

use App\Models\Payroll;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PayrollsListStatsWidget extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        return request()->routeIs('filament.admin.resources.payrolls.index');
    }

    protected function getStats(): array
    {
        $month = now()->month;
        $year  = now()->year;

        $netThisMonth = (float) Payroll::whereMonth('period_from', $month)
            ->whereYear('period_from', $year)->sum('net_salary');
        $netYear = (float) Payroll::whereYear('period_from', $year)->sum('net_salary');

        $pending = Payroll::whereIn('status', ['draft', 'finalized'])->count();
        $pendingAmount = (float) Payroll::whereIn('status', ['draft', 'finalized'])->sum('net_salary');

        $paid = Payroll::where('status', 'paid')
            ->whereYear('period_from', $year)->count();

        $chargesPatronales = (float) Payroll::whereYear('period_from', $year)->sum('total_charge_patronale');

        return [
            Stat::make(__('Masse salariale :year', ['year' => $year]), number_format($netYear, 3).' TND')
                ->description(__('Net versé cette année'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),

            Stat::make(__('À verser'), number_format($pendingAmount, 3).' TND')
                ->description(__(':n fiche(s) en attente', ['n' => $pending]))
                ->descriptionIcon('heroicon-m-clock')
                ->color($pending > 0 ? 'warning' : 'success'),

            Stat::make(__('Charges patronales :year', ['year' => $year]), number_format($chargesPatronales, 3).' TND')
                ->description(__('CNSS + FOPROLOS employeur'))
                ->descriptionIcon('heroicon-m-building-library')
                ->color('info'),

            Stat::make(__('Fiches payées'), $paid)
                ->description(__('Réglées cette année'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
