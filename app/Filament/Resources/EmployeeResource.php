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

    public static function getNavigationGroup(): ?string
    {
        return __('HR');
    }

    public static function getNavigationLabel(): string
    {
        return __('Employees');
    }

    public static function getModelLabel(): string
    {
        return __('Employee');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Employees');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Personal Information'))->schema([
                Forms\Components\TextInput::make('first_name')->label(__('First Name'))->required()->maxLength(255),
                Forms\Components\TextInput::make('last_name')->label(__('Last Name'))->required()->maxLength(255),
                Forms\Components\TextInput::make('phone')->label(__('Phone'))->required()->tel()->maxLength(20),
                Forms\Components\TextInput::make('email')->label(__('Email'))->email()->maxLength(255),
                Forms\Components\Textarea::make('address')->label(__('Address'))->columnSpanFull(),
            ])->columns(2),

            Section::make(__('Employment Details'))->schema([
                Forms\Components\TextInput::make('position')->label(__('Position'))->required()->maxLength(255),
                Forms\Components\Select::make('contract_type')
                    ->label(__('Contract Type'))
                    ->options([
                        'permanent' => __('Permanent'),
                        'temporary' => __('Fixed-term'),
                        'contract'  => __('Contractor'),
                    ])
                    ->required()->default('permanent'),
                Forms\Components\TextInput::make('salary_base')->label(__('Base Salary'))->required()->numeric()->prefix('TND'),
                Forms\Components\Toggle::make('is_active')->label(__('Active'))->default(true),
                Forms\Components\DatePicker::make('start_date')->label(__('Start Date'))->required(),
                Forms\Components\DatePicker::make('end_date')->label(__('End Date')),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('first_name')->label(__('First Name'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('last_name')->label(__('Last Name'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('position')->label(__('Position'))->searchable()->sortable(),
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
                Tables\Columns\TextColumn::make('salary_base')->label(__('Base Salary'))->money('TND')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label(__('Active'))->boolean(),
                Tables\Columns\TextColumn::make('start_date')->label(__('Start Date'))->date()->sortable()->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label(__('Active')),
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
