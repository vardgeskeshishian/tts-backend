<?php

namespace App\Orchid\Layouts\VideoEffect;

use App\Models\VideoEffects\VideoEffectPlugin;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class VideoEffectPluginsLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Select::make('video.plugins.')
                ->fromQuery(VideoEffectPlugin::query(), 'name', 'id')
                ->multiple()
                ->title(__('Plugin')),
        ];
    }
}
