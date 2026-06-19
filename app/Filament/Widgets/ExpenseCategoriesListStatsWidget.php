<?php
namespace App\Filament\Widgets;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ExpenseCategoriesListStatsWidget extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        return request()->routeIs('filament.admin.resources.expense-categories.index');
    }

    protected function getStats(): array
    {
        $total     = ExpenseCategory::count();
        $used      = ExpenseCategory::has('expenses')->count();
        $year      = now()->year;
        $spentYear = (float) Expense::whereYear('date', $year)->sum('amount');

        $topCategory = ExpenseCategory::withSum(['expenses as total' => fn ($q) => $q->whereYear('date', $year)], 'amount')
            ->orderByDesc('total')->first();

        return [
            Stat::make('Catégories', $total)
                ->description("{$used} utilisée(s)")
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('primary'),

            Stat::make('Dépenses '.$year, number_format($spentYear, 3).' TND')
                ->description(__('Toutes catégories confondues'))
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),

            Stat::make('Poste principal', $topCategory?->name ?? '—')
                ->description($topCategory && $topCategory->total ? number_format((float) $topCategory->total, 3).' TND' : 'Aucune dépense')
                ->descriptionIcon('heroicon-m-chart-pie')
                ->color('warning'),

            Stat::make('Catégories vides', $total - $used)
                ->description(($total - $used) > 0 ? 'Sans dépense enregistrée' : 'Toutes utilisées')
                ->descriptionIcon('heroicon-m-inbox')
                ->color($total - $used > 0 ? 'gray' : 'success'),
        ];
    }
}
