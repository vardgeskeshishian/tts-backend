<?php

namespace App\Orchid\Layouts\FAQ\FAQSection;

use App\Models\Structure\FAQCategory;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class FAQSectionCategoryIdLayout extends Rows
{
    public function fields(): array
    {
        return [
            Select::make('faqSection.category_id')
                ->fromModel(FAQCategory::class, 'name')
                ->title(__('Category')),
        ];
    }
}
