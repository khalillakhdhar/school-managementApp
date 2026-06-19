<?php
namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Models\Expense;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-trending-down';
    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string { return 'Finances'; }
    public static function getNavigationLabel(): string  { return __('Expenses'); }
    public static function getModelLabel(): string       { return __('Expense'); }
    public static function getPluralModelLabel(): string { return __('Expenses'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Détails de la dépense'))
                ->description(__('Catégorie, montant, fournisseur et justificatif de la dépense'))
                ->icon('heroicon-o-arrow-trending-down')
                ->schema([
                    Forms\Components\Select::make('category_id')
                        ->label(__('Catégorie'))
                        ->relationship('category', 'name')
                        ->required()->searchable()->preload()->createOptionForm([
                            Forms\Components\TextInput::make('name')->label(__('Nom de la catégorie'))->required(),
                        ]),
                    Forms\Components\DatePicker::make('date')
                        ->label(__('Date de la dépense'))
                        ->required()->default(now())->displayFormat('d/m/Y'),
                    Forms\Components\TextInput::make('amount')
                        ->label(__('Montant'))
                        ->required()->numeric()->minValue(0)->prefix('TND'),
                    Forms\Components\Select::make('payment_method')
                        ->label(__('Mode de règlement'))
                        ->options([
                            'cash'   => 'Espèces',
                            'bank'   => 'Virement bancaire',
                            'cheque' => 'Chèque',
                        ])
                        ->required()->default('cash'),
                    Forms\Components\TextInput::make('supplier')
                        ->label(__('Fournisseur / Prestataire'))
                        ->maxLength(255),
                    Forms\Components\TextInput::make('invoice_number')
                        ->label(__('N° facture / Reçu'))
                        ->maxLength(100),
                    Forms\Components\Textarea::make('description')
                        ->label(__('Description'))
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('notes')
                        ->label(__('Notes internes'))
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label(__('Date'))->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('Catégorie'))->searchable()->sortable()->badge()->color('warning'),
                Tables\Columns\TextColumn::make('description')
                    ->label(__('Description'))->limit(45)->searchable(),
                Tables\Columns\TextColumn::make('supplier')
                    ->label(__('Fournisseur'))->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label(__('N° facture'))->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label(__('Mode'))
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'cash'   => 'success',
                        'bank'   => 'info',
                        'cheque' => 'warning',
                        default  => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match($state) {
                        'cash'   => 'Espèces',
                        'bank'   => 'Virement',
                        'cheque' => 'Chèque',
                        default  => $state,
                    }),
                Tables\Columns\TextColumn::make('amount')
                    ->label(__('Montant'))
                    ->money('TND')->sortable()
                    ->color('danger')
                    ->weight(\Filament\Support\Enums\FontWeight::Bold)
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label(__('Total'))->money('TND')),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label(__('Catégorie'))
                    ->relationship('category', 'name'),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label(__('Mode de paiement'))
                    ->options([
                        'cash'   => 'Espèces',
                        'bank'   => 'Virement',
                        'cheque' => 'Chèque',
                    ]),
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label(__('Du')),
                        Forms\Components\DatePicker::make('until')->label(__('Au')),
                    ])
                    ->query(fn ($query, array $data) => $query
                        ->when($data['from'],  fn ($q) => $q->whereDate('date', '>=', $data['from']))
                        ->when($data['until'], fn ($q) => $q->whereDate('date', '<=', $data['until']))
                    ),
            ])
            ->emptyStateIcon('heroicon-o-arrow-trending-down')
            ->emptyStateHeading('Aucune dépense enregistrée')
            ->emptyStateDescription('Enregistrez les dépenses de l\'établissement pour suivre les charges.')
            ->emptyStateActions([Actions\CreateAction::make()->label(__('Ajouter une dépense'))])
            ->actions([Actions\EditAction::make(), Actions\DeleteAction::make()])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit'   => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
