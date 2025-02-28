<?php

namespace App\Orchid\Layouts\Category\SortCategory;

use App\Models\Tags\SortCategory;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class VideoListLayout extends Table
{
    public $target = 'video_categories';

    /**
     * @return iterable
     */
    public function columns(): iterable
    {
        return [
            TD::make('name', __('Name'))
                ->sort()
                ->cantHide(),

            TD::make('order', __('Order'))
                ->sort()
                ->cantHide(),

            TD::make('is_hidden', __('Hidden'))
                ->sort()
                ->cantHide(),

            TD::make(__('Actions'))
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn (SortCategory $sortCategory) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        Link::make(__('Edit'))
                            ->route('platform.systems.faqs.sort-categories.edit', $sortCategory->id)
                            ->icon('bs.pencil'),
                    ])),
        ];
    }
}