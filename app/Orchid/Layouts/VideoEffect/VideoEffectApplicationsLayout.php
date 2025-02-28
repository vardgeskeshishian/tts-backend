<?php

namespace App\Orchid\Layouts\VideoEffect;

use App\Models\VideoEffects\VideoEffectApplication;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class VideoEffectApplicationsLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Select::make('video.application_id')
                ->fromQuery(VideoEffectApplication::query(), 'name', 'id')
                ->title(__('Application')),
        ];
    }
}