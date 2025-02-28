<?php

namespace App\Orchid\Layouts\Tracks;

use App\Models\Authors\AuthorMusic;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class TrackAuthorLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Select::make('track.author_profile_id')
                ->fromQuery(AuthorMusic::query(), 'name', 'id')
                ->title(__('Author Music')),
        ];
    }
}
