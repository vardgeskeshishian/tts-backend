<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Authors;

use App\Models\Authors\AuthorProfile;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class AuthorListLayout extends Table
{
    /**
     * @var string
     */
    public $target = 'authors';

    /**
     * @return TD[]
     */
    public function columns(): array
    {
        return [

            TD::make('id', __('ID'))
                ->sort()
                ->cantHide(),

            TD::make('name', __('Name'))
                ->sort()
                ->cantHide(),

            TD::make('description', __('Description'))
                ->sort()
                ->cantHide()->width('500px'),

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
                ->render(fn (AuthorProfile $author) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([

                        Link::make(__('Edit'))
                            ->route('platform.systems.authors.edit', $author->id)
                            ->icon('bs.pencil'),

                        Button::make(__('Delete'))
                            ->icon('bs.trash3')
                            ->confirm(__('Once the account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.'))
                            ->method('remove', [
                                'id' => $author->id,
                            ]),
                    ])),
        ];
    }
}
