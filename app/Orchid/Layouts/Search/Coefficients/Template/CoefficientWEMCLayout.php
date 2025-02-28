<?php

namespace App\Orchid\Layouts\Search\Coefficients\Template;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class CoefficientWEMCLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('w_emc_video')
                ->type('text')
                ->max(3)
                ->title(__('Weight EMC'))
                ->placeholder(__('Weight EMC')),
        ];
    }
}