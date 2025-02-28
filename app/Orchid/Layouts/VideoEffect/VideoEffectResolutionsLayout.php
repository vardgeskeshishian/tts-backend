<?php

namespace App\Orchid\Layouts\VideoEffect;

use App\Models\VideoEffects\VideoEffectResolution;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class VideoEffectResolutionsLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Select::make('video.resolutions.')
                ->fromQuery(VideoEffectResolution::query(), 'name', 'id')
                ->multiple()
                ->title(__('Resolutions')),
        ];
    }
}