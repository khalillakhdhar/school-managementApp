<?php
namespace App\Filament\Resources\ClassroomResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'students';
    protected static ?string $title       = 'Élèves de la classe';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('first_name')
                ->label('Prénom')->required()->maxLength(255),
            Forms\Components\TextInput::make('last_name')
                ->label('Nom de famille')->required()->maxLength(255),
            Forms\Components\DatePicker::make('date_of_birth')
                ->label('Date de naissance')->required()->displayFormat('d/m/Y'),
            Forms\Components\TextInput::make('id_number')
                ->label('N° identité')->maxLength(255),
            Forms\Components\DatePicker::make('enrollment_date')
                ->label('Date d\'inscription')->displayFormat('d/m/Y'),
            Forms\Components\Select::make('status')
                ->label('Statut')
                ->options([
                    'active'    => 'Actif',
                    'inactive'  => 'Inactif',
                    'suspended' => 'Suspendu',
                    'graduated' => 'Diplômé',
                ])
                ->default('active')->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('full_name')
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Élève')
                    ->searchable(['first_name', 'last_name'])
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active'    => 'success',
                        'inactive'  => 'danger',
                        'suspended' => 'warning',
                        'graduated' => 'info',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active'    => 'Actif',
                        'inactive'  => 'Inactif',
                        'suspended' => 'Suspendu',
                        'graduated' => 'Diplômé',
                        default     => $state,
                    }),
                Tables\Columns\TextColumn::make('date_of_birth')
                    ->label('Né(e) le')->date('d/m/Y'),
                Tables\Columns\TextColumn::make('enrollment_date')
                    ->label('Inscription')->date('d/m/Y'),
                Tables\Columns\TextColumn::make('id_number')
                    ->label('N° identité')->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('last_name')
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Inscrire un élève dans cette classe'),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }
}
