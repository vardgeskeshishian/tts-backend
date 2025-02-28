<?php

namespace App\Orchid\Layouts\Authors;

use Orchid\Screen\Field;
use Orchid\Screen\Layouts\Rows;
use Orchid\Screen\Fields\Cropper;

class AuthorBackgroundLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Cropper::make('author.background.url')
                ->title(__('Background'))
                ->targetUrl()
        ];
    }
}