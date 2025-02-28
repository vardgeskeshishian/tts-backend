<?php

namespace App\Orchid\Layouts\Search\Coefficients;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class CoefficientWTrendFreeLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('free_coefficient')
                ->type('text')
                ->max(3)
                ->title(__('Weight Trend Free'))
                ->placeholder(__('Weight Trend Free')),
        ];
    }
}