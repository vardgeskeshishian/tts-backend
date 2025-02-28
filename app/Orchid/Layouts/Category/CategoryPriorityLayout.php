<?php

namespace App\Orchid\Layouts\Category;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class CategoryPriorityLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('tag.priority')
                ->type('number')
                ->min(1)
                ->placeholder(__('Sorting Priority')),
        ];
    }
}