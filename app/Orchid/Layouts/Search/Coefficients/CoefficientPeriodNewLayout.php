<?php

namespace App\Orchid\Layouts\Search\Coefficients;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class CoefficientPeriodNewLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('period_new')
                ->type('number')
                ->min(1)
                ->title(__('Period of novelty'))
                ->placeholder(__('Period of novelty')),
        ];
    }
}