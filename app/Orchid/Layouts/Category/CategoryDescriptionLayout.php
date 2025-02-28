<?php

namespace App\Orchid\Layouts\Category;

use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;

class CategoryDescriptionLayout extends Rows
{
    public function fields(): array
    {
        return [
            TextArea::make('tag.description')
                ->rows(5)
                ->max(255)
                ->title(__('Description'))
                ->placeholder(__('Description')),
        ];
    }
}