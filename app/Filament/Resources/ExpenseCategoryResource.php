<?php
namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseCategoryResource\Pages;
use App\Models\ExpenseCategory;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExpenseCategoryResource extends Resource
{
    protected static ?string $model = ExpenseCategory::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-tag';
    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string { return 'Paramètres'; }
    public static function getNavigationLabel(): string  { return __('Expense Categories'); }
    public static function getModelLabel(): string       { return __('Expense Category'); }
    public static function getPluralModelLabel(): string { return __('Expense Categories'); }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('expenses');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Catégorie de dépense')
                ->description('Créez une catégorie pour regrouper et analyser les dépenses de l\'établissement')
                ->icon('heroicon-o-tag')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nom de la catégorie')
                        ->required()->maxLength(100)->placeholder('ex: Fournitures scolaires'),
                    Forms\Components\Textarea::make('description')
                        ->label('Description')
                        ->columnSpanFull(),
                ])->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Catégorie')->searchable()->sortable()
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold),
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')->limit(60)->toggleable(),
                Tables\Columns\TextColumn::make('expenses_count')
                    ->label('Dépenses')
                    ->counts('expenses')
                    ->badge()->color('warning'),
            ])
            ->emptyStateIcon('heroicon-o-tag')
            ->emptyStateHeading('Aucune catégorie de dépense')
            ->emptyStateDescription('Créez des catégories pour organiser les dépenses (salaires, matériel, etc.).')
            ->emptyStateActions([Actions\CreateAction::make()->label('Créer une catégorie')])
            ->actions([Actions\EditAction::make(), Actions\DeleteAction::make()])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListExpenseCategories::route('/'),
            'create' => Pages\CreateExpenseCategory::route('/create'),
            'edit'   => Pages\EditExpenseCategory::route('/{record}/edit'),
        ];
    }
}
