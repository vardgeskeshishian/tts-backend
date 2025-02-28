<?php

namespace App\Orchid\Screens\VideoEffect;

use App\Enums\TypeContentEnum;
use App\Models\VideoEffects\VideoEffect;
use App\Orchid\Layouts\VideoEffect\VideoEffectListLayout;
use Illuminate\Http\Request;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;

class VideoEffectListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'videos' => VideoEffect::filters()->with('authorVideo')->orderByDesc('id')
                ->paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Video Effects Management';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Complete list of all video effects.';
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
                ->route('platform.systems.video.create'),

            Link::make(__('Template'))
                ->icon('bs.pencil')
                ->route('platform.systems.template', ['contentType' => TypeContentEnum::VIDEO_EFFECT])
        ];
    }

    public function layout(): iterable
    {
        return [
            VideoEffectListLayout::class,
        ];
    }

    /**
     * @param Request $request
     * @return void
     */
    public function remove(Request $request): void
    {
        VideoEffect::findOrFail($request->get('id'))->delete();

        Toast::info(__('Template was removed'));
    }
}