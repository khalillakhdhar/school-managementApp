<?php
namespace App\Filament\Resources;

use App\Filament\Resources\PayrollResource\Pages;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Payroll;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PayrollResource extends Resource
{
    protected static ?string $model = Payroll::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';
    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string  { return 'RH'; }
    public static function getNavigationLabel(): string   { return __('Payroll'); }
    public static function getModelLabel(): string        { return __('Pay Slip'); }
    public static function getPluralModelLabel(): string  { return __('Pay Slips'); }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private static function months(): array
    {
        return [
            1 => __('January'),  2 => __('February'), 3 => __('March'),
            4 => __('April'),    5 => __('May'),       6 => __('June'),
            7 => __('July'),     8 => __('August'),    9 => __('September'),
            10 => __('October'), 11 => __('November'), 12 => __('December'),
        ];
    }

    private static function isContractor(?int $employeeId): bool
    {
        if (!$employeeId) return false;
        return Employee::find($employeeId)?->isContractor() ?? false;
    }

    // ─── Recalculate for fixed-salary employees ────────────────────────────────

    protected static function recalculateFixed(callable $set, callable $get): void
    {
        $base      = (float)($get('salary_base')         ?? 0);
        $overtime  = (float)($get('overtime_pay')        ?? 0);
        $bonuses   = (float)($get('bonuses')             ?? 0);
        $transport = (float)($get('indemnite_transport') ?? 0);
        $logement  = (float)($get('indemnite_logement')  ?? 0);
        $autres    = (float)($get('autres_indemnites')   ?? 0);

        $gross    = round($base + $overtime + $bonuses + $transport + $logement + $autres, 3);
        $cnssBase = $base + $overtime + $bonuses;

        $cnss = round($cnssBase * 0.0918, 3);

        $abattement    = min($cnssBase * 0.10, 166.667);
        $baseAnnuelle  = max(0, $cnssBase - $cnss - $abattement) * 12;
        $irppAnnuel    = Payroll::irppBarem($baseAnnuelle);

        $employeeId = $get('employee_id');
        if ($employeeId) {
            $employee = Employee::find($employeeId);
            if ($employee) {
                if (in_array($employee->situation_familiale, ['marie', 'divorce', 'veuf'])) {
                    $irppAnnuel -= 300;
                }
                $irppAnnuel -= $employee->nb_enfants * 100;
            }
        }

        $irpp            = round(max(0, $irppAnnuel) / 12, 3);
        $otherDeductions = (float)($get('other_deductions') ?? 0);
        $net             = round(max(0, $gross - $cnss - $irpp - $otherDeductions), 3);

        $cnssPatronale  = round($cnssBase * 0.1657, 3);
        $foprolos       = round($cnssBase * 0.01, 3);

        $set('gross_salary',          $gross);
        $set('cnss_deduction',        $cnss);
        $set('irpp_deduction',        $irpp);
        $set('net_salary',            $net);
        $set('cnss_patronale',        $cnssPatronale);
        $set('foprolos',              $foprolos);
        $set('total_charge_patronale', round($cnssPatronale + $foprolos, 3));
    }

    // ─── Recalculate for contractor employees ─────────────────────────────────

    protected static function recalculateContractor(callable $set, callable $get): void
    {
        $rate  = (float)($get('hourly_rate_used')    ?? 0);
        $hours = (float)($get('total_hours_worked')  ?? 0);
        $rs    = (float)($get('retenue_source')      ?? 0);

        $gross = round($rate * $hours, 3);
        $net   = round(max(0, $gross - $rs), 3);

        $set('gross_salary', $gross);
        $set('net_salary',   $net);
        // Contractors: no CNSS/IRPP/charges patronales
        $set('cnss_deduction',         0);
        $set('irpp_deduction',         0);
        $set('cnss_patronale',         0);
        $set('foprolos',               0);
        $set('total_charge_patronale', 0);
    }

    // ─── Form ─────────────────────────────────────────────────────────────────

    public static function form(Schema $schema): Schema
    {
        $fixedLiveUpdate      = fn (callable $set, callable $get) => static::recalculateFixed($set, $get);
        $contractorLiveUpdate = fn (callable $set, callable $get) => static::recalculateContractor($set, $get);

        return $schema->components([

            // ── Employee & Period ──────────────────────────────────────────────
            Section::make(__('Employee & Period'))->schema([
                Forms\Components\Select::make('employee_id')
                    ->label(__('Employee'))
                    ->options(
                        Employee::active()->orderBy('last_name')->get()
                            ->mapWithKeys(fn ($e) => [
                                $e->id => $e->full_name
                                    . ($e->is_teacher  ? ' ★' : '')
                                    . ($e->isContractor() ? ' [' . __('Contractor') . ']' : ''),
                            ])
                    )
                    ->searchable()->required()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        if (!$state) return;
                        $e = Employee::find($state);
                        if (!$e) return;

                        if ($e->isContractor()) {
                            $set('hourly_rate_used', $e->hourly_rate);
                            $set('salary_base', 0);
                            static::recalculateContractor($set, $get);
                        } else {
                            $set('salary_base',         $e->salary_base);
                            $set('indemnite_transport', $e->indemnite_transport);
                            $set('indemnite_logement',  $e->indemnite_logement);
                            $set('autres_indemnites',   $e->autres_indemnites);
                            $set('hourly_rate_used', 0);
                            static::recalculateFixed($set, $get);
                        }
                    }),

                Forms\Components\Select::make('month')
                    ->label(__('Month'))
                    ->options(static::months())
                    ->required()->default((int)date('m')),

                Forms\Components\TextInput::make('year')
                    ->label(__('Year'))
                    ->required()->numeric()->default((int)date('Y'))
                    ->minValue(2000)->maxValue(2100),

                Forms\Components\Select::make('status')
                    ->label(__('Status'))
                    ->options([
                        'draft'     => __('Draft'),
                        'finalized' => __('Finalized'),
                        'paid'      => __('Paid'),
                        'rejected'  => __('Rejected'),
                    ])
                    ->required()->default('draft'),
            ])->columns(2),

            // ── Contractor Mode ────────────────────────────────────────────────
            Section::make(__('Contractor Billing'))
                ->description(__('Billing based on hours worked × hourly rate'))
                ->schema([
                    Forms\Components\DatePicker::make('period_from')
                        ->label(__('Period From'))
                        ->required(fn (callable $get) => static::isContractor($get('employee_id')))
                        ->default(fn () => now()->startOfMonth()),
                    Forms\Components\DatePicker::make('period_to')
                        ->label(__('Period To'))
                        ->required(fn (callable $get) => static::isContractor($get('employee_id')))
                        ->default(fn () => now()->endOfMonth()),
                    Forms\Components\TextInput::make('hourly_rate_used')
                        ->label(__('Hourly Rate (TND/h)'))
                        ->required()->numeric()->minValue(0)->prefix('TND')
                        ->live(onBlur: true)->afterStateUpdated($contractorLiveUpdate),
                    Forms\Components\TextInput::make('total_hours_worked')
                        ->label(__('Hours Worked'))
                        ->required()->numeric()->default(0)->minValue(0)->suffix('h')
                        ->live(onBlur: true)->afterStateUpdated($contractorLiveUpdate)
                        ->helperText(__('Edit manually or use "Load from Attendance" action in the table.')),
                    Forms\Components\TextInput::make('gross_salary')
                        ->label(__('Gross Amount'))
                        ->prefix('TND')->disabled()->dehydrated()
                        ->hint(__('Auto-calculated'))
                        ->hintIcon('heroicon-o-calculator')
                        ->hintColor('info'),
                    Forms\Components\TextInput::make('retenue_source')
                        ->label(__('Retenue à la source (RS 15%)'))
                        ->numeric()->default(0)->minValue(0)->prefix('TND')
                        ->live(onBlur: true)->afterStateUpdated($contractorLiveUpdate)
                        ->helperText(__('Applies to professional fees paid to individuals (Art. 52 CIRPPIS)')),
                    Forms\Components\TextInput::make('other_deductions')
                        ->label(__('Other Deductions'))
                        ->numeric()->default(0)->minValue(0)->prefix('TND')
                        ->live(onBlur: true)
                        ->afterStateUpdated($contractorLiveUpdate),
                    Forms\Components\TextInput::make('net_salary')
                        ->label(__('Amount to Pay'))
                        ->prefix('TND')->disabled()->dehydrated()
                        ->hint(__('Auto-calculated'))
                        ->hintIcon('heroicon-o-calculator')
                        ->hintColor('success')
                        ->extraAttributes(['class' => 'font-bold']),
                ])
                ->columns(2)
                ->visible(fn (callable $get) => static::isContractor($get('employee_id'))),

            // ── Fixed Salary Mode — Earnings ───────────────────────────────────
            Section::make(__('Earnings'))
                ->schema([
                    Forms\Components\TextInput::make('salary_base')
                        ->label(__('Base Salary'))
                        ->required()->numeric()->minValue(0)->prefix('TND')
                        ->live(onBlur: true)->afterStateUpdated($fixedLiveUpdate),
                    Forms\Components\TextInput::make('overtime_pay')
                        ->label(__('Overtime Pay'))
                        ->numeric()->default(0)->minValue(0)->prefix('TND')
                        ->live(onBlur: true)->afterStateUpdated($fixedLiveUpdate),
                    Forms\Components\TextInput::make('bonuses')
                        ->label(__('Bonuses / Premiums'))
                        ->numeric()->default(0)->minValue(0)->prefix('TND')
                        ->live(onBlur: true)->afterStateUpdated($fixedLiveUpdate),
                    Forms\Components\TextInput::make('indemnite_transport')
                        ->label(__('Transport Allowance'))
                        ->numeric()->default(0)->minValue(0)->prefix('TND')
                        ->live(onBlur: true)->afterStateUpdated($fixedLiveUpdate),
                    Forms\Components\TextInput::make('indemnite_logement')
                        ->label(__('Housing Allowance'))
                        ->numeric()->default(0)->minValue(0)->prefix('TND')
                        ->live(onBlur: true)->afterStateUpdated($fixedLiveUpdate),
                    Forms\Components\TextInput::make('autres_indemnites')
                        ->label(__('Other Allowances'))
                        ->numeric()->default(0)->minValue(0)->prefix('TND')
                        ->live(onBlur: true)->afterStateUpdated($fixedLiveUpdate),
                    Forms\Components\TextInput::make('gross_salary')
                        ->label(__('Gross Salary'))
                        ->prefix('TND')->disabled()->dehydrated()
                        ->hint(__('Auto-calculated'))
                        ->hintIcon('heroicon-o-calculator')
                        ->hintColor('info')
                        ->extraAttributes(['class' => 'font-bold']),
                ])
                ->columns(2)
                ->visible(fn (callable $get) => !static::isContractor($get('employee_id'))),

            // ── Fixed Salary Mode — Employee Deductions ────────────────────────
            Section::make(__('Employee Deductions (Retenues salariales)'))
                ->schema([
                    Forms\Components\TextInput::make('cnss_deduction')
                        ->label(__('CNSS Employee (9.18%)'))
                        ->prefix('TND')->numeric()->minValue(0)
                        ->live(onBlur: true)->afterStateUpdated($fixedLiveUpdate)
                        ->helperText(__('Auto-calculated. Editable for manual correction.')),
                    Forms\Components\TextInput::make('irpp_deduction')
                        ->label(__('IRPP (Tunisian 2024 scale)'))
                        ->prefix('TND')->numeric()->minValue(0)
                        ->live(onBlur: true)->afterStateUpdated($fixedLiveUpdate)
                        ->helperText(__('After professional expenses allowance and family deductions.')),
                    Forms\Components\TextInput::make('other_deductions')
                        ->label(__('Other Deductions'))
                        ->prefix('TND')->numeric()->default(0)->minValue(0)
                        ->live(onBlur: true)->afterStateUpdated($fixedLiveUpdate),
                    Forms\Components\TextInput::make('net_salary')
                        ->label(__('Net Salary'))
                        ->prefix('TND')->disabled()->dehydrated()
                        ->hint(__('Auto-calculated from deductions'))
                        ->hintIcon('heroicon-o-calculator')
                        ->hintColor('success')
                        ->extraAttributes(['class' => 'font-bold']),
                ])
                ->columns(2)
                ->visible(fn (callable $get) => !static::isContractor($get('employee_id'))),

            // ── Fixed Salary Mode — Employer Charges ──────────────────────────
            Section::make(__('Employer Charges (Charges patronales)'))
                ->schema([
                    Forms\Components\TextInput::make('cnss_patronale')
                        ->label(__('CNSS Employer (16.57%)'))
                        ->prefix('TND')->disabled()->dehydrated(),
                    Forms\Components\TextInput::make('foprolos')
                        ->label(__('FOPROLOS (1%)'))
                        ->prefix('TND')->disabled()->dehydrated()
                        ->helperText(__('Fonds de Promotion du Logement Social')),
                    Forms\Components\TextInput::make('total_charge_patronale')
                        ->label(__('Total Employer Charge'))
                        ->prefix('TND')->disabled()->dehydrated()
                        ->extraAttributes(['class' => 'font-bold']),
                ])
                ->columns(3)
                ->visible(fn (callable $get) => !static::isContractor($get('employee_id'))),

            // ── Notes ─────────────────────────────────────────────────────────
            Section::make(__('Notes'))->schema([
                Forms\Components\Textarea::make('notes')
                    ->label(__('Notes'))->rows(3)->columnSpanFull(),
            ]),

        ]);
    }

    // ─── Table ────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.first_name')
                    ->label('Employé')
                    ->formatStateUsing(fn ($state, $record) => $record->employee?->full_name ?? '—')
                    ->searchable()->sortable()
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold),
                Tables\Columns\TextColumn::make('employee.contract_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'permanent' => 'Permanent',
                        'temporary' => 'CDD',
                        'contract'  => 'Vacataire',
                        default     => $state ?? '—',
                    })
                    ->color(fn ($state) => match ($state) {
                        'permanent' => 'success',
                        'temporary' => 'warning',
                        'contract'  => 'primary',
                        default     => 'gray',
                    }),
                Tables\Columns\TextColumn::make('month')
                    ->label('Mois')
                    ->formatStateUsing(fn ($state) => static::months()[$state] ?? $state)
                    ->sortable(),
                Tables\Columns\TextColumn::make('year')
                    ->label('Année')->sortable(),
                Tables\Columns\TextColumn::make('period_from')
                    ->label('Période')
                    ->formatStateUsing(fn ($state, $record) => $record->period_from
                        ? $record->period_from->format('d/m') . ' → ' . ($record->period_to?->format('d/m') ?? '?')
                        : '—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('total_hours_worked')
                    ->label('Heures')
                    ->formatStateUsing(fn ($state) => $state > 0 ? "{$state}h" : '—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('gross_salary')
                    ->label('Brut')->money('TND')->sortable(),
                Tables\Columns\TextColumn::make('net_salary')
                    ->label('Net')->money('TND')->sortable()
                    ->color('success')
                    ->weight(\Filament\Support\Enums\FontWeight::Bold),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft'     => 'gray',
                        'finalized' => 'primary',
                        'paid'      => 'success',
                        'rejected'  => 'danger',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft'     => 'Brouillon',
                        'finalized' => 'Finalisé',
                        'paid'      => 'Payé',
                        'rejected'  => 'Rejeté',
                        default     => $state,
                    }),
            ])
            ->emptyStateIcon('heroicon-o-banknotes')
            ->emptyStateHeading('Aucune fiche de paie')
            ->emptyStateDescription('Générez les fiches de paie du personnel ici.')
            ->defaultSort('year', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'draft'     => __('Draft'),
                        'finalized' => __('Finalized'),
                        'paid'      => __('Paid'),
                        'rejected'  => __('Rejected'),
                    ]),
                Tables\Filters\SelectFilter::make('month')
                    ->label(__('Month'))
                    ->options(static::months()),
                Tables\Filters\SelectFilter::make('year')
                    ->label(__('Year'))
                    ->options(array_combine(
                        range(date('Y'), date('Y') - 3),
                        range(date('Y'), date('Y') - 3)
                    )),
                Tables\Filters\SelectFilter::make('employee_id')
                    ->label(__('Employee'))
                    ->relationship('employee', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn ($r) => $r?->full_name ?? '—')
                    ->searchable(),
            ])
            ->headerActions([
                // ── Generate contractor payslip from attendance ────────────────
                Actions\Action::make('generate_contractor')
                    ->label(__('Generate Contractor Payslip'))
                    ->icon('heroicon-o-clock')
                    ->color('primary')
                    ->form([
                        Forms\Components\Select::make('employee_id')
                            ->label(__('Contractor'))
                            ->options(
                                Employee::active()->contractors()->orderBy('last_name')->get()
                                    ->mapWithKeys(fn ($e) => [
                                        $e->id => "{$e->full_name} ({$e->hourly_rate} TND/h)",
                                    ])
                            )
                            ->searchable()->required(),
                        Forms\Components\DatePicker::make('period_from')
                            ->label(__('Period From'))
                            ->required()
                            ->default(fn () => now()->startOfMonth()),
                        Forms\Components\DatePicker::make('period_to')
                            ->label(__('Period To'))
                            ->required()
                            ->default(fn () => now()->endOfMonth()),
                        Forms\Components\TextInput::make('hourly_rate_override')
                            ->label(__('Override Hourly Rate (leave empty to use employee rate)'))
                            ->numeric()->minValue(0)->prefix('TND'),
                    ])
                    ->action(function (array $data): void {
                        $employee = Employee::findOrFail($data['employee_id']);
                        $from     = $data['period_from'];
                        $to       = $data['period_to'];

                        // Query attendance for the period
                        $attendance = Attendance::where('employee_id', $employee->id)
                            ->whereBetween('date', [$from, $to])
                            ->whereIn('status', ['present', 'late'])
                            ->whereNotNull('total_hours')
                            ->get();

                        $totalHours = round($attendance->sum('total_hours'), 2);
                        $rate       = (float)($data['hourly_rate_override'] ?: $employee->hourly_rate);
                        $gross      = round($rate * $totalHours, 3);
                        $rs         = round($gross * 0.15, 3); // RS 15% par défaut

                        $fromDate = \Carbon\Carbon::parse($from);

                        $existing = Payroll::where('employee_id', $employee->id)
                            ->where('month', $fromDate->month)
                            ->where('year', $fromDate->year)
                            ->first();

                        if ($existing) {
                            Notification::make()
                                ->title(__('A payslip already exists for :name for this period.', ['name' => $employee->full_name]))
                                ->warning()->send();
                            return;
                        }

                        Payroll::create([
                            'employee_id'        => $employee->id,
                            'month'              => $fromDate->month,
                            'year'               => $fromDate->year,
                            'period_from'        => $from,
                            'period_to'          => $to,
                            'total_hours_worked' => $totalHours,
                            'hourly_rate_used'   => $rate,
                            'salary_base'        => 0,
                            'gross_salary'       => $gross,
                            'retenue_source'     => $rs,
                            'net_salary'         => round(max(0, $gross - $rs), 3),
                            'status'             => 'draft',
                            'notes'              => __(':hours h worked from :from to :to (:days days with attendance)', [
                                'hours' => $totalHours,
                                'from'  => \Carbon\Carbon::parse($from)->format('d/m/Y'),
                                'to'    => \Carbon\Carbon::parse($to)->format('d/m/Y'),
                                'days'  => $attendance->count(),
                            ]),
                        ]);

                        Notification::make()
                            ->title(__('Contractor payslip generated: :hours h × :rate TND = :gross TND', [
                                'hours' => $totalHours,
                                'rate'  => $rate,
                                'gross' => number_format($gross, 3),
                            ]))
                            ->success()->send();
                    }),
            ])
            ->actions([
                // ── Load hours from attendance (contractor only) ───────────────
                Actions\Action::make('load_hours')
                    ->label(__('Load Hours from Attendance'))
                    ->icon('heroicon-o-clock')
                    ->color('info')
                    ->form([
                        Forms\Components\DatePicker::make('period_from')
                            ->label(__('Period From'))
                            ->required(),
                        Forms\Components\DatePicker::make('period_to')
                            ->label(__('Period To'))
                            ->required(),
                    ])
                    ->action(function (Payroll $record, array $data): void {
                        $attendance = Attendance::where('employee_id', $record->employee_id)
                            ->whereBetween('date', [$data['period_from'], $data['period_to']])
                            ->whereIn('status', ['present', 'late'])
                            ->whereNotNull('total_hours')
                            ->get();

                        $totalHours = round($attendance->sum('total_hours'), 2);
                        $rate       = (float)$record->hourly_rate_used;
                        $gross      = round($rate * $totalHours, 3);
                        $rs         = (float)$record->retenue_source;

                        $record->update([
                            'period_from'        => $data['period_from'],
                            'period_to'          => $data['period_to'],
                            'total_hours_worked' => $totalHours,
                            'gross_salary'       => $gross,
                            'net_salary'         => round(max(0, $gross - $rs), 3),
                            'notes'              => ($record->notes ? $record->notes . "\n" : '')
                                . __(':hours h worked (:days days) — loaded from attendance', [
                                    'hours' => $totalHours,
                                    'days'  => $attendance->count(),
                                ]),
                        ]);

                        Notification::make()
                            ->title(__(':hours hours loaded — :days days with attendance', [
                                'hours' => $totalHours,
                                'days'  => $attendance->count(),
                            ]))
                            ->success()->send();
                    })
                    ->visible(fn (Payroll $record) => $record->isContractorPayroll() && $record->status === 'draft'),

                Actions\Action::make('finalize')
                    ->label(__('Finalize'))
                    ->icon('heroicon-o-check-badge')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading(__('Finalize Pay Slip'))
                    ->modalDescription(__('This pay slip will be locked and sent for payment.'))
                    ->action(function (Payroll $record): void {
                        $record->update(['status' => 'finalized']);
                        Notification::make()->title(__('Pay slip finalized'))->success()->send();
                    })
                    ->visible(fn (Payroll $record) => $record->status === 'draft'),

                Actions\Action::make('mark_paid')
                    ->label(__('Mark as Paid'))
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Payroll $record): void {
                        $record->update(['status' => 'paid']);
                        Notification::make()->title(__('Pay slip marked as paid'))->success()->send();
                    })
                    ->visible(fn (Payroll $record) => $record->status === 'finalized'),

                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPayrolls::route('/'),
            'create' => Pages\CreatePayroll::route('/create'),
            'edit'   => Pages\EditPayroll::route('/{record}/edit'),
        ];
    }
}
