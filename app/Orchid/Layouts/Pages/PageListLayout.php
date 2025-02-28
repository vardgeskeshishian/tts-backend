<?php

namespace App\Orchid\Layouts\Pages;

use App\Models\Structure\Page;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class PageListLayout extends Table
{
    public $target = 'pages';

    public function columns(): iterable
    {
        return [
            TD::make('title', __('Title'))
                ->sort()
                ->cantHide(),

            TD::make('metaTitle', __('Meta Title'))
                ->sort()
                ->cantHide(),

            TD::make('metaDescription', __('Meta Description'))
                ->sort()
                ->cantHide(),

            TD::make('count_view', __('Count View'))
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
                ->render(fn (Page $page) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([

                        Link::make(__('Edit'))
                            ->route('platform.systems.pages.edit', $page->id)
                            ->icon('bs.pencil'),

                        Button::make(__('Delete'))
                            ->icon('bs.trash3')
                            ->confirm(__('Once a page is deleted, all of its resources and data will be permanently deleted. Before deleting your page, download any data or information you want to keep.'))
                            ->method('remove', [
                                'id' => $page->id,
                            ]),
                    ])),
        ];
    }
}