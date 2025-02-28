<?php

namespace App\Orchid\Layouts\FAQ\FAQSection;

use App\Models\Structure\FAQSection;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class FAQSectionListLayout extends Table
{
    /**
     * @var string
     */
    public $target = 'sections';

    public function columns(): array
    {
        return [
            TD::make('title', __('Title'))
                ->sort()
                ->cantHide(),

            TD::make('category.name', __('Category Name'))
                ->sort()
                ->cantHide(),

            TD::make('created_at', __('Created'))
                ->usingComponent(DateTimeSplit::class)
                ->align(TD::ALIGN_RIGHT)
                ->defaultHidden()
                ->sort(),

            TD::make('updated_at', __('Last edit'))
                ->usingComponent(DateTimeSplit::class)
                ->align(TD::ALIGN_RIGHT)
                ->sort(),

            TD::make(__('Actions'))
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn (FAQSection $faq_section) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        Link::make(__('Edit'))
                            ->route('platform.systems.faqs.sections.edit', $faq_section->id)
                            ->icon('bs.pencil'),

                        Button::make(__('Delete'))
                            ->icon('bs.trash3')
                            ->confirm(__('After deleting a faq, all its data will be deleted without the possibility of recovery. Before deleting your faq, download any data or information you want to keep.'))
                            ->method('remove', [
                                'id' => $faq_section->id,
                            ]),
                    ])),
        ];
    }
}