<?php
namespace App\Filament\Resources;

use App\Filament\Resources\TimetableEntryResource\Pages;
use App\Models\Classroom;
use App\Models\Employee;
use App\Models\Subject;
use App\Models\TimetableEntry;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class TimetableEntryResource extends Resource
{
    protected static ?string $model = TimetableEntry::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-pencil-square';
    protected static ?int $navigationSort = 5;

    public static function getNavigationGroup(): ?string { return 'Académique'; }
    public static function getNavigationLabel(): string  { return 'Saisie créneaux'; }
    public static function getModelLabel(): string       { return 'Séance'; }
    public static function getPluralModelLabel(): string { return 'Emplois du temps'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Planification de la séance')
                ->description('Définissez la classe, matière, enseignant et le créneau horaire')
                ->icon('heroicon-o-calendar-days')
                ->schema([
                    Forms\Components\Select::make('classroom_id')
                        ->label('Classe')
                        ->options(
                            Classroom::with('level')->orderBy('level_id')->get()
                                ->mapWithKeys(fn ($c) => [$c->id => $c->full_name])
                        )
                        ->required()->searchable()->preload(),
                    Forms\Components\Select::make('subject_id')
                        ->label('Matière')
                        ->relationship('subject', 'name')
                        ->required()->searchable()->preload(),
                    Forms\Components\Select::make('employee_id')
                        ->label('Enseignant')
                        ->options(
                            Employee::active()->teachers()->orderBy('last_name')->get()
                                ->mapWithKeys(fn ($e) => [$e->id => $e->full_name])
                        )
                        ->nullable()->searchable()->placeholder('Non assigné'),
                    Forms\Components\Select::make('day_of_week')
                        ->label('Jour')
                        ->options(array_combine(TimetableEntry::$days, TimetableEntry::$days))
                        ->required(),
                    Forms\Components\TimePicker::make('start_time')
                        ->label('Heure début')
                        ->required()->seconds(false),
                    Forms\Components\TimePicker::make('end_time')
                        ->label('Heure fin')
                        ->required()->seconds(false)
                        ->after('start_time'),
                    Forms\Components\TextInput::make('room')
                        ->label('Salle')
                        ->maxLength(50)->placeholder('Salle 12 / Labo'),
                    Forms\Components\TextInput::make('academic_year')
                        ->label('Année scolaire')
                        ->maxLength(10)
                        ->placeholder('2025-2026')
                        ->default(function () {
                            $y = now()->year;
                            return now()->month >= 9 ? "{$y}-" . ($y + 1) : ($y - 1) . "-{$y}";
                        }),
                    Forms\Components\Textarea::make('notes')
                        ->label('Notes')->columnSpanFull()->rows(2),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        $dayOrder = array_flip(TimetableEntry::$days);

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('day_of_week')
                    ->label('Jour')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'Lundi'    => 'primary',
                        'Mardi'    => 'info',
                        'Mercredi' => 'success',
                        'Jeudi'    => 'warning',
                        'Vendredi' => 'danger',
                        'Samedi'   => 'gray',
                        default    => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Début')
                    ->formatStateUsing(fn ($state) => substr($state, 0, 5))
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_time')
                    ->label('Fin')
                    ->formatStateUsing(fn ($state) => substr($state, 0, 5)),
                Tables\Columns\TextColumn::make('classroom.name')
                    ->label('Classe')
                    ->formatStateUsing(fn ($state, $record) => $record->classroom?->full_name ?? $state)
                    ->badge()->color('primary'),
                Tables\Columns\TextColumn::make('subject.name')
                    ->label('Matière')
                    ->searchable()->sortable()
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold),
                Tables\Columns\TextColumn::make('teacher.first_name')
                    ->label('Enseignant')
                    ->formatStateUsing(fn ($state, $record) => $record->teacher?->full_name ?? '—')
                    ->color(fn ($state, $record) => $record->employee_id ? null : 'danger'),
                Tables\Columns\TextColumn::make('room')
                    ->label('Salle')->badge()->color('gray'),
            ])
            ->defaultSort('day_of_week')
            ->filters([
                Tables\Filters\SelectFilter::make('classroom_id')
                    ->label('Classe')
                    ->options(
                        Classroom::with('level')->orderBy('level_id')->get()
                            ->mapWithKeys(fn ($c) => [$c->id => $c->full_name])
                    ),
                Tables\Filters\SelectFilter::make('subject_id')
                    ->label('Matière')
                    ->relationship('subject', 'name'),
                Tables\Filters\SelectFilter::make('employee_id')
                    ->label('Enseignant')
                    ->options(
                        Employee::active()->teachers()->orderBy('last_name')->get()
                            ->mapWithKeys(fn ($e) => [$e->id => $e->full_name])
                    ),
                Tables\Filters\SelectFilter::make('day_of_week')
                    ->label('Jour')
                    ->options(array_combine(TimetableEntry::$days, TimetableEntry::$days)),
            ])
            ->emptyStateIcon('heroicon-o-calendar-days')
            ->emptyStateHeading('Aucune séance planifiée')
            ->emptyStateDescription('Construisez les emplois du temps en ajoutant des séances.')
            ->emptyStateActions([Actions\CreateAction::make()->label('Ajouter une séance')])
            ->actions([Actions\EditAction::make(), Actions\DeleteAction::make()])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTimetableEntries::route('/'),
            'create' => Pages\CreateTimetableEntry::route('/create'),
            'edit'   => Pages\EditTimetableEntry::route('/{record}/edit'),
        ];
    }
}
