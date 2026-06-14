<?php
namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Filament\Resources\StudentResource\RelationManagers;
use App\Models\Classroom;
use App\Models\Student;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'Académique';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['first_name', 'last_name', 'id_number'];
    }

    public static function getGlobalSearchResultTitle(\Illuminate\Database\Eloquent\Model $record): string
    {
        return $record->full_name;
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Classe'  => $record->classroom?->name ?? '—',
            'Statut'  => $record->status,
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('Students');
    }

    public static function getModelLabel(): string
    {
        return __('Student');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Students');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informations personnelles')
                ->description('Identité civile de l\'élève')
                ->icon('heroicon-o-user')
                ->schema([
                    Forms\Components\TextInput::make('first_name')
                        ->label('Prénom')->required()->maxLength(255),
                    Forms\Components\TextInput::make('last_name')
                        ->label('Nom de famille')->required()->maxLength(255),
                    Forms\Components\DatePicker::make('date_of_birth')
                        ->label('Date de naissance')->required()->displayFormat('d/m/Y'),
                    Forms\Components\TextInput::make('id_number')
                        ->label('N° identité (CIN/passeport)')->maxLength(255),
                ])->columns(2),

            Section::make('Scolarité')
                ->description('Classe, niveau et statut de l\'élève')
                ->icon('heroicon-o-academic-cap')
                ->schema([
                    Forms\Components\Select::make('classroom_id')
                        ->label('Classe assignée')
                        ->options(
                            Classroom::with('level')->orderBy('name')->get()
                                ->mapWithKeys(fn ($c) => [
                                    $c->id => ($c->level?->code ? "{$c->level->code} — {$c->name}" : $c->name),
                                ])
                        )
                        ->nullable()->searchable()->placeholder('Sélectionner une classe'),
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
                    Forms\Components\Textarea::make('address')
                        ->label('Adresse')->columnSpanFull()->rows(2),
                ])->columns(2),

            Section::make('Informations médicales')
                ->description('Données de santé confidentielles')
                ->icon('heroicon-o-heart')
                ->schema([
                    Forms\Components\Textarea::make('health_info')
                        ->label('Informations santé')->rows(3),
                    Forms\Components\Textarea::make('allergies')
                        ->label('Allergies connues')->rows(3),
                    Forms\Components\Textarea::make('medications')
                        ->label('Médicaments')->rows(3),
                ])->columns(3)->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('first_name')
                    ->label('Élève')
                    ->formatStateUsing(fn ($state, Student $record): string => $record->full_name)
                    ->searchable(['first_name', 'last_name'])
                    ->sortable()
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold),
                Tables\Columns\TextColumn::make('classroom.name')
                    ->label('Classe')
                    ->badge()->color('primary')->sortable(),
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
                Tables\Columns\TextColumn::make('pending_balance')
                    ->label('Solde dû')
                    ->getStateUsing(fn (Student $record): float =>
                        $record->payments()->where('status', 'pending')->sum('amount')
                    )
                    ->money('TND')
                    ->badge()
                    ->color(fn (Student $record): string =>
                        $record->payments()->where('status', 'pending')->whereDate('due_date', '<', now())->exists()
                            ? 'danger'
                            : ($record->payments()->where('status', 'pending')->exists() ? 'warning' : 'success')
                    )
                    ->sortable(false),
                Tables\Columns\TextColumn::make('date_of_birth')
                    ->label('Date de naissance')->date('d/m/Y')->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('enrollment_date')
                    ->label('Inscription')->date('d/m/Y')->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')->dateTime('d/m/Y H:i')->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('last_name')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'active'    => 'Actif',
                        'inactive'  => 'Inactif',
                        'suspended' => 'Suspendu',
                        'graduated' => 'Diplômé',
                    ]),
                Tables\Filters\SelectFilter::make('classroom_id')
                    ->label('Classe')
                    ->relationship('classroom', 'name'),
            ])
            ->emptyStateIcon('heroicon-o-academic-cap')
            ->emptyStateHeading('Aucun élève inscrit')
            ->emptyStateDescription('Commencez par inscrire le premier élève de l\'établissement.')
            ->emptyStateActions([Actions\CreateAction::make()->label('Inscrire un élève')])
            ->actions([Actions\EditAction::make(), Actions\DeleteAction::make()])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelationManagers(): array
    {
        return [
            RelationManagers\PaymentsRelationManager::class,
            RelationManagers\ServicesRelationManager::class,
            RelationManagers\IncidentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit'   => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
