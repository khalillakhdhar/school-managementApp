<?php
namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Models\Employee;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-identification';
    protected static string|\UnitEnum|null $navigationGroup = 'RH';
    protected static ?string $modelLabel = 'Employé';
    protected static ?string $pluralModelLabel = 'Employés';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informations personnelles')->schema([
                Forms\Components\TextInput::make('first_name')->label('Prénom')->required()->maxLength(255),
                Forms\Components\TextInput::make('last_name')->label('Nom')->required()->maxLength(255),
                Forms\Components\TextInput::make('phone')->label('Téléphone')->required()->tel()->maxLength(20),
                Forms\Components\TextInput::make('email')->label('Email')->email()->maxLength(255),
                Forms\Components\Textarea::make('address')->label('Adresse')->columnSpanFull(),
            ])->columns(2),

            Section::make('Détails de l\'emploi')->schema([
                Forms\Components\TextInput::make('position')->label('Poste')->required()->maxLength(255),
                Forms\Components\Select::make('contract_type')
                    ->label('Type de contrat')
                    ->options(['permanent' => 'CDI', 'temporary' => 'CDD', 'contract' => 'Prestataire'])
                    ->required()->default('permanent'),
                Forms\Components\TextInput::make('salary_base')->label('Salaire de base')->required()->numeric()->prefix('TND'),
                Forms\Components\Toggle::make('is_active')->label('Actif')->default(true),
                Forms\Components\DatePicker::make('start_date')->label('Date de début')->required(),
                Forms\Components\DatePicker::make('end_date')->label('Date de fin'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('first_name')->label('Prénom')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('last_name')->label('Nom')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('position')->label('Poste')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('contract_type')
                    ->label('Contrat')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'permanent' => 'success',
                        'temporary' => 'warning',
                        'contract' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'permanent' => 'CDI',
                        'temporary' => 'CDD',
                        'contract' => 'Prestataire',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('salary_base')->label('Salaire')->money('TND')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label('Actif')->boolean(),
                Tables\Columns\TextColumn::make('start_date')->label('Début')->date()->sortable()->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Actif'),
                Tables\Filters\SelectFilter::make('contract_type')
                    ->label('Contrat')
                    ->options(['permanent' => 'CDI', 'temporary' => 'CDD', 'contract' => 'Prestataire']),
            ])
            ->actions([Actions\EditAction::make(), Actions\DeleteAction::make()])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
