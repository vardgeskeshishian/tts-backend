<?php

namespace App\Orchid\Layouts\VideoEffect;

use App\Models\VideoEffects\VideoEffectCategory;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class VideoEffectCategoriesLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Select::make('video.categories.')
                ->fromQuery(VideoEffectCategory::query(), 'name', 'id')
                ->title(__('Categories')),
        ];
    }
}