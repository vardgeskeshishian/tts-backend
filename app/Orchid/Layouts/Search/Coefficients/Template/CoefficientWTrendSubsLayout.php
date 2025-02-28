<?php

namespace App\Orchid\Layouts\Search\Coefficients\Template;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class CoefficientWTrendSubsLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('subs_coefficient_video')
                ->type('text')
                ->max(3)
                ->title(__('Weight Trend Subs'))
                ->placeholder(__('Weight Trend Subs')),
        ];
    }
}