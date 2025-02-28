<?php

namespace App\Orchid\Screens\Category\Template\Application;

use Illuminate\Support\Facades\DB;
use Orchid\Screen\Screen;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Facades\Toast;
use App\Models\VideoEffects\VideoEffectApplication;
use App\Orchid\Layouts\Category\Template\Application\ApplicationListLayout;

class ApplicationListScreen extends Screen
{
    /**
     * @return array
     */
    public function query(): array
    {
        return [
            'videoEffectApplications' => VideoEffectApplication::filters()
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
        return 'Category Application Management';
    }

    /**
     * @return string|null
     */
    public function description(): ?string
    {
        return 'A comprehensive list of all application.';
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
                ->route('platform.systems.category', ['category' => 'application']),

            Link::make(__('Add'))
                ->icon('bs.plus-circle')
                ->route('platform.systems.category.template.application.create'),
        ];
    }

    /**
     * @return iterable
     */
    public function layout(): iterable
    {
        return [
            ApplicationListLayout::class
        ];
    }

    public function remove(Request $request): void
    {
        VideoEffectApplication::findOrFail($request->get('id'))->delete();

        Toast::info(__('Video Effect Application was removed'));
    }
}
