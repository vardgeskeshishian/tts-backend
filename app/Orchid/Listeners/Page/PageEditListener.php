<?php

namespace App\Orchid\Listeners\Page;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Listener;
use Orchid\Screen\Repository;
use Orchid\Support\Facades\Layout;

class PageEditListener extends Listener
{
    /**
     * @var array
     */
    protected $targets = [
        'page.title',
    ];

    public function __construct(
        private bool $is_url = true
    )
    {}

    protected function layouts(): iterable
    {
        return [
            Layout::rows([
                Input::make('page.title')
                    ->type('text')
                    ->max(255)
                    ->required()
                    ->title(__('Title'))
                    ->placeholder(__('Title')),
            ]),
            Layout::rows([
                Input::make('page.url')
                    ->type('text')
                    ->max(255)
                    ->required()
                    ->title(__('URL'))
                    ->placeholder(__('URL')),
            ]),
            Layout::rows([
                Input::make('page.metaTitle')
                    ->type('text')
                    ->max(255)
                    ->required()
                    ->title(__('Meta Title'))
                    ->placeholder(__('Meta Title')),
            ]),
            Layout::rows([
                TextArea::make('page.metaDescription')
                    ->rows(5)
                    ->required()
                    ->title(__('Meta Description'))
                    ->placeholder(__('Meta Description')),
            ])
        ];
    }

    public function handle(Repository $repository, Request $request): Repository
    {
        if ($request->input('page.title'))
        {
            $this->is_url = true;
            return $repository
                ->set('page.title', $request->input('page.title'))
                ->set('page.url', Str::slug($request->input('page.title')))
                ->set('page.metaTitle', $request->input('page.metaTitle'))
                ->set('page.metaDescription', $request->input('page.metaDescription'));
        } else {
            $this->is_url = false;
            return $repository
                ->set('page.metaTitle', $request->input('page.metaTitle'))
                ->set('page.metaDescription', $request->input('page.metaDescription'));
        }
    }
}