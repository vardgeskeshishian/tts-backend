<?php

namespace App\Orchid\Layouts\SFX;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Upload;
use Orchid\Screen\Layouts\Rows;

class SFXFileLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Upload::make('sfx.attachment_id')
                ->maxFiles(1)
                ->acceptedFiles('audio/wav')
                ->title('WAV'),
        ];
    }
}