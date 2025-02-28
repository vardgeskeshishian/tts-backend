<?php

namespace App\Orchid\Layouts\SFX;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Layouts\Rows;

class SFXCheckboxLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            CheckBox::make('sfx.premium')
                ->title('Premium')
                ->sendTrueOrFalse(),

            CheckBox::make('sfx.is_new')
                ->title('New')
                ->sendTrueOrFalse(),
        ];
    }
}