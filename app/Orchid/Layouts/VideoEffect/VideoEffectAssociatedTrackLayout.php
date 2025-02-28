<?php

namespace App\Orchid\Layouts\VideoEffect;

use App\Models\Track;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class VideoEffectAssociatedTrackLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Select::make('video.associated_music')
                ->fromQuery(Track::query(), 'name', 'slug')
                ->empty('Not Select')
                ->title(__('Associated Music')),
        ];
    }
}