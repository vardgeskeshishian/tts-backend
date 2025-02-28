<?php

namespace App\Orchid\Layouts\User;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Cropper;
use Orchid\Screen\Layouts\Rows;

class UserAvatarLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Cropper::make('user.avatar')
                ->width(200)
                ->height(200)
                ->title(__('Avatar'))
                ->targetUrl(),
        ];
    }
}