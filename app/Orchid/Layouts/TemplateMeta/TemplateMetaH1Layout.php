<?php

namespace App\Orchid\Layouts\TemplateMeta;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class TemplateMetaH1Layout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('templateMeta.h1')
                ->type('text')
                ->max(255)
                ->title(__('H1'))
                ->placeholder(__('H1')),
        ];
    }
}