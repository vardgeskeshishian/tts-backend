<?php

namespace App\Orchid\Screens\FAQ\FAQSection;

use App\Models\Structure\FAQ;
use App\Models\Structure\FAQSection;
use App\Orchid\Layouts\FAQ\FAQSection\FAQSectionCategoryIdLayout;
use App\Orchid\Layouts\FAQ\FAQSection\FAQSectionMetaTitleLayout;
use App\Orchid\Layouts\FAQ\FAQSection\FAQSectionMetaDescritionLayout;
use App\Orchid\Layouts\FAQ\FAQSection\FAQSectionIsPopularLayout;
use App\Orchid\Layouts\FAQ\FAQSection\FAQ\FAQAnswerLayout;
use App\Orchid\Layouts\FAQ\FAQSection\FAQ\FAQQuestionLayout;
use App\Orchid\Listeners\FAQ\FAQSectionGenerationUrlListener;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Spatie\ResponseCache\Facades\ResponseCache;

class FAQSectionEditScreen extends Screen
{
    /**
     * @var int|null
     */
    public ?int $countAddFaqs = 0;

    public $faqSection;

    public bool $exists = false;

    /**
     * Fetch data to be displayed on the screen.
     *
     *
     * @return array
     */
    public function query(FAQSection $faqSection, Request $request): iterable
    {
        $this->exists = $faqSection->exists;
        $faqSection->load('faqs');
        $this->countAddFaqs = $request->has('countAddFaqs') ?
            $request->input('countAddFaqs') : 0;

        return [
            'faqSection' => $faqSection
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Edit FAQ Section';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Change associated with the faq section.';
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
        $layouts = [
            Layout::block([
                FAQSectionGenerationUrlListener::class,
                FAQSectionCategoryIdLayout::class,
                FAQSectionMetaTitleLayout::class,
                FAQSectionMetaDescritionLayout::class,
                FAQSectionIsPopularLayout::class
            ])->title('Section')->commands([
                Button::make('Add FAQ')
                    ->type(Color::SUCCESS)
                    ->icon('bs.plus-circle')
                    ->method('addFaq', ['countAddFaqs' => $this->countAddFaqs + 1])
                    ->novalidate()
                    ->canSee($this->exists),
            ]),
        ];

        if ($this->exists) {
            $faqs = $this->faqSection->faqs;
            foreach ($faqs as $key => $faq)
            {
                $layouts[] = Layout::block([
                    new FAQQuestionLayout($faq->id, $faq->question),
                    new FAQAnswerLayout($faq->id, $faq->answer),
                ])->title('Question '.($key + 1))->commands([
                    Button::make(__('Delete'))
                        ->type(Color::DANGER)
                        ->icon('bs.x-circle')
                        ->novalidate()
                        ->method('deleteFaq', [
                            'id' => $faq->id,
                            'countAddFaqs' => $this->countAddFaqs
                        ]),
                ]);
            }

            for($i = 0; $i < $this->countAddFaqs; $i++)
            {
                $layouts[] = Layout::block([
                    new FAQQuestionLayout($i.'_add'),
                    new FAQAnswerLayout($i.'_add'),
                ])->title('Question '.(count($faqs) + $i + 1))->commands([
                    Button::make(__('Delete'))
                        ->type(Color::DANGER)
                        ->icon('bs.x-circle')
                        ->novalidate()
                        ->method('deleteFaq', [
                            'id' => $i.'_add',
                            'countAddFaqs' => $this->countAddFaqs - 1
                        ]),
                ]);
            }
        }

        return $layouts;
    }

    public function addFaq(Request $request, FAQSection $faqSection): RedirectResponse
    {
        $countAddFaqs = $request->input('countAddFaqs');

        $faqSectionRequest = $request->get('faqSection');
        if (is_null($faqSectionRequest['url']))
            $faqSectionRequest['url'] = Str::slug($faqSection['title']);
        $faqSection->fill($faqSectionRequest)->save();
        $faqs = $request->get('faqs');
        if(!is_null($faqs)) {
            foreach ($faqs as $key => $faq)
            {
                if(!str_contains($key, '_add'))
                {
                    FAQ::where('id', $key)->update([
                        'section_id' => $faqSection->id,
                        'answer' => $faq['answer'],
                        'question' => $faq['question'],
                    ]);
                } elseif(!(is_null($faq['answer']) || is_null($faq['question']))) {
                    FAQ::create([
                        'section_id' => $faqSection->id,
                        'answer' => $faq['answer'],
                        'question' => $faq['question'],
                    ]);
                    $countAddFaqs -= 1;
                }
            }
        }

        return redirect()->route('platform.systems.faqs.sections.edit',
            ['faqSection' => $faqSection->id, 'countAddFaqs' => $countAddFaqs]);
    }

    public function deleteFaq(Request $request, FAQSection $faqSection): RedirectResponse
    {
        $countAddFaqs = $request->input('countAddFaqs');

        if(!str_contains($request->input('id'), '_add'))
        {
            FAQ::where('id', $request->input('id'))->delete();
        }

        $faqSectionRequest = $request->get('faqSection');
        if (is_null($faqSectionRequest['url']))
            $faqSectionRequest['url'] = Str::slug($faqSection['title']);
        $faqSection->fill($faqSectionRequest)->save();
        $faqs = $request->get('faqs');
        if(!is_null($faqs)) {
            foreach ($faqs as $key => $faq)
            {
                if(!str_contains($key, '_add'))
                {
                    FAQ::where('id', $key)->update([
                        'section_id' => $faqSection->id,
                        'answer' => $faq['answer'],
                        'question' => $faq['question'],
                    ]);
                } else if ($key != $request->input('id')) {
                    FAQ::create([
                        'section_id' => $faqSection->id,
                        'answer' => is_null($faq['answer']) ? '' : $faq['answer'],
                        'question' => is_null($faq['question']) ? '' : $faq['question'],
                    ]);
                    $countAddFaqs -= 1;
                }
            }
        }

        return redirect()->route('platform.systems.faqs.sections.edit',
            ['faqSection' => $faqSection->id, 'countAddFaqs' => $countAddFaqs]);
    }

    /**
     * @param Request $request
     * @param FAQSection $faqSection
     * @return RedirectResponse
     */
    public function save(Request $request, FAQSection $faqSection): RedirectResponse
    {
        $data = $request->toArray();
        $data['faqSection']['url'] = Str::slug($request->input('faqSection.url'));
        $request->replace($data);

        try {
            $request->validate([
                'faqSection.url' => [
                    'required',
                    Rule::unique(FAQSection::class, 'url')->ignore($faqSection),
                ]
            ]);
        } catch (ValidationException $e) {//ToDo redirect()->back()->withInput(); ?
            Alert::error($e->getMessage());
            return redirect()->route('platform.systems.faqs.sections.edit', $faqSection->id);
        }

        $this->exists = $faqSection->exists;
        $faqSectionRequest = $request->get('faqSection');
        $faqSection->fill($faqSectionRequest)->save();

        $faqs = $request->get('faqs');
        if(!is_null($faqs)) {
            foreach ($faqs as $key => $faq)
            {
                if(!str_contains($key, '_add'))
                {
                    FAQ::where('id', $key)->update([
                        'section_id' => $faqSection->id,
                        'answer' => $faq['answer'],
                        'question' => $faq['question'],
                    ]);
                } else {
                    FAQ::create([
                        'section_id' => $faqSection->id,
                        'answer' => is_null($faq['answer']) ? '' : $faq['answer'],
                        'question' => is_null($faq['question']) ? '' : $faq['question'],
                    ]);
                }
            }
        }
		ResponseCache::clear();

        Toast::info(__('FAQ Section was saved'));

        return $this->exists
			? redirect()->route('platform.systems.faqs.sections')
        	: redirect()->route('platform.systems.faqs.sections.edit',
                ['faqSection' => $faqSection->id]);
    }

    /**
     * @param FAQSection $faqSection
     * @return RedirectResponse
     */
    public function remove(FAQSection $faqSection): RedirectResponse
    {
        $faqSection->faqs()->delete();
        $faqSection->delete();
		ResponseCache::clear();

        Toast::info(__('FAQ Section was removed'));

        return redirect()->route('platform.systems.faqs.sections');
    }
}
