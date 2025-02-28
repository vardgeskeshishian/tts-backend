<?php

namespace App\Orchid\Layouts\Robots;

use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;

class RobotsTxtFileEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            TextArea::make('text')
                ->rows(9)
                ->title(__('Text'))
                ->placeholder(__('Text')),
        ];
    }
}