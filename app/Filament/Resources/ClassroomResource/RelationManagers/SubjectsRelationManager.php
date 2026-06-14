<?php
namespace App\Filament\Resources\ClassroomResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class SubjectsRelationManager extends RelationManager
{
    protected static string $relationship = 'subjects';
    protected static ?string $title = 'Matières assignées';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('weekly_hours')
                ->label('Heures / semaine')
                ->numeric()->minValue(0.5)->maxValue(40)->step(0.5)->default(2),
            Forms\Components\TextInput::make('coefficient')
                ->label('Coefficient (classe)')
                ->numeric()->minValue(0)->maxValue(10)->step(0.5)->default(1),
            Forms\Components\Toggle::make('is_active')
                ->label('Active')->default(true)->inline(false),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\ColorColumn::make('color')->label(''),
                Tables\Columns\TextColumn::make('name')
                    ->label('Matière')
                    ->searchable()
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold),
                Tables\Columns\TextColumn::make('code')
                    ->label('Code')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('pivot.weekly_hours')
                    ->label('H/sem.')
                    ->badge()->color('success')->suffix('h'),
                Tables\Columns\TextColumn::make('pivot.coefficient')
                    ->label('Coeff.')->badge()->color('primary')
                    ->formatStateUsing(fn ($state) => 'x' . $state),
                Tables\Columns\IconColumn::make('pivot.is_active')
                    ->label('Active')->boolean(),
            ])
            ->headerActions([Tables\Actions\AttachAction::make()->preloadRecordSelect()])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DetachAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DetachBulkAction::make()])]);
    }
}
