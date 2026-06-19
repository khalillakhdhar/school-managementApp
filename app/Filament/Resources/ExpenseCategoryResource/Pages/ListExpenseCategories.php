<?php
namespace App\Filament\Resources\ExpenseCategoryResource\Pages;

use App\Filament\Resources\ExpenseCategoryResource;
use App\Filament\Widgets\ExpenseCategoriesListStatsWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExpenseCategories extends ListRecords
{
    protected static string $resource = ExpenseCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label(__('Créer une catégorie'))];
    }

    protected function getHeaderWidgets(): array
    {
        return [ExpenseCategoriesListStatsWidget::class];
    }
}
