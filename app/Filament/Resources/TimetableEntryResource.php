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

    public static function getNavigationGroup(): ?string { return __('Académique'); }
    public static function getNavigationLabel(): string  { return __('Saisie créneaux'); }
    public static function getModelLabel(): string       { return __('Séance'); }
    public static function getPluralModelLabel(): string { return __('Emplois du temps'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Planification de la séance'))
                ->description(__('Définissez la classe, matière, enseignant et le créneau horaire'))
                ->icon('heroicon-o-calendar-days')
                ->schema([
                    Forms\Components\Select::make('classroom_id')
                        ->label(__('Classe'))
                        ->options(
                            Classroom::with('level')->orderBy('level_id')->get()
                                ->mapWithKeys(fn ($c) => [$c->id => $c->full_name])
                        )
                        ->required()->searchable()->preload(),
                    Forms\Components\Select::make('subject_id')
                        ->label(__('Matière'))
                        ->relationship('subject', 'name')
                        ->required()->searchable()->preload(),
                    Forms\Components\Select::make('employee_id')
                        ->label(__('Enseignant'))
                        ->options(
                            Employee::active()->teachers()->orderBy('last_name')->get()
                                ->mapWithKeys(fn ($e) => [$e->id => $e->full_name])
                        )
                        ->nullable()->searchable()->placeholder(__('Non assigné')),
                    Forms\Components\Select::make('day_of_week')
                        ->label(__('Jour'))
                        ->options(array_combine(TimetableEntry::$days, array_map('__', TimetableEntry::$days)))
                        ->required(),
                    Forms\Components\TimePicker::make('start_time')
                        ->label(__('Heure début'))
                        ->required()->seconds(false),
                    Forms\Components\TimePicker::make('end_time')
                        ->label(__('Heure fin'))
                        ->required()->seconds(false)
                        ->after('start_time'),
                    Forms\Components\TextInput::make('room')
                        ->label(__('Salle'))
                        ->maxLength(50)->placeholder('Salle 12 / Labo'),
                    Forms\Components\TextInput::make('academic_year')
                        ->label(__('Année scolaire'))
                        ->maxLength(10)
                        ->placeholder('2025-2026')
                        ->default(function () {
                            $y = now()->year;
                            return now()->month >= 9 ? "{$y}-" . ($y + 1) : ($y - 1) . "-{$y}";
                        }),
                    Forms\Components\Textarea::make('notes')
                        ->label(__('Notes'))->columnSpanFull()->rows(2),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        $dayOrder = array_flip(TimetableEntry::$days);

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('day_of_week')
                    ->label(__('Jour'))
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
                    ->formatStateUsing(fn ($state) => __($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->label(__('Début'))
                    ->formatStateUsing(fn ($state) => substr($state, 0, 5))
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_time')
                    ->label(__('Fin'))
                    ->formatStateUsing(fn ($state) => substr($state, 0, 5)),
                Tables\Columns\TextColumn::make('classroom.name')
                    ->label(__('Classe'))
                    ->formatStateUsing(fn ($state, $record) => $record->classroom?->full_name ?? $state)
                    ->badge()->color('primary'),
                Tables\Columns\TextColumn::make('subject.name')
                    ->label(__('Matière'))
                    ->searchable()->sortable()
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold),
                Tables\Columns\TextColumn::make('teacher.first_name')
                    ->label(__('Enseignant'))
                    ->formatStateUsing(fn ($state, $record) => $record->teacher?->full_name ?? '—')
                    ->color(fn ($state, $record) => $record->employee_id ? null : 'danger'),
                Tables\Columns\TextColumn::make('room')
                    ->label(__('Salle'))->badge()->color('gray'),
            ])
            ->defaultSort('day_of_week')
            ->filters([
                Tables\Filters\SelectFilter::make('classroom_id')
                    ->label(__('Classe'))
                    ->options(
                        Classroom::with('level')->orderBy('level_id')->get()
                            ->mapWithKeys(fn ($c) => [$c->id => $c->full_name])
                    ),
                Tables\Filters\SelectFilter::make('subject_id')
                    ->label(__('Matière'))
                    ->relationship('subject', 'name'),
                Tables\Filters\SelectFilter::make('employee_id')
                    ->label(__('Enseignant'))
                    ->options(
                        Employee::active()->teachers()->orderBy('last_name')->get()
                            ->mapWithKeys(fn ($e) => [$e->id => $e->full_name])
                    ),
                Tables\Filters\SelectFilter::make('day_of_week')
                    ->label(__('Jour'))
                    ->options(array_combine(TimetableEntry::$days, array_map('__', TimetableEntry::$days))),
            ])
            ->emptyStateIcon('heroicon-o-calendar-days')
            ->emptyStateHeading(__('Aucune séance planifiée'))
            ->emptyStateDescription(__('Construisez les emplois du temps en ajoutant des séances.'))
            ->emptyStateActions([Actions\CreateAction::make()->label(__('Ajouter une séance'))])
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
