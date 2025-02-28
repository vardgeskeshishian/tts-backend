<?php

namespace App\Orchid\Layouts\Tracks;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;

class TrackMetaLayout extends Rows
{
    public function fields(): iterable
    {
        return [
            Input::make('track.metaTitle')
                ->type('text')
                ->title(__('Title'))
                ->placeholder(__('Title')),

            TextArea::make('track.metaDescription')
                ->rows(5)
                ->title(__('Description'))
                ->placeholder(__('Description')),
        ];
    }
}