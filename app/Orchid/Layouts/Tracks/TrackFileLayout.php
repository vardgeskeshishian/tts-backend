<?php

namespace App\Orchid\Layouts\Tracks;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Upload;
use Orchid\Screen\Layouts\Rows;

class TrackFileLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Upload::make('loss.attachment_id')
                ->required()
                ->maxFiles(1)
                ->acceptedFiles('audio/mpeg')
                ->title('Loss MP3'),

            Upload::make('hq.attachment_id')
                ->maxFiles(1)
                ->acceptedFiles('audio/mpeg')
                ->title('HQ MP3'),

            Upload::make('wav.attachment_id')
                ->maxFiles(1)
                ->acceptedFiles('audio/wav')
                ->title('WAV'),

            Upload::make('track.archive.attachment_id')
                ->maxFiles(1)
                ->title('ZIP'),
        ];
    }
}
