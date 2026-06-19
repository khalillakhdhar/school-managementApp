<?php
namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use App\Filament\Widgets\PaymentsListStatsWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label(__('Enregistrer un paiement'))];
    }

    protected function getHeaderWidgets(): array
    {
        return [PaymentsListStatsWidget::class];
    }
}
