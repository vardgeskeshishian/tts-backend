<?php

namespace App\Orchid\Layouts\Search\Coefficients\Template;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class CoefficientMTELayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('mte_video')
                ->type('number')
                ->min(1)
                ->title(__('Max Tag Exact'))
                ->placeholder(__('Max Tag Exact')),
        ];
    }
}