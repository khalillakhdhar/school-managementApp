<?php
namespace App\Filament\Resources;

use App\Filament\Resources\LevelResource\Pages;
use App\Models\Level;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class LevelResource extends Resource
{
    protected static ?string $model = Level::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string { return 'Paramètres'; }
    public static function getNavigationLabel(): string  { return __('Levels'); }
    public static function getModelLabel(): string       { return __('Level'); }
    public static function getPluralModelLabel(): string { return __('Levels'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Niveau scolaire')
                ->description('Définissez un niveau d\'enseignement et son ordre d\'affichage')
                ->icon('heroicon-o-academic-cap')
                ->schema([
                    Forms\Components\TextInput::make('code')
                        ->label('Code abrégé')
                        ->required()->maxLength(10)->unique(ignoreRecord: true)
                        ->placeholder('1AP'),
                    Forms\Components\TextInput::make('name')
                        ->label('Intitulé du niveau')
                        ->required()->maxLength(100)
                        ->placeholder('1ère Année Primaire'),
                    Forms\Components\TextInput::make('order')
                        ->label('Ordre d\'affichage')
                        ->numeric()->required()->default(1)->minValue(1)->maxValue(10),
                    Forms\Components\Textarea::make('description')
                        ->label('Description')
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order')->label('Ordre')->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label('Code')->searchable()->badge()->color('primary'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Niveau')->searchable()->sortable()
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold),
                Tables\Columns\TextColumn::make('classrooms_count')
                    ->label('Classes')
                    ->counts('classrooms')
                    ->badge()->color('info'),
            ])
            ->defaultSort('order')
            ->emptyStateIcon('heroicon-o-academic-cap')
            ->emptyStateHeading('Aucun niveau créé')
            ->emptyStateDescription('Créez les niveaux scolaires (CP, CE1, etc.) pour organiser les classes.')
            ->emptyStateActions([Actions\CreateAction::make()->label('Créer un niveau')])
            ->actions([Actions\EditAction::make(), Actions\DeleteAction::make()])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLevels::route('/'),
            'create' => Pages\CreateLevel::route('/create'),
            'edit'   => Pages\EditLevel::route('/{record}/edit'),
        ];
    }
}
