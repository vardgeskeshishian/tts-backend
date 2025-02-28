<?php

namespace App\Orchid\Layouts\Pages\PageSections;

use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;

class PageSectionTextTextLayout extends Rows
{
    public function __construct(
        public string $number,
        public ?string $text = null
    )
    {}

    public function fields(): iterable
    {
        return [
            TextArea::make('sections.'.$this->number.'.text')
                ->rows(5)
                ->title('Section Text')
                ->required()
                ->value($this->text),
        ];
    }
}
