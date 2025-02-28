<?php

namespace App\Orchid\Layouts\Settings;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class SettingCreatorWhitelistsLayout extends Rows
{
    /**
     * @return iterable
     */
    public function fields(): iterable
    {
        return [
            Input::make('creator_whitelists')
                ->type('number')
                ->min(0)
                ->title(__('Creator Whitelists'))
                ->placeholder(__('Creator Whitelists')),
        ];
    }
}