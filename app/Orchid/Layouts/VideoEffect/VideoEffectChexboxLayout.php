<?php

namespace App\Orchid\Layouts\VideoEffect;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Layouts\Rows;

class VideoEffectChexboxLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            CheckBox::make('video.hidden')
                ->title('Hidden')
                ->sendTrueOrFalse(),

            CheckBox::make('video.exclusive')
                ->title('Exclusive')
                ->sendTrueOrFalse(),

            CheckBox::make('video.has_content_id')
                ->title('ContentId')
                ->sendTrueOrFalse(),

            CheckBox::make('video.is_featured')
                ->title('Featured')
                ->sendTrueOrFalse(),

            CheckBox::make('video.new')
                ->title('New')
                ->sendTrueOrFalse(),
        ];
    }
}
