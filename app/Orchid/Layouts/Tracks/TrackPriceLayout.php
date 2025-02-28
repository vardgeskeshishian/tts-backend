<?php

namespace App\Orchid\Layouts\Tracks;

use App\Models\License;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class TrackPriceLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Select::make('licenses.')
                ->fromModel(License::class, 'type')
                ->multiple()
                ->title(__('Type Licence')),
        ];
    }
}