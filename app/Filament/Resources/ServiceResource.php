<?php
namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Models\Service;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static string|\UnitEnum|null $navigationGroup = 'Finance';
    protected static ?string $modelLabel = 'Service';
    protected static ?string $pluralModelLabel = 'Services';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('name')->label('Nom')->required()->maxLength(255),
            Forms\Components\Select::make('type')
                ->label('Type')
                ->options(['annual' => 'Annuel', 'monthly' => 'Mensuel', 'daily' => 'Journalier', 'custom' => 'Personnalisé'])
                ->required(),
            Forms\Components\TextInput::make('amount')->label('Montant')->required()->numeric()->prefix('TND'),
            Forms\Components\Toggle::make('is_active')->label('Actif')->default(true),
            Forms\Components\Textarea::make('description')->label('Description')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Nom')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'annual' => 'primary',
                        'monthly' => 'success',
                        'daily' => 'warning',
                        'custom' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'annual' => 'Annuel',
                        'monthly' => 'Mensuel',
                        'daily' => 'Journalier',
                        'custom' => 'Personnalisé',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('amount')->label('Montant')->money('TND')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label('Actif')->boolean(),
                Tables\Columns\TextColumn::make('students_count')->counts('students')->label('Élèves'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Actif'),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options(['annual' => 'Annuel', 'monthly' => 'Mensuel', 'daily' => 'Journalier', 'custom' => 'Personnalisé']),
            ])
            ->actions([Actions\EditAction::make(), Actions\DeleteAction::make()])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
