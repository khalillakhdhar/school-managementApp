<?php
namespace App\Filament\Resources\BlogPostResource\Pages;

use App\Filament\Resources\BlogPostResource;
use App\Filament\Widgets\BlogStatsWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBlogPosts extends ListRecords
{
    protected static string $resource = BlogPostResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label(__('Rédiger un article'))];
    }

    protected function getHeaderWidgets(): array
    {
        return [BlogStatsWidget::class];
    }
}
