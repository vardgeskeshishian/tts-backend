<?php

namespace App\Orchid\Layouts\Paddle;

use App\Models\PaddleApiKey;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class PaddleApiKeyListLayout extends Table
{
    /**
     * @var string
     */
    public $target = 'keys';

    public function columns(): array
    {
        return [
            TD::make('type_key', __('Type Key'))
                ->sort()
                ->cantHide(),

            TD::make(__('Actions'))
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn (PaddleApiKey $key) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([

                        Link::make(__('Edit'))
                            ->route('platform.systems.paddle.keys.edit', $key->type_key)
                            ->icon('bs.pencil'),
                    ])),
        ];
    }
}