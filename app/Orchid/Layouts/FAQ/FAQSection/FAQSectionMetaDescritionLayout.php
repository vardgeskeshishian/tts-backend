<?php

namespace App\Orchid\Layouts\FAQ\FAQSection;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;

class FAQSectionMetaDescritionLayout extends Rows
{
    /**
     * @return array|Field[]
     */
    public function fields(): array
    {
        return [
            TextArea::make('faqSection.metaDescription')
                ->rows(5)
                ->title(__('Meta Description'))
                ->placeholder(__('Meta Description')),
        ];
    }
}