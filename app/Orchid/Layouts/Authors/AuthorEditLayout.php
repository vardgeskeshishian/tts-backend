<?php

namespace App\Orchid\Layouts\Authors;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class AuthorEditLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Input::make('author.name')
                ->type('text')
                ->max(255)
                ->required()
                ->title(__('Name'))
                ->placeholder(__('Name')),

            Input::make('author.description')
                ->type('text')
                ->required()
                ->title(__('Description'))
                ->placeholder(__('Description')),
        ];
    }
}
