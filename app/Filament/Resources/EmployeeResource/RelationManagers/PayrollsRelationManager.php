<?php
namespace App\Filament\Resources\EmployeeResource\RelationManagers;

use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PayrollsRelationManager extends RelationManager
{
    protected static string $relationship = 'payrolls';
    protected static ?string $title       = 'Historique des fiches de paie';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        $months = [
            1 => 'Janvier', 2 => 'Février',   3 => 'Mars',
            4 => 'Avril',   5 => 'Mai',        6 => 'Juin',
            7 => 'Juillet', 8 => 'Août',       9 => 'Septembre',
            10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
        ];

        return $table
            ->recordTitleAttribute('id')
            ->defaultSort('year', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('period')
                    ->label('Période')
                    ->getStateUsing(fn ($record): string =>
                        ($months[$record->month] ?? $record->month) . ' ' . $record->year
                    )
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold),
                Tables\Columns\TextColumn::make('gross_salary')
                    ->label('Brut')->money('TND'),
                Tables\Columns\TextColumn::make('net_salary')
                    ->label('Net')->money('TND')
                    ->weight(\Filament\Support\Enums\FontWeight::Bold)
                    ->color('success'),
                Tables\Columns\TextColumn::make('total_hours_worked')
                    ->label('Heures')
                    ->formatStateUsing(fn ($state): string => $state > 0 ? $state . ' h' : '—')
                    ->badge()->color('gray'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid'      => 'success',
                        'finalized' => 'warning',
                        'draft'     => 'gray',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'paid'      => 'Payé',
                        'finalized' => 'Finalisé',
                        'draft'     => 'Brouillon',
                        default     => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')->date('d/m/Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options(['draft' => 'Brouillon', 'finalized' => 'Finalisé', 'paid' => 'Payé']),
            ])
            ->headerActions([])
            ->actions([
                Tables\Actions\Action::make('mark_paid')
                    ->label('Marquer payé')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record): bool => $record->status === 'finalized')
                    ->action(function ($record): void {
                        $record->update(['status' => 'paid']);
                        Notification::make()->title('Fiche de paie marquée comme payée')->success()->send();
                    }),
                Tables\Actions\ViewAction::make()
                    ->url(fn ($record): string =>
                        \App\Filament\Resources\PayrollResource::getUrl('edit', ['record' => $record])
                    ),
            ])
            ->bulkActions([]);
    }
}
