<?php

namespace App\Orchid\Layouts\Settings;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class SettingFreeDownloadsLayout extends Rows
{
    /**
     * @return iterable
     */
    public function fields(): iterable
    {
        return [
            Input::make('free_downloads')
                ->type('number')
                ->min(0)
                ->title(__('Free Downloads'))
                ->placeholder(__('Free Downloads')),
        ];
    }
}