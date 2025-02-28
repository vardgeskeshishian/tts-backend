<?php

namespace App\Orchid\Screens\FAQ\FAQCategory;

use App\Models\Structure\FAQ;
use App\Models\Structure\FAQCategory;
use App\Orchid\Layouts\FAQ\FAQCategory\FAQCategoryListLayout;
use Illuminate\Http\Request;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layout;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;

class FAQCategoryListScreen extends Screen
{
    public $faqsCategories;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'faqsCategories' => FAQCategory::paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'FAQ Categories Management';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Full list of frequently asked questions from the categories';
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
            FAQCategoryListLayout::class
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
                ->route('platform.systems.faqs.categories.create'),
        ];
    }

    /**
     * @param Request $request
     * @return void
     */
    public function remove(Request $request): void
    {
        FAQCategory::findOrFail($request->get('id'))->delete();

        Toast::info(__('FAQ Category was removed'));
    }
}