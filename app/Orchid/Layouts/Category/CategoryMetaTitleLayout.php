<?php

namespace App\Orchid\Layouts\Category;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class CategoryMetaTitleLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('tag.metaTitle')
                ->type('text')
                ->max(255)
                ->title(__('Meta-title'))
                ->placeholder(__('Meta-title')),
        ];
    }
}