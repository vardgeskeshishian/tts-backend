<?php

namespace App\Orchid\Layouts\Category\Music\CuratorPick;

use App\Models\Track;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class CuratorPickTrackLayout extends Rows
{
    protected function fields(): iterable
    {
        $tracks = Track::select([
            'tracks.id as id',
            DB::raw("concat(tracks.name, ' - ', author_profiles.name) as name")
        ])->join('author_profiles', 'tracks.author_profile_id', '=', 'author_profiles.id')
            ->pluck('name', 'id')->toArray();

        return [
            Select::make('tracks')
                ->options($tracks)
                ->multiple()
                ->title(__('Tracks'))
        ];
    }
}