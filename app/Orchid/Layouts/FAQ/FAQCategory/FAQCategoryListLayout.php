<?php

namespace App\Orchid\Layouts\FAQ\FAQCategory;

use App\Models\Structure\FAQCategory;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class FAQCategoryListLayout extends Table
{
    /**
     * @var string
     */
    public $target = 'faqsCategories';

    public function columns(): array
    {
        return [
            TD::make('name', __('Name'))
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
                ->render(fn (FAQCategory $FAQCategory) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        Link::make(__('Edit'))
                            ->route('platform.systems.faqs.categories.edit', $FAQCategory->id)
                            ->icon('bs.pencil'),

                        Button::make(__('Delete'))
                            ->icon('bs.trash3')
                            ->confirm(__('After deleting a faq, all its data will be deleted without the possibility of recovery. Before deleting your faq, download any data or information you want to keep.'))
                            ->method('remove', [
                                'id' => $FAQCategory->id,
                            ]),
                    ])),
        ];
    }
}