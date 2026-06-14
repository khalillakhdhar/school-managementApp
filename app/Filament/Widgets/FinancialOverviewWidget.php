<?php
namespace App\Filament\Widgets;

use App\Models\Expense;
use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinancialOverviewWidget extends StatsOverviewWidget
{
    protected static bool $isLazy = true;
    protected static ?int $sort = 2;

    public static function canView(): bool { return false; }

    protected function getStats(): array
    {
        $revenue = (float) Payment::where('status', 'paid')
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)->sum('amount');

        $expenses = (float) Expense::whereMonth('date', now()->month)
            ->whereYear('date', now()->year)->sum('amount');

        $overdueCount = Payment::where('status', 'pending')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now())->count();

        $overdueAmount = (float) Payment::where('status', 'pending')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now())->sum('amount');

        $net = $revenue - $expenses;

        $revenueTrend = collect(range(5, 0))->map(fn ($i) =>
            (float) Payment::where('status', 'paid')
                ->whereYear('payment_date', now()->subMonths($i)->year)
                ->whereMonth('payment_date', now()->subMonths($i)->month)->sum('amount')
        )->toArray();

        return [
            Stat::make(__('Revenue This Month'), number_format($revenue, 3) . ' TND')
                ->description(($net >= 0 ? '+' : '') . number_format($net, 3) . ' TND ' . __('net'))
                ->descriptionIcon($net >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($net >= 0 ? 'success' : 'danger')
                ->chart($revenueTrend),

            Stat::make(__('Expenses This Month'), number_format($expenses, 3) . ' TND')
                ->description(($revenue > 0 ? round($expenses / $revenue * 100) : 0) . '% ' . __('of revenue'))
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('warning'),

            Stat::make(__('Overdue Payments'), number_format($overdueAmount, 3) . ' TND')
                ->description($overdueCount . ' ' . __('payments'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($overdueAmount > 0 ? 'danger' : 'success'),
        ];
    }
}
