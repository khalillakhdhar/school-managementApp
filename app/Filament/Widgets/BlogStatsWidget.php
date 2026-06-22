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
            Stat::make(__('Total articles'), $total)
                ->description(__(':n créé(s) ce mois', ['n' => $thisMonth]))
                ->descriptionIcon('heroicon-m-newspaper')
                ->color('primary')
                ->chart($trend),

            Stat::make(__('Articles publiés'), $published)
                ->description($total > 0 ? __(':pct% du contenu', ['pct' => round($published / $total * 100)]) : __('Aucun publié'))
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make(__('Brouillons'), $drafts)
                ->description($drafts > 0 ? __('À finaliser et publier') : __('Tout est publié'))
                ->descriptionIcon('heroicon-m-pencil-square')
                ->color($drafts > 0 ? 'warning' : 'gray'),

            Stat::make(__('Dernière publication'), $lastPublished?->published_at?->locale(app()->getLocale())->isoFormat('D MMM') ?? '—')
                ->description($lastPublished?->title ? \Illuminate\Support\Str::limit($lastPublished->title, 24) : __('Aucune annonce'))
                ->descriptionIcon('heroicon-m-megaphone')
                ->color('info'),
        ];
    }
}
