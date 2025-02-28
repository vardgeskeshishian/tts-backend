<?php

namespace App\Orchid\Layouts\FAQ\FAQSection;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class FAQSectionUrlLayout extends Rows
{
    /**
     * @return array|Field[]
     */
    public function fields(): array
    {
        return [
            Input::make('faqSection.url')
                ->title(__('URL'))
                ->placeholder(__('URL')),
        ];
    }
}