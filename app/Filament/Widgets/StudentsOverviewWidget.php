<?php
namespace App\Filament\Widgets;

use App\Models\Student;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StudentsOverviewWidget extends StatsOverviewWidget
{
    protected static bool $isLazy = true;
    protected static ?int $sort = 1;

    public static function canView(): bool { return false; }

    protected function getStats(): array
    {
        $total   = Student::count();
        $active  = Student::where('status', 'active')->count();
        $newThis = Student::where('status', 'active')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)->count();

        $trend = collect(range(5, 0))->map(fn ($i) =>
            Student::whereMonth('created_at', now()->subMonths($i)->month)
                ->whereYear('created_at', now()->subMonths($i)->year)->count()
        )->toArray();

        return [
            Stat::make(__('Total Students'), $total)
                ->description("{$active} " . __('active'))
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('primary')
                ->chart($trend),

            Stat::make(__('Active Students'), $active)
                ->description($total > 0 ? round($active / $total * 100) . '% ' . __('of total') : '—')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('New This Month'), $newThis)
                ->description(ucfirst(now()->locale(app()->getLocale())->isoFormat('MMMM YYYY')))
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('info'),
        ];
    }
}
