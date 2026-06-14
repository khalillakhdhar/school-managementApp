<?php
namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Models\Service;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('Finance');
    }

    public static function getNavigationLabel(): string
    {
        return __('Services');
    }

    public static function getModelLabel(): string
    {
        return __('Service');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Services');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('name')->label(__('Name'))->required()->maxLength(255),
            Forms\Components\Select::make('type')
                ->label(__('Type'))
                ->options([
                    'annual'  => __('Annual'),
                    'monthly' => __('Monthly'),
                    'daily'   => __('Daily'),
                    'custom'  => __('Custom'),
                ])
                ->required(),
            Forms\Components\TextInput::make('amount')->label(__('Amount'))->required()->numeric()->prefix('TND'),
            Forms\Components\Toggle::make('is_active')->label(__('Active'))->default(true),
            Forms\Components\Textarea::make('description')->label(__('Description'))->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('name')->label(__('Name'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('Type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'annual'  => 'primary',
                        'monthly' => 'success',
                        'daily'   => 'warning',
                        'custom'  => 'gray',
                        default   => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'annual'  => __('Annual'),
                        'monthly' => __('Monthly'),
                        'daily'   => __('Daily'),
                        'custom'  => __('Custom'),
                        default   => $state,
                    }),
                Tables\Columns\TextColumn::make('amount')->label(__('Amount'))->money('TND')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label(__('Active'))->boolean(),
                Tables\Columns\TextColumn::make('students_count')->counts('students')->label(__('Students')),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label(__('Active')),
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('Type'))
                    ->options([
                        'annual'  => __('Annual'),
                        'monthly' => __('Monthly'),
                        'daily'   => __('Daily'),
                        'custom'  => __('Custom'),
                    ]),
            ])
            ->actions([Actions\EditAction::make(), Actions\DeleteAction::make()])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit'   => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
