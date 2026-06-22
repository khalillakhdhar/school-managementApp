<?php
namespace App\Filament\Resources\EmployeeResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AttendancesRelationManager extends RelationManager
{
    protected static string $relationship = 'attendances';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Présences');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\DatePicker::make('date')
                ->label(__('Date'))->required()->displayFormat('d/m/Y'),
            Forms\Components\Select::make('status')
                ->label(__('Statut'))
                ->options([
                    'present' => __('Présent'),
                    'absent'  => __('Absent'),
                    'late'    => __('Retard'),
                    'leave'   => __('Congé'),
                ])
                ->default('present')->required(),
            Forms\Components\TimePicker::make('time_in')
                ->label(__("Heure d'arrivée"))->seconds(false),
            Forms\Components\TimePicker::make('time_out')
                ->label(__('Heure de départ'))->seconds(false),
            Forms\Components\TextInput::make('total_hours')
                ->label(__('Heures travaillées'))->numeric()->minValue(0)->step(0.25)->suffix('h'),
            Forms\Components\TextInput::make('overtime_hours')
                ->label(__('Heures supplémentaires'))->numeric()->minValue(0)->step(0.25)->suffix('h'),
            Forms\Components\Textarea::make('notes')
                ->label(__('Notes'))->rows(2)->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('date')
            ->defaultSort('date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label(__('Date'))->date('d/m/Y')
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('Statut'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'present' => 'success',
                        'absent'  => 'danger',
                        'late'    => 'warning',
                        'leave'   => 'info',
                        default   => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'present' => __('Présent'),
                        'absent'  => __('Absent'),
                        'late'    => __('Retard'),
                        'leave'   => __('Congé'),
                        default   => $state,
                    }),
                Tables\Columns\TextColumn::make('time_in')
                    ->label(__('Entrée')),
                Tables\Columns\TextColumn::make('time_out')
                    ->label(__('Sortie')),
                Tables\Columns\TextColumn::make('total_hours')
                    ->label(__('Heures'))
                    ->formatStateUsing(fn ($state): string => $state ? $state . ' h' : '—')
                    ->badge()->color('primary'),
                Tables\Columns\TextColumn::make('overtime_hours')
                    ->label(__('Supp.'))
                    ->formatStateUsing(fn ($state): string => $state > 0 ? $state . ' h' : '—')
                    ->badge()->color('warning'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('Statut'))
                    ->options([
                        'present' => __('Présent'),
                        'absent'  => __('Absent'),
                        'late'    => __('Retard'),
                        'leave'   => __('Congé'),
                    ]),
            ])
            ->headerActions([Tables\Actions\CreateAction::make()->label(__('Ajouter une présence'))])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }
}
