<?php
namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PaymentsListStatsWidget extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        return request()->routeIs('filament.admin.resources.payments.index');
    }

    protected function getStats(): array
    {
        $now   = now();
        $month = $now->month;
        $year  = $now->year;

        $revenueMonth = (float) Payment::where('status', 'paid')
            ->whereMonth('payment_date', $month)->whereYear('payment_date', $year)->sum('amount');

        $revenueYear = (float) Payment::where('status', 'paid')
            ->whereYear('payment_date', $year)->sum('amount');

        $overdueTotal = (float) Payment::where('status', 'pending')
            ->whereNotNull('due_date')->whereDate('due_date', '<', $now)->sum('amount');
        $overdueCount = Payment::where('status', 'pending')
            ->whereNotNull('due_date')->whereDate('due_date', '<', $now)->count();

        $invoiced    = (float) Payment::whereYear('due_date', $year)->sum('amount');
        $collectRate = $invoiced > 0 ? round($revenueYear / $invoiced * 100, 1) : 0;

        $prevMonth  = $now->copy()->subMonth();
        $revenuePrev = (float) Payment::where('status', 'paid')
            ->whereMonth('payment_date', $prevMonth->month)
            ->whereYear('payment_date', $prevMonth->year)->sum('amount');
        $trend = $revenuePrev > 0 ? round(($revenueMonth - $revenuePrev) / $revenuePrev * 100, 1) : 0;

        $revenueChart = collect(range(5, 0))->map(fn ($i) =>
            (float) Payment::where('status', 'paid')
                ->whereMonth('payment_date', $now->copy()->subMonths($i)->month)
                ->whereYear('payment_date', $now->copy()->subMonths($i)->year)->sum('amount')
        )->toArray();

        return [
            Stat::make('Recettes ce mois', number_format($revenueMonth, 3).' TND')
                ->description(($trend >= 0 ? '+' : '').$trend.'% vs mois précédent')
                ->descriptionIcon($trend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($trend >= 0 ? 'success' : 'warning')
                ->chart($revenueChart),

            Stat::make('Recettes '.$year, number_format($revenueYear, 3).' TND')
                ->description('Total encaissé cette année')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),

            Stat::make('Impayés en retard', number_format($overdueTotal, 3).' TND')
                ->description($overdueCount.' paiement(s) échu(s)')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($overdueCount > 0 ? 'danger' : 'success'),

            Stat::make('Taux de recouvrement', $collectRate.'%')
                ->description(number_format($invoiced, 3).' TND facturé')
                ->descriptionIcon('heroicon-m-chart-pie')
                ->color($collectRate >= 80 ? 'success' : ($collectRate >= 50 ? 'warning' : 'danger')),
        ];
    }
}
