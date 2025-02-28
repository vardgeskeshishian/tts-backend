<?php

namespace App\Orchid\Layouts\Search\Coefficients\Template;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class CoefficientWTrendLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('w_trend_video')
                ->type('text')
                ->max(3)
                ->title(__('Weight Trend'))
                ->placeholder(__('Weight Trend')),
        ];
    }
}