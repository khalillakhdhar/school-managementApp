<?php
namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Models\Service;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
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
        return __('Paramètres');
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
            Section::make(__('Service proposé'))
                ->description(__('Définissez un service ou une prestation facturée aux familles'))
                ->icon('heroicon-o-clipboard-document-list')
                ->schema([
                    Forms\Components\TextInput::make('name')->label(__('Nom du service'))->required()->maxLength(255),
                    Forms\Components\Select::make('type')
                        ->label(__('Périodicité de facturation'))
                        ->options([
                            'annual'  => 'Annuelle',
                            'monthly' => 'Mensuelle',
                            'daily'   => 'Journalière',
                            'custom'  => 'Personnalisée',
                        ])
                        ->required(),
                    Forms\Components\TextInput::make('amount')->label(__('Montant'))->required()->numeric()->prefix('TND'),
                    Forms\Components\Toggle::make('is_active')->label(__('Service actif'))->default(true)->inline(false),
                    Forms\Components\Textarea::make('description')->label(__('Description'))->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Service'))->searchable()->sortable()
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold),
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
                        'annual'  => 'Annuel',
                        'monthly' => 'Mensuel',
                        'daily'   => 'Quotidien',
                        'custom'  => 'Personnalisé',
                        default   => $state,
                    }),
                Tables\Columns\TextColumn::make('amount')
                    ->label(__('Montant'))->money('TND')->sortable()
                    ->weight(\Filament\Support\Enums\FontWeight::Bold),
                Tables\Columns\IconColumn::make('is_active')->label(__('Actif'))->boolean(),
                Tables\Columns\TextColumn::make('students_count')
                    ->counts('students')->label(__('Élèves inscrits'))
                    ->badge()->color('info'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label(__('Actif')),
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('Type'))
                    ->options([
                        'annual'  => 'Annuel',
                        'monthly' => 'Mensuel',
                        'daily'   => 'Quotidien',
                        'custom'  => 'Personnalisé',
                    ]),
            ])
            ->emptyStateIcon('heroicon-o-clipboard-document-list')
            ->emptyStateHeading('Aucun service configuré')
            ->emptyStateDescription('Créez les services proposés aux élèves (scolarité, transport, etc.).')
            ->emptyStateActions([Actions\CreateAction::make()->label(__('Créer un service'))])
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
