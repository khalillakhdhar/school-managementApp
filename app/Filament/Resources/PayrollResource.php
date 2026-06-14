<?php
namespace App\Filament\Resources;

use App\Filament\Resources\PayrollResource\Pages;
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

    public static function getNavigationGroup(): ?string  { return __('HR'); }
    public static function getNavigationLabel(): string   { return __('Payroll'); }
    public static function getModelLabel(): string        { return __('Pay Slip'); }
    public static function getPluralModelLabel(): string  { return __('Pay Slips'); }

    // ─── Months helper ────────────────────────────────────────────────────────

    private static function months(): array
    {
        return [
            1 => __('January'),  2 => __('February'), 3 => __('March'),
            4 => __('April'),    5 => __('May'),       6 => __('June'),
            7 => __('July'),     8 => __('August'),    9 => __('September'),
            10 => __('October'), 11 => __('November'), 12 => __('December'),
        ];
    }

    // ─── Live auto-calculation helper ─────────────────────────────────────────

    /**
     * Recalculates all derived payroll fields (gross, CNSS, IRPP, net, charges patronales)
     * from the current form state. Called on every live field change.
     */
    protected static function recalculate(callable $set, callable $get): void
    {
        $base      = (float)($get('salary_base')         ?? 0);
        $overtime  = (float)($get('overtime_pay')        ?? 0);
        $bonuses   = (float)($get('bonuses')             ?? 0);
        $transport = (float)($get('indemnite_transport') ?? 0);
        $logement  = (float)($get('indemnite_logement')  ?? 0);
        $autres    = (float)($get('autres_indemnites')   ?? 0);

        // Gross = all earnings
        $gross = round($base + $overtime + $bonuses + $transport + $logement + $autres, 3);
        $set('gross_salary', $gross);

        // CNSS base: salaire de base + heures sup + primes (indemnités exonérées)
        $cnssBase = $base + $overtime + $bonuses;

        // CNSS salariale 9,18%
        $cnss = round($cnssBase * 0.0918, 3);
        $set('cnss_deduction', $cnss);

        // Abattement frais professionnels : 10%, plafonné à 2 000 TND/an → 166,667/mois
        $abattement = min($cnssBase * 0.10, 166.667);

        // Base imposable mensuelle, annualisée
        $baseMensuelle  = max(0, $cnssBase - $cnss - $abattement);
        $baseAnnuelle   = $baseMensuelle * 12;

        // IRPP annuel (barème 2024)
        $irppAnnuel = Payroll::irppBarem($baseAnnuelle);

        // Déductions chef de famille
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

        $irpp = round(max(0, $irppAnnuel) / 12, 3);
        $set('irpp_deduction', $irpp);

        $otherDeductions = (float)($get('other_deductions') ?? 0);
        $net = round(max(0, $gross - $cnss - $irpp - $otherDeductions), 3);
        $set('net_salary', $net);

        // Charges patronales
        $cnssPatronale = round($cnssBase * 0.1657, 3);
        $foprolos      = round($cnssBase * 0.01, 3);
        $set('cnss_patronale', $cnssPatronale);
        $set('foprolos', $foprolos);
        $set('total_charge_patronale', round($cnssPatronale + $foprolos, 3));
    }

    // ─── Form ─────────────────────────────────────────────────────────────────

    public static function form(Schema $schema): Schema
    {
        $liveUpdate = fn (callable $set, callable $get) => static::recalculate($set, $get);

        return $schema->components([

            Section::make(__('Employee & Period'))->schema([
                Forms\Components\Select::make('employee_id')
                    ->label(__('Employee'))
                    ->options(
                        Employee::active()
                            ->orderBy('last_name')
                            ->get()
                            ->mapWithKeys(fn ($e) => [
                                $e->id => $e->full_name . ($e->is_teacher ? ' ★' : ''),
                            ])
                    )
                    ->searchable()->required()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        if ($state) {
                            $e = Employee::find($state);
                            if ($e) {
                                $set('salary_base',          $e->salary_base);
                                $set('indemnite_transport',  $e->indemnite_transport);
                                $set('indemnite_logement',   $e->indemnite_logement);
                                $set('autres_indemnites',    $e->autres_indemnites);
                            }
                        }
                        static::recalculate($set, $get);
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

            Section::make(__('Earnings'))->schema([
                Forms\Components\TextInput::make('salary_base')
                    ->label(__('Base Salary'))
                    ->required()->numeric()->minValue(0)->prefix('TND')
                    ->live(onBlur: true)->afterStateUpdated($liveUpdate),
                Forms\Components\TextInput::make('overtime_pay')
                    ->label(__('Overtime Pay'))
                    ->numeric()->default(0)->minValue(0)->prefix('TND')
                    ->live(onBlur: true)->afterStateUpdated($liveUpdate),
                Forms\Components\TextInput::make('bonuses')
                    ->label(__('Bonuses / Premiums'))
                    ->numeric()->default(0)->minValue(0)->prefix('TND')
                    ->live(onBlur: true)->afterStateUpdated($liveUpdate),
                Forms\Components\TextInput::make('indemnite_transport')
                    ->label(__('Transport Allowance'))
                    ->numeric()->default(0)->minValue(0)->prefix('TND')
                    ->live(onBlur: true)->afterStateUpdated($liveUpdate),
                Forms\Components\TextInput::make('indemnite_logement')
                    ->label(__('Housing Allowance'))
                    ->numeric()->default(0)->minValue(0)->prefix('TND')
                    ->live(onBlur: true)->afterStateUpdated($liveUpdate),
                Forms\Components\TextInput::make('autres_indemnites')
                    ->label(__('Other Allowances'))
                    ->numeric()->default(0)->minValue(0)->prefix('TND')
                    ->live(onBlur: true)->afterStateUpdated($liveUpdate),
                Forms\Components\TextInput::make('gross_salary')
                    ->label(__('Gross Salary'))
                    ->prefix('TND')->disabled()->dehydrated()
                    ->extraAttributes(['class' => 'font-bold']),
            ])->columns(2),

            Section::make(__('Employee Deductions (Retenues salariales)'))->schema([
                Forms\Components\TextInput::make('cnss_deduction')
                    ->label(__('CNSS Employee (9.18%)'))
                    ->prefix('TND')->numeric()->minValue(0)
                    ->live(onBlur: true)->afterStateUpdated($liveUpdate)
                    ->helperText(__('Auto-calculated. Editable for manual correction.')),
                Forms\Components\TextInput::make('irpp_deduction')
                    ->label(__('IRPP (Tunisian 2024 scale)'))
                    ->prefix('TND')->numeric()->minValue(0)
                    ->live(onBlur: true)->afterStateUpdated($liveUpdate)
                    ->helperText(__('After professional expenses allowance and family deductions.')),
                Forms\Components\TextInput::make('other_deductions')
                    ->label(__('Other Deductions'))
                    ->prefix('TND')->numeric()->default(0)->minValue(0)
                    ->live(onBlur: true)->afterStateUpdated($liveUpdate),
                Forms\Components\TextInput::make('net_salary')
                    ->label(__('Net Salary'))
                    ->prefix('TND')->disabled()->dehydrated()
                    ->extraAttributes(['class' => 'font-bold text-success-600']),
            ])->columns(2),

            Section::make(__('Employer Charges (Charges patronales)'))->schema([
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
            ])->columns(3),

            Section::make(__('Notes'))->schema([
                Forms\Components\Textarea::make('notes')
                    ->label(__('Notes'))
                    ->rows(3)->columnSpanFull(),
            ]),

        ]);
    }

    // ─── Table ────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.first_name')
                    ->label(__('Employee'))
                    ->formatStateUsing(fn ($state, $record) => $record->employee?->full_name ?? '—')
                    ->searchable()->sortable(),
                Tables\Columns\TextColumn::make('month')
                    ->label(__('Month'))
                    ->formatStateUsing(fn ($state) => static::months()[$state] ?? $state)
                    ->sortable(),
                Tables\Columns\TextColumn::make('year')
                    ->label(__('Year'))->sortable(),
                Tables\Columns\TextColumn::make('gross_salary')
                    ->label(__('Gross Salary'))->money('TND')->sortable(),
                Tables\Columns\TextColumn::make('cnss_deduction')
                    ->label(__('CNSS'))->money('TND')->toggleable(),
                Tables\Columns\TextColumn::make('irpp_deduction')
                    ->label(__('IRPP'))->money('TND')->toggleable(),
                Tables\Columns\TextColumn::make('net_salary')
                    ->label(__('Net Salary'))->money('TND')->sortable()
                    ->color('success'),
                Tables\Columns\TextColumn::make('total_charge_patronale')
                    ->label(__('Employer Charge'))->money('TND')->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft'     => 'gray',
                        'finalized' => 'primary',
                        'paid'      => 'success',
                        'rejected'  => 'danger',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft'     => __('Draft'),
                        'finalized' => __('Finalized'),
                        'paid'      => __('Paid'),
                        'rejected'  => __('Rejected'),
                        default     => $state,
                    }),
            ])
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
                    ->getOptionLabelFromRecordUsing(fn ($r) => $r->full_name)
                    ->searchable(),
            ])
            ->actions([
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
