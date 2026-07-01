<?php

namespace App\Filament\Platform\Widgets;

use App\Filament\Platform\Resources\SchoolResource;
use App\Models\School;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

/**
 * PHASE 7 — the most recently onboarded schools, shown on the platform dashboard.
 */
class LatestSchoolsWidget extends BaseWidget
{
    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('Dernières écoles'))
            ->query(fn (): Builder => School::query()->latest())
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('École'))->searchable()->sortable()
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold)
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
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Créée le'))->dateTime('d/m/Y')->sortable(),
            ])
            ->actions([
                Actions\Action::make('open')
                    ->label(__('Ouvrir'))
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (School $record): string => SchoolResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}
