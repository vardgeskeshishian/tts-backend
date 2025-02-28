<?php

namespace App\Orchid\Screens\FAQ\FAQCategory;

use App\Models\Structure\FAQCategory;
use App\Orchid\Layouts\FAQ\FAQCategory\FAQCategoryNameLayout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;
use Spatie\ResponseCache\Facades\ResponseCache;

class FAQCategoryEditScreen extends Screen
{
    public $faqCategory;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(FAQCategory $faqCategory): iterable
    {
        return [
            'faqCategory' => $faqCategory,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Edit FAQ Category';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Change associated with the faq category.';
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
            Button::make(__('Save'))
                ->icon('bs.check-circle')
                ->method('save'),

            Button::make(__('Remove'))
                ->icon('bs.trash3')
                ->novalidate()
                ->method('remove'),
        ];
    }

    /**
     * @return iterable
     */
    public function layout(): iterable
    {
        return [
            FAQCategoryNameLayout::class
        ];
    }

    /**
     * @param Request $request
     * @param FAQCategory $faqCategory
     * @return RedirectResponse
     */
    public function save(Request $request, FAQCategory $faqCategory): RedirectResponse
    {
        $faqCategory->fill(['name' => $request->input('faqCategory.name')])->save();

		ResponseCache::clear();
        Toast::info(__('FAQ Category was saved'));
        return redirect()->route('platform.systems.faqs.categories');
    }

    /**
     * @param FAQCategory $faqCategory
     * @return RedirectResponse
     */
    public function remove(FAQCategory $faqCategory): RedirectResponse
    {
        $faqCategory->delete();

		ResponseCache::clear();
        Toast::info(__('FAQ Category was removed'));
        return redirect()->route('platform.systems.faqs.categories');
    }
}
