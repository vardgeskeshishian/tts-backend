<?php

namespace App\Orchid\Screens\Category\Template\Tag;

use Illuminate\Support\Facades\DB;
use Orchid\Screen\Screen;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Facades\Toast;
use App\Models\VideoEffects\VideoEffectTag;
use App\Orchid\Layouts\Category\Template\Tag\TagListLayout;

class TagListScreen extends Screen
{
    /**
     * @return array
     */
    public function query(): array
    {
        return [
            'videoEffectTags' => VideoEffectTag::filters()
                ->orderBy(DB::raw('ISNULL(priority), priority'), 'ASC')
                ->orderBy('priority')
                ->orderByDesc('id')
                ->paginate(),
        ];
    }

    /**
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Template Tags Management';
    }

    /**
     * @return string|null
     */
    public function description(): ?string
    {
        return 'A comprehensive list of all tags.';
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
                ->route('platform.systems.category', ['category' => 'templateTag']),

            Link::make(__('Add'))
                ->icon('bs.plus-circle')
                ->route('platform.systems.category.template.templateTag.create'),
        ];
    }

    /**
     * @return iterable
     */
    public function layout(): iterable
    {
        return [
            TagListLayout::class
        ];
    }

    public function remove(Request $request): void
    {
        VideoEffectTag::findOrFail($request->get('id'))->delete();

        Toast::info(__('Video Effect Tag was removed'));
    }
}
