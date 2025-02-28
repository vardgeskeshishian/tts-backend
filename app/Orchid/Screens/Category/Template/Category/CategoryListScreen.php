<?php

namespace App\Orchid\Screens\Category\Template\Category;

use Illuminate\Support\Facades\DB;
use Orchid\Screen\Screen;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Facades\Toast;
use App\Models\VideoEffects\VideoEffectCategory;
use App\Orchid\Layouts\Category\Template\Category\CategoryListLayout;

class CategoryListScreen extends Screen
{
    /**
     * @return array
     */
    public function query(): array
    {
        return [
            'videoEffectCategories' => VideoEffectCategory::filters()
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
        return 'Template Category Management';
    }

    /**
     * @return string|null
     */
    public function description(): ?string
    {
        return 'A comprehensive list of all category.';
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
                ->route('platform.systems.category', ['category' => 'category']),

            Link::make(__('Add'))
                ->icon('bs.plus-circle')
                ->route('platform.systems.category.template.category.create'),
        ];
    }

    /**
     * @return iterable
     */
    public function layout(): iterable
    {
        return [
            CategoryListLayout::class
        ];
    }

    public function remove(Request $request): void
    {
        VideoEffectCategory::findOrFail($request->get('id'))->delete();

        Toast::info(__('Video Effect Category was removed'));
    }
}
