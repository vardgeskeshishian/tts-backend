<?php

namespace App\Orchid\Listeners\Category;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Listener;
use Orchid\Screen\Repository;
use Orchid\Support\Facades\Layout;

class CategoryEditListener extends Listener
{
    public function __construct(
        private bool $is_url = false
    )
    {}

    protected $targets = [
        'tag.name'
    ];

    protected function layouts(): iterable
    {
        return [
            Layout::rows([
                Input::make('tag.name')
                    ->type('text')
                    ->max(255)
                    ->required()
                    ->title(__('Name'))
                    ->placeholder(__('Name')),
            ]),
            Layout::rows([
                Input::make('tag.slug')
                    ->type('text')
                    ->max(255)
                    ->canSee($this->is_url)
                    ->required()
                    ->title(__('URL'))
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
        if (!empty($request->input('tag.name')))
        {
            $this->is_url = true;
            return $repository
                ->set('tag.name', $request->input('tag.name'))
                ->set('tag.slug', Str::slug($request->input('tag.name')));
        } else {
            $this->is_url = false;
            return $repository;
        }
    }
}
