<?php

namespace App\Orchid\Screens\Category\SFX\Category;

use Illuminate\Support\Facades\DB;
use Orchid\Screen\Screen;
use Illuminate\Http\Request;
use App\Models\SFX\SFXCategory;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Facades\Toast;
use App\Orchid\Layouts\Category\SFX\Category\CategoryListLayout;

class CategoryListScreen extends Screen
{
    /**
     * @return array
     */
    public function query(): array
    {
        return [
            'sfxCategories' => SFXCategory::filters()
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
        return 'SFX Category Management';
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
                ->route('platform.systems.category', ['category' => 'sfxCategory']),

            Link::make(__('Add'))
                ->icon('bs.plus-circle')
                ->route('platform.systems.category.sfx.sfxCategory.create'),
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
        SFXCategory::findOrFail($request->get('id'))->delete();

        Toast::info(__('SFXCategory was removed'));
    }
}
