<?php
namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Models\Classroom;
use App\Models\Employee;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
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
                    ->label(__('CIN'))->maxLength(20)->placeholder('00000000'),
                Forms\Components\TextInput::make('phone')
                    ->label(__('Phone'))->required()->tel()->maxLength(20),
                Forms\Components\TextInput::make('email')
                    ->label(__('Email'))->email()->maxLength(255),
                Forms\Components\TextInput::make('rib')
                    ->label(__('RIB'))->maxLength(24)->placeholder('00 000 0000000000000 00'),
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
                    ->default(false)->inline(false)->live(),
            ])->columns(2),

            // ── Teacher profile (visible when is_teacher) ─────────────────────
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

            // ── Classroom assignment (visible when is_teacher) ─────────────────
            Section::make(__('Assigned Classes'))
                ->schema([
                    Forms\Components\Select::make('classroom_ids')
                        ->label(__('Classes'))
                        ->options(function () {
                            return Classroom::with(['level', 'teacher'])
                                ->get()
                                ->mapWithKeys(fn ($c) => [
                                    $c->id => $c->full_name
                                        . ($c->teacher
                                            ? ' — (' . __('current') . ': ' . $c->teacher->full_name . ')'
                                            : ''),
                                ]);
                        })
                        ->multiple()
                        ->searchable()
                        ->columnSpanFull()
                        ->helperText(__('Classes with a teacher already assigned are shown with that teacher\'s name.')),
                ])
                ->visible(fn (callable $get) => (bool) $get('is_teacher')),

            // ── Salary & Allowances ───────────────────────────────────────────
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
                    ->label(__('Teacher'))->boolean()
                    ->trueColor('primary')->falseColor('gray'),
                Tables\Columns\TextColumn::make('classrooms_count')
                    ->label(__('Classes'))
                    ->counts('classrooms')
                    ->badge()->color('primary')->toggleable(),
                Tables\Columns\TextColumn::make('contract_type')
                    ->label(__('Contract Type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'permanent' => 'success',
                        'temporary' => 'warning',
                        'contract'  => 'primary',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'permanent' => __('Permanent'),
                        'temporary' => __('Fixed-term'),
                        'contract'  => __('Contractor'),
                        default     => $state,
                    }),
                Tables\Columns\TextColumn::make('salary_base')
                    ->label(__('Base Salary'))->money('TND')->sortable()
                    ->visible(fn () => true)
                    ->formatStateUsing(fn ($state, $record) => $record->isContractor()
                        ? "{$record->hourly_rate} TND/h"
                        : number_format((float)$state, 3) . ' TND'),

                // ── Contractor-specific columns ────────────────────────────────
                Tables\Columns\TextColumn::make('unpaid_hours')
                    ->label(__('Unpaid Hours'))
                    ->badge()
                    ->getStateUsing(function (Employee $record): string {
                        if (!$record->isContractor()) return '—';
                        $total = $record->payrolls()
                            ->whereIn('status', ['draft', 'finalized'])
                            ->sum('total_hours_worked');
                        return $total > 0 ? "{$total} h" : '0 h';
                    })
                    ->color(fn (string $state): string => match (true) {
                        $state === '—' || $state === '0 h' => 'gray',
                        default => 'warning',
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('unpaid_amount')
                    ->label(__('Amount Due'))
                    ->badge()
                    ->getStateUsing(function (Employee $record): string {
                        if (!$record->isContractor()) return '—';
                        $total = $record->payrolls()
                            ->whereIn('status', ['draft', 'finalized'])
                            ->sum('net_salary');
                        return $total > 0
                            ? number_format((float)$total, 3) . ' TND'
                            : '0 TND';
                    })
                    ->color(fn (string $state): string => match (true) {
                        $state === '—' || $state === '0 TND' => 'gray',
                        default => 'danger',
                    })
                    ->toggleable(),

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
            ->actions([
                // ── Pay all pending payslips for a contractor ──────────────────
                Actions\Action::make('pay_contractor')
                    ->label(__('Pay All Pending'))
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading(fn (Employee $record) => __('Pay :name', ['name' => $record->full_name]))
                    ->modalDescription(function (Employee $record): string {
                        $payrolls = $record->payrolls()
                            ->whereIn('status', ['draft', 'finalized'])
                            ->get();
                        $hours  = round($payrolls->sum('total_hours_worked'), 2);
                        $amount = number_format($payrolls->sum('net_salary'), 3);
                        return __('Mark :count payslip(s) as paid. :hours h worked — total: :amount TND', [
                            'count'  => $payrolls->count(),
                            'hours'  => $hours,
                            'amount' => $amount,
                        ]);
                    })
                    ->action(function (Employee $record): void {
                        $count = $record->payrolls()
                            ->whereIn('status', ['draft', 'finalized'])
                            ->update(['status' => 'paid']);
                        Notification::make()
                            ->title(__(':count payslip(s) paid for :name', [
                                'count' => $count,
                                'name'  => $record->full_name,
                            ]))
                            ->success()->send();
                    })
                    ->visible(fn (Employee $record): bool =>
                        $record->isContractor() &&
                        $record->payrolls()->whereIn('status', ['draft', 'finalized'])->exists()
                    ),

                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
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
