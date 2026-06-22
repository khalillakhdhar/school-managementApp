<?php
namespace App\Filament\Resources;

use App\Filament\Resources\IncidentResource\Pages;
use App\Mail\IncidentNotificationMail;
use App\Models\Incident;
use App\Models\Student;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;

class IncidentResource extends Resource
{
    protected static ?string $model = Incident::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string { return __('Communication'); }
    public static function getNavigationLabel(): string  { return __('Incidents'); }
    public static function getModelLabel(): string       { return __('Incident'); }
    public static function getPluralModelLabel(): string { return __('Incidents'); }

    public static function getNavigationBadge(): ?string
    {
        $count = Incident::where('parent_notified', false)->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__("Signalement de l'incident"))
                ->description(__("Renseignez les détails de l'événement pour le dossier de l'élève"))
                ->icon('heroicon-o-exclamation-triangle')
                ->schema([
                    Forms\Components\Select::make('student_id')
                        ->label(__('Élève concerné'))
                        ->options(
                            Student::orderBy('last_name')->get()
                                ->mapWithKeys(fn ($s) => [$s->id => $s->full_name])
                        )
                        ->required()->searchable()->placeholder(__('Rechercher un élève...')),
                    Forms\Components\DatePicker::make('incident_date')
                        ->label(__("Date de l'incident"))
                        ->required()->default(now())->displayFormat('d/m/Y'),
                    Forms\Components\Select::make('type')
                        ->label(__("Type d'incident"))
                        ->options([
                            'accident'     => __('Accident'),
                            'health'       => __('Santé'),
                            'disciplinary' => __('Disciplinaire'),
                            'absence'      => __('Absence'),
                            'behavioral'   => __('Comportement'),
                            'other'        => __('Autre'),
                        ])
                        ->required()->default('other'),
                    Forms\Components\Select::make('severity')
                        ->label(__('Gravité'))
                        ->options([
                            'low'    => '🟢 ' . __('Faible'),
                            'medium' => '🟡 ' . __('Moyenne'),
                            'high'   => '🔴 ' . __('Élevée'),
                        ])
                        ->required()->default('low'),
                    Forms\Components\TextInput::make('title')
                        ->label(__("Titre de l'incident"))
                        ->required()->maxLength(255)->columnSpanFull(),
                    Forms\Components\Textarea::make('description')
                        ->label(__('Description détaillée'))
                        ->required()->rows(4)->columnSpanFull(),
                    Forms\Components\Textarea::make('action_taken')
                        ->label(__('Mesures prises'))
                        ->rows(3)->columnSpanFull(),
                    Forms\Components\Toggle::make('parent_notified')
                        ->label(__('Parents notifiés'))
                        ->disabled()->helperText(__("Géré automatiquement par l'envoi d'email")),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('incident_date')
                    ->label(__('Date'))->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('student.first_name')
                    ->label(__('Élève'))
                    ->formatStateUsing(fn ($state, $record) => $record->student?->full_name ?? '—')
                    ->searchable()->sortable()
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('Titre'))->limit(45)->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('Type'))
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'accident'     => 'danger',
                        'health'       => 'warning',
                        'disciplinary' => 'primary',
                        'absence'      => 'gray',
                        'behavioral'   => 'info',
                        default        => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match($state) {
                        'accident'     => __('Accident'),
                        'health'       => __('Santé'),
                        'disciplinary' => __('Discipline'),
                        'absence'      => __('Absence'),
                        'behavioral'   => __('Comportement'),
                        default        => __('Autre'),
                    }),
                Tables\Columns\TextColumn::make('severity')
                    ->label(__('Gravité'))
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'high'   => 'danger',
                        'medium' => 'warning',
                        default  => 'success',
                    })
                    ->formatStateUsing(fn ($state) => match($state) {
                        'high'   => __('Élevée'),
                        'medium' => __('Moyenne'),
                        default  => __('Faible'),
                    }),
                Tables\Columns\IconColumn::make('parent_notified')
                    ->label(__('Parent notifié'))
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->defaultSort('incident_date', 'desc')
            ->emptyStateIcon('heroicon-o-exclamation-triangle')
            ->emptyStateHeading(__('Aucun incident enregistré'))
            ->emptyStateDescription(__('Les incidents signalés apparaîtront ici.'))
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('Type'))
                    ->options([
                        'accident'     => __('Accident'),
                        'health'       => __('Health'),
                        'disciplinary' => __('Disciplinary'),
                        'absence'      => __('Absence'),
                        'behavioral'   => __('Behavioral'),
                        'other'        => __('Other'),
                    ]),
                Tables\Filters\SelectFilter::make('severity')
                    ->label(__('Severity'))
                    ->options([
                        'low' => __('Low'), 'medium' => __('Medium'), 'high' => __('High'),
                    ]),
                Tables\Filters\TernaryFilter::make('parent_notified')
                    ->label(__('Parent Notified')),
            ])
            ->actions([
                Actions\Action::make('notify_parent')
                    ->label(__('Notify Parent'))
                    ->icon('heroicon-o-envelope')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading(__('Send Email Notification to Parent'))
                    ->modalDescription(fn ($record) => __('Send incident notification to parents of :student', ['student' => $record->student?->full_name]))
                    ->action(function (Incident $record): void {
                        $parents = $record->student?->parents ?? collect();
                        $sent    = 0;

                        foreach ($parents as $parent) {
                            if ($parent->email) {
                                Mail::to($parent->email)->send(new IncidentNotificationMail($record, $parent));
                                $sent++;
                            }
                        }

                        $record->update([
                            'parent_notified'      => true,
                            'notification_sent_at' => now(),
                        ]);

                        Notification::make()
                            ->title($sent > 0
                                ? __('Notification sent to :count parent(s)', ['count' => $sent])
                                : __('No parent email found'))
                            ->status($sent > 0 ? 'success' : 'warning')
                            ->send();
                    })
                    ->visible(fn ($record) => !$record->parent_notified),
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListIncidents::route('/'),
            'create' => Pages\CreateIncident::route('/create'),
            'edit'   => Pages\EditIncident::route('/{record}/edit'),
        ];
    }
}
