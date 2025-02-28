<?php

namespace App\Orchid\Layouts\Paddle;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class PaddleApiKeyVendorIdLayout extends Rows
{
    public function fields(): iterable
    {
        return [
            Input::make('key.vendor_id')
                ->type('integer')
                ->title(__('Vendor ID'))
                ->placeholder(__('Vendor ID')),
        ];
    }
}