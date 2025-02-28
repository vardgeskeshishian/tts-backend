<?php

namespace App\Orchid\Layouts\Search\Coefficients\Template;

use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;

class CoefficientWordsLayout extends Rows
{
    public function fields(): array
    {
        return [
            TextArea::make('words_video')
                ->rows(5)
                ->title(__('Words of Exception'))
                ->placeholder(__('Words of Exception')),
        ];
    }
}