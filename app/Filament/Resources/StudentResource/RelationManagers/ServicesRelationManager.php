<?php
namespace App\Filament\Resources\StudentResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ServicesRelationManager extends RelationManager
{
    protected static string $relationship = 'services';
    protected static ?string $title       = 'Services souscrits';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('amount_override')
                ->label(__('Montant personnalisé (TND)'))
                ->numeric()->minValue(0)->prefix('TND')
                ->helperText(__('Laissez vide pour utiliser le tarif standard du service')),
            Forms\Components\DatePicker::make('start_date')
                ->label(__('Date de début'))->displayFormat('d/m/Y'),
            Forms\Components\DatePicker::make('end_date')
                ->label(__('Date de fin'))->displayFormat('d/m/Y'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Service'))
                    ->searchable()
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('Type'))->badge()->color('primary'),
                Tables\Columns\TextColumn::make('amount')
                    ->label(__('Tarif standard'))->money('TND')->color('gray'),
                Tables\Columns\TextColumn::make('pivot.amount_override')
                    ->label(__('Tarif appliqué'))
                    ->formatStateUsing(fn ($state, $record): string =>
                        $state ? number_format((float) $state, 3) . ' TND' : number_format((float) $record->amount, 3) . ' TND'
                    )
                    ->badge()->color('success'),
                Tables\Columns\TextColumn::make('pivot.start_date')
                    ->label(__('Du'))->date('d/m/Y'),
                Tables\Columns\TextColumn::make('pivot.end_date')
                    ->label(__('Au'))->date('d/m/Y'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label(__('Souscrire à un service'))
                    ->preloadRecordSelect(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make()->label(__('Désabonner')),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DetachBulkAction::make()])]);
    }
}
