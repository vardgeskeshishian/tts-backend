<?php

namespace App\Orchid\Layouts\Search\Coefficients;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class CoefficientWEMCLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('w_emc')
                ->type('text')
                ->max(3)
                ->title(__('Weight EMC'))
                ->placeholder(__('Weight EMC')),
        ];
    }
}