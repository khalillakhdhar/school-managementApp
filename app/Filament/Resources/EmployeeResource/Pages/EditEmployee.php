<?php
namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use App\Models\Classroom;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmployee extends EditRecord
{
    protected static string $resource = EmployeeResource::class;

    /** Stash classroom IDs between mutate and afterSave (not fillable on Employee). */
    private array $classroomIds = [];

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['classroom_ids'] = $this->record->classrooms()->pluck('id')->toArray();
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->classroomIds = $data['classroom_ids'] ?? [];
        unset($data['classroom_ids']);
        return $data;
    }

    protected function afterSave(): void
    {
        // Unassign this teacher from all classrooms, then re-assign selected ones
        $this->record->classrooms()->update(['teacher_id' => null]);

        if (!empty($this->classroomIds)) {
            Classroom::whereIn('id', $this->classroomIds)
                ->update(['teacher_id' => $this->record->id]);
        }
    }
}
