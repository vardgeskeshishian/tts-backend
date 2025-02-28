<?php

namespace App\Orchid\Layouts\Search\Coefficients\Template;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class CoefficientWTrendFreeLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('free_coefficient_video')
                ->type('text')
                ->max(3)
                ->title(__('Weight Trend Free'))
                ->placeholder(__('Weight Trend Free')),
        ];
    }
}