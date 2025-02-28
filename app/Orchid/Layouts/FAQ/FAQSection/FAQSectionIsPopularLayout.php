<?php

namespace App\Orchid\Layouts\FAQ\FAQSection;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Layouts\Rows;

class FAQSectionIsPopularLayout extends Rows
{
    /**
     * @return array|Field[]
     */
    public function fields(): array
    {
        return [
            CheckBox::make('faqSection.is_popular')
                ->title(__('Popular'))
                ->sendTrueOrFalse(),
        ];
    }
}