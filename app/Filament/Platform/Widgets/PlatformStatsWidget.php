<?php

namespace App\Filament\Platform\Widgets;

use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * PHASE 7 — platform-wide overview for the SaaS operator. Counts span every
 * tenant (School is not tenant-scoped; Student is queried without the school
 * scope on purpose to total across all schools).
 */
class PlatformStatsWidget extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $total     = School::count();
        $active    = School::where('status', School::STATUS_ACTIVE)->count();
        $trial     = School::where('status', School::STATUS_TRIAL)->count();
        $suspended = School::where('status', School::STATUS_SUSPENDED)->count();

        // Across all tenants (bypass the per-tenant scope explicitly).
        $students = Student::withoutGlobalScope('school')->count();
        $users    = User::where('role', '!=', 'platform_admin')->count();

        return [
            Stat::make(__('Écoles'), $total)
                ->description(__(':a active(s) · :t essai · :s suspendue(s)', ['a' => $active, 't' => $trial, 's' => $suspended]))
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('primary'),

            Stat::make(__('Élèves (plateforme)'), $students)
                ->description(__('Toutes écoles confondues'))
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make(__('Utilisateurs'), $users)
                ->description(__('Admins, enseignants, parents'))
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),
        ];
    }
}
