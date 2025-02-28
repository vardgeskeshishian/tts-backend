<?php

namespace App\Orchid\Layouts\UploadBulk;

use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class UploadBulkListLayout extends Table
{
    /**
     * @var string
     */
    public $target = 'uploads';

    public function columns(): array
    {
        return [
            TD::make('id', __('ID'))
                ->cantHide(),

            TD::make('file_name', __('File Name'))
				->sort()
				->filter(Input::make())
                ->cantHide(),

            TD::make('name_content', __('Name Content'))
                ->cantHide(),

            TD::make('status', __('Status'))
				->sort()
				->filter(Input::make())
                ->cantHide(),

            TD::make('typeContent', __('Type Content'))
                ->cantHide(),

            TD::make('error_message', __('Message'))
                ->cantHide()->width('250px'),

            TD::make('created_at', __('Created'))
                ->usingComponent(DateTimeSplit::class)
                ->align(TD::ALIGN_RIGHT)
                ->sort(),

            TD::make('updated_at', __('Last Edit'))
                ->usingComponent(DateTimeSplit::class)
                ->align(TD::ALIGN_RIGHT)
                ->sort(),
        ];
    }
}
