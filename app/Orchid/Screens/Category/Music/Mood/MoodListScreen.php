<?php

namespace App\Orchid\Screens\Category\Music\Mood;

use App\Models\Tags\Mood;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Screen;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Facades\Toast;
use App\Orchid\Layouts\Category\Music\Mood\MoodListLayout;

class MoodListScreen extends Screen
{
    public function query(): array
    {
        return [
            'moods' => Mood::filters()
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
        return 'Category Mood Management';
    }

    /**
     * @return string|null
     */
    public function description(): ?string
    {
        return 'A comprehensive list of all mood.';
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
                ->route('platform.systems.category', ['category' => 'mood']),

            Link::make(__('Add'))
                ->icon('bs.plus-circle')
                ->route('platform.systems.category.music.mood.create'),
        ];
    }

    /**
     * @return iterable
     */
    public function layout(): iterable
    {
        return [
            MoodListLayout::class
        ];
    }

    public function remove(Request $request): void
    {
        Mood::findOrFail($request->get('id'))->delete();

        Toast::info(__('Mood was removed'));
    }
}
