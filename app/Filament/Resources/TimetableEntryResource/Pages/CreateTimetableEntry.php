<?php
namespace App\Filament\Resources\TimetableEntryResource\Pages;

use App\Filament\Resources\TimetableEntryResource;
use App\Models\TimetableEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateTimetableEntry extends CreateRecord
{
    protected static string $resource = TimetableEntryResource::class;

    protected function beforeCreate(): void
    {
        $this->checkConflicts();
    }

    private function checkConflicts(): void
    {
        $data = $this->form->getState();

        $classroomConflict = TimetableEntry::where('classroom_id', $data['classroom_id'])
            ->where('day_of_week', $data['day_of_week'])
            ->where('start_time', '<', $data['end_time'])
            ->where('end_time', '>', $data['start_time'])
            ->exists();

        if ($classroomConflict) {
            Notification::make()->danger()
                ->title(__('Conflit de salle de classe'))
                ->body(__('Cette classe a déjà une séance sur ce créneau.'))
                ->persistent()->send();
            $this->halt();
        }

        if (!empty($data['employee_id'])) {
            $teacherConflict = TimetableEntry::where('employee_id', $data['employee_id'])
                ->where('day_of_week', $data['day_of_week'])
                ->where('start_time', '<', $data['end_time'])
                ->where('end_time', '>', $data['start_time'])
                ->exists();

            if ($teacherConflict) {
                Notification::make()->danger()
                    ->title(__('Conflit d\'enseignant'))
                    ->body(__('Cet enseignant est déjà occupé sur ce créneau.'))
                    ->persistent()->send();
                $this->halt();
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
