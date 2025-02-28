<?php

namespace App\Orchid\Layouts\Webhooks;

use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use ReflectionException;

class WebhookListLayout extends Table
{
    /**
     * @var string
     */
    public $target = 'webhooks';

    /**
     * @return iterable
     * @throws ReflectionException
     */
    public function columns(): iterable
    {
        return [
            TD::make('id', __('ID'))
                ->sort()
                ->cantHide(),

            TD::make('type', __('Type'))
                ->sort()
                ->cantHide(),

            TD::make('data', __('Data'))
                ->sort()
                ->width('700px')
                ->cantHide(),

            TD::make('created_at', __('Created'))
                ->usingComponent(DateTimeSplit::class)
                ->align(TD::ALIGN_RIGHT)
                ->sort(),
        ];
    }
}
