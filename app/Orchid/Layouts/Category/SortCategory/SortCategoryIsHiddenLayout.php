<?php

namespace App\Orchid\Layouts\Category\SortCategory;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Layouts\Rows;

class SortCategoryIsHiddenLayout extends Rows
{
    /**
     * @return array|Field[]
     */
    public function fields(): array
    {
        return [
            CheckBox::make('sortCategory.is_hidden')
                ->title('Hidden')
                ->sendTrueOrFalse(),
        ];
    }
}