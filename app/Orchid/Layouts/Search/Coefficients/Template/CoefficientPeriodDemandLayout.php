<?php


namespace App\Orchid\Layouts\Search\Coefficients\Template;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class CoefficientPeriodDemandLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('period_demand_video')
                ->type('number')
                ->min(1)
                ->title(__('Period of demand'))
                ->placeholder(__('Period of demand')),
        ];
    }
}