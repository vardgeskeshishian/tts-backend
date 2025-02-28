<?php

namespace App\Orchid\Screens\Track;

use App\Enums\TypeContentEnum;
use Illuminate\Http\Request;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layout;
use Orchid\Screen\Screen;
use App\Models\Track;
use App\Orchid\Layouts\Tracks\TrackListLayout;
use Orchid\Support\Facades\Toast;

class TrackListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'tracks' => Track::filters()->with('author')
                ->orderBy('id')
                ->paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Track Management';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Complete list of all tracks';
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
     * The screen's layout elements.
     *
     * @return string[]|Layout[]
     */
    public function layout(): iterable
    {
        return [
            TrackListLayout::class,
        ];
    }

    /**
     * The screen's action buttons.
     *
     * @return Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make(__('Add'))
                ->icon('bs.plus-circle')
                ->route('platform.systems.tracks.create'),

            Link::make(__('Template'))
                ->icon('bs.pencil')
                ->route('platform.systems.template', ['contentType' => TypeContentEnum::TRACK])
        ];
    }

    /**
     * @param Request $request
     * @return void
     */
    public function remove(Request $request): void
    {
        Track::findOrFail($request->get('id'))->delete();

        Toast::info(__('Track was removed'));
    }
}