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
            Section::make(__('Expense Details'))->schema([
                Forms\Components\Select::make('category_id')
                    ->label(__('Category'))
                    ->relationship('category', 'name')
                    ->required()->searchable()->preload()->createOptionForm([
                        Forms\Components\TextInput::make('name')->label(__('Name'))->required(),
                    ]),
                Forms\Components\DatePicker::make('date')
                    ->label(__('Date'))
                    ->required()->default(now()),
                Forms\Components\TextInput::make('amount')
                    ->label(__('Amount'))
                    ->required()->numeric()->minValue(0)->prefix('TND'),
                Forms\Components\Select::make('payment_method')
                    ->label(__('Payment Method'))
                    ->options([
                        'cash'  => __('Cash'),
                        'bank'  => __('Bank Transfer'),
                        'cheque'=> __('Cheque'),
                    ])
                    ->required()->default('cash'),
                Forms\Components\TextInput::make('supplier')
                    ->label(__('Supplier'))
                    ->maxLength(255),
                Forms\Components\TextInput::make('invoice_number')
                    ->label(__('Invoice Number'))
                    ->maxLength(100),
                Forms\Components\Textarea::make('description')
                    ->label(__('Description'))
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('notes')
                    ->label(__('Notes'))
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Date')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Catégorie')->searchable()->sortable()->badge()->color('warning'),
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')->limit(45)->searchable(),
                Tables\Columns\TextColumn::make('supplier')
                    ->label('Fournisseur')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('N° facture')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Mode')
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
                    ->label('Montant')
                    ->money('TND')->sortable()
                    ->color('danger')
                    ->weight(\Filament\Support\Enums\FontWeight::Bold)
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total')->money('TND')),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Catégorie')
                    ->relationship('category', 'name'),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Mode de paiement')
                    ->options([
                        'cash'   => 'Espèces',
                        'bank'   => 'Virement',
                        'cheque' => 'Chèque',
                    ]),
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Du'),
                        Forms\Components\DatePicker::make('until')->label('Au'),
                    ])
                    ->query(fn ($query, array $data) => $query
                        ->when($data['from'],  fn ($q) => $q->whereDate('date', '>=', $data['from']))
                        ->when($data['until'], fn ($q) => $q->whereDate('date', '<=', $data['until']))
                    ),
            ])
            ->emptyStateIcon('heroicon-o-arrow-trending-down')
            ->emptyStateHeading('Aucune dépense enregistrée')
            ->emptyStateDescription('Enregistrez les dépenses de l\'établissement pour suivre les charges.')
            ->emptyStateActions([Actions\CreateAction::make()->label('Ajouter une dépense')])
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
