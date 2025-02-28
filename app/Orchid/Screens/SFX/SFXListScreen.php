<?php

namespace App\Orchid\Screens\SFX;

use App\Enums\TypeContentEnum;
use App\Models\SFX\SFXTrack;
use App\Orchid\Layouts\SFX\SFXListLayout;
use Illuminate\Http\Request;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layout;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;

class SFXListScreen extends Screen
{
    /**
     * @return array
     */
    public function query(): array
    {
        return [
            'sfxs' => SFXTrack::filters()->paginate()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'SFX Track Management';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'A comprehensive list of all registered users, including their profiles and privileges.';
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
     * The screen's action buttons.
     *
     * @return Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make(__('Add'))
                ->icon('bs.plus-circle')
                ->route('platform.systems.sfx.create'),

            Link::make(__('Template'))
                ->icon('bs.pencil')
                ->route('platform.systems.template', ['contentType' => TypeContentEnum::SFX])
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
            SFXListLayout::class
        ];
    }

    /**
     * @param Request $request
     * @return void
     */
    public function remove(Request $request): void
    {
        SFXTrack::findOrFail($request->get('id'))->delete();

        Toast::info(__('SFX Track was removed'));
    }
}