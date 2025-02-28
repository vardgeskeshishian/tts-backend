<?php

namespace App\Orchid\Layouts\Pages;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class PageUrlLayout extends Rows
{
    /**
     * @return array|Field[]
     */
    public function fields(): array
    {
        return [
            Input::make('page.url')
                ->type('text')
                ->max(255)
                ->required()
                ->title(__('URL'))
                ->placeholder(__('URL')),
        ];
    }
}