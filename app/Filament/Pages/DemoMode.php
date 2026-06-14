<?php
namespace App\Filament\Pages;

use App\Services\DemoDataService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class DemoMode extends Page
{
    protected string $view = 'filament.pages.demo-mode';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';
    protected static ?int $navigationSort = 99;

    public static function getNavigationGroup(): ?string { return 'Paramètres'; }
    public static function getNavigationLabel(): string  { return 'Mode Démo'; }
    public function getTitle(): string                   { return 'Mode Démo'; }

    public bool $active = false;

    public function mount(): void
    {
        $this->active = DemoDataService::isActive();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('activate')
                ->label('Activer le mode démo')
                ->icon('heroicon-o-sparkles')
                ->color('primary')
                ->visible(fn () => ! $this->active)
                ->requiresConfirmation()
                ->modalHeading('Activer le mode démo')
                ->modalDescription('Cela remplit la base avec une école tunisienne complète : élèves, classes, enseignants, emplois du temps, paiements, dépenses, etc. Les données existantes seront remplacées.')
                ->modalSubmitActionLabel('Activer')
                ->action(function () {
                    $stats = DemoDataService::seed();
                    $this->active = true;
                    Notification::make()
                        ->title('Mode démo activé')
                        ->body("{$stats['students']} élèves · {$stats['classes']} classes · {$stats['employees']} employés · {$stats['seances']} séances · {$stats['payments']} paiements créés.")
                        ->success()->duration(8000)->send();
                }),

            Action::make('purge')
                ->label('Supprimer les données démo')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->visible(fn () => $this->active)
                ->requiresConfirmation()
                ->modalHeading('Supprimer toutes les données démo')
                ->modalDescription('Cela supprime définitivement toutes les données métier (élèves, classes, paiements, emplois du temps…). Le compte administrateur et les paramètres sont conservés.')
                ->modalSubmitActionLabel('Supprimer définitivement')
                ->action(function () {
                    DemoDataService::purge();
                    $this->active = false;
                    Notification::make()
                        ->title('Données démo supprimées')
                        ->body('La base a été vidée. Vous pouvez réactiver le mode démo à tout moment.')
                        ->success()->send();
                }),
        ];
    }
}
