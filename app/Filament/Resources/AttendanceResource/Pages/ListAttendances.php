<?php
namespace App\Filament\Resources\AttendanceResource\Pages;

use App\Filament\Resources\AttendanceResource;
use App\Filament\Widgets\AttendancesListStatsWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Pointer un employé')];
    }

    protected function getHeaderWidgets(): array
    {
        return [AttendancesListStatsWidget::class];
    }
}
