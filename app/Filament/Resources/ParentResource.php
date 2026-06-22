<?php
namespace App\Filament\Resources;

use App\Filament\Resources\ParentResource\Pages;
use App\Mail\ParentWelcomeMail;
use App\Models\SchoolParent;
use App\Services\AccountService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;

class ParentResource extends Resource
{
    protected static ?string $model = SchoolParent::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string { return __('Académique'); }
    public static function getNavigationLabel(): string  { return __('Parents'); }
    public static function getModelLabel(): string       { return __('Parent'); }
    public static function getPluralModelLabel(): string { return __('Parents'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Informations du parent'))
                ->description(__('Identité, coordonnées et profession du responsable légal'))
                ->icon('heroicon-o-user-circle')
                ->schema([
                    Forms\Components\TextInput::make('first_name')->label(__('Prénom'))->required()->maxLength(255),
                    Forms\Components\TextInput::make('last_name')->label(__('Nom de famille'))->required()->maxLength(255),
                    Forms\Components\TextInput::make('phone')->label(__('Téléphone'))->required()->tel()->maxLength(20),
                    Forms\Components\TextInput::make('email')->label(__('Adresse email'))->email()->maxLength(255),
                    Forms\Components\TextInput::make('occupation')->label(__('Profession'))->maxLength(255),
                    Forms\Components\Toggle::make('is_payer')->label(__('Payeur principal des frais de scolarité'))->default(false)->inline(false),
                    Forms\Components\Textarea::make('address')->label(__('Adresse'))->columnSpanFull(),
                ])->columns(2),

            Section::make(__('Élèves rattachés'))
                ->description(__("Associez ce parent aux enfants scolarisés dans l'établissement"))
                ->icon('heroicon-o-academic-cap')
                ->schema([
                    Forms\Components\Select::make('students')
                        ->label(__('Enfants inscrits'))
                        ->relationship('students', 'first_name')
                        ->getOptionLabelFromRecordUsing(fn ($r) => $r?->full_name ?? '—')
                        ->multiple()->preload()->searchable()->columnSpanFull()
                        ->placeholder(__('Rechercher un élève...')),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name')
                    ->label(__('Parent'))
                    ->formatStateUsing(fn ($state, SchoolParent $record): string => $record->full_name)
                    ->searchable(['first_name', 'last_name'])
                    ->sortable()
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold),
                Tables\Columns\TextColumn::make('phone')
                    ->label(__('Téléphone'))->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('Email'))->searchable()->toggleable(),
                Tables\Columns\IconColumn::make('is_payer')
                    ->label(__('Payeur principal'))->boolean(),
                Tables\Columns\TextColumn::make('students_count')
                    ->counts('students')->label(__('Enfants'))
                    ->badge()->color('info'),
                Tables\Columns\IconColumn::make('user_id')
                    ->label(__('Accès portail'))
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->user_id !== null)
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_payer')->label(__('Payeur principal')),
            ])
            ->emptyStateIcon('heroicon-o-users')
            ->emptyStateHeading(__('Aucun parent enregistré'))
            ->emptyStateDescription(__('Ajoutez les parents des élèves pour activer le portail parents.'))
            ->emptyStateActions([Actions\CreateAction::make()->label(__('Ajouter un parent'))])
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

                        $result = AccountService::forParent($record, null, true);
                        $loginUrl = url('/parent/login');

                        try {
                            Mail::to($record->email)->send(new ParentWelcomeMail($record, $result['password'], $loginUrl));
                            Notification::make()
                                ->title(__('Account created and email sent to :email', ['email' => $record->email]))
                                ->success()->send();
                        } catch (\Exception $e) {
                            $notification = Notification::make()
                                ->title(__('Account created, but the email could not be sent'))
                                ->body(app()->environment('local')
                                    ? __('Temporary password: :password', ['password' => $result['password']])
                                    : __('Send the credentials through a trusted channel.'))
                                ->warning();

                            if (app()->environment('local')) {
                                $notification->persistent();
                            }

                            $notification->send();
                        }
                    })
                    ->visible(fn ($record) => $record->user_id === null && $record->email),
                Actions\Action::make('reset_password')
                    ->label(__('Reset Password'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (SchoolParent $record): void {
                        $result = AccountService::forParent($record, null, true);
                        try {
                            Mail::to($record->email)->send(new ParentWelcomeMail($record, $result['password'], url('/parent/login')));
                            Notification::make()->title(__('Password reset and email sent'))->success()->send();
                        } catch (\Exception $e) {
                            $notification = Notification::make()
                                ->title(__('Password reset, but the email could not be sent'))
                                ->body(app()->environment('local')
                                    ? __('Temporary password: :password', ['password' => $result['password']])
                                    : __('Send the credentials through a trusted channel.'))
                                ->warning();

                            if (app()->environment('local')) {
                                $notification->persistent();
                            }

                            $notification->send();
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
