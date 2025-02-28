<?php

namespace App\Orchid\Layouts\DefaultPicture;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Cropper;
use Orchid\Screen\Layouts\Rows;

class DefaultPictureLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Cropper::make('picture.value')
                ->title(__('Default Picture'))
                ->targetUrl(),
        ];
    }
}