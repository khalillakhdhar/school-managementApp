<?php
namespace App\Filament\Resources\ParentResource\Pages;

use App\Filament\Resources\ParentResource;
use App\Filament\Widgets\ParentsListStatsWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListParents extends ListRecords
{
    protected static string $resource = ParentResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label(__('Ajouter un parent'))];
    }

    protected function getHeaderWidgets(): array
    {
        return [ParentsListStatsWidget::class];
    }
}
