<?php

namespace App\Filament\Platform\Widgets;

use App\Models\Payment;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * PHASE 7 — platform-wide overview for the SaaS operator. Counts span every
 * tenant (School is not tenant-scoped; tenant-owned models are queried without
 * the school scope on purpose to total across all schools).
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
        $revenue  = (float) Payment::withoutGlobalScope('school')->where('status', 'paid')->sum('amount');

        // Trials ending within the next 7 days.
        $trialsEnding = School::where('status', School::STATUS_TRIAL)
            ->whereNotNull('trial_ends_at')
            ->whereBetween('trial_ends_at', [now(), now()->addDays(7)])
            ->count();

        return [
            Stat::make(__('Écoles'), $total)
                ->description(__(':a active(s) · :t essai · :s suspendue(s)', ['a' => $active, 't' => $trial, 's' => $suspended]))
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('primary'),

            Stat::make(__('Élèves (plateforme)'), number_format($students, 0, ',', ' '))
                ->description(__('Toutes écoles confondues'))
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make(__('Utilisateurs'), number_format($users, 0, ',', ' '))
                ->description(__('Admins, enseignants, parents'))
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),

            Stat::make(__('Encaissements'), number_format($revenue, 0, ',', ' ') . ' TND')
                ->description(__('Total des paiements réglés'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make(__('Essais à échéance'), $trialsEnding)
                ->description(__('Fin d\'essai sous 7 jours'))
                ->descriptionIcon('heroicon-m-clock')
                ->color($trialsEnding > 0 ? 'warning' : 'gray'),
        ];
    }
}
