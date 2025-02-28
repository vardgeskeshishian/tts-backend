<?php

namespace App\Orchid\Layouts\License;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class LicenseDownloadCountLayout extends Rows
{
    public function fields(): iterable
    {
        return [
            Input::make('license.download_count')
                ->type('number')
                ->required()
                ->title(__('Download Count'))
                ->placeholder(__('Download Count')),
        ];
    }
}