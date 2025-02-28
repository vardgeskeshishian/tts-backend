<?php

namespace App\Orchid\Layouts\Search\Coefficients;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class CoefficientWTrendSubsLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('subs_coefficient')
                ->type('text')
                ->max(3)
                ->title(__('Weight Trend Subs'))
                ->placeholder(__('Weight Trend Subs')),
        ];
    }
}