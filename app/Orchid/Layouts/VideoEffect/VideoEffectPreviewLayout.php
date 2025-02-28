<?php

namespace App\Orchid\Layouts\VideoEffect;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Upload;
use Orchid\Screen\Layouts\Rows;

class VideoEffectPreviewLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Upload::make('video.preview_id')
                ->maxFileSize(100)
                ->title('Preview Video')
                ->maxFiles(1)
        ];
    }
}