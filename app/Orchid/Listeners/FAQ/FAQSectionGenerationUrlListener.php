<?php

namespace App\Orchid\Listeners\FAQ;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Listener;
use Orchid\Screen\Repository;
use Orchid\Support\Facades\Layout;

class FAQSectionGenerationUrlListener extends Listener
{
    /**
     * @var array
     */
    protected $targets = [
        'faqSection.title',
    ];

    /**
     * @return iterable
     */
    protected function layouts(): iterable
    {
        return [
            Layout::rows([
                Input::make('faqSection.title')
                    ->title(__('Title'))
                    ->placeholder(__('Title'))
                    ->required(),
            ]),

            Layout::rows([
                Input::make('faqSection.url')
                    ->title(__('URL'))
                    ->required()
                    ->placeholder(__('URL')),
            ])
        ];
    }

    /**
     * @param Repository $repository
     * @param Request $request
     * @return Repository
     */
    public function handle(Repository $repository, Request $request): Repository
    {
        return $repository
            ->set('faqSection.title', $request->input('faqSection.title'))
            ->set('faqSection.url', Str::slug($request->input('faqSection.title')));
    }
}