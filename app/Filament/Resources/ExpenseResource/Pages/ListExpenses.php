<?php
namespace App\Filament\Resources\ExpenseResource\Pages;

use App\Filament\Resources\ExpenseResource;
use App\Filament\Widgets\ExpensesListStatsWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExpenses extends ListRecords
{
    protected static string $resource = ExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label(__('Ajouter une dépense'))];
    }

    protected function getHeaderWidgets(): array
    {
        return [ExpensesListStatsWidget::class];
    }
}
