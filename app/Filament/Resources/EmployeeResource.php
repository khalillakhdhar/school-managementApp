<?php
namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Filament\Resources\EmployeeResource\RelationManagers;
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
use Illuminate\Database\Eloquent\Builder;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-identification';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string  { return __('RH'); }
    public static function getNavigationLabel(): string   { return __('Employees'); }
    public static function getModelLabel(): string        { return __('Employee'); }
    public static function getPluralModelLabel(): string  { return __('Employees'); }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withSum([
                'payrolls as unpaid_hours_sum' => fn ($query) => $query->whereIn('status', ['draft', 'finalized'])
            ], 'total_hours_worked')
            ->withSum([
                'payrolls as unpaid_amount_sum' => fn ($query) => $query->whereIn('status', ['draft', 'finalized'])
            ], 'net_salary');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            Section::make(__('Informations personnelles'))
                ->description(__('Identité et coordonnées du membre du personnel'))
                ->icon('heroicon-o-user')
                ->schema([
                    Forms\Components\TextInput::make('first_name')
                        ->label(__('Prénom'))->required()->maxLength(255),
                    Forms\Components\TextInput::make('last_name')
                        ->label(__('Nom de famille'))->required()->maxLength(255),
                    Forms\Components\TextInput::make('cin')
                        ->label(__('CIN'))->maxLength(20)->placeholder('00000000'),
                    Forms\Components\TextInput::make('phone')
                        ->label(__('Téléphone'))->required()->tel()->maxLength(20),
                    Forms\Components\TextInput::make('email')
                        ->label(__('Email professionnel'))->email()->maxLength(255),
                    Forms\Components\TextInput::make('rib')
                        ->label(__('RIB bancaire'))->maxLength(24)->placeholder('00 000 0000000000000 00'),
                    Forms\Components\Textarea::make('address')
                        ->label(__('Adresse'))->columnSpanFull(),
                ])->columns(2),

            Section::make(__('Détails du poste'))
                ->description('Type de contrat, poste occupé et dates d\'emploi')
                ->icon('heroicon-o-briefcase')
                ->schema([
                    Forms\Components\Select::make('contract_type')
                        ->label(__('Type de contrat'))
                        ->options([
                            'permanent' => 'CDI (Permanent)',
                            'temporary' => 'CDD (Durée déterminée)',
                            'contract'  => 'Vacataire / Prestataire',
                        ])
                        ->required()->default('permanent')->live(),
                    Forms\Components\TextInput::make('position')
                        ->label(__('Intitulé du poste'))->required()->maxLength(255),
                    Forms\Components\DatePicker::make('start_date')
                        ->label(__('Date de prise de poste'))->required()->displayFormat('d/m/Y'),
                    Forms\Components\DatePicker::make('end_date')
                        ->label(__('Date de fin de contrat'))->displayFormat('d/m/Y'),
                    Forms\Components\Toggle::make('is_active')
                        ->label(__('Employé actif'))->default(true)->inline(false),
                    Forms\Components\Toggle::make('is_teacher')
                        ->label(__('Est enseignant'))
                        ->helperText('Active les champs spécifiques aux enseignants et l\'affectation aux classes')
                        ->default(false)->inline(false)->live(),
                ])->columns(2),

            Section::make(__('Profil enseignant'))
                ->description(__('Matière enseignée, numéro CNSS et situation familiale'))
                ->icon('heroicon-o-academic-cap')
                ->schema([
                    Forms\Components\TextInput::make('specialite')
                        ->label(__('Matière / Spécialité'))->maxLength(255),
                    Forms\Components\TextInput::make('matricule_cnss')
                        ->label(__('Matricule CNSS'))->maxLength(30),
                    Forms\Components\Select::make('situation_familiale')
                        ->label(__('Situation familiale'))
                        ->options([
                            'celibataire' => 'Célibataire',
                            'marie'       => 'Marié(e)',
                            'divorce'     => 'Divorcé(e)',
                            'veuf'        => 'Veuf/Veuve',
                        ])
                        ->default('celibataire')->required(),
                    Forms\Components\TextInput::make('nb_enfants')
                        ->label('Nombre d\'enfants')
                        ->numeric()->default(0)->minValue(0)->maxValue(20),
                ])
                ->columns(2)
                ->visible(fn (callable $get) => (bool) $get('is_teacher')),

            Section::make(__('Classes assignées'))
                ->description(__('Sélectionnez les classes dont cet enseignant est responsable'))
                ->icon('heroicon-o-building-office-2')
                ->schema([
                    Forms\Components\Select::make('classroom_ids')
                        ->label(__('Classes'))
                        ->options(function () {
                            return Classroom::with(['level', 'teacher'])
                                ->get()
                                ->mapWithKeys(fn ($c) => [
                                    $c->id => $c->full_name
                                        . ($c->teacher
                                            ? ' — (actuel: ' . $c->teacher->full_name . ')'
                                            : ''),
                                ]);
                        })
                        ->multiple()
                        ->searchable()
                        ->columnSpanFull()
                        ->helperText('Les classes ayant déjà un enseignant l\'affichent entre parenthèses.'),
                ])
                ->visible(fn (callable $get) => (bool) $get('is_teacher')),

            Section::make(__('Rémunération et indemnités'))
                ->description(__('Salaire de base, indemnités et taux horaire pour les vacataires'))
                ->icon('heroicon-o-currency-dollar')
                ->schema([
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
                Tables\Columns\TextColumn::make('id')
                    ->label('#')->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('first_name')
                    ->label(__('Employé'))
                    ->formatStateUsing(fn ($state, Employee $record): string => $record->full_name)
                    ->searchable(['first_name', 'last_name'])
                    ->sortable()
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold),
                Tables\Columns\TextColumn::make('position')
                    ->label(__('Poste'))->searchable()->sortable(),
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
                        $total = $record->unpaid_hours_sum ?? 0;
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
                        $total = $record->unpaid_amount_sum ?? 0;
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
                // ── Create a staff login account ───────────────────────────────
                Actions\Action::make('create_account')
                    ->label(__('Créer un compte'))
                    ->icon('heroicon-o-key')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading(fn (Employee $record) => 'Créer un accès pour ' . $record->full_name)
                    ->modalDescription(__('Un compte « Espace Personnel » sera créé. Le mot de passe temporaire devra être changé à la première connexion.'))
                    ->action(function (Employee $record): void {
                        $result = \App\Services\AccountService::forEmployee($record, null, true);
                        $loginUrl = url('/staff/login');
                        try {
                            \Illuminate\Support\Facades\Mail::to($result['email'])
                                ->send(new \App\Mail\StaffWelcomeMail($record, $result['email'], $result['password'], $loginUrl));
                            Notification::make()
                                ->title(__('Compte créé — email envoyé à :email', ['email' => $result['email']]))
                                ->success()->send();
                        } catch (\Throwable $e) {
                            $notification = Notification::make()
                                ->title(__('Compte créé pour :name', ['name' => $record->full_name]))
                                ->body(app()->environment('local')
                                    ? __('Identifiant : :email — Mot de passe : :password', ['email' => $result['email'], 'password' => $result['password']])
                                    : __('Identifiant : :email. Envoyez le mot de passe via un canal de confiance.', ['email' => $result['email']]))
                                ->warning();

                            if (app()->environment('local')) {
                                $notification->persistent();
                            }

                            $notification->send();
                        }
                    })
                    ->visible(fn (Employee $record) => $record->user_id === null),
                Actions\Action::make('reset_account_password')
                    ->label(__('Réinitialiser mot de passe'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (Employee $record): void {
                        $result = \App\Services\AccountService::forEmployee($record, null, true);
                        $notification = Notification::make()
                            ->title(__('Mot de passe réinitialisé'))
                            ->body(app()->environment('local')
                                ? __('Nouveau mot de passe : :password', ['password' => $result['password']])
                                : __('Envoyez le nouveau mot de passe via un canal de confiance.'))
                            ->warning();

                        if (app()->environment('local')) {
                            $notification->persistent();
                        }

                        $notification->send();
                    })
                    ->visible(fn (Employee $record) => $record->user_id !== null),
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
            ->emptyStateIcon('heroicon-o-identification')
            ->emptyStateHeading('Aucun employé enregistré')
            ->emptyStateDescription('Ajoutez les membres du personnel de l\'établissement.')
            ->emptyStateActions([Actions\CreateAction::make()->label(__('Ajouter un employé'))])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelationManagers(): array
    {
        return [
            RelationManagers\PayrollsRelationManager::class,
            RelationManagers\AttendancesRelationManager::class,
        ];
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
