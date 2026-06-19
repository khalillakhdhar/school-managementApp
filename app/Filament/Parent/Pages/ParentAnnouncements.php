<?php
namespace App\Filament\Parent\Pages;

use App\Models\BlogPost;
use Filament\Pages\Page;

class ParentAnnouncements extends Page
{
    protected string $view = 'filament.parent.pages.parent-announcements';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';
    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string { return __('Annonces'); }
    public function getTitle(): string { return __("Annonces de l'établissement"); }

    protected function getViewData(): array
    {
        $posts = BlogPost::where('is_published', true)
            ->orderByDesc('published_at')->take(30)->get()
            ->map(fn ($p) => [
                'title'   => $p->title,
                'excerpt' => $p->excerpt,
                'content' => $p->content,
                'date'    => $p->published_at?->locale('fr')->isoFormat('D MMMM YYYY') ?? '',
            ])->toArray();

        return ['posts' => $posts];
    }
}
