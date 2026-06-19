<?php
namespace App\Filament\Resources\PayrollResource\Pages;

use App\Filament\Resources\PayrollResource;
use App\Filament\Widgets\PayrollsListStatsWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPayrolls extends ListRecords
{
    protected static string $resource = PayrollResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label(__('Générer une fiche de paie'))];
    }

    protected function getHeaderWidgets(): array
    {
        return [PayrollsListStatsWidget::class];
    }
}
