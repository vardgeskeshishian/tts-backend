<?php

namespace App\Orchid\Layouts\Settings;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class SettingFreeWhitelistsLayout extends Rows
{
    /**
     * @return iterable
     */
    public function fields(): iterable
    {
        return [
            Input::make('free_whitelists')
                ->type('number')
                ->min(0)
                ->title(__('Free Whitelists'))
                ->placeholder(__('Free Whitelists')),
        ];
    }
}