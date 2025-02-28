<?php

namespace App\Orchid\Layouts\Search\Coefficients;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class CoefficientMTELayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('mte')
                ->type('number')
                ->min(1)
                ->title(__('Max Tag Exact'))
                ->placeholder(__('Max Tag Exact')),
        ];
    }
}