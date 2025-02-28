<?php

namespace App\Orchid\Layouts\Tracks;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class TrackTempoLayout extends Rows
{
    /**
     * @return iterable
     */
    public function fields(): iterable
    {
        return [
            Input::make('track.tempo')
                ->type('number')
                ->title(__('Tempo'))
                ->placeholder(__('Tempo')),
        ];
    }
}