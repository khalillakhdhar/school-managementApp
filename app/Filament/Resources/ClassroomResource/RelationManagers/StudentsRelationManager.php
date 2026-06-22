<?php
namespace App\Filament\Resources\ClassroomResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Élèves de la classe');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('first_name')
                ->label(__('Prénom'))->required()->maxLength(255),
            Forms\Components\TextInput::make('last_name')
                ->label(__('Nom de famille'))->required()->maxLength(255),
            Forms\Components\DatePicker::make('date_of_birth')
                ->label(__('Date de naissance'))->required()->displayFormat('d/m/Y'),
            Forms\Components\TextInput::make('id_number')
                ->label(__('N° identité'))->maxLength(255),
            Forms\Components\DatePicker::make('enrollment_date')
                ->label(__("Date d'inscription"))->displayFormat('d/m/Y'),
            Forms\Components\Select::make('status')
                ->label(__('Statut'))
                ->options([
                    'active'    => __('Actif'),
                    'inactive'  => __('Inactif'),
                    'suspended' => __('Suspendu'),
                    'graduated' => __('Diplômé'),
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
                    ->label(__('Élève'))
                    ->searchable(['first_name', 'last_name'])
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('Statut'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active'    => 'success',
                        'inactive'  => 'danger',
                        'suspended' => 'warning',
                        'graduated' => 'info',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active'    => __('Actif'),
                        'inactive'  => __('Inactif'),
                        'suspended' => __('Suspendu'),
                        'graduated' => __('Diplômé'),
                        default     => $state,
                    }),
                Tables\Columns\TextColumn::make('date_of_birth')
                    ->label(__('Né(e) le'))->date('d/m/Y'),
                Tables\Columns\TextColumn::make('enrollment_date')
                    ->label(__('Inscription'))->date('d/m/Y'),
                Tables\Columns\TextColumn::make('id_number')
                    ->label(__('N° identité'))->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('last_name')
            ->headerActions([
                Tables\Actions\CreateAction::make()->label(__('Inscrire un élève dans cette classe')),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }
}
