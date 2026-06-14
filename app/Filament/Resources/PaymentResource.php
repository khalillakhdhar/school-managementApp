<?php
namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';
    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('Finance');
    }

    public static function getNavigationLabel(): string
    {
        return __('Payments');
    }

    public static function getModelLabel(): string
    {
        return __('Payment');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Payments');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Select::make('student_id')
                ->label(__('Student'))
                ->relationship('student', 'first_name')
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                ->searchable()->preload()->required(),
            Forms\Components\TextInput::make('amount')->label(__('Amount'))->required()->numeric()->prefix('TND'),
            Forms\Components\DatePicker::make('payment_date')->label(__('Payment Date'))->required()->default(now()),
            Forms\Components\Select::make('payment_method')
                ->label(__('Payment Method'))
                ->options([
                    'cash'          => __('Cash'),
                    'bank_transfer' => __('Bank Transfer'),
                    'cheque'        => __('Cheque'),
                    'app'           => __('App'),
                ])
                ->required()->default('cash'),
            Forms\Components\Select::make('status')
                ->label(__('Status'))
                ->options([
                    'paid'      => __('Paid'),
                    'pending'   => __('Pending'),
                    'failed'    => __('Failed'),
                    'cancelled' => __('Cancelled'),
                ])
                ->required()->default('paid'),
            Forms\Components\TextInput::make('reference_number')->label(__('Reference'))->maxLength(255),
            Forms\Components\Textarea::make('notes')->label(__('Notes'))->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('student.first_name')->label(__('Student'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('amount')->label(__('Amount'))->money('TND')->sortable(),
                Tables\Columns\TextColumn::make('payment_date')->label(__('Payment Date'))->date()->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label(__('Method'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cash'          => 'success',
                        'bank_transfer' => 'primary',
                        'cheque'        => 'warning',
                        'app'           => 'gray',
                        default         => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cash'          => __('Cash'),
                        'bank_transfer' => __('Bank Transfer'),
                        'cheque'        => __('Cheque'),
                        'app'           => __('App'),
                        default         => $state,
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid'      => 'success',
                        'pending'   => 'warning',
                        'failed'    => 'danger',
                        'cancelled' => 'gray',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'paid'      => __('Paid'),
                        'pending'   => __('Pending'),
                        'failed'    => __('Failed'),
                        'cancelled' => __('Cancelled'),
                        default     => $state,
                    }),
                Tables\Columns\TextColumn::make('reference_number')->label(__('Reference'))->toggleable(),
            ])
            ->defaultSort('payment_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'paid'      => __('Paid'),
                        'pending'   => __('Pending'),
                        'failed'    => __('Failed'),
                        'cancelled' => __('Cancelled'),
                    ]),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label(__('Method'))
                    ->options([
                        'cash'          => __('Cash'),
                        'bank_transfer' => __('Bank Transfer'),
                        'cheque'        => __('Cheque'),
                        'app'           => __('App'),
                    ]),
            ])
            ->actions([Actions\EditAction::make(), Actions\DeleteAction::make()])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit'   => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
