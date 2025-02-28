<?php

namespace App\Orchid\Layouts\License;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class LayoutDescriptionLayout extends Rows
{
    /**
     * @return iterable
     */
    public function fields(): iterable
    {
        return [
            Input::make('license.description')
                ->required()
                ->title(__('Description'))
                ->placeholder(__('Description')),
        ];
    }
}
