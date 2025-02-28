<?php

namespace App\Orchid\Screens\Category\Music\Genre;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Screen;
use App\Models\Tags\Genre;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Link;
use App\Orchid\Layouts\Category\Music\Genre\GenreListLayout;
use Orchid\Support\Facades\Toast;
use Illuminate\Support\Facades\Log;

class GenreListScreen extends Screen
{
    /**
     * @return array
     */
    public function query(): array
    {
        return [
            'genres' => Genre::filters()
                ->orderBy(DB::raw('ISNULL(priority), priority'), 'ASC')
                ->orderBy('priority')
                ->orderBy('id')
                ->paginate(),
        ];
    }

    /**
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Category Genre Management';
    }

    /**
     * @return string|null
     */
    public function description(): ?string
    {
        return 'A comprehensive list of all genre.';
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
     * @return iterable
     */
    public function commandBar(): iterable
    {
        return [
            Link::make(__('Edit Template Meta'))
                ->icon('bs.pencil')
                ->route('platform.systems.category', ['category' => 'genre']),

            Link::make(__('Add'))
                ->icon('bs.plus-circle')
                ->route('platform.systems.category.music.genre.create'),
        ];
    }

    /**
     * @return iterable
     */
    public function layout(): iterable
    {
        return [
            GenreListLayout::class
        ];
    }

    public function remove(Request $request): void
    {
        Genre::findOrFail($request->get('id'))->delete();

        Toast::info(__('Genre was removed'));
    }
}
