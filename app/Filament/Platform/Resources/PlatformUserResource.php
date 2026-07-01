<?php

namespace App\Filament\Platform\Resources;

use App\Filament\Platform\Resources\PlatformUserResource\Pages;
use App\Models\User;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * PHASE 7 — cross-tenant user directory for the SaaS operator. User is not
 * tenant-scoped, so the /platform panel sees every account (admins, teachers,
 * parents, and other platform admins) with the school(s) they belong to.
 */
class PlatformUserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $slug = 'users';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';
    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string  { return __('Utilisateurs'); }
    public static function getModelLabel(): string       { return __('Utilisateur'); }
    public static function getPluralModelLabel(): string { return __('Utilisateurs'); }

    private const ROLES = [
        'platform_admin' => 'Super-admin',
        'admin'          => 'Admin école',
        'teacher'        => 'Enseignant',
        'employee'       => 'Employé',
        'parent'         => 'Parent',
    ];

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Compte'))
                ->icon('heroicon-o-user')
                ->schema([
                    Forms\Components\TextInput::make('name')->label(__('Nom'))->required()->maxLength(150),
                    Forms\Components\TextInput::make('email')->label(__('Email'))->email()->required()
                        ->unique(ignoreRecord: true)->maxLength(150),
                    Forms\Components\Select::make('role')->label(__('Rôle'))
                        ->options(self::ROLES)->required(),
                    Forms\Components\Toggle::make('must_change_password')
                        ->label(__('Doit changer le mot de passe')),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Nom'))->searchable()->sortable()
                    ->weight(FontWeight::SemiBold)
                    ->description(fn (User $r): string => $r->email),
                Tables\Columns\TextColumn::make('role')
                    ->label(__('Rôle'))->badge()
                    ->formatStateUsing(fn (string $state): string => self::ROLES[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'platform_admin' => 'purple',
                        'admin'          => 'primary',
                        'teacher', 'employee' => 'info',
                        'parent'         => 'gray',
                        default          => 'gray',
                    }),
                Tables\Columns\TextColumn::make('schools.name')
                    ->label(__('École(s)'))->badge()->color('success')
                    ->placeholder('—')
                    ->listWithLineBreaks()->limitList(2),
                Tables\Columns\IconColumn::make('must_change_password')
                    ->label(__('MDP à changer'))->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Créé le'))->date()->sortable()->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('role')->label(__('Rôle'))->options(self::ROLES),
            ])
            ->emptyStateIcon('heroicon-o-user-group')
            ->emptyStateHeading(__('Aucun utilisateur'))
            ->actions([
                Actions\Action::make('resetPassword')
                    ->label(__('Réinitialiser MDP'))
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading(fn (User $record) => __('Réinitialiser le mot de passe de :name', ['name' => $record->name]))
                    ->action(function (User $record) {
                        $temp = Str::password(12);
                        $record->forceFill([
                            'password'             => Hash::make($temp),
                            'must_change_password' => true,
                        ])->save();

                        Notification::make()
                            ->title(__('Mot de passe réinitialisé'))
                            ->body(__('Nouveau mot de passe temporaire : :pwd', ['pwd' => $temp]))
                            ->success()->persistent()->send();
                    }),
                Actions\EditAction::make(),
                Actions\DeleteAction::make()
                    ->visible(fn (User $record): bool => $record->id !== auth()->id()),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlatformUsers::route('/'),
            'edit'  => Pages\EditPlatformUser::route('/{record}/edit'),
        ];
    }
}
