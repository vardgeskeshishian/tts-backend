<?php

namespace App\Orchid\Layouts\TemplateMeta;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class TemplateMetaMetaTitleLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('templateMeta.metaTitle')
                ->type('text')
                ->max(255)
                ->required()
                ->title(__('Meta-title'))
                ->placeholder(__('Meta-title')),
        ];
    }
}