<?php
namespace App\Filament\Resources\StudentResource\RelationManagers;

use App\Mail\IncidentNotificationMail;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;

class IncidentsRelationManager extends RelationManager
{
    protected static string $relationship = 'incidents';
    protected static ?string $title       = 'Incidents';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('title')
                ->label('Titre')->required()->maxLength(255)->columnSpanFull(),
            Forms\Components\Select::make('type')
                ->label('Type')
                ->options([
                    'behavioral'  => 'Comportement',
                    'medical'     => 'Médical',
                    'academic'    => 'Scolaire',
                    'physical'    => 'Physique',
                    'other'       => 'Autre',
                ])
                ->required(),
            Forms\Components\Select::make('severity')
                ->label('Gravité')
                ->options([
                    'low'    => 'Faible',
                    'medium' => 'Moyen',
                    'high'   => 'Grave',
                ])
                ->default('low')->required(),
            Forms\Components\DatePicker::make('incident_date')
                ->label('Date de l\'incident')->required()->displayFormat('d/m/Y'),
            Forms\Components\Textarea::make('description')
                ->label('Description')->rows(3)->columnSpanFull(),
            Forms\Components\Textarea::make('action_taken')
                ->label('Mesure prise')->rows(2)->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('incident_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Incident')
                    ->searchable()
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()->color('primary')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'behavioral' => 'Comportement',
                        'medical'    => 'Médical',
                        'academic'   => 'Scolaire',
                        'physical'   => 'Physique',
                        default      => 'Autre',
                    }),
                Tables\Columns\TextColumn::make('severity')
                    ->label('Gravité')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'high'   => 'danger',
                        'medium' => 'warning',
                        default  => 'info',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'high'   => 'Grave',
                        'medium' => 'Moyen',
                        default  => 'Faible',
                    }),
                Tables\Columns\TextColumn::make('incident_date')
                    ->label('Date')->date('d/m/Y'),
                Tables\Columns\IconColumn::make('parent_notified')
                    ->label('Parents notifiés')->boolean(),
            ])
            ->headerActions([Tables\Actions\CreateAction::make()->label('Signaler un incident')])
            ->actions([
                Tables\Actions\Action::make('notify_parent')
                    ->label('Notifier les parents')
                    ->icon('heroicon-o-bell')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn ($record): bool => !$record->parent_notified)
                    ->action(function ($record): void {
                        $student = $record->student;
                        $emails  = $student->parents->map(fn ($p) => $p->email)->filter()->toArray();

                        if (empty($emails)) {
                            Notification::make()->title('Aucun parent avec email trouvé')->warning()->send();
                            return;
                        }

                        foreach ($emails as $email) {
                            Mail::to($email)->send(new IncidentNotificationMail($record));
                        }

                        $record->update(['parent_notified' => true, 'notification_sent_at' => now()]);
                        Notification::make()->title('Notification envoyée aux parents')->success()->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }
}
