<?php

namespace App\Orchid\Layouts\Settings;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class SettingFreeClaimsLayout extends Rows
{
    /**
     * @return iterable
     */
    public function fields(): iterable
    {
        return [
            Input::make('free_claims')
                ->type('number')
                ->min(0)
                ->title(__('Free Claims'))
                ->placeholder(__('Free Claims')),
        ];
    }
}