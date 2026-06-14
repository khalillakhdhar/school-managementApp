<?php
namespace App\Filament\Resources;

use App\Filament\Resources\PayrollResource\Pages;
use App\Models\Payroll;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;

class PayrollResource extends Resource
{
    protected static ?string $model = Payroll::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-currency-dollar';
    protected static string|\UnitEnum|null $navigationGroup = 'RH';
    protected static ?string $modelLabel = 'Fiche de paie';
    protected static ?string $pluralModelLabel = 'Fiches de paie';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Select::make('employee_id')
                ->label('Employé')
                ->relationship('employee', 'first_name')
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                ->searchable()->preload()->required(),
            Forms\Components\Select::make('month')
                ->label('Mois')
                ->options([
                    1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
                    5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
                    9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
                ])
                ->required(),
            Forms\Components\TextInput::make('year')->label('Année')->required()->numeric()->default(date('Y')),
            Forms\Components\TextInput::make('salary_base')->label('Salaire de base')->required()->numeric()->prefix('TND'),
            Forms\Components\TextInput::make('overtime_pay')->label('Heures sup.')->numeric()->default(0)->prefix('TND'),
            Forms\Components\TextInput::make('bonuses')->label('Primes')->numeric()->default(0)->prefix('TND'),
            Forms\Components\TextInput::make('cnss_deduction')->label('Déduction CNSS')->numeric()->default(0)->prefix('TND'),
            Forms\Components\TextInput::make('irpp_deduction')->label('Déduction IRPP')->numeric()->default(0)->prefix('TND'),
            Forms\Components\TextInput::make('other_deductions')->label('Autres déductions')->numeric()->default(0)->prefix('TND'),
            Forms\Components\TextInput::make('gross_salary')->label('Salaire brut')->numeric()->prefix('TND')->disabled(),
            Forms\Components\TextInput::make('net_salary')->label('Salaire net')->numeric()->prefix('TND')->disabled(),
            Forms\Components\Select::make('status')
                ->label('Statut')
                ->options(['draft' => 'Brouillon', 'finalized' => 'Finalisé', 'paid' => 'Payé', 'rejected' => 'Rejeté'])
                ->required()->default('draft'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.first_name')->label('Employé')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('month')
                    ->label('Mois')
                    ->formatStateUsing(fn ($state) => [
                        1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
                        5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
                        9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
                    ][$state] ?? $state)
                    ->sortable(),
                Tables\Columns\TextColumn::make('year')->label('Année')->sortable(),
                Tables\Columns\TextColumn::make('gross_salary')->label('Brut')->money('TND')->sortable(),
                Tables\Columns\TextColumn::make('net_salary')->label('Net')->money('TND')->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'finalized' => 'primary',
                        'paid' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Brouillon',
                        'finalized' => 'Finalisé',
                        'paid' => 'Payé',
                        'rejected' => 'Rejeté',
                        default => $state,
                    }),
            ])
            ->defaultSort('year', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options(['draft' => 'Brouillon', 'finalized' => 'Finalisé', 'paid' => 'Payé', 'rejected' => 'Rejeté']),
                Tables\Filters\SelectFilter::make('year')
                    ->label('Année')
                    ->options(array_combine(range(date('Y'), date('Y') - 3), range(date('Y'), date('Y') - 3))),
            ])
            ->actions([Actions\EditAction::make(), Actions\DeleteAction::make()])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayrolls::route('/'),
            'create' => Pages\CreatePayroll::route('/create'),
            'edit' => Pages\EditPayroll::route('/{record}/edit'),
        ];
    }
}
