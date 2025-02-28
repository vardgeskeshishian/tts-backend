<?php

namespace App\Orchid\Layouts\License;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class LicensePriceLayout extends Rows
{
    /**
     * @return iterable
     */
    public function fields(): iterable
    {
        return [
            Input::make('license.price')
                ->required()
                ->title(__('Price'))
                ->placeholder(__('Price')),
        ];
    }
}