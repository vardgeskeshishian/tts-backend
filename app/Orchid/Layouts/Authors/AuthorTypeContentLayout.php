<?php

namespace App\Orchid\Layouts\Authors;

use Orchid\Screen\Layouts\Rows;
use Orchid\Screen\Fields\CheckBox;

class AuthorTypeContentLayout extends Rows
{
    public function fields(): iterable
    {
        return [
            CheckBox::make('author.is_track')
                ->sendTrueOrFalse()
                ->placeholder('Music'),

            CheckBox::make('author.is_video')
                ->sendTrueOrFalse()
                ->placeholder('Video'),
        ];
    }
}