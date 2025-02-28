<?php

namespace App\Orchid\Layouts\Tracks;

use App\Models\Tags\CuratorPick;
use App\Models\Tags\Genre;
use App\Models\Tags\Instrument;
use App\Models\Tags\Mood;
use App\Models\Tags\Tag;
use App\Models\Tags\Type;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class TrackTagLayout extends Rows
{
    public function fields(): iterable
    {
        return [
            Select::make('track.tags.')
                ->fromModel(Tag::class, 'name')
                ->multiple()
                ->title(__('Tags')),

            Select::make('track.types.')
                ->fromModel(Type::class, 'name')
                ->multiple()
                ->title(__('Types')),

            Select::make('track.moods.')
                ->fromModel(Mood::class, 'name')
                ->multiple()
                ->title(__('Moods')),

            Select::make('track.genres.')
                ->fromModel(Genre::class, 'name')
                ->multiple()
                ->title(__('Genres')),

            Select::make('track.instruments.')
                ->fromModel(Instrument::class, 'name')
                ->multiple()
                ->title(__('Instruments')),

            Select::make('track.curatorPicks.')
                ->fromModel(CuratorPick::class, 'name')
                ->multiple()
                ->title(__('Curator Picks')),
        ];
    }
}