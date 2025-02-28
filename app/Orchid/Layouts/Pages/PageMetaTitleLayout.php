<?php

namespace App\Orchid\Layouts\Pages;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class PageMetaTitleLayout extends Rows
{
    /**
     * @return array|Field[]
     */
    public function fields(): array
    {
        return [
            Input::make('page.metaTitle')
                ->type('text')
                ->max(255)
                ->required()
                ->title(__('Meta Title'))
                ->placeholder(__('Meta Title')),
        ];
    }
}