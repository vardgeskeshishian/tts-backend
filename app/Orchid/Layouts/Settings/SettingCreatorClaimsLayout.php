<?php

namespace App\Orchid\Layouts\Settings;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class SettingCreatorClaimsLayout extends Rows
{
    /**
     * @return iterable
     */
    public function fields(): iterable
    {
        return [
            Input::make('creator_claims')
                ->type('number')
                ->min(0)
                ->title(__('Creator Claims'))
                ->placeholder(__('Creator Claims')),
        ];
    }
}