<?php
namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use App\Models\Classroom;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    private array $classroomIds = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->classroomIds = $data['classroom_ids'] ?? [];
        unset($data['classroom_ids']);
        return $data;
    }

    protected function afterCreate(): void
    {
        if (!empty($this->classroomIds)) {
            Classroom::whereIn('id', $this->classroomIds)
                ->update(['teacher_id' => $this->record->id]);
        }
    }
}
