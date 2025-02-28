<?php

namespace App\Orchid\Layouts\FAQ\FAQSection\FAQ;

use App\Models\Structure\FAQ;
use App\Orchid\Fields\TinyMCE;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;

class FAQAnswerLayout extends Rows
{
    public function __construct(
        public ?string $number,
        public ?string $answer = null,
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
            TinyMCE::make('faqs.'.$this->number.'.answer')
                ->title(__('Answer'))
                ->required()
                ->value($this->answer),
        ];
    }
}