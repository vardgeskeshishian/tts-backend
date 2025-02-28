<?php

namespace App\Orchid\Layouts\Category;

use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;

class CategoryMetaDescriptionLayout extends Rows
{
    public function fields(): array
    {
        return [
            TextArea::make('tag.metaDescription')
                ->rows(5)
                ->max(255)
                ->title(__('Meta-description'))
                ->placeholder(__('Meta-description')),
        ];
    }
}