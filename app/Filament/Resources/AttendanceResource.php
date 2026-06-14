<?php
namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use App\Models\Employee;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return 'RH';
    }

    public static function getNavigationLabel(): string
    {
        return __('Attendance');
    }

    public static function getNavigationBadge(): ?string
    {
        $today   = now()->toDateString();
        $total   = Employee::where('is_active', true)->count();
        $present = Attendance::whereDate('date', $today)->count();

        if ($total === 0) return null;
        return $present < $total ? ($total - $present) . ' manquants' : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function getModelLabel(): string
    {
        return __('Attendance');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Attendance');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Pointage de présence')
                ->description('Enregistrez la présence ou l\'absence d\'un employé pour une journée donnée')
                ->icon('heroicon-o-calendar-days')
                ->schema([
                    Forms\Components\Select::make('employee_id')
                        ->label('Employé')
                        ->relationship('employee', 'first_name')
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                        ->searchable()->preload()->required()->placeholder('Rechercher un employé...'),
                    Forms\Components\DatePicker::make('date')
                        ->label('Date')->required()->default(now())->displayFormat('d/m/Y'),
                    Forms\Components\Select::make('status')
                        ->label('Statut de présence')
                        ->options([
                            'present' => 'Présent',
                            'absent'  => 'Absent',
                            'late'    => 'En retard',
                            'leave'   => 'Congé',
                        ])
                        ->required()->default('present'),
                    Forms\Components\TimePicker::make('time_in')->label('Heure d\'arrivée'),
                    Forms\Components\TimePicker::make('time_out')->label('Heure de départ'),
                    Forms\Components\TextInput::make('total_hours')->label('Heures travaillées')->numeric()->suffix('h'),
                    Forms\Components\TextInput::make('overtime_hours')->label('Heures supplémentaires')->numeric()->default(0)->suffix('h'),
                    Forms\Components\Textarea::make('notes')->label('Remarques')->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.first_name')
                    ->label('Employé')
                    ->formatStateUsing(fn ($state, $record) => $record->employee?->full_name ?? '—')
                    ->searchable()->sortable()
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold),
                Tables\Columns\TextColumn::make('date')
                    ->label('Date')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'present' => 'success',
                        'absent'  => 'danger',
                        'late'    => 'warning',
                        'leave'   => 'gray',
                        default   => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'present' => 'Présent',
                        'absent'  => 'Absent',
                        'late'    => 'En retard',
                        'leave'   => 'Congé',
                        default   => $state,
                    }),
                Tables\Columns\TextColumn::make('time_in')->label('Arrivée'),
                Tables\Columns\TextColumn::make('time_out')->label('Départ'),
                Tables\Columns\TextColumn::make('total_hours')
                    ->label('Heures')->suffix(' h')
                    ->weight(\Filament\Support\Enums\FontWeight::Bold),
                Tables\Columns\TextColumn::make('overtime_hours')
                    ->label('Heures sup.')->suffix(' h')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'present' => 'Présent',
                        'absent'  => 'Absent',
                        'late'    => 'En retard',
                        'leave'   => 'Congé',
                    ]),
                Tables\Filters\SelectFilter::make('employee')
                    ->label('Employé')
                    ->relationship('employee', 'first_name'),
            ])
            ->emptyStateIcon('heroicon-o-calendar-days')
            ->emptyStateHeading('Aucune présence enregistrée')
            ->emptyStateDescription('Les pointages des employés apparaîtront ici.')
            ->headerActions([
                Tables\Actions\Action::make('mark_all_present_today')
                    ->label('Marquer tous présents aujourd\'hui')
                    ->icon('heroicon-o-user-group')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Pointage global — ' . now()->translatedFormat('d/m/Y'))
                    ->modalDescription('Crée un enregistrement "Présent" pour chaque employé actif sans pointage aujourd\'hui.')
                    ->action(function (): void {
                        $today     = now()->toDateString();
                        $employees = Employee::where('is_active', true)->get();
                        $existing  = Attendance::whereDate('date', $today)->pluck('employee_id');
                        $created   = 0;

                        foreach ($employees as $employee) {
                            if ($existing->contains($employee->id)) continue;
                            Attendance::create([
                                'employee_id' => $employee->id,
                                'date'        => $today,
                                'status'      => 'present',
                                'time_in'     => '08:00',
                            ]);
                            $created++;
                        }

                        if ($created > 0) {
                            Notification::make()
                                ->title("{$created} employé(s) marqués présents aujourd'hui")
                                ->success()->send();
                        } else {
                            Notification::make()
                                ->title('Tous les employés sont déjà pointés aujourd\'hui')
                                ->info()->send();
                        }
                    }),
            ])
            ->actions([Actions\EditAction::make(), Actions\DeleteAction::make()])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'edit'   => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }
}
