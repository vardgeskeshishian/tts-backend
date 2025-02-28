<?php

namespace App\Orchid\Layouts\Search\Coefficients\Template;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class CoefficientWNLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('w_n_video')
                ->type('text')
                ->max(3)
                ->title(__('Weight N'))
                ->placeholder(__('Weight N')),
        ];
    }
}