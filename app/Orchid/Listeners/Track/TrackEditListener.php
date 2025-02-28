<?php

namespace App\Orchid\Listeners\Track;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Listener;
use Orchid\Screen\Repository;
use Orchid\Support\Facades\Layout;

class TrackEditListener extends Listener
{
    protected $targets = [
        'track.name',
    ];

    /**
     * @return iterable
     */
    protected function layouts(): iterable
    {
        return [
            Layout::rows([
                Input::make('track.name')
                    ->type('text')
                    ->max(255)
                    ->required()
                    ->title(__('Name'))
                    ->placeholder(__('Name')),

                Input::make('track.slug')
                    ->type('text')
                    ->title(__('Slug'))
                    ->placeholder(__('Slug')),

                TextArea::make('track.description')
                    ->rows(5)
                    ->title(__('Description'))
                    ->placeholder(__('Description')),
				
				Input::make('track.avatar_name')
					->type('text')
					->title(__('Avatar name'))
					->placeholder(__('Avatar name')),
            ]),
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
            ->set('track.name', $request->input('track.name'))
            ->set('track.slug', Str::slug($request->input('track.name')));
    }
}
