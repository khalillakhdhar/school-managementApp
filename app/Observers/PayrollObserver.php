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

        $months = [1 => 'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'];
        $period = __($months[$payroll->month] ?? (string) $payroll->month) . ' ' . $payroll->year;

        $user->notifyNow(
            Notification::make()
                ->title($payroll->status === 'paid' ? __('Votre salaire a été versé') : __('Votre fiche de paie est prête'))
                ->body(__(':period — Net : :amount TND', ['period' => $period, 'amount' => number_format((float) $payroll->net_salary, 3)]))
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->toDatabase()
        );
    }
}
