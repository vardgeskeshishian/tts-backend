<?php

namespace App\Orchid\Layouts\Settings;

use App\Orchid\Fields\TinyMCE;
use Orchid\Screen\Layouts\Rows;

class FreeDownloadTextLayout extends Rows
{
    public function fields(): iterable
    {
        return [
            TinyMCE::make('free_download_text')
                ->title(__('Free download text'))
                ->placeholder(__('Free download text')),
        ];
    }
}