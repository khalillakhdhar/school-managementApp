<?php

namespace App\Observers;

use App\Models\Incident;
use App\Models\User;
use App\Support\Audit;
use Filament\Notifications\Notification;

class IncidentObserver
{
    public function created(Incident $incident): void
    {
        // Pas de notifications pendant les imports/seed massifs.
        if (! Audit::$enabled) {
            return;
        }

        $severityColor = match ($incident->severity) {
            'high'   => 'danger',
            'medium' => 'warning',
            default  => 'info',
        };
        $studentName = $incident->student?->full_name ?? 'Élève';

        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            Notification::make()
                ->title('Nouvel incident signalé')
                ->body($incident->title . ' — ' . $studentName)
                ->icon('heroicon-o-exclamation-triangle')
                ->color($severityColor)
                ->sendToDatabase($admin);
        }
    }
}
