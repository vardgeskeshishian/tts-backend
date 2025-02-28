<?php

namespace App\Orchid\Listeners\VideoEffect;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Listener;
use Orchid\Screen\Repository;
use Orchid\Support\Facades\Layout;

class VideoEffectNameListener extends Listener
{
    protected $targets = [
        'video.name',
    ];

    /**
     * @return iterable
     */
    protected function layouts(): iterable
    {
        return [
            Layout::rows([
                Input::make('video.name')
                    ->type('text')
                    ->max(255)
                    ->required()
                    ->title(__('Name'))
                    ->placeholder(__('Name')),

                Input::make('video.slug')
                    ->type('text')
                    ->title(__('Slug'))
                    ->placeholder(__('Slug')),
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
            ->set('video.name', $request->input('video.name'))
            ->set('video.slug', Str::slug($request->input('video.name')));
    }
}