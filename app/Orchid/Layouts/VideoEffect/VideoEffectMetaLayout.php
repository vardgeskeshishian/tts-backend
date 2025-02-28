<?php

namespace App\Orchid\Layouts\VideoEffect;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;

class VideoEffectMetaLayout extends Rows
{
    public function fields(): iterable
    {
        return [
            Input::make('video.meta_title')
                ->type('text')
                ->title(__('Title'))
                ->placeholder(__('Title')),

            TextArea::make('video.meta_description')
                ->rows(5)
                ->title(__('Description'))
                ->placeholder(__('Description')),
        ];
    }
}
