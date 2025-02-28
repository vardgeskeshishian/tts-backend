<?php

namespace App\Orchid\Layouts\FAQ\FAQSection;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class FAQSectionTitleLayout extends Rows
{
    /**
     * @return array|Field[]
     */
    public function fields(): array
    {
        return [
            Input::make('faqSection.title')
                ->title(__('Title'))
                ->placeholder(__('Title'))
                ->required(),
        ];
    }
}