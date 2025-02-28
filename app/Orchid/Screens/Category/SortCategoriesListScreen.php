<?php

namespace App\Orchid\Screens\Category;

use App\Models\Tags\SortCategory;
use App\Orchid\Layouts\Category\SortCategory\MusicListLayout;
use App\Orchid\Layouts\Category\SortCategory\VideoListLayout;
use Orchid\Screen\Action;
use Orchid\Screen\Screen;

class SortCategoriesListScreen extends Screen
{
    /**
     * @return array
     */
    public function query(): array
    {
        return [
            'music_categories' => SortCategory::where('type', 'music')
                ->orderBy('order')->get(),
            'video_categories' => SortCategory::where('type', 'video')
                ->orderBy('order')->get(),
        ];
    }

    /**
     * @return string|null
     */
    public function description(): ?string
    {
        return 'A comprehensive list of all type categories.';
    }

    /**
     * @return iterable|null
     */
    public function permission(): ?iterable
    {
        return [
            'platform.systems.users',
        ];
    }

    /**
     * @return array|Action[]
     */
    public function commandBar(): array
    {
        return [];
    }

    /**
     * @return iterable
     */
    public function layout(): iterable
    {
        return [
            MusicListLayout::class,
            VideoListLayout::class
        ];
    }
}