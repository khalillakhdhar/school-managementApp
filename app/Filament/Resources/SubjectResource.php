<?php
namespace App\Filament\Resources;

use App\Filament\Resources\SubjectResource\Pages;
use App\Filament\Resources\SubjectResource\RelationManagers;
use App\Models\Subject;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class SubjectResource extends Resource
{
    protected static ?string $model = Subject::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-book-open';
    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string { return __('Académique'); }
    public static function getNavigationLabel(): string  { return __('Matières'); }
    public static function getModelLabel(): string       { return __('Matière'); }
    public static function getPluralModelLabel(): string { return __('Matières'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Informations de la matière'))
                ->description(__('Définissez la matière, son code et son coefficient'))
                ->icon('heroicon-o-book-open')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label(__('Nom de la matière'))
                        ->required()->maxLength(100)
                        ->placeholder('Mathématiques'),
                    Forms\Components\TextInput::make('code')
                        ->label(__('Code'))
                        ->maxLength(20)
                        ->placeholder('MATH')
                        ->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('coefficient')
                        ->label(__('Coefficient'))
                        ->numeric()->default(1.00)
                        ->minValue(0)->maxValue(10)->step(0.5),
                    Forms\Components\ColorPicker::make('color')
                        ->label(__('Couleur'))
                        ->default('#1d4ed8'),
                    Forms\Components\Textarea::make('description')
                        ->label(__('Description'))
                        ->columnSpanFull()->rows(2),
                    Forms\Components\Toggle::make('is_active')
                        ->label(__('Active'))
                        ->default(true)
                        ->inline(false),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ColorColumn::make('color')
                    ->label(''),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Matière'))
                    ->searchable()->sortable()
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold),
                Tables\Columns\TextColumn::make('code')
                    ->label(__('Code'))
                    ->badge()->color('gray'),
                Tables\Columns\TextColumn::make('coefficient')
                    ->label(__('Coeff.'))
                    ->badge()->color('primary')
                    ->formatStateUsing(fn ($state) => 'x' . $state),
                Tables\Columns\TextColumn::make('timetable_entries_count')
                    ->label(__('Séances/sem.'))
                    ->counts('timetableEntries')
                    ->badge()->color('success'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('Statut'))
                    ->trueLabel('Actives')->falseLabel('Inactives'),
            ])
            ->emptyStateIcon('heroicon-o-book-open')
            ->emptyStateHeading(__('Aucune matière créée'))
            ->emptyStateDescription(__('Créez les matières enseignées dans votre établissement.'))
            ->emptyStateActions([Actions\CreateAction::make()->label(__('Créer une matière'))])
            ->actions([Actions\EditAction::make(), Actions\DeleteAction::make()])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelationManagers(): array
    {
        return [
            RelationManagers\TeachersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSubjects::route('/'),
            'create' => Pages\CreateSubject::route('/create'),
            'edit'   => Pages\EditSubject::route('/{record}/edit'),
        ];
    }
}
