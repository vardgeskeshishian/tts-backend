<?php

namespace App\Orchid\Layouts\User;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class UserPaypalAccountLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Input::make('user.paypal_account')
                ->type('email')
                ->title(__('PaypalAccount'))
                ->placeholder(__('PaypalAccount')),
        ];
    }
}