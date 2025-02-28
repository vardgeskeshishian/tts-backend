<?php

namespace App\Orchid\Layouts\Pages\PageSections;

use Orchid\Screen\Fields\Cropper;
use Orchid\Screen\Layouts\Rows;

class PageSectionImageTextLayout extends Rows
{
    public function __construct(
        public string $number,
        public ?string $text = null
    )
    {}

    public function fields(): iterable
    {
        return [
            Cropper::make('sections.'.$this->number.'.text')
                ->title(__('Section Image'))
                ->required()
                ->value($this->text)
                ->targetUrl(),
        ];
    }
}
