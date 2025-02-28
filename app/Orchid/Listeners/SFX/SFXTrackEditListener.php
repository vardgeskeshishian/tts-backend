<?php

namespace App\Orchid\Listeners\SFX;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Listener;
use Orchid\Screen\Repository;
use Orchid\Support\Facades\Layout;

class SFXTrackEditListener extends Listener
{
    /**
     * @var array
     */
    protected $targets = [
        'sfx.name',
    ];

    /**
     * @return iterable
     */
    protected function layouts(): iterable
    {
        return [
            Layout::block(
                Layout::rows([
                    Input::make('sfx.name')
                        ->type('text')
                        ->max(255)
                        ->required()
                        ->title(__('Name'))
                        ->placeholder(__('Name')),
                ])
            ),

            Layout::block(
                Layout::rows([
                    Input::make('sfx.slug')
                        ->type('text')
                        ->max(255)
                        ->required()
                        ->title(__('Slug'))
                        ->placeholder(__('Slug')),
                ])
            ),
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
            ->set('sfx.name', $request->input('sfx.name'))
            ->set('sfx.slug', Str::slug($request->input('sfx.name')));
    }
}