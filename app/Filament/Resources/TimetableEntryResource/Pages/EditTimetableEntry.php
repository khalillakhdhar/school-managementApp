<?php
namespace App\Filament\Resources\TimetableEntryResource\Pages;

use App\Filament\Resources\TimetableEntryResource;
use App\Models\TimetableEntry;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditTimetableEntry extends EditRecord
{
    protected static string $resource = TimetableEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function beforeSave(): void
    {
        $this->checkConflicts();
    }

    private function checkConflicts(): void
    {
        $data    = $this->form->getState();
        $recordId = $this->record->id;

        $classroomConflict = TimetableEntry::where('classroom_id', $data['classroom_id'])
            ->where('day_of_week', $data['day_of_week'])
            ->where('start_time', '<', $data['end_time'])
            ->where('end_time', '>', $data['start_time'])
            ->where('id', '!=', $recordId)
            ->exists();

        if ($classroomConflict) {
            Notification::make()->danger()
                ->title('Conflit de salle de classe')
                ->body('Cette classe a déjà une séance sur ce créneau.')
                ->persistent()->send();
            $this->halt();
        }

        if (!empty($data['employee_id'])) {
            $teacherConflict = TimetableEntry::where('employee_id', $data['employee_id'])
                ->where('day_of_week', $data['day_of_week'])
                ->where('start_time', '<', $data['end_time'])
                ->where('end_time', '>', $data['start_time'])
                ->where('id', '!=', $recordId)
                ->exists();

            if ($teacherConflict) {
                Notification::make()->danger()
                    ->title('Conflit d\'enseignant')
                    ->body('Cet enseignant est déjà occupé sur ce créneau.')
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
