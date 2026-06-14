<?php
namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
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
            Forms\Components\Select::make('employee_id')
                ->label(__('Employee'))
                ->relationship('employee', 'first_name')
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                ->searchable()->preload()->required(),
            Forms\Components\DatePicker::make('date')->label(__('Date'))->required()->default(now()),
            Forms\Components\Select::make('status')
                ->label(__('Status'))
                ->options([
                    'present' => __('Present'),
                    'absent'  => __('Absent'),
                    'late'    => __('Late'),
                    'leave'   => __('Leave'),
                ])
                ->required()->default('present'),
            Forms\Components\TimePicker::make('time_in')->label(__('Arrival Time')),
            Forms\Components\TimePicker::make('time_out')->label(__('Departure Time')),
            Forms\Components\TextInput::make('total_hours')->label(__('Total Hours'))->numeric(),
            Forms\Components\TextInput::make('overtime_hours')->label(__('Overtime'))->numeric()->default(0),
            Forms\Components\Textarea::make('notes')->label(__('Notes'))->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.first_name')->label(__('Employee'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('date')->label(__('Date'))->date()->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'present' => 'success',
                        'absent'  => 'danger',
                        'late'    => 'warning',
                        'leave'   => 'gray',
                        default   => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'present' => __('Present'),
                        'absent'  => __('Absent'),
                        'late'    => __('Late'),
                        'leave'   => __('Leave'),
                        default   => $state,
                    }),
                Tables\Columns\TextColumn::make('time_in')->label(__('Arrival Time')),
                Tables\Columns\TextColumn::make('time_out')->label(__('Departure Time')),
                Tables\Columns\TextColumn::make('total_hours')->label(__('Hours'))->suffix(' h'),
                Tables\Columns\TextColumn::make('overtime_hours')->label(__('Overtime'))->suffix(' h')->toggleable(),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'present' => __('Present'),
                        'absent'  => __('Absent'),
                        'late'    => __('Late'),
                        'leave'   => __('Leave'),
                    ]),
                Tables\Filters\SelectFilter::make('employee')
                    ->label(__('Employee'))
                    ->relationship('employee', 'first_name'),
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
