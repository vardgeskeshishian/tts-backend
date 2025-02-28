<?php

namespace App\Orchid\Layouts\Tracks;

use App\Models\Track;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class TrackListLayout extends Table
{
    /**
     * @var string
     */
    public $target = 'tracks';

    public function columns(): array
    {
        return [
            TD::make('id', __('ID'))
                ->sort()
                ->cantHide(),

            TD::make('name', __('Name'))
                ->filter(Input::make())
                ->sort()
                ->cantHide(),

            TD::make('description', __('Description'))
                ->cantHide()->width('500px'),

            TD::make('author.name', __('Author'))
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
                ->render(fn (Track $track) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        Link::make(__('Edit'))
                            ->route('platform.systems.tracks.edit', $track->id)
                            ->icon('bs.pencil'),

                        Button::make(__('Delete'))
                            ->icon('bs.trash3')
                            ->confirm(__('Once the track is deleted, all of its data will be permanently deleted. Before deleting your track, please download any data or information that you wish to retain.'))
                            ->method('remove', [
                                'id' => $track->id,
                            ]),
                    ])),
        ];
    }
}