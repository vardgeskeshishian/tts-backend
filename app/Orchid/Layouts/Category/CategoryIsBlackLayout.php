<?php

namespace App\Orchid\Layouts\Category;

use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Layouts\Rows;

class CategoryIsBlackLayout extends Rows
{
    public function fields(): array
    {
        return [
            CheckBox::make('tag.is_black')
                ->title('Black text')
                ->sendTrueOrFalse(),
        ];
    }
}