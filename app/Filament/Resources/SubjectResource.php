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

    public static function getNavigationGroup(): ?string { return 'Académique'; }
    public static function getNavigationLabel(): string  { return 'Matières'; }
    public static function getModelLabel(): string       { return 'Matière'; }
    public static function getPluralModelLabel(): string { return 'Matières'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informations de la matière')
                ->description('Définissez la matière, son code et son coefficient')
                ->icon('heroicon-o-book-open')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nom de la matière')
                        ->required()->maxLength(100)
                        ->placeholder('Mathématiques'),
                    Forms\Components\TextInput::make('code')
                        ->label('Code')
                        ->maxLength(20)
                        ->placeholder('MATH')
                        ->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('coefficient')
                        ->label('Coefficient')
                        ->numeric()->default(1.00)
                        ->minValue(0)->maxValue(10)->step(0.5),
                    Forms\Components\ColorPicker::make('color')
                        ->label('Couleur')
                        ->default('#1d4ed8'),
                    Forms\Components\Textarea::make('description')
                        ->label('Description')
                        ->columnSpanFull()->rows(2),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Active')
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
                    ->label('Matière')
                    ->searchable()->sortable()
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold),
                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->badge()->color('gray'),
                Tables\Columns\TextColumn::make('coefficient')
                    ->label('Coeff.')
                    ->badge()->color('primary')
                    ->formatStateUsing(fn ($state) => 'x' . $state),
                Tables\Columns\TextColumn::make('timetable_entries_count')
                    ->label('Séances/sem.')
                    ->counts('timetableEntries')
                    ->badge()->color('success'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Statut')
                    ->trueLabel('Actives')->falseLabel('Inactives'),
            ])
            ->emptyStateIcon('heroicon-o-book-open')
            ->emptyStateHeading('Aucune matière créée')
            ->emptyStateDescription('Créez les matières enseignées dans votre établissement.')
            ->emptyStateActions([Actions\CreateAction::make()->label('Créer une matière')])
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
