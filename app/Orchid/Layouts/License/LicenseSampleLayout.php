<?php

namespace App\Orchid\Layouts\License;

use App\Orchid\Fields\TinyMCE;
use Orchid\Screen\Layouts\Rows;

class LicenseSampleLayout extends Rows
{
    public function fields(): iterable
    {
        return [
            TinyMCE::make('license.sample')
                ->title(__('Template'))
                ->placeholder(__('Template')),
        ];
    }
}