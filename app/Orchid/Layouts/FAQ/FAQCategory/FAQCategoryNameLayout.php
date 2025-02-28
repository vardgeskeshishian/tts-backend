<?php

namespace App\Orchid\Layouts\FAQ\FAQCategory;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class FAQCategoryNameLayout extends Rows
{
    /**
     * @return array|Field[]
     */
    public function fields(): array
    {
        return [
            Input::make('faqCategory.name')
                ->title(__('Name'))
                ->placeholder(__('Name')),
        ];
    }
}
