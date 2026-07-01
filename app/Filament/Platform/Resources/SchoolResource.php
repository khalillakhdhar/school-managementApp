<?php

namespace App\Filament\Platform\Resources;

use App\Filament\Platform\Resources\SchoolResource\Pages;
use App\Models\School;
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

/**
 * PHASE 7 — the SaaS operator's view of every client school (tenant).
 * Lives on the /platform panel, which has no ->tenant(), so School (not
 * tenant-scoped) is visible across the whole platform here.
 */
class SchoolResource extends Resource
{
    protected static ?string $model = School::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    public static function getNavigationLabel(): string  { return __('Écoles'); }
    public static function getModelLabel(): string       { return __('École'); }
    public static function getPluralModelLabel(): string { return __('Écoles'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Identité'))
                ->icon('heroicon-o-building-office-2')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label(__('Nom de l\'école'))
                        ->required()->maxLength(150),
                    Forms\Components\TextInput::make('slug')
                        ->label(__('Slug (URL)'))
                        ->helperText(__('Laisser vide pour générer automatiquement.'))
                        ->maxLength(150)
                        ->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('email')->label(__('Email'))->email()->maxLength(150),
                    Forms\Components\TextInput::make('phone')->label(__('Téléphone'))->maxLength(50),
                    Forms\Components\TextInput::make('city')->label(__('Ville'))->maxLength(100),
                    Forms\Components\TextInput::make('country')->label(__('Pays'))->default('Tunisie')->maxLength(100),
                ])->columns(2),

            Section::make(__('Abonnement'))
                ->icon('heroicon-o-credit-card')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->label(__('Statut'))
                        ->options([
                            School::STATUS_ACTIVE    => __('Active'),
                            School::STATUS_TRIAL     => __('Essai'),
                            School::STATUS_SUSPENDED => __('Suspendue'),
                        ])
                        ->default(School::STATUS_TRIAL)
                        ->required(),
                    Forms\Components\TextInput::make('plan')
                        ->label(__('Plan'))->default('trial')->maxLength(50),
                    Forms\Components\DatePicker::make('trial_ends_at')
                        ->label(__('Fin d\'essai')),
                ])->columns(3),

            Section::make(__('Branding'))
                ->icon('heroicon-o-swatch')
                ->schema([
                    Forms\Components\ColorPicker::make('primary_color')
                        ->label(__('Couleur principale')),
                    Forms\Components\FileUpload::make('logo_path')
                        ->label(__('Logo'))
                        ->image()
                        ->directory('schools/logos')
                        ->visibility('public'),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('École'))->searchable()->sortable()
                    ->weight(FontWeight::SemiBold)
                    ->description(fn (School $r): string => '/' . $r->slug),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('Statut'))->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        School::STATUS_ACTIVE    => __('Active'),
                        School::STATUS_TRIAL     => __('Essai'),
                        School::STATUS_SUSPENDED => __('Suspendue'),
                        default                  => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        School::STATUS_ACTIVE    => 'success',
                        School::STATUS_TRIAL     => 'warning',
                        School::STATUS_SUSPENDED => 'danger',
                        default                  => 'gray',
                    }),
                Tables\Columns\TextColumn::make('plan')->label(__('Plan'))->badge()->color('gray'),
                Tables\Columns\TextColumn::make('students_count')
                    ->label(__('Élèves'))->counts('students')->badge()->color('info'),
                Tables\Columns\TextColumn::make('users_count')
                    ->label(__('Utilisateurs'))->counts('users')->badge()->color('primary'),
                Tables\Columns\TextColumn::make('trial_ends_at')
                    ->label(__('Fin d\'essai'))->date()->sortable()->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Créée le'))->date()->sortable()->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label(__('Statut'))->options([
                    School::STATUS_ACTIVE    => __('Active'),
                    School::STATUS_TRIAL     => __('Essai'),
                    School::STATUS_SUSPENDED => __('Suspendue'),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-building-office-2')
            ->emptyStateHeading(__('Aucune école'))
            ->emptyStateDescription(__('Créez une école via l\'interface ou `php artisan school:create`.'))
            ->actions([
                Actions\Action::make('impersonate')
                    ->label(__('Se connecter'))
                    ->icon('heroicon-o-arrow-right-end-on-rectangle')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading(fn (School $record) => __('Se connecter à :name', ['name' => $record->name]))
                    ->modalDescription(__('Vous serez connecté en tant qu\'administrateur de cette école. Une bannière permettra de revenir à la plateforme.'))
                    ->action(function (School $record) {
                        $admin = $record->users()->where('role', 'admin')->first();

                        if (! $admin) {
                            Notification::make()
                                ->title(__('Aucun administrateur rattaché à cette école.'))
                                ->danger()->send();
                            return null;
                        }

                        session()->put('impersonator_id', auth()->id());
                        auth()->login($admin);

                        return redirect("/admin/{$record->slug}");
                    }),
                Actions\Action::make('toggleStatus')
                    ->label(fn (School $record) => $record->isSuspended() ? __('Réactiver') : __('Suspendre'))
                    ->icon(fn (School $record) => $record->isSuspended() ? 'heroicon-o-play' : 'heroicon-o-pause')
                    ->color(fn (School $record) => $record->isSuspended() ? 'success' : 'warning')
                    ->requiresConfirmation()
                    ->action(function (School $record) {
                        $record->update([
                            'status' => $record->isSuspended() ? School::STATUS_ACTIVE : School::STATUS_SUSPENDED,
                        ]);
                        Notification::make()->title(__('Statut mis à jour.'))->success()->send();
                    }),
                Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSchools::route('/'),
            'create' => Pages\CreateSchool::route('/create'),
            'edit'   => Pages\EditSchool::route('/{record}/edit'),
        ];
    }
}
