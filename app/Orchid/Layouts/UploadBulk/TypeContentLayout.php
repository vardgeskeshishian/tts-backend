<?php

namespace App\Orchid\Layouts\UploadBulk;

use Orchid\Screen\Fields\RadioButtons;
use Orchid\Screen\Layouts\Rows;

class TypeContentLayout extends Rows
{
    /**
     * @return iterable
     */
    public function fields(): iterable
    {
        return [
            RadioButtons::make('typeContent')
                ->options([
                    'track' => 'Track',
                    'template' => 'Template',
                    'sfx' => 'SFX',
                ])
        ];
    }
}