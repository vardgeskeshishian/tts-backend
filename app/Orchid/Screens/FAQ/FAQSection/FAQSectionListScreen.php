<?php

namespace App\Orchid\Screens\FAQ\FAQSection;

use App\Models\Structure\FAQSection;
use App\Orchid\Layouts\FAQ\FAQSection\FAQSectionListLayout;
use Illuminate\Http\Request;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layout;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;

class FAQSectionListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'sections' => FAQSection::orderBy('id')
                ->with('category')
                ->paginate()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'FAQ Section Management';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Full list of sections';
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
            FAQSectionListLayout::class
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
                ->route('platform.systems.faqs.sections.create'),
        ];
    }

    /**
     * @param Request $request
     * @return void
     */
    public function remove(Request $request): void
    {
        FAQSection::findOrFail($request->get('id'))->delete();

        Toast::info(__('FAQ Section was removed'));
    }
}

