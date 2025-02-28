<?php

namespace App\Orchid\Layouts\Category;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class CategoryGoogleUrlLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('tag.google_url')
                ->type('text')
                ->max(255)
                ->title(__('Google Bot Redirect URL'))
                ->placeholder(__('Google Bot Redirect URL')),
        ];
    }
}