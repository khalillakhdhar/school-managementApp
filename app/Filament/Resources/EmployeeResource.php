<?php
namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Models\Employee;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-identification';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string  { return __('HR'); }
    public static function getNavigationLabel(): string   { return __('Employees'); }
    public static function getModelLabel(): string        { return __('Employee'); }
    public static function getPluralModelLabel(): string  { return __('Employees'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Personal Information'))->schema([
                Forms\Components\TextInput::make('first_name')
                    ->label(__('First Name'))->required()->maxLength(255),
                Forms\Components\TextInput::make('last_name')
                    ->label(__('Last Name'))->required()->maxLength(255),
                Forms\Components\TextInput::make('cin')
                    ->label(__('CIN'))->maxLength(20)
                    ->placeholder('00000000'),
                Forms\Components\TextInput::make('phone')
                    ->label(__('Phone'))->required()->tel()->maxLength(20),
                Forms\Components\TextInput::make('email')
                    ->label(__('Email'))->email()->maxLength(255),
                Forms\Components\TextInput::make('rib')
                    ->label(__('RIB'))->maxLength(24)
                    ->placeholder('00 000 0000000000000 00'),
                Forms\Components\Textarea::make('address')
                    ->label(__('Address'))->columnSpanFull(),
            ])->columns(2),

            Section::make(__('Employment Details'))->schema([
                Forms\Components\Select::make('contract_type')
                    ->label(__('Contract Type'))
                    ->options([
                        'permanent' => __('Permanent'),
                        'temporary' => __('Fixed-term'),
                        'contract'  => __('Contractor'),
                    ])
                    ->required()->default('permanent')->live(),
                Forms\Components\TextInput::make('position')
                    ->label(__('Position'))->required()->maxLength(255),
                Forms\Components\DatePicker::make('start_date')
                    ->label(__('Start Date'))->required(),
                Forms\Components\DatePicker::make('end_date')
                    ->label(__('End Date')),
                Forms\Components\Toggle::make('is_active')
                    ->label(__('Active'))->default(true)->inline(false),
                Forms\Components\Toggle::make('is_teacher')
                    ->label(__('Is Teacher'))
                    ->helperText(__('Enables classroom assignment and teaching-specific fields'))
                    ->default(false)->inline(false)
                    ->live(),
            ])->columns(2),

            Section::make(__('Teacher Information'))
                ->schema([
                    Forms\Components\TextInput::make('specialite')
                        ->label(__('Subject / Speciality'))->maxLength(255),
                    Forms\Components\TextInput::make('matricule_cnss')
                        ->label(__('CNSS Number'))->maxLength(30),
                    Forms\Components\Select::make('situation_familiale')
                        ->label(__('Marital Status'))
                        ->options([
                            'celibataire' => __('Single'),
                            'marie'       => __('Married'),
                            'divorce'     => __('Divorced'),
                            'veuf'        => __('Widowed'),
                        ])
                        ->default('celibataire')->required(),
                    Forms\Components\TextInput::make('nb_enfants')
                        ->label(__('Number of Children'))
                        ->numeric()->default(0)->minValue(0)->maxValue(20),
                ])
                ->columns(2)
                ->visible(fn (callable $get) => (bool) $get('is_teacher')),

            Section::make(__('Salary & Allowances'))->schema([
                Forms\Components\TextInput::make('salary_base')
                    ->label(__('Base Salary'))
                    ->numeric()->minValue(0)->prefix('TND')
                    ->required(fn (callable $get) => $get('contract_type') !== 'contract')
                    ->visible(fn (callable $get) => $get('contract_type') !== 'contract'),
                Forms\Components\TextInput::make('hourly_rate')
                    ->label(__('Hourly Rate'))
                    ->numeric()->minValue(0)->prefix('TND')
                    ->required(fn (callable $get) => $get('contract_type') === 'contract')
                    ->visible(fn (callable $get) => $get('contract_type') === 'contract')
                    ->helperText(__('Rate per hour billed to the school')),
                Forms\Components\TextInput::make('indemnite_transport')
                    ->label(__('Transport Allowance'))
                    ->numeric()->default(0)->minValue(0)->prefix('TND')
                    ->visible(fn (callable $get) => $get('contract_type') !== 'contract'),
                Forms\Components\TextInput::make('indemnite_logement')
                    ->label(__('Housing Allowance'))
                    ->numeric()->default(0)->minValue(0)->prefix('TND')
                    ->visible(fn (callable $get) => $get('contract_type') !== 'contract'),
                Forms\Components\TextInput::make('autres_indemnites')
                    ->label(__('Other Allowances'))
                    ->numeric()->default(0)->minValue(0)->prefix('TND')
                    ->visible(fn (callable $get) => $get('contract_type') !== 'contract'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('first_name')
                    ->label(__('First Name'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->label(__('Last Name'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('position')
                    ->label(__('Position'))->searchable()->sortable(),
                Tables\Columns\IconColumn::make('is_teacher')
                    ->label(__('Teacher'))
                    ->boolean()
                    ->trueColor('primary')
                    ->falseColor('gray'),
                Tables\Columns\TextColumn::make('classrooms_count')
                    ->label(__('Classes'))
                    ->counts('classrooms')
                    ->badge()->color('primary')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('contract_type')
                    ->label(__('Contract Type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'permanent' => 'success',
                        'temporary' => 'warning',
                        'contract'  => 'gray',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'permanent' => __('Permanent'),
                        'temporary' => __('Fixed-term'),
                        'contract'  => __('Contractor'),
                        default     => $state,
                    }),
                Tables\Columns\TextColumn::make('salary_base')
                    ->label(__('Base Salary'))->money('TND')->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('Active'))->boolean(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label(__('Start Date'))->date()->sortable()->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label(__('Active')),
                Tables\Filters\TernaryFilter::make('is_teacher')->label(__('Teachers only')),
                Tables\Filters\SelectFilter::make('contract_type')
                    ->label(__('Contract Type'))
                    ->options([
                        'permanent' => __('Permanent'),
                        'temporary' => __('Fixed-term'),
                        'contract'  => __('Contractor'),
                    ]),
            ])
            ->actions([Actions\EditAction::make(), Actions\DeleteAction::make()])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit'   => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
