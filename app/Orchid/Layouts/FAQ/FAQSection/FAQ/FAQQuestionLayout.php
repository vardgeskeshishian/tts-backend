<?php

namespace App\Orchid\Layouts\FAQ\FAQSection\FAQ;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class FAQQuestionLayout extends Rows
{
    public function __construct(
        public ?string $number,
        public ?string $question = null,
    )
    {}

    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Input::make('faqs.'.$this->number.'.question')
                ->title(__('Question'))
                ->placeholder(__('Question'))
                ->required()
                ->value($this->question),
        ];
    }
}