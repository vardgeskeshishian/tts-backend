<?php

namespace App\Orchid\Screens\Category\SFX\Tag;

use Illuminate\Support\Facades\DB;
use Orchid\Screen\Screen;
use App\Models\SFX\SFXTag;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Facades\Toast;
use App\Orchid\Layouts\Category\SFX\Tag\TagListLayout;

class TagListScreen extends Screen
{
    /**
     * @return array
     */
    public function query(): array
    {
        return [
            'sfxTags' => SFXTag::filters()
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
        return 'Category Tag Management';
    }

    /**
     * @return string|null
     */
    public function description(): ?string
    {
        return 'A comprehensive list of all tag.';
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
                ->route('platform.systems.category', ['category' => 'sfxTag']),

            Link::make(__('Add'))
                ->icon('bs.plus-circle')
                ->route('platform.systems.category.sfx.sfxTag.create'),
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
        SFXTag::findOrFail($request->get('id'))->delete();

        Toast::info(__('SFXTag was removed'));
    }
}
