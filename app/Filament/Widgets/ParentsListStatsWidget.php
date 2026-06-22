<?php
namespace App\Filament\Widgets;

use App\Models\SchoolParent;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ParentsListStatsWidget extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        return request()->routeIs('filament.admin.resources.parents.index');
    }

    protected function getStats(): array
    {
        $total      = SchoolParent::count();
        $payers     = SchoolParent::where('is_payer', true)->count();
        $withAccount = SchoolParent::whereNotNull('user_id')->count();
        $withoutAccount = $total - $withAccount;
        $linkedChildren = SchoolParent::has('students')->count();

        return [
            Stat::make(__('Total parents'), $total)
                ->description(__(':n avec enfant(s) lié(s)', ['n' => $linkedChildren]))
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make(__('Responsables payeurs'), $payers)
                ->description($total > 0 ? __(':pct% des parents', ['pct' => round($payers / $total * 100)]) : '—')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make(__('Comptes portail actifs'), $withAccount)
                ->description($total > 0 ? __(':pct% connectés', ['pct' => round($withAccount / $total * 100)]) : '—')
                ->descriptionIcon('heroicon-m-key')
                ->color('info'),

            Stat::make(__('Sans accès portail'), $withoutAccount)
                ->description($withoutAccount > 0 ? __('Inviter au portail parents') : __('Tous ont un accès'))
                ->descriptionIcon('heroicon-m-user-plus')
                ->color($withoutAccount > 0 ? 'warning' : 'gray'),
        ];
    }
}
