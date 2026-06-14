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
            Stat::make('Total parents', $total)
                ->description("{$linkedChildren} avec enfant(s) lié(s)")
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Responsables payeurs', $payers)
                ->description($total > 0 ? round($payers / $total * 100).'% des parents' : '—')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Comptes portail actifs', $withAccount)
                ->description($total > 0 ? round($withAccount / $total * 100).'% connectés' : '—')
                ->descriptionIcon('heroicon-m-key')
                ->color('info'),

            Stat::make('Sans accès portail', $withoutAccount)
                ->description($withoutAccount > 0 ? 'Inviter au portail parents' : 'Tous ont un accès')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color($withoutAccount > 0 ? 'warning' : 'gray'),
        ];
    }
}
