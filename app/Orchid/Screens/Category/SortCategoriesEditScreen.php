<?php

namespace App\Orchid\Screens\Category;

use App\Models\Tags\SortCategory;
use App\Orchid\Layouts\Category\SortCategory\SortCategoryOrderLayout;
use App\Orchid\Layouts\Category\SortCategory\SortCategoryIsHiddenLayout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class SortCategoriesEditScreen extends Screen
{
    /**
     * @param SortCategory $sortCategory
     * @return SortCategory[]
     */
    public function query(SortCategory $sortCategory): array
    {
        return [
            'sortCategory' => $sortCategory
        ];
    }

    /**
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Edit Sort Category';
    }

    /**
     * @return string|null
     */
    public function description(): ?string
    {
        return '';
    }

    /**
     * @return array|Action[]
     */
    public function commandBar(): array
    {
        return [
            Button::make(__('Save'))
                ->icon('bs.check-circle')
                ->method('save'),
        ];
    }

    /**
     * @return iterable
     */
    public function layout(): iterable
    {
        return [
            Layout::block(
                SortCategoryOrderLayout::class
            )->title('Order'),

            Layout::block(
                SortCategoryIsHiddenLayout::class
            )->title('Hidden'),
        ];
    }

    /**
     * @param Request $request
     * @param SortCategory $sortCategory
     * @return RedirectResponse
     */
    public function save(Request $request, SortCategory $sortCategory): RedirectResponse
    {
        $data = $request->input('sortCategory');
        $sortCategory->fill($data)->save();

        Toast::info(__('Sort Category was saved'));

        return redirect()->route('platform.systems.sort-categories');
    }
}