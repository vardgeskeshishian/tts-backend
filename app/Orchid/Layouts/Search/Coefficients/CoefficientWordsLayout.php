<?php

namespace App\Orchid\Layouts\Search\Coefficients;

use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;

class CoefficientWordsLayout extends Rows
{
    public function fields(): array
    {
        return [
            TextArea::make('words')
                ->rows(5)
                ->title(__('Words of Exception'))
                ->placeholder(__('Words of Exception')),
        ];
    }
}