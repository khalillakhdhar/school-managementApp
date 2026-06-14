<?php
namespace App\Filament\Resources\ServiceResource\Pages;

use App\Filament\Resources\ServiceResource;
use App\Filament\Widgets\ServicesListStatsWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListServices extends ListRecords
{
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Créer un service')];
    }

    protected function getHeaderWidgets(): array
    {
        return [ServicesListStatsWidget::class];
    }
}
