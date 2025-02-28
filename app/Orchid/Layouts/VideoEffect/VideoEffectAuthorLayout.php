<?php

namespace App\Orchid\Layouts\VideoEffect;

use App\Models\Authors\AuthorVideo;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class VideoEffectAuthorLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Select::make('video.author_profile_id')
                ->fromQuery(AuthorVideo::query(), 'name', 'id')
                ->title(__('Author Video'))
                ->empty('Not Select'),
        ];
    }
}
