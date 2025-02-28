<?php

namespace App\Orchid\Layouts\TemplateMeta;

use Orchid\Screen\Fields\Cropper;
use Orchid\Screen\Layouts\Rows;

class TemplateMetaImageLayout extends Rows
{
    public function fields(): array
    {
        return [
            Cropper::make('templateMeta.image')
                ->title(__('Default Picture'))
                ->targetUrl(),
        ];
    }
}