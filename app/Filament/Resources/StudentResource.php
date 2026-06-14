<?php
namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Models\Student;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';
    protected static string|\UnitEnum|null $navigationGroup = 'Élèves';
    protected static ?string $navigationLabel = 'Élèves';
    protected static ?string $modelLabel = 'Élève';
    protected static ?string $pluralModelLabel = 'Élèves';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informations personnelles')->schema([
                Forms\Components\TextInput::make('first_name')->label('Prénom')->required()->maxLength(255),
                Forms\Components\TextInput::make('last_name')->label('Nom')->required()->maxLength(255),
                Forms\Components\DatePicker::make('date_of_birth')->label('Date de naissance')->required(),
                Forms\Components\TextInput::make('id_number')->label('N° identifiant')->maxLength(255),
            ])->columns(2),

            Section::make('Informations scolaires')->schema([
                Forms\Components\TextInput::make('class')->label('Classe')->required()->maxLength(255),
                Forms\Components\TextInput::make('level')->label('Niveau')->required()->maxLength(255),
                Forms\Components\Select::make('status')
                    ->label('Statut')
                    ->options(['active' => 'Actif', 'inactive' => 'Inactif'])
                    ->default('active')->required(),
                Forms\Components\Textarea::make('address')->label('Adresse')->columnSpanFull(),
            ])->columns(2),

            Section::make('Informations de santé')->schema([
                Forms\Components\Textarea::make('health_info')->label('Infos santé'),
                Forms\Components\Textarea::make('allergies')->label('Allergies'),
                Forms\Components\Textarea::make('medications')->label('Médicaments'),
            ])->columns(3)->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('first_name')->label('Prénom')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('last_name')->label('Nom')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('class')->label('Classe')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('level')->label('Niveau')->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Actif',
                        'inactive' => 'Inactif',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('date_of_birth')->label('Naissance')->date()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Créé le')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options(['active' => 'Actif', 'inactive' => 'Inactif']),
                Tables\Filters\SelectFilter::make('class')
                    ->label('Classe')
                    ->options(fn () => Student::distinct()->pluck('class', 'class')),
            ])
            ->actions([Actions\EditAction::make(), Actions\DeleteAction::make()])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
