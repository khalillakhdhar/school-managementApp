<?php
namespace App\Filament\Resources\StudentResource\RelationManagers;

use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';
    protected static ?string $title       = 'Paiements';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('amount')
                ->label(__('Montant (TND)'))->numeric()->required()->minValue(0)->prefix('TND'),
            Forms\Components\Select::make('status')
                ->label(__('Statut'))
                ->options(['pending' => 'En attente', 'paid' => 'Payé', 'cancelled' => 'Annulé'])
                ->default('pending')->required(),
            Forms\Components\DatePicker::make('due_date')
                ->label('Date d\'échéance')->displayFormat('d/m/Y'),
            Forms\Components\DatePicker::make('payment_date')
                ->label(__('Date de paiement'))->displayFormat('d/m/Y'),
            Forms\Components\Select::make('payment_method')
                ->label(__('Mode de paiement'))
                ->options([
                    'cash'          => 'Espèces',
                    'bank_transfer' => 'Virement bancaire',
                    'check'         => 'Chèque',
                    'card'          => 'Carte bancaire',
                ])
                ->nullable(),
            Forms\Components\TextInput::make('reference_number')
                ->label(__('Référence'))->maxLength(100),
            Forms\Components\Textarea::make('notes')
                ->label(__('Notes'))->rows(2)->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reference_number')
            ->defaultSort('due_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('amount')
                    ->label(__('Montant'))->money('TND')
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('Statut'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid'      => 'success',
                        'pending'   => 'warning',
                        'cancelled' => 'danger',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'paid'      => 'Payé',
                        'pending'   => 'En attente',
                        'cancelled' => 'Annulé',
                        default     => $state,
                    }),
                Tables\Columns\TextColumn::make('due_date')
                    ->label(__('Échéance'))->date('d/m/Y')
                    ->color(fn ($record): string =>
                        $record->status === 'pending' && $record->due_date?->isPast() ? 'danger' : 'gray'
                    ),
                Tables\Columns\TextColumn::make('payment_date')
                    ->label(__('Payé le'))->date('d/m/Y'),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label(__('Mode'))
                    ->formatStateUsing(fn ($state): string => match ($state) {
                        'cash'          => 'Espèces',
                        'bank_transfer' => 'Virement',
                        'check'         => 'Chèque',
                        'card'          => 'Carte',
                        default         => $state ?? '—',
                    })
                    ->badge()->color('primary'),
                Tables\Columns\TextColumn::make('reference_number')
                    ->label(__('Référence'))->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([Tables\Actions\CreateAction::make()->label(__('Enregistrer un paiement'))])
            ->actions([
                Tables\Actions\Action::make('mark_paid')
                    ->label(__('Marquer payé'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record): bool => $record->status === 'pending')
                    ->action(function ($record): void {
                        $record->update(['status' => 'paid', 'payment_date' => now()]);
                        Notification::make()->title(__('Paiement marqué comme payé'))->success()->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }
}
