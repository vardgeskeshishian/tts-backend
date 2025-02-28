<?php

namespace App\Orchid\Layouts\Search\Coefficients\Template;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class CoefficientMTCLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('mtc_video')
                ->type('number')
                ->min(1)
                ->title(__('Max Tag Count'))
                ->placeholder(__('Max Tag Count')),
        ];
    }
}