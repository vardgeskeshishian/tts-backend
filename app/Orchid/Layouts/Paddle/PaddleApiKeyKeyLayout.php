<?php

namespace App\Orchid\Layouts\Paddle;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class PaddleApiKeyKeyLayout extends Rows
{
    public function fields(): iterable
    {
        return [
            Input::make('key.key')
                ->type('text')
                ->max(255)
                ->required()
                ->title(__('Key'))
                ->placeholder(__('Key')),
        ];
    }
}