<?php

namespace App\Orchid\Layouts\Search\Coefficients;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class CoefficientMTCLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('mtc')
                ->type('number')
                ->min(1)
                ->title(__('Max Tag Count'))
                ->placeholder(__('Max Tag Count')),
        ];
    }
}