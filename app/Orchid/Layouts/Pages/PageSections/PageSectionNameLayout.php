<?php

namespace App\Orchid\Layouts\Pages\PageSections;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class PageSectionNameLayout extends Rows
{
    public function __construct(
        public string $number,
        public ?string $name = null
    )
    {}

    /**
     * @return array|Field[]
     */
    public function fields(): array
    {
        return [
            Input::make('sections.'.$this->number.'.name')
                ->title('Section Name')
                ->required()
                ->value($this->name),
        ];
    }
}