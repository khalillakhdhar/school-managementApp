<?php
namespace App\Filament\Resources\StudentAttendanceResource\Pages;

use App\Filament\Resources\StudentAttendanceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStudentAttendance extends EditRecord
{
    protected static string $resource = StudentAttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
