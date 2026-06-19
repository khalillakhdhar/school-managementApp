<?php
namespace App\Filament\Resources;

use App\Filament\Resources\HolidayResource\Pages;
use App\Models\Holiday;
use App\Services\HolidayService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class HolidayResource extends Resource
{
    protected static ?string $model = Holiday::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-date-range';
    protected static ?int $navigationSort = 50;

    public static function getNavigationGroup(): ?string { return 'Paramètres'; }
    public static function getNavigationLabel(): string  { return 'Jours fériés'; }
    public static function getModelLabel(): string       { return 'Jour férié'; }
    public static function getPluralModelLabel(): string { return 'Jours fériés'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Jour férié / vacances')
                ->icon('heroicon-o-calendar-date-range')
                ->schema([
                    Forms\Components\DatePicker::make('date')
                        ->label(__('Date'))->required()->displayFormat('d/m/Y')->native(false),
                    Forms\Components\Select::make('type')
                        ->label(__('Type'))
                        ->options(Holiday::$typeLabels)
                        ->required()->default('national'),
                    Forms\Components\TextInput::make('name')
                        ->label(__('Intitulé'))->required()->maxLength(255)->columnSpanFull(),
                    Forms\Components\Textarea::make('description')
                        ->label(__('Description'))->rows(2)->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')->label(__('Date'))->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('name')->label(__('Intitulé'))->searchable()->wrap(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('Type'))->badge()
                    ->color(fn (string $state) => match ($state) {
                        'national' => 'primary', 'religieux' => 'success', 'scolaire' => 'warning', default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => Holiday::$typeLabels[$state] ?? $state),
            ])
            ->defaultSort('date')
            ->filters([
                Tables\Filters\SelectFilter::make('type')->label(__('Type'))->options(Holiday::$typeLabels),
            ])
            ->headerActions([
                Actions\Action::make('sync')
                    ->label(__('Synchroniser une année'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->schema([
                        Forms\Components\TextInput::make('year')
                            ->label(__('Année'))->numeric()->required()
                            ->default(now()->year)->minValue(2020)->maxValue(2100),
                    ])
                    ->action(function (array $data): void {
                        $n = HolidayService::sync((int) $data['year']);
                        Notification::make()
                            ->title("{$n} jours fériés synchronisés pour {$data['year']}")
                            ->body('Jours civils + fêtes religieuses (calendrier hégirien). Les dates religieuses sont indicatives et ajustables.')
                            ->success()->send();
                    }),
            ])
            ->emptyStateIcon('heroicon-o-calendar-date-range')
            ->emptyStateHeading('Aucun jour férié')
            ->emptyStateDescription('Utilisez « Synchroniser une année » pour générer automatiquement les jours fériés tunisiens.')
            ->actions([Actions\EditAction::make(), Actions\DeleteAction::make()])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListHolidays::route('/'),
            'create' => Pages\CreateHoliday::route('/create'),
            'edit'   => Pages\EditHoliday::route('/{record}/edit'),
        ];
    }
}
