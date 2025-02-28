<?php

namespace App\Orchid\Screens\Pages;

use App\Models\Structure\Page;
use App\Models\Structure\PageSection;
use App\Orchid\Layouts\Pages\PageSections\PageSectionNameLayout;
use App\Orchid\Layouts\Pages\PageSections\PageSectionHtmlTextLayout;
use App\Orchid\Layouts\Pages\PageSections\PageSectionTextTextLayout;
use App\Orchid\Layouts\Pages\PageSections\PageSectionImageTextLayout;
use App\Orchid\Listeners\Page\PageEditListener;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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

class PageEditScreen extends Screen
{
    public $page;

    public bool $exists = false;

    /**
     * @var int|null
     */
    public ?int $countAddSectionHtml = 0;

    /**
     * @var int|null
     */
    public ?int $countAddSectionText = 0;

    /**
     * @var int|null
     */
    public ?int $countAddSectionImage = 0;

    /**
     * @param Page $page
     * @param Request $request
     * @return array
     */
    public function query(Page $page, Request $request): array
    {
        $this->exists = $page->exists;
        $page->load('sections');

        $this->countAddSectionImage = $request->has('countAddSectionImage') ?
            $request->input('countAddSectionImage') : 0;

        $this->countAddSectionHtml = $request->has('countAddSectionHtml') ?
            $request->input('countAddSectionHtml') : 0;

        $this->countAddSectionText = $request->has('countAddSectionText') ?
            $request->input('countAddSectionText') : 0;

        return [
            'page' => $page,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return $this->page->exists ? 'Edit Page' : 'Create Page';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return '';
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
            Button::make(__('Remove'))
                ->icon('bs.trash3')
                ->confirm(__('Once a page is deleted, all of its resources and data will be permanently deleted. Before deleting your page, download any data or information you want to keep.'))
                ->method('remove')
                ->novalidate()
                ->canSee($this->exists),

            Button::make(__('Save'))
                ->icon('bs.check-circle')
                ->method('save'),
        ];
    }

    /**
     * @return string
     */
    public function formValidateMessage(): string
    {
        return __('Please check the entered data.');
    }

    /**
     * @return iterable
     */
    public function layout(): iterable
    {
        $layouts = [
            Layout::block(
                new PageEditListener($this->exists)
            )->title('Page')->commands([
                Button::make('Add Html Section')
                    ->type(Color::SUCCESS)
                    ->icon('bs.plus-circle')
                    ->method('addSection', [
                        'countAddSectionHtml' => $this->countAddSectionHtml + 1,
                        'countAddSectionText' => $this->countAddSectionText,
                        'countAddSectionImage' => $this->countAddSectionImage
                    ])->novalidate()->canSee($this->exists),

                Button::make('Add Text Section')
                    ->type(Color::SUCCESS)
                    ->icon('bs.plus-circle')
                    ->method('addSection', [
                        'countAddSectionHtml' => $this->countAddSectionHtml,
                        'countAddSectionText' => $this->countAddSectionText + 1,
                        'countAddSectionImage' => $this->countAddSectionImage
                    ])->novalidate()->canSee($this->exists),

                Button::make('Add Image Section')
                    ->type(Color::SUCCESS)
                    ->icon('bs.plus-circle')
                    ->method('addSection', [
                        'countAddSectionHtml' => $this->countAddSectionHtml,
                        'countAddSectionText' => $this->countAddSectionText,
                        'countAddSectionImage' => $this->countAddSectionImage + 1
                    ])->novalidate()->canSee($this->exists),
            ])
        ];

        if ($this->exists) {
            $sections = $this->page->sections;
            foreach ($sections as $key => $section)
            {
                switch ($section->type) {
                    case 'html':
                        $layouts[] = Layout::block([
                            new PageSectionNameLayout($section->id, $section->name),
                            new PageSectionHtmlTextLayout($section->id, $section->text),
                        ])->title('Section '.($key + 1))->commands([
                            Button::make(__('Delete'))
                                ->type(Color::DANGER)
                                ->icon('bs.x-circle')
                                ->novalidate()
                                ->method('deleteSection', [
                                    'id' => $section->id,
                                    'countAddSectionHtml' => $this->countAddSectionHtml,
                                    'countAddSectionText' => $this->countAddSectionText,
                                    'countAddSectionImage' => $this->countAddSectionImage
                                ]),
                        ]);
                        break;
                    case 'text':
                        $layouts[] = Layout::block([
                            new PageSectionNameLayout($section->id, $section->name),
                            new PageSectionTextTextLayout($section->id, $section->text),
                        ])->title('Section '.($key + 1))->commands([
                            Button::make(__('Delete'))
                                ->type(Color::DANGER)
                                ->icon('bs.x-circle')
                                ->novalidate()
                                ->method('deleteSection', [
                                    'id' => $section->id,
                                    'countAddSectionHtml' => $this->countAddSectionHtml,
                                    'countAddSectionText' => $this->countAddSectionText,
                                    'countAddSectionImage' => $this->countAddSectionImage
                                ]),
                        ]);
                        break;
                    case 'image':
                        $layouts[] = Layout::block([
                            new PageSectionNameLayout($section->id, $section->name),
                            new PageSectionImageTextLayout($section->id, $section->text),
                        ])->title('Section '.($key + 1))->commands([
                            Button::make(__('Delete'))
                                ->type(Color::DANGER)
                                ->icon('bs.x-circle')
                                ->novalidate()
                                ->method('deleteSection', [
                                    'id' => $section->id,
                                    'countAddSectionHtml' => $this->countAddSectionHtml,
                                    'countAddSectionText' => $this->countAddSectionText,
                                    'countAddSectionImage' => $this->countAddSectionImage
                                ]),
                        ]);
                        break;
                }
            }
        }

        for ($i = 0; $i < $this->countAddSectionHtml; $i++)
        {
            $layouts[] = Layout::block([
                new PageSectionNameLayout($i.'_addHtml'),
                new PageSectionHtmlTextLayout($i.'_addHtml'),
            ])->title('Section '.(count($sections) + $i + 1))->commands([
                Button::make(__('Delete'))
                    ->type(Color::DANGER)
                    ->icon('bs.x-circle')
                    ->novalidate()
                    ->method('deleteSection', [
                        'id' => $i.'_addHtml',
                        'countAddSectionHtml' => $this->countAddSectionHtml - 1,
                        'countAddSectionText' => $this->countAddSectionText,
                        'countAddSectionImage' => $this->countAddSectionImage
                    ]),
            ]);
        }

        for ($i = 0; $i < $this->countAddSectionText; $i++)
        {
            $layouts[] = Layout::block([
                new PageSectionNameLayout($i.'_addText'),
                new PageSectionTextTextLayout($i.'_addText'),
            ])->title('Section '.(count($sections) + $this->countAddSectionHtml + $i + 1))->commands([
                Button::make(__('Delete'))
                    ->type(Color::DANGER)
                    ->icon('bs.x-circle')
                    ->novalidate()
                    ->method('deleteSection', [
                        'id' => $i.'_addText',
                        'countAddSectionHtml' => $this->countAddSectionHtml,
                        'countAddSectionText' => $this->countAddSectionText - 1,
                        'countAddSectionImage' => $this->countAddSectionImage
                    ]),
            ]);
        }

        for ($i = 0; $i < $this->countAddSectionImage; $i++)
        {
            $layouts[] = Layout::block([
                new PageSectionNameLayout($i.'_addImage'),
                new PageSectionImageTextLayout($i.'_addImage'),
            ])->title('Section '.(count($sections) + $this->countAddSectionHtml + $this->countAddSectionText + $i + 1))->commands([
                Button::make(__('Delete'))
                    ->type(Color::DANGER)
                    ->icon('bs.x-circle')
                    ->novalidate()
                    ->method('deleteSection', [
                        'id' => $i.'_addImage',
                        'countAddSectionHtml' => $this->countAddSectionHtml,
                        'countAddSectionText' => $this->countAddSectionText,
                        'countAddSectionImage' => $this->countAddSectionImage - 1
                    ]),
            ]);
        }

        return $layouts;
    }

    /**
     * @param Page $page
     * @return RedirectResponse
     */
    public function remove(Page $page): RedirectResponse
    {
        $page->sections()->delete();
        $page->delete();

        Toast::info(__('Page was removed'));

        return redirect()->route('platform.systems.pages');
    }

    /**
     * @param Page $page
     * @param Request $request
     * @return RedirectResponse
     */
    public function save(Page $page, Request $request): RedirectResponse
    {
        $data = $request->toArray();
        $data['page']['url'] = Str::slug($request->input('page.url'));
        $request->replace($data);

        try {
            $request->validate([
                'page.url' => [
                    'required',
                    Rule::unique(Page::class, 'url')->ignore($page),
                ]
            ]);
        } catch (ValidationException $e) {//ToDo redirect()->back()->withInput(); ?
            Alert::error($e->getMessage());
            return redirect()->route('platform.systems.pages.edit', $page->id);
        }

        $this->exists = $page->exists;
        $pageRequest = $request->get('page');
        $pageRequest['updated_at'] = Carbon::now();

        $page->fill($pageRequest)->save();
        $sections = $request->get('sections');
        if (!is_null($sections))
        {
            foreach ($sections as $key => $section)
            {
                if(!str_contains($key, '_add'))
                {
                    PageSection::where('id', $key)->update([
                        'name' => $section['name'],
                        'text' => $section['text'],
                        'page_id' => $page->id,
                    ]);
                } else {
                    if (str_contains($key, 'Html')) {
                        PageSection::create([
                            'name' => is_null($section['name']) ? '' : $section['name'],
                            'text' => is_null($section['text']) ? '' : $section['text'],
                            'page_id' => $page->id,
                            'type' => 'html'
                        ]);
                    } else if (str_contains($key, 'Text'))
                    {
                        PageSection::create([
                            'name' => is_null($section['name']) ? '' : $section['name'],
                            'text' => is_null($section['text']) ? '' : $section['text'],
                            'page_id' => $page->id,
                            'type' => 'text'
                        ]);
                    } else if (str_contains($key, 'Image'))
                    {
                        PageSection::create([
                            'name' => is_null($section['name']) ? '' : $section['name'],
                            'text' => is_null($section['text']) ? '' : $section['text'],
                            'page_id' => $page->id,
                            'type' => 'image'
                        ]);
                    }
                }
            }
        }

        Toast::info(__('Page was saved.'));

        if ($this->exists)
            return redirect()->route('platform.systems.pages');
        else
            return redirect()->route('platform.systems.pages.edit',
                ['page' => $page->id]);
    }

    /**
     * @param Page $page
     * @param Request $request
     * @return RedirectResponse
     */
    public function addSection(Page $page, Request $request): RedirectResponse
    {
        $countAddSectionHtml = $request->input('countAddSectionHtml');
        $countAddSectionText = $request->input('countAddSectionText');
        $countAddSectionImage = $request->input('countAddSectionImage');
        $pageRequest = $request->get('page');
        if (is_null($pageRequest['url']))
            $pageRequest['url'] = Str::slug($pageRequest['title']);
        $page->fill($pageRequest)->save();
        $sections = $request->get('sections');
        if (!is_null($sections))
        {
            foreach ($sections as $key => $section)
            {
                if(!str_contains($key, '_add'))
                {
                    PageSection::where('id', $key)->update([
                        'name' => $section['name'],
                        'text' => $section['text'],
                        'page_id' => $page->id,
                    ]);
                } else {
                    if (str_contains($key, 'Html')) {
                        PageSection::create([
                            'name' => is_null($section['name']) ? '' : $section['name'],
                            'text' => is_null($section['text']) ? '' : $section['text'],
                            'page_id' => $page->id,
                            'type' => 'html'
                        ]);
                        $countAddSectionHtml -= 1;
                    } else if (str_contains($key, 'Text'))
                    {
                        PageSection::create([
                            'name' => is_null($section['name']) ? '' : $section['name'],
                            'text' => is_null($section['text']) ? '' : $section['text'],
                            'page_id' => $page->id,
                            'type' => 'text'
                        ]);
                        $countAddSectionText -= 1;
                    } else if (str_contains($key, 'Image'))
                    {
                        PageSection::create([
                            'name' => is_null($section['name']) ? '' : $section['name'],
                            'text' => is_null($section['text']) ? '' : $section['text'],
                            'page_id' => $page->id,
                            'type' => 'image'
                        ]);
                        $countAddSectionImage -= 1;
                    }
                }
            }
        }
        return redirect()->route('platform.systems.pages.edit',
            ['page' => $page->id, 'countAddSectionHtml' => $countAddSectionHtml,
                'countAddSectionText' => $countAddSectionText, 'countAddSectionImage' => $countAddSectionImage]);
    }

    public function deleteSection(Page $page, Request $request): RedirectResponse
    {
        $countAddSectionHtml = $request->input('countAddSectionHtml');
        $countAddSectionText = $request->input('countAddSectionText');
        $countAddSectionImage = $request->input('countAddSectionImage');

        if(!str_contains($request->input('id'), '_add'))
        {
            PageSection::where('id', $request->input('id'))->delete();
        }

        $pageRequest = $request->get('page');
        if (is_null($pageRequest['url']))
            $pageRequest['url'] = Str::slug($pageRequest['title']);
        $page->fill($pageRequest)->save();
        $sections = $request->get('sections');
        if (!is_null($sections))
        {
            foreach ($sections as $key => $section)
            {
                if(!str_contains($key, '_add'))
                {
                    PageSection::where('id', $key)->update([
                        'name' => $section['name'],
                        'text' => $section['text'],
                        'page_id' => $page->id,
                    ]);
                } else {
                    if (str_contains($key, 'Html') && $key != $request->input('id')) {
                        PageSection::create([
                            'name' => is_null($section['name']) ? '' : $section['name'],
                            'text' => is_null($section['text']) ? '' : $section['text'],
                            'page_id' => $page->id,
                            'type' => 'html'
                        ]);
                        $countAddSectionHtml -= 1;
                    } else if (str_contains($key, 'Text') && $key != $request->input('id'))
                    {
                        PageSection::create([
                            'name' => is_null($section['name']) ? '' : $section['name'],
                            'text' => is_null($section['text']) ? '' : $section['text'],
                            'page_id' => $page->id,
                            'type' => 'text'
                        ]);
                        $countAddSectionText -= 1;
                    } else if (str_contains($key, 'Image') && $key != $request->input('id'))
                    {
                        PageSection::create([
                            'name' => is_null($section['name']) ? '' : $section['name'],
                            'text' => is_null($section['text']) ? '' : $section['text'],
                            'page_id' => $page->id,
                            'type' => 'image'
                        ]);
                        $countAddSectionImage -= 1;
                    }
                }
            }
        }

        return redirect()->route('platform.systems.pages.edit',
            ['page' => $page->id, 'countAddSectionHtml' => $countAddSectionHtml,
                'countAddSectionText' => $countAddSectionText, 'countAddSectionImage' => $countAddSectionImage]);
    }
}