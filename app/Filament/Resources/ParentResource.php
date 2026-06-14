<?php
namespace App\Filament\Resources;

use App\Filament\Resources\ParentResource\Pages;
use App\Mail\ParentWelcomeMail;
use App\Models\SchoolParent;
use App\Models\User;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ParentResource extends Resource
{
    protected static ?string $model = SchoolParent::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string { return __('Students'); }
    public static function getNavigationLabel(): string  { return __('Parents'); }
    public static function getModelLabel(): string       { return __('Parent'); }
    public static function getPluralModelLabel(): string { return __('Parents'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Parent Information'))->schema([
                Forms\Components\TextInput::make('first_name')->label(__('First Name'))->required()->maxLength(255),
                Forms\Components\TextInput::make('last_name')->label(__('Last Name'))->required()->maxLength(255),
                Forms\Components\TextInput::make('phone')->label(__('Phone'))->required()->tel()->maxLength(20),
                Forms\Components\TextInput::make('email')->label(__('Email'))->email()->maxLength(255),
                Forms\Components\TextInput::make('occupation')->label(__('Occupation'))->maxLength(255),
                Forms\Components\Toggle::make('is_payer')->label(__('Primary Payer'))->default(false),
                Forms\Components\Textarea::make('address')->label(__('Address'))->columnSpanFull(),
            ])->columns(2),

            Section::make(__('Assigned Students'))->schema([
                Forms\Components\Select::make('students')
                    ->label(__('Assigned Students'))
                    ->relationship('students', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn ($r) => $r?->full_name ?? '—')
                    ->multiple()->preload()->searchable()->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name')->label(__('First Name'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('last_name')->label(__('Last Name'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('phone')->label(__('Phone'))->searchable(),
                Tables\Columns\TextColumn::make('email')->label(__('Email'))->searchable()->toggleable(),
                Tables\Columns\IconColumn::make('is_payer')->label(__('Payer'))->boolean(),
                Tables\Columns\TextColumn::make('students_count')->counts('students')->label(__('Children'))->badge()->color('info'),
                Tables\Columns\IconColumn::make('user_id')
                    ->label(__('Portal Account'))
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->user_id !== null)
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_payer')->label(__('Primary Payer')),
            ])
            ->actions([
                Actions\Action::make('create_account')
                    ->label(__('Create Portal Account'))
                    ->icon('heroicon-o-key')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading(__('Create Parent Portal Account'))
                    ->modalDescription(fn ($record) => __('Create a login account for :name and send credentials by email.', ['name' => $record->full_name]))
                    ->action(function (SchoolParent $record): void {
                        if (!$record->email) {
                            Notification::make()
                                ->title(__('No email address for this parent'))
                                ->danger()->send();
                            return;
                        }

                        $tempPassword = Str::password(10, symbols: false);

                        $user = User::create([
                            'name'                 => $record->full_name,
                            'email'                => $record->email,
                            'password'             => Hash::make($tempPassword),
                            'role'                 => 'parent',
                            'must_change_password' => true,
                        ]);

                        $record->update(['user_id' => $user->id]);

                        $loginUrl = url('/parent/login');

                        try {
                            Mail::to($record->email)->send(new ParentWelcomeMail($record, $tempPassword, $loginUrl));
                            Notification::make()
                                ->title(__('Account created and email sent to :email', ['email' => $record->email]))
                                ->success()->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title(__('Account created. Temp password: :password', ['password' => $tempPassword]))
                                ->warning()->persistent()->send();
                        }
                    })
                    ->visible(fn ($record) => $record->user_id === null && $record->email),
                Actions\Action::make('reset_password')
                    ->label(__('Reset Password'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (SchoolParent $record): void {
                        $tempPassword = Str::password(10, symbols: false);
                        $record->user?->update([
                            'password'             => Hash::make($tempPassword),
                            'must_change_password' => true,
                        ]);
                        try {
                            Mail::to($record->email)->send(new ParentWelcomeMail($record, $tempPassword, url('/parent/login')));
                            Notification::make()->title(__('Password reset and email sent'))->success()->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title(__('New temp password: :password', ['password' => $tempPassword]))
                                ->warning()->persistent()->send();
                        }
                    })
                    ->visible(fn ($record) => $record->user_id !== null),
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListParents::route('/'),
            'create' => Pages\CreateParent::route('/create'),
            'edit'   => Pages\EditParent::route('/{record}/edit'),
        ];
    }
}
