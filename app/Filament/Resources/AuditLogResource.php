<?php
namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use App\Models\AuditLog;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';
    protected static ?int $navigationSort = 90;

    public static function getNavigationGroup(): ?string { return 'Paramètres'; }
    public static function getNavigationLabel(): string  { return 'Journal d\'audit'; }
    public static function getModelLabel(): string       { return 'Entrée d\'audit'; }
    public static function getPluralModelLabel(): string { return 'Journal d\'audit'; }

    public static function canCreate(): bool { return false; }

    protected static array $typeLabels = [
        \App\Models\Payment::class => 'Paiement',
        \App\Models\Payroll::class => 'Fiche de paie',
        \App\Models\Grade::class   => 'Note',
    ];

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Date'))->dateTime('d/m/Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('user_name')
                    ->label(__('Utilisateur'))
                    ->formatStateUsing(fn ($state) => $state ?: 'Système')
                    ->searchable(),
                Tables\Columns\TextColumn::make('event')
                    ->label(__('Action'))->badge()
                    ->color(fn (string $state) => match ($state) {
                        'created' => 'success', 'updated' => 'warning', 'deleted' => 'danger', default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'created' => 'Création', 'updated' => 'Modification', 'deleted' => 'Suppression', default => $state,
                    }),
                Tables\Columns\TextColumn::make('auditable_type')
                    ->label(__('Type'))->badge()->color('gray')
                    ->formatStateUsing(fn ($state) => self::$typeLabels[$state] ?? class_basename($state)),
                Tables\Columns\TextColumn::make('label')
                    ->label(__('Élément'))->wrap()->searchable(),
                Tables\Columns\TextColumn::make('changes_summary')
                    ->label(__('Changements'))
                    ->state(function (AuditLog $record): string {
                        $new = $record->new_values ?? [];
                        if ($record->event === 'updated' && $new) {
                            return collect($new)->map(fn ($v, $k) => $k . ': ' . (is_scalar($v) ? $v : json_encode($v)))->implode(' · ');
                        }
                        return $record->event === 'deleted' ? 'Élément supprimé' : 'Nouvel élément';
                    })
                    ->limit(80)->wrap()->toggleable(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label(__('IP'))->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->label(__('Action'))
                    ->options(['created' => 'Création', 'updated' => 'Modification', 'deleted' => 'Suppression']),
                Tables\Filters\SelectFilter::make('auditable_type')
                    ->label(__('Type'))
                    ->options(self::$typeLabels),
            ])
            ->emptyStateIcon('heroicon-o-shield-check')
            ->emptyStateHeading('Aucune activité enregistrée')
            ->emptyStateDescription('Les créations, modifications et suppressions financières apparaîtront ici.')
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListAuditLogs::route('/')];
    }
}
