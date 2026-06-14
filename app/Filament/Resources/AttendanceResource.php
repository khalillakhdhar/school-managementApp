<?php
namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';
    protected static string|\UnitEnum|null $navigationGroup = 'RH';
    protected static ?string $modelLabel = 'Présence';
    protected static ?string $pluralModelLabel = 'Présences';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Select::make('employee_id')
                ->label('Employé')
                ->relationship('employee', 'first_name')
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                ->searchable()->preload()->required(),
            Forms\Components\DatePicker::make('date')->label('Date')->required()->default(now()),
            Forms\Components\Select::make('status')
                ->label('Statut')
                ->options(['present' => 'Présent', 'absent' => 'Absent', 'late' => 'En retard', 'leave' => 'Congé'])
                ->required()->default('present'),
            Forms\Components\TimePicker::make('time_in')->label('Heure d\'arrivée'),
            Forms\Components\TimePicker::make('time_out')->label('Heure de départ'),
            Forms\Components\TextInput::make('total_hours')->label('Heures totales')->numeric(),
            Forms\Components\TextInput::make('overtime_hours')->label('Heures sup.')->numeric()->default(0),
            Forms\Components\Textarea::make('notes')->label('Notes')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.first_name')->label('Employé')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('date')->label('Date')->date()->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'present' => 'success',
                        'absent' => 'danger',
                        'late' => 'warning',
                        'leave' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'present' => 'Présent',
                        'absent' => 'Absent',
                        'late' => 'En retard',
                        'leave' => 'Congé',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('time_in')->label('Arrivée'),
                Tables\Columns\TextColumn::make('time_out')->label('Départ'),
                Tables\Columns\TextColumn::make('total_hours')->label('Heures')->suffix(' h'),
                Tables\Columns\TextColumn::make('overtime_hours')->label('Heures sup.')->suffix(' h')->toggleable(),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options(['present' => 'Présent', 'absent' => 'Absent', 'late' => 'En retard', 'leave' => 'Congé']),
                Tables\Filters\SelectFilter::make('employee')
                    ->label('Employé')
                    ->relationship('employee', 'first_name'),
            ])
            ->actions([Actions\EditAction::make(), Actions\DeleteAction::make()])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }
}
