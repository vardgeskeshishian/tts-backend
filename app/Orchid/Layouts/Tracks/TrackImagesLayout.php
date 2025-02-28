<?php

namespace App\Orchid\Layouts\Tracks;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Cropper;
use Orchid\Screen\Layouts\Rows;

class TrackImagesLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Cropper::make('background.url')
                ->title(__('Background'))
                ->targetUrl(),
        ];
    }
}