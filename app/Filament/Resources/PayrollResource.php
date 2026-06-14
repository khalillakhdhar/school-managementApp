<?php
namespace App\Filament\Resources;

use App\Filament\Resources\PayrollResource\Pages;
use App\Models\Payroll;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PayrollResource extends Resource
{
    protected static ?string $model = Payroll::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('HR');
    }

    public static function getNavigationLabel(): string
    {
        return __('Payroll');
    }

    public static function getModelLabel(): string
    {
        return __('Payroll');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Payroll');
    }

    private static function months(): array
    {
        return [
            1  => __('January'),  2  => __('February'), 3  => __('March'),
            4  => __('April'),    5  => __('May'),       6  => __('June'),
            7  => __('July'),     8  => __('August'),    9  => __('September'),
            10 => __('October'),  11 => __('November'),  12 => __('December'),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Select::make('employee_id')
                ->label(__('Employee'))
                ->relationship('employee', 'first_name')
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                ->searchable()->preload()->required(),
            Forms\Components\Select::make('month')
                ->label(__('Month'))
                ->options(static::months())
                ->required(),
            Forms\Components\TextInput::make('year')->label(__('Year'))->required()->numeric()->default(date('Y')),
            Forms\Components\TextInput::make('salary_base')->label(__('Base Salary'))->required()->numeric()->prefix('TND'),
            Forms\Components\TextInput::make('overtime_pay')->label(__('Overtime Pay'))->numeric()->default(0)->prefix('TND'),
            Forms\Components\TextInput::make('bonuses')->label(__('Bonuses'))->numeric()->default(0)->prefix('TND'),
            Forms\Components\TextInput::make('cnss_deduction')->label(__('CNSS Deduction'))->numeric()->default(0)->prefix('TND'),
            Forms\Components\TextInput::make('irpp_deduction')->label(__('IRPP Deduction'))->numeric()->default(0)->prefix('TND'),
            Forms\Components\TextInput::make('other_deductions')->label(__('Other Deductions'))->numeric()->default(0)->prefix('TND'),
            Forms\Components\TextInput::make('gross_salary')->label(__('Gross Salary'))->numeric()->prefix('TND')->disabled(),
            Forms\Components\TextInput::make('net_salary')->label(__('Net Salary'))->numeric()->prefix('TND')->disabled(),
            Forms\Components\Select::make('status')
                ->label(__('Status'))
                ->options([
                    'draft'     => __('Draft'),
                    'finalized' => __('Finalized'),
                    'paid'      => __('Paid'),
                    'rejected'  => __('Rejected'),
                ])
                ->required()->default('draft'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.first_name')->label(__('Employee'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('month')
                    ->label(__('Month'))
                    ->formatStateUsing(fn ($state) => static::months()[$state] ?? $state)
                    ->sortable(),
                Tables\Columns\TextColumn::make('year')->label(__('Year'))->sortable(),
                Tables\Columns\TextColumn::make('gross_salary')->label(__('Gross Salary'))->money('TND')->sortable(),
                Tables\Columns\TextColumn::make('net_salary')->label(__('Net Salary'))->money('TND')->sortable(),
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
                Tables\Filters\SelectFilter::make('year')
                    ->label(__('Year'))
                    ->options(array_combine(range(date('Y'), date('Y') - 3), range(date('Y'), date('Y') - 3))),
            ])
            ->actions([Actions\EditAction::make(), Actions\DeleteAction::make()])
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
