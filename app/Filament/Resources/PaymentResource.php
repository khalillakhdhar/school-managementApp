<?php
namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';
    protected static string|\UnitEnum|null $navigationGroup = 'Finance';
    protected static ?string $modelLabel = 'Paiement';
    protected static ?string $pluralModelLabel = 'Paiements';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Select::make('student_id')
                ->label('Élève')
                ->relationship('student', 'first_name')
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                ->searchable()->preload()->required(),
            Forms\Components\TextInput::make('amount')->label('Montant')->required()->numeric()->prefix('TND'),
            Forms\Components\DatePicker::make('payment_date')->label('Date de paiement')->required()->default(now()),
            Forms\Components\Select::make('payment_method')
                ->label('Mode de paiement')
                ->options(['cash' => 'Espèces', 'bank_transfer' => 'Virement', 'cheque' => 'Chèque', 'app' => 'Application'])
                ->required()->default('cash'),
            Forms\Components\Select::make('status')
                ->label('Statut')
                ->options(['paid' => 'Payé', 'pending' => 'En attente', 'failed' => 'Échoué', 'cancelled' => 'Annulé'])
                ->required()->default('paid'),
            Forms\Components\TextInput::make('reference_number')->label('N° référence')->maxLength(255),
            Forms\Components\Textarea::make('notes')->label('Notes')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('student.first_name')->label('Élève')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('amount')->label('Montant')->money('TND')->sortable(),
                Tables\Columns\TextColumn::make('payment_date')->label('Date')->date()->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Mode')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cash' => 'success',
                        'bank_transfer' => 'primary',
                        'cheque' => 'warning',
                        'app' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cash' => 'Espèces',
                        'bank_transfer' => 'Virement',
                        'cheque' => 'Chèque',
                        'app' => 'Application',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        'cancelled' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'paid' => 'Payé',
                        'pending' => 'En attente',
                        'failed' => 'Échoué',
                        'cancelled' => 'Annulé',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('reference_number')->label('Référence')->toggleable(),
            ])
            ->defaultSort('payment_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options(['paid' => 'Payé', 'pending' => 'En attente', 'failed' => 'Échoué', 'cancelled' => 'Annulé']),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Mode')
                    ->options(['cash' => 'Espèces', 'bank_transfer' => 'Virement', 'cheque' => 'Chèque', 'app' => 'Application']),
            ])
            ->actions([Actions\EditAction::make(), Actions\DeleteAction::make()])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
