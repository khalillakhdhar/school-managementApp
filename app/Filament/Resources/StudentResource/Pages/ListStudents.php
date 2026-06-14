<?php
namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use App\Filament\Widgets\StudentsListStatsWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Inscrire un élève')];
    }

    protected function getHeaderWidgets(): array
    {
        return [StudentsListStatsWidget::class];
    }
}
