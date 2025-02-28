<?php

namespace App\Orchid\Layouts\Tracks;

use Orchid\Screen\Field;
use Orchid\Screen\Layouts\Rows;
use Orchid\Screen\Fields\CheckBox;

class TrackChexboxLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            CheckBox::make('track.hidden')
                ->title('Hidden')
                ->sendTrueOrFalse(),

            CheckBox::make('track.premium')
                ->title('Premium')
                ->sendTrueOrFalse(),

            CheckBox::make('track.exclusive')
                ->title('Exclusive')
                ->sendTrueOrFalse(),

            CheckBox::make('track.has_content_id')
                ->title('ContentId')
                ->sendTrueOrFalse(),

            CheckBox::make('track.featured')
                ->title('Featured')
                ->sendTrueOrFalse(),

            CheckBox::make('track.new')
                ->title('New')
                ->sendTrueOrFalse(),
			
			CheckBox::make('track.is_orfium')
				->title('Is orfium')
				->sendTrueOrFalse(),
			
			CheckBox::make('track.is_commercial')
				->title('Is commercial')
				->sendTrueOrFalse(),
        ];
    }
}
