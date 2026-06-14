<?php
namespace App\Filament\Resources\LevelResource\Pages;

use App\Filament\Resources\LevelResource;
use App\Filament\Widgets\LevelsListStatsWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLevels extends ListRecords
{
    protected static string $resource = LevelResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Créer un niveau')];
    }

    protected function getHeaderWidgets(): array
    {
        return [LevelsListStatsWidget::class];
    }
}
