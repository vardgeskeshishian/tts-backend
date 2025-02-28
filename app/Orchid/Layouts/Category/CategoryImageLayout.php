<?php

namespace App\Orchid\Layouts\Category;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Cropper;
use Orchid\Screen\Layouts\Rows;

class CategoryImageLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Cropper::make('tag.icon.url')
                ->title(__('Image'))
                ->targetUrl(),
        ];
    }
}
