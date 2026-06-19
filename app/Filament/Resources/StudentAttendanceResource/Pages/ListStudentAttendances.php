<?php
namespace App\Filament\Resources\StudentAttendanceResource\Pages;

use App\Filament\Resources\StudentAttendanceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStudentAttendances extends ListRecords
{
    protected static string $resource = StudentAttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label(__('Ajouter une présence'))];
    }
}
