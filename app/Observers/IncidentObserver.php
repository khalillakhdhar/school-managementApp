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

        $notification = Notification::make()
            ->title('Nouvel incident signalé')
            ->body($incident->title . ' — ' . $studentName)
            ->icon('heroicon-o-exclamation-triangle')
            ->color($severityColor)
            ->toDatabase();

        // notifyNow : persistance immédiate (la DatabaseNotification est ShouldQueue,
        // sendToDatabase la mettrait en file et nécessiterait un worker).
        foreach (User::where('role', 'admin')->get() as $admin) {
            $admin->notifyNow($notification);
        }
    }
}
