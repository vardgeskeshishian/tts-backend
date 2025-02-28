<?php

namespace App\Orchid\Layouts\Category\SortCategory;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class SortCategoryOrderLayout extends Rows
{
    /**
     * @return iterable
     */
    public function fields(): iterable
    {
        return [
            Input::make('sortCategory.order')
                ->type('number')
                ->title(__('Order'))
                ->placeholder(__('Order')),
        ];
    }
}