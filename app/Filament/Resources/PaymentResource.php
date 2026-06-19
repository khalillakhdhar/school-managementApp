<?php
namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use App\Models\Student;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';
    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('Finances');
    }

    public static function getNavigationLabel(): string
    {
        return __('Payments');
    }

    public static function getModelLabel(): string
    {
        return __('Payment');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Payments');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Payment::where('status', 'pending')
            ->whereDate('due_date', '<', now())->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Paiement'))
                ->description(__('Informations du paiement à enregistrer'))
                ->icon('heroicon-o-banknotes')
                ->schema([
                    Forms\Components\Select::make('student_id')
                        ->label(__('Élève'))
                        ->options(
                            Student::orderBy('last_name')->get()
                                ->mapWithKeys(fn ($s) => [$s->id => $s->full_name])
                        )
                        ->searchable()->required()->placeholder(__('Rechercher un élève...')),
                    Forms\Components\TextInput::make('amount')
                        ->label(__('Montant'))->required()->numeric()->prefix('TND')
                        ->minValue(0),
                    Forms\Components\DatePicker::make('payment_date')
                        ->label(__('Date du paiement'))->required()->default(now())->displayFormat('d/m/Y'),
                    Forms\Components\DatePicker::make('due_date')
                        ->label('Date d\'échéance')->nullable()->displayFormat('d/m/Y'),
                    Forms\Components\Select::make('payment_method')
                        ->label(__('Mode de paiement'))
                        ->options([
                            'cash'          => 'Espèces',
                            'bank_transfer' => 'Virement bancaire',
                            'cheque'        => 'Chèque',
                            'app'           => 'Application',
                        ])
                        ->required()->default('cash'),
                    Forms\Components\Select::make('status')
                        ->label(__('Statut'))
                        ->options([
                            'paid'      => 'Payé',
                            'pending'   => 'En attente',
                            'failed'    => 'Échoué',
                            'cancelled' => 'Annulé',
                        ])
                        ->required()->default('paid'),
                    Forms\Components\TextInput::make('reference_number')
                        ->label(__('Référence / N° reçu'))->maxLength(255),
                    Forms\Components\Textarea::make('notes')
                        ->label(__('Notes'))->columnSpanFull()->rows(2),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('student.first_name')
                    ->label(__('Élève'))
                    ->formatStateUsing(fn ($state, Payment $record): string => $record->student?->full_name ?? '—')
                    ->searchable(['students.first_name', 'students.last_name'])
                    ->sortable()
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold),
                Tables\Columns\TextColumn::make('amount')
                    ->label(__('Montant'))
                    ->money('TND')->sortable()
                    ->weight(\Filament\Support\Enums\FontWeight::Bold),
                Tables\Columns\TextColumn::make('payment_date')
                    ->label(__('Date paiement'))->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->label(__('Échéance'))->date('d/m/Y')->sortable()
                    ->color(fn ($state, Payment $record) => $record->status === 'pending' && $state && $state < now() ? 'danger' : null)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label(__('Mode'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cash'          => 'success',
                        'bank_transfer' => 'primary',
                        'cheque'        => 'warning',
                        'app'           => 'info',
                        default         => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cash'          => 'Espèces',
                        'bank_transfer' => 'Virement',
                        'cheque'        => 'Chèque',
                        'app'           => 'Application',
                        default         => $state,
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('Statut'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid'      => 'success',
                        'pending'   => 'warning',
                        'failed'    => 'danger',
                        'cancelled' => 'gray',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'paid'      => 'Payé',
                        'pending'   => 'En attente',
                        'failed'    => 'Échoué',
                        'cancelled' => 'Annulé',
                        default     => $state,
                    }),
                Tables\Columns\IconColumn::make('is_verified')
                    ->label(__('Vérifié'))
                    ->boolean()
                    ->trueIcon('heroicon-o-shield-check')
                    ->falseIcon('heroicon-o-shield-exclamation')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn (Payment $record) => $record->is_verified && $record->verified_at
                        ? 'Validé le ' . $record->verified_at->format('d/m/Y') . ($record->verifier ? ' par ' . $record->verifier->name : '')
                        : 'En attente de validation comptable'),
                Tables\Columns\TextColumn::make('reference_number')
                    ->label(__('Référence'))->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('payment_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('Statut'))
                    ->options([
                        'paid'      => 'Payé',
                        'pending'   => 'En attente',
                        'failed'    => 'Échoué',
                        'cancelled' => 'Annulé',
                    ]),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label(__('Mode de paiement'))
                    ->options([
                        'cash'          => 'Espèces',
                        'bank_transfer' => 'Virement',
                        'cheque'        => 'Chèque',
                        'app'           => 'Application',
                    ]),
                Tables\Filters\TernaryFilter::make('is_verified')
                    ->label(__('Validation comptable'))
                    ->placeholder(__('Tous'))
                    ->trueLabel('Validés')
                    ->falseLabel('À valider'),
            ])
            ->emptyStateIcon('heroicon-o-banknotes')
            ->emptyStateHeading('Aucun paiement enregistré')
            ->emptyStateDescription('Les paiements des élèves apparaîtront ici une fois enregistrés.')
            ->actions([
                Actions\Action::make('mark_paid')
                    ->label(__('Mark Paid'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Payment $record) => app(\App\Services\PaymentService::class)->markPaid($record))
                    ->visible(fn (Payment $record): bool => $record->status === 'pending'),
                // Validation comptable (séparation saisie / contrôle)
                Actions\Action::make('verify')
                    ->label(__('Valider (comptable)'))
                    ->icon('heroicon-o-shield-check')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading(__('Valider ce paiement'))
                    ->modalDescription(__('Confirme que ce paiement encaissé a été contrôlé et rapproché.'))
                    ->action(fn (Payment $record) => app(\App\Services\PaymentService::class)->verify($record))
                    ->visible(fn (Payment $record): bool => $record->status === 'paid' && ! $record->is_verified),
                Actions\Action::make('unverify')
                    ->label(__('Annuler la validation'))
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(fn (Payment $record) => app(\App\Services\PaymentService::class)->unverify($record))
                    ->visible(fn (Payment $record): bool => $record->is_verified),
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit'   => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
