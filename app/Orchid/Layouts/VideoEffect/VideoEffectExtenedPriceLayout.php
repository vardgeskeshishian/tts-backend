<?php

namespace App\Orchid\Layouts\VideoEffect;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class VideoEffectExtenedPriceLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Input::make('video.price_extended')
                ->title(__('Extended Price'))
                ->placeholder(__('Extended Price')),
        ];
    }
}