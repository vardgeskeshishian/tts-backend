<?php

namespace App\Orchid\Layouts\Search\Coefficients;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class CoefficientWTrendLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('w_trend')
                ->type('text')
                ->max(3)
                ->title(__('Weight Trend'))
                ->placeholder(__('Weight Trend')),
        ];
    }
}