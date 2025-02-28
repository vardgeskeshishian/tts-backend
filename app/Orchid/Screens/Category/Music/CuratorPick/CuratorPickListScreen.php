<?php

namespace App\Orchid\Screens\Category\Music\CuratorPick;

use Illuminate\Http\Request;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use App\Models\Tags\CuratorPick;
use App\Orchid\Layouts\Category\Music\CuratorPick\CuratorPickListLayout;
use Orchid\Support\Facades\Toast;

class CuratorPickListScreen extends Screen
{
    /**
     * @return array
     */
    public function query(): array
    {
        return [
            'curator_picks' => CuratorPick::filters()->paginate(),
        ];
    }

    /**
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Category Curator Picks Management';
    }

    /**
     * @return string|null
     */
    public function description(): ?string
    {
        return 'A comprehensive list of all curator picks.';
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
                ->route('platform.systems.category', ['category' => 'curator-pick']),

            Link::make(__('Add'))
                ->icon('bs.plus-circle')
                ->route('platform.systems.category.music.curator-pick.create'),
        ];
    }

    /**
     * @return iterable
     */
    public function layout(): iterable
    {
        return [
            CuratorPickListLayout::class
        ];
    }

    /**
     * @param Request $request
     * @return void
     */
    public function remove(Request $request): void
    {
        CuratorPick::findOrFail($request->get('id'))->delete();

        Toast::info(__('Genre was removed'));
    }
}