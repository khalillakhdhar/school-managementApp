<?php
namespace App\Filament\Resources\SubjectResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class TeachersRelationManager extends RelationManager
{
    protected static string $relationship = 'teachers';
    protected static ?string $title = 'Enseignants assignés';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('specialization')
                ->label(__('Spécialisation'))
                ->maxLength(100),
            Forms\Components\TextInput::make('max_hours_per_week')
                ->label(__('Heures max / semaine'))
                ->numeric()->minValue(1)->maxValue(40)->default(20),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('full_name')
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label(__('Enseignant'))
                    ->searchable(['first_name', 'last_name'])
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold),
                Tables\Columns\TextColumn::make('specialite')
                    ->label(__('Spécialité générale'))
                    ->color('gray'),
                Tables\Columns\TextColumn::make('pivot.specialization')
                    ->label(__('Spécialisation matière'))
                    ->badge()->color('primary'),
                Tables\Columns\TextColumn::make('pivot.max_hours_per_week')
                    ->label(__('H max/sem.'))
                    ->badge()->color('warning')
                    ->suffix('h'),
            ])
            ->headerActions([Tables\Actions\AttachAction::make()->preloadRecordSelect()])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DetachAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DetachBulkAction::make()])]);
    }
}
