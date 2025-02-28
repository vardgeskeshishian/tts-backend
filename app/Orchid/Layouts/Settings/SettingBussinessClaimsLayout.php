<?php

namespace App\Orchid\Layouts\Settings;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class SettingBussinessClaimsLayout extends Rows
{
    /**
     * @return iterable
     */
    public function fields(): iterable
    {
        return [
            Input::make('bussiness_claims')
                ->type('number')
                ->min(0)
                ->title(__('Bussiness Claims'))
                ->placeholder(__('Bussiness Claims')),
        ];
    }
}