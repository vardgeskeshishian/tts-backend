<?php

namespace App\Orchid\Layouts\Category\Template\Application;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Cropper;
use Orchid\Screen\Layouts\Rows;

class ApplicationForegroundImageLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Cropper::make('foreground.url')
                ->title(__('Foreground Image'))
                ->targetUrl(),
        ];
    }
}
