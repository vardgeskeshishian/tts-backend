<?php

namespace App\Orchid\Layouts\Category;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class CategoryH1Layout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('tag.h1')
                ->type('text')
                ->max(255)
                ->title(__('H1'))
                ->placeholder(__('H1')),
        ];
    }
}