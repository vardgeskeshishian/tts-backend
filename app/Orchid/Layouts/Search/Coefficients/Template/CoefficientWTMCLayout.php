<?php

namespace App\Orchid\Layouts\Search\Coefficients\Template;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class CoefficientWTMCLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('w_tmc_video')
                ->type('text')
                ->min(3)
                ->title(__('Weight TMC'))
                ->placeholder(__('Weight TMC')),
        ];
    }
}