<?php

namespace App\Orchid\Layouts\TemplateMeta;

use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;

class TemplateMetaDescriptionLayout extends Rows
{
    public function fields(): array
    {
        return [
            TextArea::make('templateMeta.description')
                ->rows(5)
                ->required()
                ->title(__('Description'))
                ->placeholder(__('Description')),
        ];
    }
}