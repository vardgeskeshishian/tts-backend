<?php

namespace App\Orchid\Layouts\Settings;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class SettingBussinessWhitelistsLayout extends Rows
{
    /**
     * @return iterable
     */
    public function fields(): iterable
    {
        return [
            Input::make('bussiness_whitelists')
                ->type('number')
                ->min(0)
                ->title(__('Bussiness Whitelists'))
                ->placeholder(__('Bussiness Whitelists')),
        ];
    }
}