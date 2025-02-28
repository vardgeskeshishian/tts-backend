<?php

namespace App\Orchid\Listeners\User;

use Illuminate\Http\Request;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Listener;
use Orchid\Screen\Repository;
use Orchid\Support\Facades\Layout;
use Illuminate\Support\Facades\Log;

class UserEditListener extends Listener
{
    protected $targets = [
        'user.email',
    ];

    /**
     * @return iterable
     */
    protected function layouts(): iterable
    {
        return [
            Layout::rows([
                Input::make('user.email')
                    ->type('email')
                    ->required()
                    ->title(__('Email'))
                    ->placeholder(__('Email')),

                Input::make('user.name')
                    ->type('text')
                    ->required()
                    ->max(255)
                    ->title(__('Name'))
                    ->placeholder(__('Name')),
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
        if(is_null($request->input('user.name')))
        {
            return $repository
                ->set('user.name', explode('@', $request->input('user.email'))[0])
                ->set('user.email', $request->input('user.email'));
        } else {
            return $repository
                ->set('user.name', $request->input('user.name'))
                ->set('user.email', $request->input('user.email'));
        }
    }
}