<?php

namespace App\Observers;

use App\Models\Payroll;
use App\Support\Audit;
use Filament\Notifications\Notification;

class PayrollObserver
{
    public function updated(Payroll $payroll): void
    {
        if (! Audit::$enabled) {
            return;
        }

        // Notifier l'employé quand sa fiche passe à "finalisée" ou "payée".
        if (! $payroll->wasChanged('status')) {
            return;
        }
        if (! in_array($payroll->status, ['finalized', 'paid'], true)) {
            return;
        }

        $user = $payroll->employee?->user;
        if (! $user) {
            return;
        }

        $months = [1 => 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
            'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
        $period = ($months[$payroll->month] ?? $payroll->month) . ' ' . $payroll->year;

        $user->notifyNow(
            Notification::make()
                ->title($payroll->status === 'paid' ? 'Votre salaire a été versé' : 'Votre fiche de paie est prête')
                ->body($period . ' — Net : ' . number_format((float) $payroll->net_salary, 3) . ' TND')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->toDatabase()
        );
    }
}
