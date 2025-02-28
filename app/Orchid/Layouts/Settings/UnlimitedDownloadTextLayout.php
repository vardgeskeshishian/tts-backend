<?php

namespace App\Orchid\Layouts\Settings;

use App\Orchid\Fields\TinyMCE;
use Orchid\Screen\Layouts\Rows;

class UnlimitedDownloadTextLayout extends Rows
{
    public function fields(): iterable
    {
        return [
            TinyMCE::make('unlimited_download_text')
                ->title(__('Unlimited download text'))
                ->placeholder(__('Unlimited download text')),
        ];
    }
}