<?php

namespace App\Orchid\Layouts\TemplateMeta;

use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;

class TemplateMetaMetaDescriptionLayout extends Rows
{
    public function fields(): array
    {
        return [
            TextArea::make('templateMeta.metaDescription')
                ->rows(5)
                ->required()
                ->title(__('Meta-description'))
                ->placeholder(__('Meta-description')),
        ];
    }
}