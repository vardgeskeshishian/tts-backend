<?php

namespace App\Orchid\Layouts\Pages\PageSections;

use App\Orchid\Fields\TinyMCE;
use Orchid\Screen\Layouts\Rows;

class PageSectionHtmlTextLayout extends Rows
{
    public function __construct(
        public string $number,
        public ?string $text = null
    )
    {}

    public function fields(): iterable
    {
        return [
            TinyMCE::make('sections.'.$this->number.'.text')
                ->required()
                ->title('Section Text')
                ->value($this->text),
        ];
    }
}
