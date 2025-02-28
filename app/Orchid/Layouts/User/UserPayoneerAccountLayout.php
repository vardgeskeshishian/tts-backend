<?php

namespace App\Orchid\Layouts\User;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class UserPayoneerAccountLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Input::make('user.payout_email')
                ->type('email')
                ->title(__('Payoneer Account'))
                ->placeholder(__('Payoneer Account')),
        ];
    }
}