<?php
namespace App\Filament\Resources\SubjectResource\Pages;

use App\Filament\Resources\SubjectResource;
use App\Filament\Widgets\SubjectsListStatsWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSubjects extends ListRecords
{
    protected static string $resource = SubjectResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Créer une matière')];
    }

    protected function getHeaderWidgets(): array
    {
        return [SubjectsListStatsWidget::class];
    }
}
