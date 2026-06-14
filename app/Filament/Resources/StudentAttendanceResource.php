<?php
namespace App\Filament\Resources;

use App\Filament\Resources\StudentAttendanceResource\Pages;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\StudentAttendance;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class StudentAttendanceResource extends Resource
{
    protected static ?string $model = StudentAttendance::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string { return 'Académique'; }
    public static function getNavigationLabel(): string  { return 'Présence élèves'; }
    public static function getModelLabel(): string       { return 'Présence élève'; }
    public static function getPluralModelLabel(): string { return 'Présences élèves'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Présence')
                ->icon('heroicon-o-clipboard-document-check')
                ->schema([
                    Forms\Components\Select::make('student_id')
                        ->label('Élève')
                        ->options(fn () => Student::orderBy('last_name')->get()->mapWithKeys(fn ($s) => [$s->id => $s->full_name]))
                        ->searchable()->required(),
                    Forms\Components\Select::make('classroom_id')
                        ->label('Classe')
                        ->options(fn () => Classroom::with('level')->get()->mapWithKeys(fn ($c) => [$c->id => $c->full_name]))
                        ->searchable(),
                    Forms\Components\DatePicker::make('date')->label('Date')->required()->default(now())->displayFormat('d/m/Y'),
                    Forms\Components\Select::make('status')
                        ->label('Statut')
                        ->options(['present' => 'Présent', 'absent' => 'Absent', 'late' => 'En retard', 'excused' => 'Excusé'])
                        ->required()->default('present'),
                    Forms\Components\Textarea::make('notes')->label('Remarque')->columnSpanFull()->rows(2),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.first_name')
                    ->label('Élève')
                    ->formatStateUsing(fn ($state, $record) => $record->student?->full_name ?? '—')
                    ->searchable()->sortable()
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold),
                Tables\Columns\TextColumn::make('classroom.name')->label('Classe')->badge()->color('primary'),
                Tables\Columns\TextColumn::make('date')->label('Date')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'present' => 'success', 'absent' => 'danger', 'late' => 'warning', 'excused' => 'info', default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'present' => 'Présent', 'absent' => 'Absent', 'late' => 'En retard', 'excused' => 'Excusé', default => $state,
                    }),
                Tables\Columns\TextColumn::make('employee.first_name')
                    ->label('Saisi par')
                    ->formatStateUsing(fn ($state, $record) => $record->employee?->full_name ?? '—')
                    ->toggleable(),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('classroom_id')
                    ->label('Classe')
                    ->options(fn () => Classroom::with('level')->get()->mapWithKeys(fn ($c) => [$c->id => $c->full_name])),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options(['present' => 'Présent', 'absent' => 'Absent', 'late' => 'En retard', 'excused' => 'Excusé']),
            ])
            ->emptyStateIcon('heroicon-o-clipboard-document-check')
            ->emptyStateHeading('Aucune présence enregistrée')
            ->emptyStateDescription('Les appels saisis par les enseignants apparaîtront ici.')
            ->actions([Actions\EditAction::make(), Actions\DeleteAction::make()])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListStudentAttendances::route('/'),
            'create' => Pages\CreateStudentAttendance::route('/create'),
            'edit'   => Pages\EditStudentAttendance::route('/{record}/edit'),
        ];
    }
}
