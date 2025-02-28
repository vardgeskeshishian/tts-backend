<?php


namespace App\Orchid\Layouts\Search\Coefficients;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class CoefficientPeriodDemandLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('period_demand')
                ->type('number')
                ->min(1)
                ->title(__('Period of demand'))
                ->placeholder(__('Period of demand')),
        ];
    }
}