<?php
namespace App\Filament\Widgets;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ExpensesListStatsWidget extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        return request()->routeIs('filament.admin.resources.expenses.index');
    }

    protected function getStats(): array
    {
        $now   = now();
        $month = $now->month;
        $year  = $now->year;

        $monthTotal = (float) Expense::whereMonth('date', $month)->whereYear('date', $year)->sum('amount');
        $yearTotal  = (float) Expense::whereYear('date', $year)->sum('amount');
        $count      = Expense::whereMonth('date', $month)->whereYear('date', $year)->count();

        $prevMonthTotal = (float) Expense::whereMonth('date', $now->copy()->subMonth()->month)
            ->whereYear('date', $now->copy()->subMonth()->year)->sum('amount');
        $trend = $prevMonthTotal > 0 ? round(($monthTotal - $prevMonthTotal) / $prevMonthTotal * 100, 1) : 0;

        $topCategory = Expense::whereYear('date', $year)
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->with('category')
            ->first();
        $topCatName = $topCategory?->category?->name ?? '—';
        $topCatAmount = $topCategory ? number_format((float)$topCategory->total, 3) : '0';

        $expenseChart = collect(range(5, 0))->map(fn ($i) =>
            (float) Expense::whereMonth('date', $now->copy()->subMonths($i)->month)
                ->whereYear('date', $now->copy()->subMonths($i)->year)->sum('amount')
        )->toArray();

        return [
            Stat::make(__('Dépenses ce mois'), number_format($monthTotal, 3).' TND')
                ->description(__(':pct% vs mois précédent', ['pct' => ($trend >= 0 ? '+' : '').$trend]))
                ->descriptionIcon($trend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($trend >= 0 ? 'danger' : 'success')
                ->chart($expenseChart),

            Stat::make(__('Dépenses :year', ['year' => $year]), number_format($yearTotal, 3).' TND')
                ->description(__(':n opérations ce mois', ['n' => $count]))
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('warning'),

            Stat::make(__('Catégorie principale'), $topCatName)
                ->description(__(':amount TND cette année', ['amount' => $topCatAmount]))
                ->descriptionIcon('heroicon-m-tag')
                ->color('primary'),

            Stat::make(__('Opérations ce mois'), $count)
                ->description(ucfirst(now()->locale(app()->getLocale())->isoFormat('MMMM YYYY')))
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),
        ];
    }
}
