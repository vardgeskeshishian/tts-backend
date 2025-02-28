<?php

namespace App\Orchid\Layouts\Category\Template\Tag;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class TagPositionLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('tag.position')
                ->type('text')
                ->max(255)
                ->title(__('Position'))
                ->placeholder(__('Position')),
        ];
    }
}
