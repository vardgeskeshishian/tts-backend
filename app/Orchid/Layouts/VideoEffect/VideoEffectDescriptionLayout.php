<?php

namespace App\Orchid\Layouts\VideoEffect;

use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;

class VideoEffectDescriptionLayout extends Rows
{
    public function fields(): array
    {
        return [
            TextArea::make('video.description')
                ->rows(5)
                ->title(__('Description'))
                ->placeholder(__('Description')),
        ];
    }
}