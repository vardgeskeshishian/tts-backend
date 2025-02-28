<?php

namespace App\Orchid\Listeners\Author;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Listener;
use Orchid\Screen\Repository;
use Orchid\Support\Facades\Layout;

class AuthorEditListener extends Listener
{
    protected $targets = [
        'author.name'
    ];

    /**
     * @return iterable
     */
    protected function layouts(): iterable
    {
        return [
            Layout::rows([
                Input::make('author.name')
                    ->type('text')
                    ->max(255)
                    ->required()
                    ->title(__('Name'))
                    ->placeholder(__('Name')),

                Input::make('author.slug')
                    ->type('text')
                    ->max(255)
                    ->required()
                    ->title(__('Slug'))
                    ->placeholder(__('Slug')),

                TextArea::make('author.description')
                    ->rows(5)
                    ->required()
                    ->title(__('Description'))
                    ->placeholder(__('Description')),
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
            ->set('author.name', $request->input('author.name'))
            ->set('author.description', $request->input('author.description'))
            ->set('author.slug', Str::slug($request->input('author.name')));
    }
}
