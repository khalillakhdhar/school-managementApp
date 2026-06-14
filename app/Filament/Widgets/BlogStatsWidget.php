<?php
namespace App\Filament\Widgets;

use App\Models\BlogPost;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BlogStatsWidget extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        return request()->routeIs('filament.admin.resources.blog-posts.index');
    }

    protected function getStats(): array
    {
        $total     = BlogPost::count();
        $published = BlogPost::where('is_published', true)->count();
        $drafts    = BlogPost::where('is_published', false)->count();
        $thisMonth = BlogPost::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)->count();

        $lastPublished = BlogPost::where('is_published', true)
            ->orderByDesc('published_at')->first();

        $trend = collect(range(5, 0))->map(fn ($i) =>
            BlogPost::where('created_at', '<=', now()->subMonths($i)->endOfMonth())->count()
        )->toArray();

        return [
            Stat::make('Total articles', $total)
                ->description("{$thisMonth} créé(s) ce mois")
                ->descriptionIcon('heroicon-m-newspaper')
                ->color('primary')
                ->chart($trend),

            Stat::make('Articles publiés', $published)
                ->description($total > 0 ? round($published / $total * 100).'% du contenu' : 'Aucun publié')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('Brouillons', $drafts)
                ->description($drafts > 0 ? 'À finaliser et publier' : 'Tout est publié')
                ->descriptionIcon('heroicon-m-pencil-square')
                ->color($drafts > 0 ? 'warning' : 'gray'),

            Stat::make('Dernière publication', $lastPublished?->published_at?->locale('fr')->isoFormat('D MMM') ?? '—')
                ->description($lastPublished?->title ? \Illuminate\Support\Str::limit($lastPublished->title, 24) : 'Aucune annonce')
                ->descriptionIcon('heroicon-m-megaphone')
                ->color('info'),
        ];
    }
}
