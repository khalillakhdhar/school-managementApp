<?php
namespace App\Filament\Resources;

use App\Filament\Resources\ClassroomResource\Pages;
use App\Filament\Resources\ClassroomResource\RelationManagers;
use App\Models\Classroom;
use App\Models\Employee;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ClassroomResource extends Resource
{
    protected static ?string $model = Classroom::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office';
    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string { return __('Académique'); }
    public static function getNavigationLabel(): string  { return __('Classrooms'); }
    public static function getModelLabel(): string       { return __('Classroom'); }
    public static function getPluralModelLabel(): string { return __('Classrooms'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Informations de la classe'))
                ->description('Niveau, nom, capacité d\'accueil et enseignant titulaire')
                ->icon('heroicon-o-building-office')
                ->schema([
                    Forms\Components\Select::make('level_id')
                        ->label(__('Niveau scolaire'))
                        ->relationship('level', 'name')
                        ->required()->searchable()->preload(),
                    Forms\Components\TextInput::make('name')
                        ->label(__('Nom de la classe'))
                        ->required()->maxLength(50)
                        ->placeholder('1A'),
                    Forms\Components\TextInput::make('capacity')
                        ->label(__('Capacité maximale'))
                        ->numeric()->required()->default(30)->minValue(1)->maxValue(100)
                        ->suffix('élèves'),
                    Forms\Components\Select::make('teacher_id')
                        ->label(__('Enseignant titulaire'))
                        ->options(
                            Employee::active()
                                ->teachers()
                                ->orderBy('last_name')
                                ->get()
                                ->mapWithKeys(fn ($e) => [$e->id => "{$e->full_name}" . ($e->specialite ? " — {$e->specialite}" : '')])
                        )
                        ->nullable()->searchable()->placeholder(__('Aucun enseignant assigné')),
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
                Tables\Columns\TextColumn::make('level.code')
                    ->label(__('Niveau'))->badge()->color('primary')->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Classe'))->searchable()->sortable()
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold),
                Tables\Columns\TextColumn::make('teacher.first_name')
                    ->label(__('Enseignant titulaire'))
                    ->formatStateUsing(fn ($state, $record) => $record->teacher
                        ? $record->teacher->full_name . ($record->teacher->specialite ? " · {$record->teacher->specialite}" : '')
                        : '—')
                    ->color(fn ($state, $record) => $record->teacher_id ? null : 'danger'),
                Tables\Columns\TextColumn::make('students_count')
                    ->label(__('Élèves'))
                    ->counts('students')
                    ->badge()->color('success'),
                Tables\Columns\TextColumn::make('capacity')
                    ->label(__('Capacité'))->sortable()
                    ->formatStateUsing(fn ($state, $record) =>
                        ($record->students_count ?? 0) . ' / ' . $state
                    ),
            ])
            ->defaultSort('level_id')
            ->filters([
                Tables\Filters\SelectFilter::make('level_id')
                    ->label(__('Niveau'))
                    ->relationship('level', 'name'),
                Tables\Filters\Filter::make('no_teacher')
                    ->label(__('Sans enseignant'))
                    ->query(fn ($query) => $query->whereNull('teacher_id')),
            ])
            ->emptyStateIcon('heroicon-o-building-office')
            ->emptyStateHeading('Aucune classe créée')
            ->emptyStateDescription('Créez les classes de l\'établissement et assignez-leur un enseignant.')
            ->emptyStateActions([Actions\CreateAction::make()->label(__('Créer une classe'))])
            ->actions([Actions\EditAction::make(), Actions\DeleteAction::make()])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelationManagers(): array
    {
        return [
            RelationManagers\StudentsRelationManager::class,
            RelationManagers\SubjectsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListClassrooms::route('/'),
            'create' => Pages\CreateClassroom::route('/create'),
            'edit'   => Pages\EditClassroom::route('/{record}/edit'),
        ];
    }
}
